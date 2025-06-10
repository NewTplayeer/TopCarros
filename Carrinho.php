<!DOCTYPE html>
<html lang="pt-br">
<?php session_start(); // Inicia a sessão no topo da página ?>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Carrinho de Compras - TopCarros</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
      
      <link rel="stylesheet" href="css/carrinho.css"> 
      
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    </head>
<body>
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
      <a class="navbar-brand navbar-TopCarros" href="index.php">TopCarros</a>
      
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
          <?php if (isset($_SESSION['usuario_nome'])): // Verifica se o nome do usuário está na sessão ?>
            <li class="nav-item">
              <span class="nav-link">Bem-vindo, <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</span>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="logout.php">Sair</a>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link" href="login.php">Login</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="register.php">Registrar</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <section class="container py-5">
    <h2 class="text-center mb-4">Carrinho de Compras</h2>
    <div id="lista-carrinho" class="mt-4">
      </div>

    <hr />

    <div class="row mb-4">
      <div class="col-md-6">
        <label for="cep" class="form-label">Informe seu CEP:</label>
        <div class="input-group mb-3">
          <input type="text" id="cep" class="form-control" placeholder="00000-000" maxlength="9">
          <button class="btn btn-outline-secondary" type="button" id="btn-buscar-cep">Buscar</button>
        </div>
        <div id="detalhes-endereco" class="card mb-3 d-none">
          <div class="card-body">
            <h6 class="card-title">Endereço de Entrega</h6>
            <p class="card-text mb-1" id="endereco-completo"></p>
            <p class="card-text mb-1" id="cidade-estado"></p>
          </div>
        </div>
        <button class="btn btn-primary" onclick="calcularFrete()">Calcular Frete</button>
        <small class="text-muted d-block mt-1">Frete calculado para entrega em domicílio</small>
      </div>
    </div>

    <div class="d-flex justify-content-between align-items-center">
      <a href="index.php" class="btn btn-customcontinue">Continuar Comprando</a>
      <h4 class="mb-0">Total: <span id="total" class="fw-bold">R$ 0,00</span></h4>
      <button class="btn btn-customfinish" onclick="finalizarCompra()">Finalizar Compra</button>
    </div>
  </section>

  <div class="modal fade" id="modalCarrinhoVazio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-warning text-white">
          <h5 class="modal-title">Carrinho Vazio</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <i class="bi bi-cart-x-fill text-warning" style="font-size: 3rem;"></i>
          <p class="mt-3">Seu carrinho está vazio. Adicione itens antes de finalizar.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
          <a href="index.php" class="btn btn-primary">Ver Produtos</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalConfirmacao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Compra Finalizada!</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="text-center">
            <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
            <h4 class="mt-3">Obrigado por sua compra!</h4>
            <p>Seu pedido foi processado com sucesso.</p>
            <div class="alert alert-success">
              <strong>Total:</strong> <span id="total-compra"></span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Voltar à Loja</button>
        </div>
      </div>
    </div>
  </div>

  <footer class="bg-dark text-white text-center py-4">
    <p>&copy; 2025 TopCarros. Todos os direitos reservados.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/carrinho.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

  <script>
    // Seus scripts JavaScript personalizados para esta página, como:
    // - Lógica para carregar e exibir os itens do carrinho (geralmente do localStorage ou de um backend).
    // - Funções `calcularFrete()` e `finalizarCompra()` que você já tem definidas para os botões.
    // - Interação com a API do ViaCEP para buscar endereços.
    // - Controle da visibilidade dos modais.
  </script>
</body>
</html>