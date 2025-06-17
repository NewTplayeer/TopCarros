// Remove chamadas ao localStorage completamente e usa AJAX
document.addEventListener("DOMContentLoaded", function() {
    // Carregamento inicial dos dados do carrinho do servidor
    carregarCarrinho();

    // Sua lógica existente de filtroMarca para a página index.php (se houver)
    const filtroMarca = document.getElementById("filtroMarca");
    const mensagem = document.getElementById("nenhum-veiculo-mensagem");

    if (mensagem) {
        mensagem.style.display = "none";
    }

    if (filtroMarca) {
        filtroMarca.addEventListener("change", function() {
            const marcaId = this.value;
            filtrarVeiculosPorMarca(marcaId);
        });
    }

    // Adiciona o listener de evento para o botão de buscar CEP
    const buscarCepBtn = document.getElementById("btn-buscar-cep");
    if (buscarCepBtn) {
        buscarCepBtn.addEventListener("click", buscarEnderecoPorCEP);
    }

    // Adiciona o listener de evento para o input do CEP para formatação em tempo real
    const cepInput = document.getElementById("cep");
    if (cepInput) {
        cepInput.addEventListener('input', function() {
            formatarCEP(this);
            // Ao digitar um novo CEP, limpamos a seleção de frete para que seja recalculado.
            sessionStorage.removeItem('freteSelecionado');
            carregarCarrinho(); // Recarrega o carrinho para mostrar 'A calcular' para o frete
        });
    }
});

// Lógica de filtro original (se for usada em index.php)
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

    if (mensagem) {
        if (marcaId !== "" && !algumVeiculoVisivel && cards.length > 0) {
            mensagem.style.display = "block";
        } else {
            mensagem.style.display = "none";
        }
    }
}

// Configuração da loja (CEP de origem, etc.)
const CONFIG_LOJA = {
    cepOrigem: "72140-340", // CEP completo para Taguatinga/DF
    ufOrigem: "DF", // UF para comparação mais robusta
    enderecoLoja: "QNJ 34 LOJA 21, Taguatinga, DF - CEP: 72140-340", // Endereço CORRIGIDO DA LOJA
    dimensoesPadrao: { // Dimensões e peso padrão (pode ser usado para cálculo de frete externo)
        peso: 1500, // em gramas
        comprimento: 450, // em milímetros
        altura: 150, // em milímetros
        largura: 180 // em milímetros
    }
};

// Formatação de moeda para exibir valores em R$
function formatarMoeda(valor) {
    if (typeof valor === 'string') {
        valor = valor.replace(/\./g, '').replace(',', '.');
    }
    const numero = Number(valor);
    if (isNaN(numero)) return "R$ 0,00"; // Retorna R$ 0,00 se não for um número válido
    return numero.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Formatação de CEP em tempo real (ex: 00000-000)
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
    return cep.length === 8; // Retorna true se o CEP tiver 8 dígitos
}

// **MODIFICADO: carregarCarrinho para buscar dados do backend E chamar a atualização do resumo**
async function carregarCarrinho() {
    const lista = document.getElementById("lista-carrinho");
    if (!lista) return;
    // O totalElement (do botão de finalizar compra) será atualizado na função de resumo.
    // const totalElement = document.getElementById("total"); 

    lista.innerHTML = ""; // Limpa o conteúdo atual da lista do carrinho

    try {
        const response = await fetch('api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get'
        });
        const data = await response.json();

        if (!data.success) {
            console.error("Erro ao carregar carrinho:", data.message);
            lista.innerHTML = `<p class="text-center text-danger">Erro ao carregar seu carrinho: ${data.message}</p>`;
            // Atualiza o resumo do pedido mesmo em caso de erro para mostrar 0
            atualizarResumoDoPedidoNaPagina(0, null, 0); 
            return;
        }

        const carrinho = data.carrinho || [];
        // O subtotal REAL deve vir de data.cart_total, que é o total calculado no backend.

        if (carrinho.length === 0) {
            lista.innerHTML = '<p class="text-center text-muted">Seu carrinho está vazio.</p>';
        } else {
            carrinho.forEach(item => {
                const preco = item.preco;
                const qtd = item.qtd || 1;
                const subtotalItem = preco * qtd;

                const itemHTML = `
                    <div class="row align-items-center mb-3 border-bottom pb-3">
                        <div class="col-md-4">
                            <strong>${item.nome}</strong><br>
                            <small>Preço unitário: ${formatarMoeda(preco)}</small>
                        </div>
                        <div class="col-md-4 d-flex align-items-center">
                            <button class="btn btn-outline-secondary btn-sm me-2" onclick="alterarQuantidade(${item.item_id}, -1)">-</button>
                            <span>${qtd}</span>
                            <button class="btn btn-outline-secondary btn-sm ms-2" onclick="alterarQuantidade(${item.item_id}, 1)">+</button>
                        </div>
                        <div class="col-md-2">${formatarMoeda(subtotalItem)}</div>
                        <div class="col-md-2 text-end">
                            <button class="btn btn-remover-custom btn-sm" onclick="removerDoCarrinho(${item.item_id})">Remover</button>
                        </div>
                    </div>
                `;
                lista.innerHTML += itemHTML;
            });
        }

        // Obtém as informações de frete da sessionStorage (onde 'selecionarFrete' as salvou)
        // Isso garante que a opção selecionada pelo usuário seja usada para o cálculo.
        const frete = JSON.parse(sessionStorage.getItem('freteSelecionado')) || { tipo: "A calcular", valor: 0, prazo: "-" };

        // Usa o subtotal que veio do backend (data.cart_total)
        const subtotalItensBackend = data.cart_total || 0;

        // Calcula o total geral (subtotal dos itens do backend + valor do frete da sessionStorage)
        const totalFinalComFrete = subtotalItensBackend + frete.valor;

        // *** AQUI É A CHAMADA PARA ATUALIZAR O RESUMO DO PEDIDO NA PÁGINA ***
        // Esta função agora é responsável por gerar e injetar o HTML do resumo
        atualizarResumoDoPedidoNaPagina(subtotalItensBackend, frete, totalFinalComFrete);
        
        // Remove a linha que atualizava o totalElement diretamente aqui,
        // pois a função `atualizarResumoDoPedidoNaPagina` já faz isso para o elemento do resumo.
        // totalElement.textContent = formatarMoeda(totalFinalComFrete); 

    } catch (error) {
        console.error("Erro na comunicação com o servidor:", error);
        lista.innerHTML = '<p class="text-center text-danger">Não foi possível carregar seu carrinho. Tente novamente mais tarde.</p>';
        // Atualiza o resumo do pedido mesmo em caso de erro de comunicação
        atualizarResumoDoPedidoNaPagina(0, null, 0); 
    }
}

// **MODIFICADO: alterarQuantidade para enviar requisição AJAX**
async function alterarQuantidade(item_id, delta) {
    const currentCartData = await getCartFromServer(); // Obtém os dados mais recentes do carrinho do servidor
    const itemToUpdate = currentCartData.carrinho.find(item => item.item_id === item_id);

    if (!itemToUpdate) {
        console.error("Item não encontrado no carrinho para atualização.");
        return;
    }

    const newQuantity = Math.max(1, itemToUpdate.qtd + delta); // Garante que a quantidade não seja menor que 1

    try {
        // Envia uma requisição AJAX para o backend para atualizar a quantidade
        const response = await fetch('api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update_quantity&item_id=${item_id}&quantidade=${newQuantity}`
        });
        const data = await response.json();

        if (data.success) {
            carregarCarrinho(); // Recarrega o carrinho para refletir as alterações na UI
        } else {
            alert(`Erro ao alterar quantidade: ${data.message}`);
        }
    } catch (error) {
        console.error("Erro na comunicação para alterar quantidade:", error);
        alert("Erro de comunicação ao alterar quantidade. Tente novamente.");
    }
}

// **MODIFICADO: removerDoCarrinho para enviar requisição AJAX**
async function removerDoCarrinho(item_id) {
    if (!confirm('Tem certeza que deseja remover este item do carrinho?')) {
        return; // Cancela a remoção se o usuário não confirmar
    }

    try {
        // Envia uma requisição AJAX para o backend para remover o item
        const response = await fetch('api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=remove&item_id=${item_id}`
        });
        const data = await response.json();

        if (data.success) {
            carregarCarrinho(); // Recarrega o carrinho para refletir as alterações na UI
        } else {
            alert(`Erro ao remover item: ${data.message}`);
        }
    } catch (error) {
        console.error("Erro na comunicação para remover item:", error);
        alert("Erro de comunicação ao remover item. Tente novamente.");
    }
}

async function buscarEnderecoPorCEP() {
    const cepInput = document.getElementById("cep");
    if (!cepInput) return; // Sai se o elemento não existir

    const cep = cepInput.value.replace(/\D/g, ''); // Remove caracteres não numéricos

    if (!validarCEP(cep)) {
        alert("Por favor, digite um CEP válido com 8 dígitos.");
        return;
    }

    try {
        // Faz a requisição à API do ViaCEP
        const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const endereco = await response.json(); // Converte a resposta para JSON

        if (endereco.erro) {
            throw new Error("CEP não encontrado"); // Lança um erro se o CEP não for encontrado
        }

        // Atualiza os elementos HTML com os detalhes do endereço
        const enderecoCompletoElem = document.getElementById("endereco-completo");
        const cidadeEstadoElem = document.getElementById("cidade-estado");
        const detalhesEnderecoElem = document.getElementById("detalhes-endereco");

        if (enderecoCompletoElem) enderecoCompletoElem.textContent = `${endereco.logradouro || 'Endereço não especificado'}, ${endereco.bairro || ''}`;
        if (cidadeEstadoElem) cidadeEstadoElem.textContent = `${endereco.localidade}/${endereco.uf}`;
        if (detalhesEnderecoElem) detalhesEnderecoElem.classList.remove("d-none"); // Mostra o card de detalhes

        // Armazena o endereço completo na sessionStorage para uso posterior
        sessionStorage.setItem('enderecoEntrega', JSON.stringify(endereco));

    } catch (error) {
        console.error("Erro ao buscar CEP:", error);
        alert("Não foi possível encontrar o endereço. Verifique o CEP e tente novamente.");
        const detalhesEnderecoElem = document.getElementById("detalhes-endereco");
        if (detalhesEnderecoElem) detalhesEnderecoElem.classList.add("d-none"); // Oculta o card se houver erro
    }
}

async function calcularFrete() {
    const cepInput = document.getElementById("cep");
    if (!cepInput) return;

    const cep = cepInput.value.replace(/\D/g, '');
    const endereco = JSON.parse(sessionStorage.getItem('enderecoEntrega')) || {};
    const cartData = await getCartFromServer(); // Obtém os dados atuais do carrinho do BD
    const carrinho = cartData.carrinho || [];
    const subtotalCarrinho = carrinho.reduce((total, item) => total + (item.preco * item.qtd), 0);

    if (!validarCEP(cep)) {
        alert("Por favor, digite um CEP válido com 8 dígitos.");
        return;
    }

    if (!endereco.uf) {
        alert("Por favor, busque um CEP válido primeiro para obter o endereço completo.");
        return;
    }

    if (carrinho.length === 0) {
        alert("Seu carrinho está vazio! Adicione itens antes de calcular o frete.");
        return;
    }

    try {
        const isLocal = endereco.uf.toUpperCase() === CONFIG_LOJA.ufOrigem.toUpperCase();
        const fretes = await calcularFretesDisponiveis({
            cepDestino: cep,
            isLocal,
            valorTotal: subtotalCarrinho
        });

        exibirOpcoesFrete(fretes, isLocal); // Chama para exibir as opções em um modal

    } catch (error) {
        console.error("Erro ao calcular frete:", error);
        alert("Não foi possível calcular o frete. Entre em contato conosco.");
    }
}

// Simula o cálculo de fretes disponíveis com base na localidade e valor total
async function calcularFretesDisponiveis({ cepDestino, isLocal, valorTotal }) {
    // Transportadoras de exemplo com lógica de preço e prazo
    const transportadoras = [
        {
            nome: "Transporte Especializado",
            descricao: "Carreta com equipamento especial para transporte de veículos grandes ou múltiplos.",
            prazo: isLocal ? 3 : 7, // Prazo em dias úteis
            valorBase: 2500 // Valor base hipotético
        },
        {
            nome: "Guincho Plataforma",
            descricao: "Serviço de guincho plataforma para veículos não funcionais ou transporte rápido local.",
            prazo: isLocal ? 2 : 5,
            valorBase: 1800
        }
    ];

    return transportadoras.map(transp => {
        // Lógica para calcular o valor do frete: desconto para local, aumento aleatório para outros estados
        const valorCalculado = isLocal
            ? transp.valorBase * 0.7 // 30% de desconto para entrega local
            : transp.valorBase * (1 + (Math.random() * 0.5)); // Aumento de 0 a 50% para outros estados

        return {
            nome: transp.nome,
            descricao: transp.descricao,
            valor: valorCalculado,
            prazo: transp.prazo,
            valorFormatado: formatarMoeda(valorCalculado), // Formata para exibição
            isLocal // Indica se é entrega local
        };
    });
}

function exibirOpcoesFrete(fretes, isLocal) {
    // Remove qualquer modal de frete existente para evitar duplicação
    const existingModal = document.getElementById('modalFrete');
    if (existingModal) {
        existingModal.remove();
    }

    // Obtém o frete atualmente selecionado da sessionStorage para pré-marcar o rádio button
    const currentSelectedFrete = JSON.parse(sessionStorage.getItem('freteSelecionado')) || {};

    // Mapeia as transportadoras para o HTML das opções de rádio
    const opcoesTransporte = fretes.map(frete => `
        <div class="card mb-3">
            <div class="card-body">
                <div class="form-check">
                    <input class="form-check-input" type="radio"
                                name="opcaoFrete" id="frete-${frete.nome.toLowerCase().replace(/\s/g, '-')}"
                                value="${frete.nome}"
                                data-valor="${frete.valor}"
                                data-prazo="${frete.prazo}"
                                ${frete.nome === currentSelectedFrete.tipo ? 'checked' : ''}
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

    // Constrói o HTML completo do modal de frete
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
                                         ${"Retirada na Loja" === currentSelectedFrete.tipo ? 'checked' : ''}>
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

    // Adiciona o HTML do modal ao corpo do documento e o exibe
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

    // Esconde o modal do Bootstrap
    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('modalFrete'));
    if (modalInstance) {
        modalInstance.hide();
    }

    // Captura os dados do frete selecionado
    const tipoFrete = opcaoSelecionada.value;
    let valorFrete = parseFloat(opcaoSelecionada.dataset.valor || 0);
    let prazoFrete = opcaoSelecionada.dataset.prazo || "-";
    
    // Garante valores corretos se for "Retirada na Loja"
    if (tipoFrete === "Retirada na Loja") {
        valorFrete = 0;
        prazoFrete = "Imediato";
    }

    // Salva a opção de frete selecionada na sessionStorage
    sessionStorage.setItem('freteSelecionado', JSON.stringify({
        tipo: tipoFrete,
        valor: valorFrete,
        prazo: prazoFrete
    }));

    // Remove o elemento modal do DOM após a seleção
    const modalElement = document.getElementById('modalFrete');
    if (modalElement) {
        modalElement.remove();
    }
    
    // Atualiza a exibição do carrinho para mostrar o novo valor do frete
    carregarCarrinho();
}

// **MODIFICADO: finalizarCompra para usar o AJAX de checkout**
async function finalizarCompra() {
    const cartData = await getCartFromServer(); // Obtém os dados mais recentes do carrinho do servidor
    const carrinho = cartData.carrinho || []; // Pega os itens do carrinho
    const frete = JSON.parse(sessionStorage.getItem('freteSelecionado')) || {}; // Pega a opção de frete da sessionStorage

    if (carrinho.length === 0) {
        // Se o carrinho estiver vazio, exibe o modal de carrinho vazio
        const modalErro = new bootstrap.Modal(document.getElementById('modalCarrinhoVazio'));
        modalErro.show();
        return;
    }

    // Valida se o frete foi selecionado e não está como "A calcular"
    if (!frete.tipo || frete.tipo === "A calcular" || frete.valor === undefined) {
        alert("Por favor, selecione uma opção de frete válida antes de finalizar.");
        return;
    }

    // Calcula o subtotal dos itens no carrinho
    const subtotalCarrinho = carrinho.reduce((total, item) => total + (Number(item.preco) * Number(item.qtd || 1)), 0);
    // O total final será calculado no backend (subtotal + frete)

    try {
        // Envia uma requisição AJAX para o backend para finalizar a compra
        const response = await fetch('api/checkout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json', // Envia JSON
            },
            body: JSON.stringify({ // Converte os dados para JSON
                frete_tipo: frete.tipo,
                frete_valor: frete.valor,
                frete_prazo: frete.prazo,
                valor_total_carrinho: subtotalCarrinho // Envia o subtotal para o backend calcular o total final
            })
        });
        const data = await response.json(); // Converte a resposta para JSON

        if (data.success) {
            // Se a compra foi finalizada com sucesso, exibe o modal de confirmação
            document.getElementById('total-compra').textContent = formatarMoeda(data.total_final);
            const modalConfirmacao = new bootstrap.Modal(document.getElementById('modalConfirmacao'));
            modalConfirmacao.show();

            // Adiciona um listener para limpar a sessionStorage e redirecionar APÓS o modal de confirmação ser fechado
            document.getElementById('modalConfirmacao').addEventListener('hidden.bs.modal', function() {
                sessionStorage.removeItem('freteSelecionado'); // Limpa a seleção de frete
                window.location.href = 'index.php'; // Redireciona para a página inicial
            }, { once: true }); // O listener será removido após a primeira execução
        } else {
            alert(`Erro ao finalizar compra: ${data.message}`);
        }
    } catch (error) {
        console.error("Erro na comunicação para finalizar compra:", error);
        alert("Erro de comunicação ao finalizar compra. Tente novamente.");
    }
}

// **MODIFICADO: adicionarAoCarrinho para enviar requisição AJAX**
async function adicionarAoCarrinho(veiculo_id) {
    try {
        // Envia uma requisição AJAX para adicionar o veículo ao carrinho
        const response = await fetch('api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=add&veiculo_id=${veiculo_id}&quantidade=1`
        });
        const data = await response.json();

        if (data.success) {
            alert(data.message);
            // Se estiver na página do carrinho, recarrega para exibir as mudanças
            if (window.location.pathname.includes('Carrinho.php')) {
                carregarCarrinho();
            }
        } else {
            alert(`Erro ao adicionar ao carrinho: ${data.message}`);
        }
    } catch (error) {
        console.error("Erro na comunicação para adicionar ao carrinho:", error);
        alert("Erro de comunicação ao adicionar ao carrinho. Tente novamente.");
    }
}

// Nova função auxiliar para obter os dados do carrinho do servidor
async function getCartFromServer() {
    try {
        const response = await fetch('api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get'
        });
        const data = await response.json();
        if (data.success) {
            return data;
        } else {
            console.error("Falha ao buscar carrinho do servidor:", data.message);
            // Retorna um objeto de carrinho vazio em caso de falha
            return { carrinho: [], cart_total: 0, frete: { tipo: "A calcular", valor: 0, prazo: "-" } };
        }
    } catch (error) {
        console.error("Erro de rede ao buscar carrinho:", error);
        // Retorna um objeto de carrinho vazio em caso de erro de rede
        return { carrinho: [], cart_total: 0, frete: { tipo: "A calcular", valor: 0, prazo: "-" } };
    }
}

// **FUNÇÃO ADICIONADA: `atualizarResumoDoPedidoNaPagina`**
/**
 * Atualiza o bloco de resumo do pedido na página principal.
 * Esta função deve ser chamada por `carregarCarrinho` e outras funções que alteram o carrinho/frete.
 * @param {number} subtotalItensBackend - O subtotal dos itens do carrinho.
 * @param {object|null} frete - Objeto de frete com {tipo, prazo, valor}, ou null se não houver frete.
 * @param {number} totalFinalComFrete - O total final incluindo subtotal e frete.
 */
function atualizarResumoDoPedidoNaPagina(subtotalItensBackend, frete, totalFinalComFrete) {
    const resumoContainer = document.getElementById('resumoPedidoContainer');

    if (!resumoContainer) {
        console.error('Elemento #resumoPedidoContainer não encontrado no HTML. Certifique-se de que o div está presente.');
        return;
    }

    // Lógica para determinar o HTML do frete
    let freteHTML = '';
    const valorFrete = frete ? frete.valor : 0;
    const valorFreteFormatado = formatarMoeda(valorFrete);

    if (frete && frete.tipo && frete.prazo) {
        if (frete.tipo === "Retirada na Loja") { // Casos específicos de frete
            freteHTML = `
                <div class="d-flex justify-content-between mb-1">
                    <span>Frete (${frete.tipo}):</span>
                    <span class="text-success fw-bold">Grátis</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Prazo estimado:</span>
                    <span>${frete.prazo}</span>
                </div>
            `;
        } else if (frete.tipo === "A calcular") { // Caso do frete ainda não calculado
             freteHTML = `
                <div class="d-flex justify-content-between mb-1">
                    <span>Frete:</span>
                    <span class="text-muted">A calcular</span>
                </div>
            `;
        }
        else { // Frete pago normal
            freteHTML = `
                <div class="d-flex justify-content-between mb-1">
                    <span>Frete (${frete.tipo}):</span>
                    <span>${valorFreteFormatado}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Prazo estimado:</span>
                    <span>${frete.prazo} dias úteis</span>
                </div>
            `;
        }
    } else {
        // Frete completamente ausente ou inválido
        freteHTML = `
            <div class="d-flex justify-content-between mb-1">
                <span>Frete:</span>
                <span class="text-muted">Não disponível</span>
            </div>
        `;
    }

    // Gera o HTML completo do resumo
    const resumoHTML = `
        <div class="row justify-content-center"> <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5"> <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-center mb-3">Resumo do Pedido</h5>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold">Subtotal dos itens:</span>
                            <span>${formatarMoeda(subtotalItensBackend)}</span>
                        </div>
                        
                        ${freteHTML} <hr class="my-3">
                        
                        <div class="d-flex justify-content-between fw-bold fs-5 text-primary">
                            <span>Total geral:</span>
                            <span>${formatarMoeda(totalFinalComFrete)}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Atualiza o conteúdo do contêiner do resumo
    resumoContainer.innerHTML = resumoHTML;

    // Atualiza o elemento de "total" principal, se ele existir (ex: no botão de finalizar compra)
    const totalElement = document.getElementById("total"); 
    if (totalElement) {
        totalElement.textContent = formatarMoeda(totalFinalComFrete);
    }
}