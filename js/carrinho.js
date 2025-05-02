document.addEventListener("DOMContentLoaded", function() {
    carregarCarrinho();
    // Verifica se já tem frete selecionado
    if (!sessionStorage.getItem('freteSelecionado')) {
        sessionStorage.setItem('freteSelecionado', JSON.stringify({
            tipo: "A calcular",
            valor: 0,
            prazo: "-"
        }));
    }
});

// Configuração da loja
const CONFIG_LOJA = {
    cepOrigem: "80000000",
    enderecoLoja: "Av. dos Carros, 1000 - Centro, Curitiba/PR",
    dimensoesPadrao: {
        peso: 1500,
        comprimento: 450,
        altura: 150,
        largura: 180
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

// Formatação de CEP
function formatarCEP(input) {
    let cep = input.value.replace(/\D/g, '');
    cep = cep.substring(0, 8);
    if (cep.length > 5) {
        cep = cep.substring(0, 5) + '-' + cep.substring(5);
    }
    input.value = cep;
}

// Validação de CEP
function validarCEP(cep) {
    cep = cep.replace(/\D/g, '');
    return cep.length === 8;
}

// Carregar carrinho
function carregarCarrinho() {
    const carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
    const lista = document.getElementById("lista-carrinho");
    const totalElement = document.getElementById("total");

    lista.innerHTML = "";
    let subtotal = 0;

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

// Funções do carrinho
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

// Funções de frete
document.getElementById("btn-buscar-cep").addEventListener("click", buscarEnderecoPorCEP);

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

    if (!endereco.uf) {
        alert("Por favor, busque um CEP válido primeiro.");
        return;
    }

    if (carrinho.length === 0) {
        alert("Seu carrinho está vazio!");
        return;
    }

    try {
        const isLocal = endereco.uf === CONFIG_LOJA.cepOrigem.substring(0, 2);
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
            prazo: isLocal ? 3 : 7,
            valorBase: 2500
        },
        {
            nome: "Guincho Plataforma",
            descricao: "Para veículos não funcionando",
            prazo: isLocal ? 2 : 5,
            valorBase: 1800
        }
    ];

    return transportadoras.map(transp => {
        const valorCalculado = isLocal 
            ? transp.valorBase * 0.7
            : transp.valorBase * (1 + (Math.random() * 0.5));
        
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
    const opcoesTransporte = fretes.map(frete => `
        <div class="card mb-3">
            <div class="card-body">
                <div class="form-check">
                    <input class="form-check-input" type="radio" 
                           name="opcaoFrete" id="frete-${frete.nome.toLowerCase().replace(/\s/g, '-')}" 
                           value="${frete.nome}">
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

    const modalHTML = `
    <div class="modal fade" id="modalFrete" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Opções de Transporte</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Selecione o método de transporte:</p>
                    
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="opcaoFrete" 
                                       id="retiradaLoja" value="Retirada na Loja" checked>
                                <label class="form-check-label" for="retiradaLoja">
                                    <h5 class="d-inline">Retirada na Loja</h5>
                                </label>
                                <p class="mt-2 mb-0 text-success">Grátis - Disponível imediatamente</p>
                                <small class="text-muted">${CONFIG_LOJA.enderecoLoja}</small>
                            </div>
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
        alert("Selecione uma opção de transporte");
        return;
    }

    const modal = bootstrap.Modal.getInstance(document.getElementById('modalFrete'));
    modal.hide();

    if (opcaoSelecionada.value === "Retirada na Loja") {
        sessionStorage.setItem('freteSelecionado', JSON.stringify({
            tipo: "Retirada na Loja",
            valor: 0,
            prazo: "Imediato"
        }));
    } else {
        // Captura os dados corretamente para outras transportadoras
        const cardBody = opcaoSelecionada.closest('.card-body');
        const valorText = cardBody.querySelector('.fw-bold').textContent;
        const valor = parseFloat(valorText.replace(/[^\d,]/g, '').replace(',', '.'));
        const prazo = cardBody.querySelector('span:not(.fw-bold)').textContent.replace("Prazo: ", "");
        
        sessionStorage.setItem('freteSelecionado', JSON.stringify({
            tipo: opcaoSelecionada.value,
            valor: valor,
            prazo: prazo
        }));
    }

    // Remove o modal do DOM
    document.getElementById('modalFrete').remove();
    
    // Atualiza o carrinho para mostrar o novo valor
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

    if (!frete.tipo || frete.tipo === "A calcular") {
        alert("Por favor, selecione uma opção de frete antes de finalizar.");
        return;
    }

    document.getElementById('total-compra').textContent = 
        formatarMoeda(carrinho.reduce((total, item) => total + (item.preco * item.qtd), 0) + frete.valor);
    
    const modalConfirmacao = new bootstrap.Modal(document.getElementById('modalConfirmacao'));
    modalConfirmacao.show();
    
    document.getElementById('modalConfirmacao').addEventListener('hidden.bs.modal', function() {
        localStorage.removeItem('carrinho');
        sessionStorage.removeItem('freteSelecionado');
        window.location.href = 'index.html';
    });
}

// Adicionar ao carrinho
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
}