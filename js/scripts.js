function adicionarAoCarrinho(nome, preco) {
    let carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];

    const index = carrinho.findIndex(item => item.nome === nome);
    if (index !== -1) {
        carrinho[index].qtd = (carrinho[index].qtd || 1) + 1;
    } else {
        carrinho.push({ nome, preco, qtd: 1 });
    }

    localStorage.setItem('carrinho', JSON.stringify(carrinho));

  //-  alert(`${nome} foi adicionado ao carrinho!`);
}


    document.addEventListener('DOMContentLoaded', function () {
        const filtro = document.getElementById('filtroMarca');
        const cards = document.querySelectorAll('.car-card');

        filtro.addEventListener('change', () => {
            const marcaSelecionada = filtro.value;
            cards.forEach(card => {
                const marca = card.getAttribute('data-marca');
                card.style.display = (!marcaSelecionada || marca === marcaSelecionada) ? 'block' : 'none';
            });
        });
    });
