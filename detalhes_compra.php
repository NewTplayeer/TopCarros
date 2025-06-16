<?php
require_once 'config.php'; // Inclui o arquivo de configuração do banco de dados
session_start(); // Inicia a sessão

if (!isset($_SESSION['usuario_id'])) { // Verifica se o usuário está logado
    header("Location: login.php"); // Redireciona para a página de login se não estiver logado
    exit(); // Encerra o script
}

if (!isset($_GET['id'])) { // Verifica se o ID da compra foi fornecido na URL
    header("Location: historico_compras.php"); // Redireciona para o histórico de compras se não houver ID
    exit(); // Encerra o script
}

$compra_id = $_GET['id']; // Obtém o ID da compra da URL
$usuario_id = $_SESSION['usuario_id']; // Obtém o ID do usuário logado da sessão

// Consulta para buscar os detalhes da compra, incluindo tipo e valor do frete
$sql_compra = "SELECT c.id, c.data_compra, c.valor_total, c.status, c.tipo_frete, c.valor_frete
               FROM compras c
               WHERE c.id = ? AND c.usuario_id = ?";
$stmt = $conn->prepare($sql_compra); // Prepara a consulta SQL
$stmt->bind_param("ii", $compra_id, $usuario_id); // Associa os parâmetros (ID da compra e ID do usuário)
$stmt->execute(); // Executa a consulta
$result_compra = $stmt->get_result(); // Obtém o resultado da consulta
$compra = $result_compra->fetch_assoc(); // Busca a linha do resultado como um array associativo

if (!$compra) { // Se a compra não for encontrada (ou não pertencer ao usuário)
    header("Location: historico_compras.php"); // Redireciona para o histórico de compras
    exit(); // Encerra o script
}

// Consulta para os itens (veículos) da compra
$sql_itens = "SELECT v.id, m.nome AS marca, v.modelo, v.ano, ci.preco_unitario, v.imagem
              FROM compra_itens ci
              JOIN veiculos v ON ci.veiculo_id = v.id
              JOIN marcas m ON v.marca_id = m.id
              WHERE ci.compra_id = ?";
$stmt = $conn->prepare($sql_itens); // Prepara a consulta SQL
$stmt->bind_param("i", $compra_id); // Associa o parâmetro (ID da compra)
$stmt->execute(); // Executa a consulta
$result_itens = $stmt->get_result(); // Obtém o resultado da consulta
$itens = $result_itens->fetch_all(MYSQLI_ASSOC); // Busca todas as linhas como um array associativo

// Calcular valor total da compra + valor do frete
$valor_total_com_frete = $compra['valor_total'] + $compra['valor_frete'];

// Mapear tipos de frete para nomes amigáveis.
// As CHAVES deste array AGORA CORRESPONDEM EXATAMENTE aos valores que são salvos
// na coluna `tipo_frete` do banco de dados (ex: 'Guincho Plataforma').
$tipos_frete = [
    'Retirada na Loja' => 'Retirada na Loja',
    'Transporte Especializado' => 'Transporte Especializado',
    'Guincho Plataforma' => 'Guincho Plataforma'
];

// Função para formatar o valor do frete
function formatarValorFrete($tipo_frete, $valor_frete) {
    // A condição agora compara com o nome completo do tipo de frete salvo no BD
    if ($tipo_frete === 'Retirada na Loja') {
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
                        <p>
                            <strong>Frete:</strong> 
                            <?= $tipos_frete[$compra['tipo_frete']] ?? $compra['tipo_frete'] ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="frete-info mb-4">
            <h5>Informações de Frete</h5>
            <div class="row">
                <div class="col-md-4">
                    <p>
                        <strong>Tipo de Frete:</strong> 
                        <?= $tipos_frete[$compra['tipo_frete']] ?? $compra['tipo_frete'] ?>
                    </p>
                </div>
                <div class="col-md-4">
                    <p><strong>Valor do Frete:</strong> 
                        <span class="<?= $compra['tipo_frete'] === 'Retirada na Loja' ? 'valor-frete-gratis' : '' ?>">
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