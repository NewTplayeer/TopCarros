
<!DOCTYPE html>
<html lang="pt-br">
<?php session_start(); ?>
  require 'config.php';
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Carrinho de Compras - TopCarros</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="css/styles.css" />
  <link rel="stylesheet" href="css/carrinho.css">
  <style>
    .btn-outline-calcular {
      border-color: #0d6efd;
      color: #0d6efd;
    }
    .btn-outline-calcular:hover {
      background-color: #0d6efd;
      color: white;
    }
    .btn-customcontinue {
      background-color: #6c757d;
      color: white;
      padding: 8px 20px;
    }
    .btn-customfinish {
      background-color: #198754;
      color: white;
      padding: 8px 20px;
    }
    .btn-remover-custom {
      color: #dc3545;
      border-color: #dc3545;
    }
    .btn-remover-custom:hover {
      background-color: #dc3545;
      color: white;
    }
    #lista-carrinho .row {
      transition: all 0.3s ease;
    }
    #lista-carrinho .row:hover {
      background-color: #f8f9fa;
    }
    
  /* Estilo para destacar a opção selecionada */
.form-check-input:checked ~ .card-body {
    background-color: #f8f9fa;
    border-left: 3px solid #0d6efd;
}

.valor-frete {
    color: #198754;
    font-size: 1.1rem;
}

.prazo-frete {
    color: #6c757d;
}
  /* Estilos para o carrinho */
.btn-remover-custom {
    color: #dc3545;
    border-color: #dc3545;
}

.btn-remover-custom:hover {
    background-color: #dc3545;
    color: white;
}

/* Estilos para as opções de frete */
.form-check-input[type="radio"] {
    transform: scale(1.2);
    margin-top: 0.3rem;
}

.form-check-label h5 {
    margin-left: 0.5rem;
}

.card:hover {
    border-color: #0d6efd;
    background-color: #f8f9fa;
}

#resumo-frete div {
    padding: 0.5rem 0;
    border-bottom: 1px solid #eee;
}

/* Ícones Bootstrap */
.bi {
    vertical-align: -.125em;
}
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
      <a class="navbar-brand" href="index.html">TopCarros</a>
    </div>
  </nav>

  <!-- Carrinho -->
  <section class="container py-5">
    <h2 class="text-center mb-4">Carrinho de Compras</h2>
    <div id="lista-carrinho" class="mt-4"></div>

    <hr />

    <!-- Adicione este campo no formulário de frete -->
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
      <a href="index.html" class="btn btn-customcontinue">Continuar Comprando</a>
      <h4 class="mb-0">Total: <span id="total" class="fw-bold">R$ 0,00</span></h4>
      <button class="btn btn-customfinish" onclick="finalizarCompra()">Finalizar Compra</button>
    </div>
  </section>

  <!-- Modal Carrinho Vazio -->
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
        <a href="index.html" class="btn btn-primary">Ver Produtos</a>
      </div>
    </div>
  </div>
</div>

  <!-- Modal de Confirmação -->
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

  <!-- Rodapé -->
  <footer class="bg-dark text-white text-center py-4">
    <p>&copy; 2025 TopCarros. Todos os direitos reservados.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/carrinho.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
  
</script>
</body>
</html>