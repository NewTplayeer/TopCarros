document.addEventListener("DOMContentLoaded", function () {
    carregarCarrinho();
});

function carregarCarrinho() {
    let carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
    const lista = document.getElementById("lista-carrinho");
    const totalElement = document.getElementById("total");

    lista.innerHTML = "";
    let total = 0;

    carrinho.forEach((item, index) => {
        const subtotal = item.preco * (item.qtd || 1);
        total += subtotal;

        const itemHTML = `
            <div class="row align-items-center mb-3 border-bottom pb-3">
                <div class="col-md-4">
                    <strong>${item.nome}</strong><br>
                    <small>Preço unitário: R$ ${item.preco.toFixed(2)}</small>
                </div>
                <div class="col-md-4 d-flex align-items-center">
                    <button class="btn btn-outline-secondary btn-sm me-2" onclick="alterarQuantidade(${index}, -1)">-</button>
                    <span>${item.qtd || 1}</span>
                    <button class="btn btn-outline-secondary btn-sm ms-2" onclick="alterarQuantidade(${index}, 1)">+</button>
                </div>
                <div class="col-md-2">R$ ${subtotal.toFixed(2)}</div>
                <div class="col-md-2 text-end">
                    <button class="btn btn-remover-custom btn-sm" onclick="removerDoCarrinho(${index})">Remover</button>
                </div>
            </div>
        `;
        lista.innerHTML += itemHTML;
    });

    totalElement.textContent = `R$ ${total.toFixed(4)}`;
}

function alterarQuantidade(index, delta) {
    let carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
    if (!carrinho[index].qtd) carrinho[index].qtd = 1;

    carrinho[index].qtd += delta;
    if (carrinho[index].qtd < 1) carrinho[index].qtd = 1;

    localStorage.setItem('carrinho', JSON.stringify(carrinho));
    carregarCarrinho();
}

function removerDoCarrinho(index) {
    let carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
    carrinho.splice(index, 1);
    localStorage.setItem('carrinho', JSON.stringify(carrinho));
    carregarCarrinho();
}

function calcularFrete() {
    const cep = document.getElementById("cep").value;
    if (!cep || cep.length < 8) {
        alert("Por favor, digite um CEP válido.");
        return;
    }
    alert("Frete calculado com base no CEP: " + cep + " (simulado)");
}

