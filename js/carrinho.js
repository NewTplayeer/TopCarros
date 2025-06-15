document.addEventListener("DOMContentLoaded", function() {
    const filtroMarca = document.getElementById("filtroMarca");
    const mensagem = document.getElementById("nenhum-veiculo-mensagem");
    

    mensagem.style.display = "none";
    
    if (filtroMarca) {
        filtroMarca.addEventListener("change", function() {
            const marcaId = this.value;
            filtrarVeiculosPorMarca(marcaId);
        });
        
        if (filtroMarca.value) {
            filtrarVeiculosPorMarca(filtroMarca.value);
        }
    }
});

function filtrarVeiculosPorMarca(marcaId) {
    const cards = document.querySelectorAll(".car-card");
    const mensagem = document.getElementById("nenhum-veiculo-mensagem");
    let algumVeiculoVisivel = false;
    
    cards.forEach(card => {
        if (marcaId === "" || card.getAttribute("data-marca") === marcaId) {
            card.style.display = "block";
            algumVeiculoVisivel = true;
        } else {
            card.style.display = "none";
        }
    });
    

    if (marcaId !== "" && !algumVeiculoVisivel && cards.length > 0) {
        mensagem.style.display = "block";
    } else {
        mensagem.style.display = "none";
    }
}
// logo acima é o js de filtro de marca pelo Id

document.addEventListener("DOMContentLoaded", function() {
    carregarCarrinho();

    // Se o frete não estiver definido na sessionStorage, inicializa como "A calcular"
    // Isso garante que sempre haja um estado inicial para o frete.
    if (!sessionStorage.getItem('freteSelecionado')) {
        sessionStorage.setItem('freteSelecionado', JSON.stringify({
            tipo: "A calcular",
            valor: 0,
            prazo: "-"
        }));
    }

    // Adiciona o listener para formatar o CEP em tempo real
    const cepInput = document.getElementById("cep");
    if (cepInput) {
        cepInput.addEventListener('input', function() {
            formatarCEP(this);
            // Ao digitar um novo CEP, resetamos o frete para "A calcular"
            // pois o frete anterior pode não ser mais válido
            sessionStorage.setItem('freteSelecionado', JSON.stringify({
                tipo: "A calcular",
                valor: 0,
                prazo: "-"
            }));
            carregarCarrinho(); // Recarrega o carrinho para refletir o frete "A calcular"
        });
    }
});

// Configuração da loja
const CONFIG_LOJA = {
    cepOrigem: "72140-340", // CEP completo para Taguatinga/DF
    ufOrigem: "DF", // UF para comparação mais robusta
    enderecoLoja: "QNJ 34 LOJA 21, Taguatinga, DF - CEP: 72140-340", // Endereço CORRIGIDO DA LOJA
    dimensoesPadrao: {
        peso: 1500, // em gramas
        comprimento: 450, // em milímetros
        altura: 150, // em milímetros
        largura: 180 // em milímetros
    }
};

// Formatação de moeda
function formatarMoeda(valor) {
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

// Formatação de CEP (em tempo real)
function formatarCEP(input) {
    let cep = input.value.replace(/\D/g, ''); // Remove tudo que não é dígito
    cep = cep.substring(0, 8); // Limita a 8 dígitos
    if (cep.length > 5) {
        cep = cep.substring(0, 5) + '-' + cep.substring(5);
    }
    input.value = cep;
}

// Validação de CEP
function validarCEP(cep) {
    cep = cep.replace(/\D/g, ''); // Remove caracteres não numéricos
    return cep.length === 8;
}

// Carregar e renderizar carrinho
function carregarCarrinho() {
    const carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
    const lista = document.getElementById("lista-carrinho");
    if (!lista) return; // Sai da função se o elemento não existir
    const totalElement = document.getElementById("total");
    
    // Limpa o conteúdo atual para evitar duplicações
    lista.innerHTML = ""; 
    let subtotal = 0;

    if (carrinho.length === 0) {
        lista.innerHTML = '<p class="text-center text-muted">Seu carrinho está vazio.</p>';
    } else {
        carrinho.forEach((item, index) => {
            const preco = Number(item.preco);
            const qtd = Number(item.qtd) || 1;
            const subtotalItem = preco * qtd;
            subtotal += subtotalItem;

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
                    <div class="col-md-2">${formatarMoeda(subtotalItem)}</div>
                    <div class="col-md-2 text-end">
                        <button class="btn btn-remover-custom btn-sm" onclick="removerDoCarrinho(${index})">Remover</button>
                    </div>
                </div>
            `;
            lista.innerHTML += itemHTML;
        });
    }

    // Resumo do pedido
    const frete = JSON.parse(sessionStorage.getItem('freteSelecionado')) || {
        tipo: "A calcular",
        valor: 0,
        prazo: "-"
    };

    const total = subtotal + frete.valor;

    lista.insertAdjacentHTML('beforeend', `
        <div class="row mt-4">
            <div class="col-md-6 offset-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Resumo do Pedido</h5>
                        <div class="d-flex justify-content-between">
                            <span>Subtotal:</span>
                            <span>${formatarMoeda(subtotal)}</span>
                        </div>
                        <div id="resumo-frete">
                            <div class="d-flex justify-content-between">
                                <span>Frete (${frete.tipo}):</span>
                                <span>${frete.valor > 0 ? formatarMoeda(frete.valor) : frete.tipo === "Retirada na Loja" ? 'Grátis' : 'A calcular'}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Prazo estimado:</span>
                                <span>${frete.prazo}</span>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total:</span>
                            <span>${formatarMoeda(total)}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `);

    totalElement.textContent = formatarMoeda(total);
}

// Funções de alteração e remoção do carrinho
function alterarQuantidade(index, delta) {
    const carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
    if (carrinho[index]) { // Garante que o item existe
        carrinho[index].qtd = Math.max(1, (carrinho[index].qtd || 1) + delta);
        localStorage.setItem('carrinho', JSON.stringify(carrinho));
        carregarCarrinho();
    }
}

function removerDoCarrinho(index) {
    const carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
    if (carrinho[index]) { // Garante que o item existe
        carrinho.splice(index, 1);
        localStorage.setItem('carrinho', JSON.stringify(carrinho));
        carregarCarrinho();
    }
}

// Funções de frete
const buscarCepBtn = document.getElementById("btn-buscar-cep");
if (buscarCepBtn) {
    buscarCepBtn.addEventListener("click", buscarEnderecoPorCEP);
}

async function buscarEnderecoPorCEP() {
    const cep = document.getElementById("cep").value.replace(/\D/g, '');
    
    if (!validarCEP(cep)) {
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
        
        sessionStorage.setItem('enderecoEntrega', JSON.stringify(endereco));
        
    } catch (error) {
        console.error("Erro ao buscar CEP:", error);
        alert("Não foi possível encontrar o endereço. Verifique o CEP e tente novamente.");
        document.getElementById("detalhes-endereco").classList.add("d-none"); // Oculta o card se houver erro
    }
}

async function calcularFrete() {
    const cep = document.getElementById("cep").value.replace(/\D/g, '');
    const endereco = JSON.parse(sessionStorage.getItem('enderecoEntrega')) || {};
    const carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];

    if (!validarCEP(cep)) {
        alert("Por favor, digite um CEP válido com 8 dígitos.");
        return;
    }

    // Agora, verificamos o UF do endereço retornado pelo ViaCEP
    if (!endereco.uf) {
        alert("Por favor, busque um CEP válido primeiro para obter o endereço completo.");
        return;
    }

    if (carrinho.length === 0) {
        alert("Seu carrinho está vazio! Adicione itens antes de calcular o frete.");
        return;
    }

    try {
        // Compara o UF do endereço de destino com o UF de origem da loja
        const isLocal = endereco.uf.toUpperCase() === CONFIG_LOJA.ufOrigem.toUpperCase();
        const fretes = await calcularFretesDisponiveis({
            cepDestino: cep,
            isLocal,
            valorTotal: carrinho.reduce((total, item) => total + (item.preco * item.qtd), 0)
        });

        exibirOpcoesFrete(fretes, isLocal);
        
    } catch (error) {
        console.error("Erro ao calcular frete:", error);
        alert("Não foi possível calcular o frete. Entre em contato conosco.");
    }
}

async function calcularFretesDisponiveis({ cepDestino, isLocal, valorTotal }) {
    const transportadoras = [
        {
            nome: "Transporte Especializado",
            descricao: "Carreta com equipamento especial",
            prazo: isLocal ? 3 : 7, // Exemplo de prazo: 3 dias local, 7 dias para outros estados
            valorBase: 2500 // Valor base hipotético
        },
        {
            nome: "Guincho Plataforma",
            descricao: "Para veículos não funcionando",
            prazo: isLocal ? 2 : 5, // Exemplo de prazo: 2 dias local, 5 dias para outros estados
            valorBase: 1800 // Valor base hipotético
        }
    ];

    return transportadoras.map(transp => {
        // Cálculo de valor: local tem 30% de desconto, outros estados têm variação aleatória
        const valorCalculado = isLocal 
            ? transp.valorBase * 0.7 
            : transp.valorBase * (1 + (Math.random() * 0.5)); // Aumento de 0 a 50%

        return {
            nome: transp.nome,
            descricao: transp.descricao,
            valor: valorCalculado,
            prazo: transp.prazo,
            valorFormatado: formatarMoeda(valorCalculado),
            isLocal // Inclui a informação se é entrega local
        };
    });
}

function exibirOpcoesFrete(fretes, isLocal) {
    // Remove qualquer modal de frete existente para evitar duplicação
    const existingModal = document.getElementById('modalFrete');
    if (existingModal) {
        existingModal.remove();
    }

    const opcoesTransporte = fretes.map(frete => `
        <div class="card mb-3">
            <div class="card-body">
                <div class="form-check">
                    <input class="form-check-input" type="radio" 
                            name="opcaoFrete" id="frete-${frete.nome.toLowerCase().replace(/\s/g, '-')}" 
                            value="${frete.nome}"
                            data-valor="${frete.valor}"
                            data-prazo="${frete.prazo}"
                            ${frete.nome === JSON.parse(sessionStorage.getItem('freteSelecionado'))?.tipo ? 'checked' : ''}
                    >
                    <label class="form-check-label" for="frete-${frete.nome.toLowerCase().replace(/\s/g, '-')}">
                        <h5 class="d-inline">${frete.nome}</h5>
                    </label>
                    <p class="card-text">${frete.descricao}</p>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold valor-frete">${frete.valorFormatado}</span>
                        <span class="prazo-frete">Prazo: ${frete.prazo} dias úteis</span>
                    </div>
                    ${isLocal ? '<div class="mt-2 text-success">Entrega local</div>' : ''}
                </div>
            </div>
        </div>
    `).join('');

    // A declaração da modalHTML agora está aqui, única e utilizando CONFIG_LOJA.enderecoLoja
    const modalHTML = `
    <div class="modal fade" id="modalFrete" tabindex="-1" aria-labelledby="modalFreteLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFreteLabel">Opções de Transporte</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Selecione o método de transporte:</p>
                    
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="opcaoFrete" 
                                        id="retiradaLoja" value="Retirada na Loja"
                                        data-valor="0" data-prazo="Imediato"
                                        ${"Retirada na Loja" === JSON.parse(sessionStorage.getItem('freteSelecionado'))?.tipo ? 'checked' : ''}>
                                <label class="form-check-label" for="retiradaLoja">
                                    <h5 class="d-inline">Retirada na Loja</h5>
                                </label>
                                <p class="mt-2 mb-0 text-success">Grátis - Disponível imediatamente</p>
                                <small class="text-muted">${CONFIG_LOJA.enderecoLoja}</small> </div>
                        </div>
                    </div>
                    
                    <div id="opcoes-transporte">${opcoesTransporte}</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" onclick="selecionarFrete()">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);
    const modal = new bootstrap.Modal(document.getElementById('modalFrete'));
    modal.show();
}

function selecionarFrete() {
    const opcaoSelecionada = document.querySelector('input[name="opcaoFrete"]:checked');
    
    if (!opcaoSelecionada) {
        alert("Selecione uma opção de transporte antes de confirmar.");
        return;
    }

    // Esconde o modal Bootstrap
    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('modalFrete'));
    if (modalInstance) {
        modalInstance.hide();
    }

    // Captura os dados do atributo data-* ou de valores fixos
    const tipoFrete = opcaoSelecionada.value;
    let valorFrete = parseFloat(opcaoSelecionada.dataset.valor || 0); // Pega de data-valor
    let prazoFrete = opcaoSelecionada.dataset.prazo || "-"; // Pega de data-prazo
    
    // Se for retirada na loja, garante valores corretos
    if (tipoFrete === "Retirada na Loja") {
        valorFrete = 0;
        prazoFrete = "Imediato";
    }

    sessionStorage.setItem('freteSelecionado', JSON.stringify({
        tipo: tipoFrete,
        valor: valorFrete,
        prazo: prazoFrete
    }));

    // Remove o modal do DOM após a seleção
    const modalElement = document.getElementById('modalFrete');
    if (modalElement) {
        modalElement.remove();
    }
    
    // Atualiza o carrinho para mostrar o novo valor do frete
    carregarCarrinho();
}

// Finalizar compra
function finalizarCompra() {
    const carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
    const frete = JSON.parse(sessionStorage.getItem('freteSelecionado')) || {};
    
    if (carrinho.length === 0) {
        const modalErro = new bootstrap.Modal(document.getElementById('modalCarrinhoVazio'));
        modalErro.show();
        return;
    }

    // Valida se o frete foi selecionado e não é "A calcular"
    if (!frete.tipo || frete.tipo === "A calcular" || frete.valor === undefined) {
        alert("Por favor, selecione uma opção de frete válida antes de finalizar.");
        return;
    }

    const subtotalCarrinho = carrinho.reduce((total, item) => total + (Number(item.preco) * Number(item.qtd || 1)), 0);
    const totalComFrete = subtotalCarrinho + frete.valor;

    document.getElementById('total-compra').textContent = formatarMoeda(totalComFrete);
    
    const modalConfirmacao = new bootstrap.Modal(document.getElementById('modalConfirmacao'));
    modalConfirmacao.show();
    
    // Adiciona listener para limpar carrinho APÓS o modal de confirmação ser fechado
    document.getElementById('modalConfirmacao').addEventListener('hidden.bs.modal', function() {
        localStorage.removeItem('carrinho');
        sessionStorage.removeItem('freteSelecionado');
        window.location.href = 'index.php'; // Redireciona para index.php
    }, { once: true }); // Usamos { once: true } para o listener ser removido após a primeira execução
}

// Adicionar ao carrinho (função que provavelmente vem de outra página, mas mantida aqui)
function adicionarAoCarrinho(nome, preco) {
    const carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
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
    carregarCarrinho(); // Adicionado para atualizar o carrinho na mesma página
}