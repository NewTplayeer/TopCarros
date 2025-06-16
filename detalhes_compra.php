<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: historico_compras.php");
    exit();
}

$compra_id = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

// Consulta atualizada para incluir o frete
$sql_compra = "SELECT c.id, c.data_compra, c.valor_total, c.status, c.tipo_frete, c.valor_frete
               FROM compras c
               WHERE c.id = ? AND c.usuario_id = ?";
$stmt = $conn->prepare($sql_compra);
$stmt->bind_param("ii", $compra_id, $usuario_id);
$stmt->execute();
$result_compra = $stmt->get_result();
$compra = $result_compra->fetch_assoc();

if (!$compra) {
    header("Location: historico_compras.php");
    exit();
}

// Consulta para os itens da compra
$sql_itens = "SELECT v.id, m.nome AS marca, v.modelo, v.ano, ci.preco_unitario, v.imagem
              FROM compra_itens ci
              JOIN veiculos v ON ci.veiculo_id = v.id
              JOIN marcas m ON v.marca_id = m.id
              WHERE ci.compra_id = ?";
$stmt = $conn->prepare($sql_itens);
$stmt->bind_param("i", $compra_id);
$stmt->execute();
$result_itens = $stmt->get_result();
$itens = $result_itens->fetch_all(MYSQLI_ASSOC);

// Calcular valor total + frete
$valor_total_com_frete = $compra['valor_total'] + $compra['valor_frete'];

// Mapear tipos de frete para nomes amigáveis
$tipos_frete = [
    'retirada_loja' => 'Retirada na Loja',
    'transporte_proprio' => 'Transporte Próprio',
    'transportadora' => 'Transportadora'
];

// Função para formatar valor do frete
function formatarValorFrete($tipo_frete, $valor_frete) {
    if ($tipo_frete === 'retirada_loja') {
        return 'Grátis';
    }
    return 'R$ ' . number_format($valor_frete, 2, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Compra - TopCarros</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/index.css">
    <style>
        .frete-info {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .valor-frete-gratis {
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <section class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Detalhes da Compra #<?= $compra['id'] ?></h2>
            <a href="historico_compras.php" class="btn btn-outline-secondary">Voltar</a>
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($compra['data_compra'])) ?></p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Status:</strong> 
                            <span class="badge bg-<?= 
                                $compra['status'] == 'pago' ? 'success' : 
                                ($compra['status'] == 'pendente' ? 'warning' : 'danger') 
                            ?>">
                                <?= ucfirst($compra['status']) ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Total:</strong> R$ <?= number_format($valor_total_com_frete, 2, ',', '.') ?></p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Frete:</strong> <?= $tipos_frete[$compra['tipo_frete']] ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seção de informações do frete -->
        <div class="frete-info mb-4">
            <h5>Informações de Frete</h5>
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Tipo de Frete:</strong> <?= $tipos_frete[$compra['tipo_frete']] ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Valor do Frete:</strong> 
                        <span class="<?= $compra['tipo_frete'] === 'retirada_loja' ? 'valor-frete-gratis' : '' ?>">
                            <?= formatarValorFrete($compra['tipo_frete'], $compra['valor_frete']) ?>
                        </span>
                    </p>
                </div>
                <div class="col-md-4">
                    <p><strong>Subtotal:</strong> R$ <?= number_format($compra['valor_total'], 2, ',', '.') ?></p>
                </div>
            </div>
        </div>
        
        <h4 class="mb-3">Veículos Comprados</h4>
        <div class="row">
            <?php foreach ($itens as $item): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <img src="uploads/<?= htmlspecialchars($item['imagem']) ?>" class="img-fluid rounded-start" alt="<?= htmlspecialchars($item['marca'] . ' ' . $item['modelo']) ?>">
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($item['marca'] . ' ' . $item['modelo']) ?></h5>
                                    <p class="card-text">Ano: <?= htmlspecialchars($item['ano']) ?></p>
                                    <p class="card-text"><strong>Preço:</strong> R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>