<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Adicione debug para verificar o ID do usuário
error_log("Usuário ID: " . $usuario_id);

// Consulta melhorada com tratamento de erros
try {
    $sql_compras = "SELECT c.id, c.data_compra, c.valor_total, c.status,
                   GROUP_CONCAT(v.modelo SEPARATOR ', ') AS veiculos,
                   GROUP_CONCAT(m.nome SEPARATOR ', ') AS marcas
                   FROM compras c
                   JOIN compra_itens ci ON c.id = ci.compra_id
                   JOIN veiculos v ON ci.veiculo_id = v.id
                   JOIN marcas m ON v.marca_id = m.id
                   WHERE c.usuario_id = ?
                   GROUP BY c.id
                   ORDER BY c.data_compra DESC";

    $stmt = $conn->prepare($sql_compras);
    if (!$stmt) {
        throw new Exception("Erro ao preparar consulta: " . $conn->error);
    }
    
    $stmt->bind_param("i", $usuario_id);
    if (!$stmt->execute()) {
        throw new Exception("Erro ao executar consulta: " . $stmt->error);
    }
    
    $result_compras = $stmt->get_result();
    $compras = $result_compras->fetch_all(MYSQLI_ASSOC);
    
    // Debug: ver resultados da consulta
    error_log("Número de compras encontradas: " . count($compras));
    
} catch (Exception $e) {
    error_log("Erro no histórico de compras: " . $e->getMessage());
    $compras = [];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Compras - TopCarros</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/index.css">
    <style>
        .status-pendente { color: #ffc107; }
        .status-pago { color: #28a745; }
        .status-cancelado { color: #dc3545; }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <section class="container py-5">
        <h2 class="text-center mb-4">Histórico de Compras</h2>
        
        <?php if (empty($compras)): ?>
            <div class="alert alert-info">
                Você ainda não realizou nenhuma compra.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Data da Compra</th>
                            <th>Veículos</th>
                            <th>Valor Total</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($compras as $compra): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($compra['data_compra'])) ?></td>
                                <td>
                                    <?= htmlspecialchars($compra['marcas']) ?> - 
                                    <?= htmlspecialchars($compra['veiculos']) ?>
                                </td>
                                <td>R$ <?= number_format($compra['valor_total'], 2, ',', '.') ?></td>
                                <td>
                                    <span class="status-<?= $compra['status'] ?>">
                                        <?= ucfirst($compra['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="detalhes_compra.php?id=<?= $compra['id'] ?>" class="btn btn-sm btn-outline-primary">Detalhes</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <footer class="bg-dark text-white text-center py-4">
    <p>&copy; 2025 TopCarros. Todos os direitos reservados.</p>
</footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>