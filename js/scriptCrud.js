// Script para pegar o nome da marca no modal
document.addEventListener('DOMContentLoaded', function() {
  const botoesEditar = document.querySelectorAll('.btn-editar-marca');

  botoesEditar.forEach(botao => {
    botao.addEventListener('click', function() {
      const id = this.getAttribute('data-id');
      const nome = this.getAttribute('data-nome');

      document.getElementById('editMarcaId').value = id;
      document.getElementById('editMarcaNome').value = nome;
    });
  });
});
