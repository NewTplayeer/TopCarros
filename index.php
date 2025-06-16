<?php
require_once 'config.php'; // Inclua a conexão com o banco de dados

// Consulta para buscar todos os veículos, juntando com o nome da marca
$sql_veiculos = "SELECT v.id, m.nome AS marca_nome, v.marca_id, v.modelo, v.ano, v.preco, v.imagem FROM veiculos v JOIN marcas m ON v.marca_id = m.id ORDER BY v.id DESC";

$result_veiculos = $conn->query($sql_veiculos);

if ($result_veiculos === false) {
    error_log("Erro ao buscar veículos no index: " . $conn->error);
    $veiculos = []; // Garante que $veiculos seja um array vazio em caso de erro
} else {
    $veiculos = $result_veiculos->fetch_all(MYSQLI_ASSOC);
}

// Opcional: Se você estiver usando o filtro de marca (dropdown "Todas")
$sql_marcas_filtro = "SELECT id, nome FROM marcas ORDER BY nome ASC";
$result_marcas_filtro = $conn->query($sql_marcas_filtro);
$marcas_filtro = [];
if ($result_marcas_filtro) {
    while($row = $result_marcas_filtro->fetch_assoc()) {
        $marcas_filtro[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TopCarros - Venda de Veículos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/index.css">
</head>

<body>

    <?php session_start(); ?>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container"> 
        <a class="navbar-brand" href="">TopCarros</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['usuario_nome'])): ?>
                    <li class="nav-item">
                        <span class="nav-link">Bem-vindo, <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</span>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Registro</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">DashBoard</a>
                <li class="nav-item">
                    <a class="nav-link" href="Carrinho.php">Carrinho</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="historico_compras.php">Histórico de Compras</a>
                </li>
                <li class="nav-item">
                        <a class="nav-link" href="logout.php">Sair</a>
                    </li>
            </ul>
        </div>
    </div>
</nav>

    <header class="bg-dark text-white text-center py-5">
        <h1 class="display-4">Bem-vindo à TopCarros!</h1>
        <p class="lead">Encontre o carro dos seus sonhos</p>
    </header>

    <section class="container mt-5">
        <div class="row justify-content-center mb-4">
            <div class="col-md-2">
                <label for="filtroMarca" class="form-label">Filtrar por marca:</label>
                <select class="form-select" id="filtroMarca">
                    <option value="">Todas</option>
                    <?php foreach ($marcas_filtro as $marca): ?>
                        <option value="<?= htmlspecialchars($marca['id']) ?>"><?= htmlspecialchars($marca['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </section>

    <section class="container py-5">
    <h2 class="text-center mb-4">Catálogo de Veículos</h2>
    
    <!-- Mensagem para quando não há veículos no catálogo (banco vazio) -->
    <?php if (empty($veiculos)): ?>
        <div class="col-12 text-center">
            <p>Nenhum veículo encontrado no catálogo.</p>
        </div>
    <?php endif; ?>
    
    <!-- Container principal dos veículos -->
    <div class="row row-cols-1 row-cols-md-3 g-4" id="container-veiculos">
        <?php foreach ($veiculos as $veiculo): ?>
            <div class="col car-card" data-marca="<?= htmlspecialchars($veiculo['marca_id']) ?>">
                <div class="card h-100">
                    <?php
                    // if (strtotime($veiculo['data_cadastro']) > strtotime('-24 hours')) {
                        echo '<div class="badge bg-dark text-white position-absolute" style="top: 0.5rem; right: 0.5rem">Novo</div>';
                    // }
                    ?>
                    <img src="uploads/<?= htmlspecialchars($veiculo['imagem']) ?>" class="card-img-top" alt="<?= htmlspecialchars($veiculo['modelo']) ?>">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?= htmlspecialchars($veiculo['marca_nome']) ?> <?= htmlspecialchars($veiculo['modelo']) ?></h5>
                        <p class="card-text">R$ <?= number_format($veiculo['preco'], 2, ',', '.') ?></p>
                        <button class="btn btn-outline-dark w-100"
    onclick="adicionarAoCarrinho(<?= htmlspecialchars($veiculo['id']) ?>)">Adicionar ao carrinho</button>
                    </div>
                </div>
            </div>  
        <?php endforeach; ?>
    </div>
    
    <!-- Mensagem para quando não há veículos com o filtro aplicado -->
    <div id="nenhum-veiculo-mensagem" class="col-12 text-center mt-4" style="display: none;">
        <div class="alert alert-warning">
            Nenhum veículo encontrado para a marca selecionada.
        </div>
    </div>
</section>

    <footer class="bg-dark text-white text-center py-4">
        <p>&copy; 2025 TopCarros. Todos os direitos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/carrinho.js"></script>

</body>

</html>