document.addEventListener("DOMContentLoaded", carregarCarrinho);

// Função robusta para formatar moeda
function formatarMoeda(valor) {
    // Converte strings com vírgula para número
    if (typeof valor === 'string') {
        valor = valor.replace(/\./g, '').replace(',', '.');
    }
    
    const numero = Number(valor);
    if (isNaN(numero)) return "R$ 0,00";
    
    return numero.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function carregarCarrinho() {
    const carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
    const lista = document.getElementById("lista-carrinho");
    const totalElement = document.getElementById("total");

    lista.innerHTML = "";
    let total = 0;

    carrinho.forEach((item, index) => {
        // Garante que preço e quantidade são números
        const preco = Number(item.preco);
        const qtd = Number(item.qtd) || 1;
        const subtotal = preco * qtd;
        total += subtotal;

        const itemHTML = `
            <div class="row align-items-center mb-3 border-bottom pb-3">
                <div class="col-md-4">
                    <strong>${item.nome}</strong><br>
                    <small>Preço unitário: ${formatarMoeda(preco)}</small>
                </div>
                <div class="col-md-4 d-flex align-items-center">
                    <button class="btn btn-outline-secondary btn-sm me-2" onclick="alterarQuantidade(${index}, -1)">-</button>
                    <span>${qtd}</span>
                    <button class="btn btn-outline-secondary btn-sm ms-2" onclick="alterarQuantidade(${index}, 1)">+</button>
                </div>
                <div class="col-md-2">${formatarMoeda(subtotal)}</div>
                <div class="col-md-2 text-end">
                    <button class="btn btn-remover-custom btn-sm" onclick="removerDoCarrinho(${index})">Remover</button>
                </div>
            </div>
        `;
        lista.innerHTML += itemHTML;
    });

    totalElement.textContent = formatarMoeda(total);
}

function alterarQuantidade(index, delta) {
    const carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
    carrinho[index].qtd = Math.max(1, (carrinho[index].qtd || 1) + delta);
    localStorage.setItem('carrinho', JSON.stringify(carrinho));
    carregarCarrinho();
}

function removerDoCarrinho(index) {
    const carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
    carrinho.splice(index, 1);
    localStorage.setItem('carrinho', JSON.stringify(carrinho));
    carregarCarrinho();
}

async function calcularFrete() {
    const cep = document.getElementById("cep").value.replace(/\D/g, '');
    const carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
    
    if (cep.length !== 8) {
        alert("Por favor, digite um CEP válido com 8 dígitos.");
        return;
    }

    if (carrinho.length === 0) {
        alert("Seu carrinho está vazio!");
        return;
    }

    try {
        // Simula os dados do pacote (adaptar conforme seus produtos)
        const pacote = {
            peso: 1, // kg (somar peso dos itens se tiver essa informação)
            comprimento: 30, // cm
            altura: 20, // cm
            largura: 20, // cm
            valorDeclarado: carrinho.reduce((total, item) => total + (item.preco * item.qtd), 0),
            cepDestino: cep
        };

        // Calcula frete para todos os serviços disponíveis
        const fretes = await calcularTodosFretes(pacote);
        
        // Exibe os resultados em um modal
        exibirModalFrete(fretes);
        
    } catch (error) {
        console.error("Erro ao calcular frete:", error);
        alert("Não foi possível calcular o frete. Por favor, tente novamente mais tarde.");
    }
}

async function calcularTodosFretes(pacote) {
    // Serviços dos Correios (códigos)
    const servicos = [
        { codigo: '04014', nome: 'SEDEX' },
        { codigo: '04510', nome: 'PAC' }
    ];

    const resultados = [];
    
    for (const servico of servicos) {
        try {
            const frete = await consultarFreteCorreios({
                ...pacote,
                servico: servico.codigo
            });
            
            resultados.push({
                servico: servico.nome,
                valor: frete.valor,
                prazo: frete.prazo,
                erro: frete.erro
            });
        } catch (error) {
            resultados.push({
                servico: servico.nome,
                erro: "Falha no cálculo"
            });
        }
    }

    return resultados;
}

async function consultarFreteCorreios(dados) {
    // IMPORTANTE: Na prática, você precisaria de um backend para essa consulta
    // pois os Correios não permitem chamadas diretamente do frontend
    
    // Esta é uma implementação simulada para demonstração
    // Na prática, você faria uma chamada para SEU backend que consultaria os Correios
    
    // Simulação de resposta - valores aleatórios baseados no valor declarado
    const valorBase = Math.min(50, dados.valorDeclarado * 0.01);
    const valorFrete = (valorBase * (1 + Math.random() * 0.3)).toFixed(2);
    const prazo = Math.floor(3 + Math.random() * 7);
    
    return {
        valor: parseFloat(valorFrete),
        prazo: prazo,
        erro: null
    };
}

function exibirModalFrete(fretes) {
    // Cria o modal dinamicamente
    const modalHTML = `
    <div class="modal fade" id="modalFrete" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Opções de Frete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Serviço</th>
                                <th>Valor</th>
                                <th>Prazo (dias)</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${fretes.map(frete => `
                                <tr>
                                    <td>${frete.servico}</td>
                                    <td>${frete.erro ? '--' : formatarMoeda(frete.valor)}</td>
                                    <td>${frete.erro ? '--' : frete.prazo}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                    ${fretes.some(f => f.erro) ? 
                        '<div class="alert alert-warning">Alguns serviços podem não estar disponíveis para esta região</div>' : ''}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
    `;
    
    // Adiciona o modal ao body
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Mostra o modal
    const modal = new bootstrap.Modal(document.getElementById('modalFrete'));
    modal.show();
    
    // Remove o modal quando fechado
    document.getElementById('modalFrete').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Função para adicionar itens ao carrinho (deve ser chamada pelas páginas de produtos)
function adicionarAoCarrinho(nome, preco) {
    const carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
    
    // Normaliza o preço para número
    const precoNumerico = typeof preco === 'string' 
        ? parseFloat(preco.replace(/\./g, '').replace(',', '.')) 
        : Number(preco);

    const itemExistente = carrinho.find(item => item.nome === nome);
    
    if (itemExistente) {
        itemExistente.qtd = (itemExistente.qtd || 1) + 1;
    } else {
        carrinho.push({ 
            nome, 
            preco: precoNumerico, 
            qtd: 1 
        });
    }

    localStorage.setItem('carrinho', JSON.stringify(carrinho));
    alert(`${nome} adicionado ao carrinho!`);
}

//script de frete

// Configuração da loja 
const CONFIG_LOJA = {
    cepOrigem: "80000000", // Seu CEP de origem
    enderecoLoja: "Av. dos Carros, 1000 - Centro, Curitiba/PR",
    dimensoesPadrao: {
      peso: 1500, // Peso médio de um carro em kg (para transporte)
      comprimento: 450, // cm
      altura: 150, // cm
      largura: 180 // cm
    }
  };
  
  // Busca endereço completo pelo CEP
  document.getElementById("btn-buscar-cep").addEventListener("click", buscarEnderecoPorCEP);
  
  async function buscarEnderecoPorCEP() {
    const cep = document.getElementById("cep").value.replace(/\D/g, '');
    
    if (cep.length !== 8) {
      alert("Por favor, digite um CEP válido com 8 dígitos.");
      return;
    }
  
    try {
      const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
      const endereco = await response.json();
      
      if (endereco.erro) {
        throw new Error("CEP não encontrado");
      }
  
      document.getElementById("endereco-completo").textContent = 
        `${endereco.logradouro || 'Endereço não especificado'}, ${endereco.bairro || ''}`;
      document.getElementById("cidade-estado").textContent = 
        `${endereco.localidade}/${endereco.uf}`;
      
      document.getElementById("detalhes-endereco").classList.remove("d-none");
      
      // Armazena os dados para cálculo do frete
      sessionStorage.setItem('enderecoEntrega', JSON.stringify(endereco));
      
    } catch (error) {
      console.error("Erro ao buscar CEP:", error);
      alert("Não foi possível encontrar o endereço. Verifique o CEP e tente novamente.");
    }
  }
  
  async function calcularFrete() {
    const cep = document.getElementById("cep").value.replace(/\D/g, '');
    const endereco = JSON.parse(sessionStorage.getItem('enderecoEntrega')) || {};
    const carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
  
    if (!cep || !endereco.uf) {
      alert("Por favor, busque um CEP válido primeiro.");
      return;
    }
  
    if (carrinho.length === 0) {
      alert("Seu carrinho está vazio!");
      return;
    }
  
    try {
      // Verifica se é entrega local (mesmo estado)
      const isLocal = endereco.uf === CONFIG_LOJA.cepOrigem.substring(0, 2);
  
      // Calcula valores de frete
      const fretes = await calcularFretesDisponiveis({
        cepDestino: cep,
        isLocal,
        valorTotal: carrinho.reduce((total, item) => total + (item.preco * item.qtd), 0)
      });
  
      // Exibe modal com opções
      exibirOpcoesFrete(fretes, isLocal);
      
    } catch (error) {
      console.error("Erro ao calcular frete:", error);
      alert("Não foi possível calcular o frete. Entre em contato conosco.");
    }
  }
  
  async function calcularFretesDisponiveis({ cepDestino, isLocal, valorTotal }) {
    // Em uma implementação real, isso viria de uma API de transportadoras
    // Esta é uma simulação baseada em regras de negócio para loja de carros
    
    const transportadoras = [
      {
        nome: "Transporte Especializado",
        descricao: "Carreta com equipamento especial",
        prazo: isLocal ? 3 : 7,
        valorBase: 2500 // R$ 2.500,00 base
      },
      {
        nome: "Guincho Plataforma",
        descricao: "Para veículos não funcionando",
        prazo: isLocal ? 2 : 5,
        valorBase: 1800
      }
    ];
  
    // Cálculos simulados (adaptar para sua regra de negócio)
    return transportadoras.map(transp => {
      const valorCalculado = isLocal 
        ? transp.valorBase * 0.7
        : transp.valorBase * (1 + (Math.random() * 0.5)); // Variação por distância
      
      return {
        nome: transp.nome,
        descricao: transp.descricao,
        valor: valorCalculado,
        prazo: transp.prazo,
        valorFormatado: formatarMoeda(valorCalculado),
        isLocal
      };
    });
  }
  
  function exibirOpcoesFrete(fretes, isLocal) {
    const modalBody = fretes.map(frete => `
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title">${frete.nome}</h5>
          <p class="card-text">${frete.descricao}</p>
          <div class="d-flex justify-content-between">
            <span class="fw-bold">${frete.valorFormatado}</span>
            <span>Prazo: ${frete.prazo} dias úteis</span>
          </div>
          ${isLocal ? '<div class="mt-2 text-success">Entrega local</div>' : ''}
        </div>
      </div>
    `).join('');
  
    const modalHTML = `
      <div class="modal fade" id="modalFrete" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Opções de Transporte</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <p class="mb-3">Selecione o método de transporte para seu veículo:</p>
              ${modalBody}
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
              <button type="button" class="btn btn-primary">Selecionar Transporte</button>
            </div>
          </div>
        </div>
      </div>
    `;
  
    // Adiciona e exibe o modal
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    const modal = new bootstrap.Modal(document.getElementById('modalFrete'));
    modal.show();
  }


  // format cep
  function formatarCEP(input) {
    input.value = input.value.replace(/\D/g, '')
                            .replace(/(\d{5})(\d)/, '$1-$2')
                            .substring(0, 9);
  }
  function finalizarCompra() {
    const carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
    
    if (carrinho.length === 0) {
      // Modal de erro se o carrinho estiver vazio
      const modalErro = new bootstrap.Modal(document.getElementById('modalCarrinhoVazio'));
      modalErro.show();
      return;
    }
  
    // Configura o modal de sucesso
    document.getElementById('total-compra').textContent = document.getElementById('total').textContent;
    
    // Mostra o modal
    const modalConfirmacao = new bootstrap.Modal(document.getElementById('modalConfirmacao'));
    modalConfirmacao.show();
    
    // Limpa o carrinho quando o modal for fechado
    document.getElementById('modalConfirmacao').addEventListener('hidden.bs.modal', function() {
      localStorage.removeItem('carrinho');
      window.location.href = 'index.html';
    });
  }