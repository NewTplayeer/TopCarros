<?php
// veiculo_delete.php
require_once 'config.php';

// Verifica se um ID de veículo foi passado via GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php?status=error&message=ID de veículo inválido.");
    exit();
}

$veiculo_id = (int)$_GET['id'];

// --- Parte 1: Confirmar a exclusão (Melhor Prática) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Busca os dados do veículo para exibir na tela de confirmação
    $stmt = $conn->prepare("SELECT modelo, imagem FROM veiculos WHERE id = ?");
    if ($stmt === false) {
        error_log("Erro ao preparar busca de veículo para exclusão: " . $conn->error);
        header("Location: dashboard.php?status=error&message=Erro interno.");
        exit();
    }
    $stmt->bind_param("i", $veiculo_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $veiculo = $result->fetch_assoc();
    $stmt->close();

    if (!$veiculo) {
        header("Location: dashboard.php?status=error&message=Veículo não encontrado.");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Confirmar Exclusão</title>
    <link rel="stylesheet" href="css/login.css"> </head>
<body>
    <div class="container">
        <h2>Confirmar Exclusão de Veículo</h2>
        <p>Tem certeza que deseja deletar o veículo **"<?= htmlspecialchars($veiculo['modelo']) ?>"**?</p>
        <?php if (!empty($veiculo['imagem'])): ?>
            <p>A imagem associada **"<?= htmlspecialchars($veiculo['imagem']) ?>"** também será removida.</p>
        <?php endif; ?>
        <form method="post" action="veiculo_delete.php">
            <input type="hidden" name="id" value="<?= $veiculo_id ?>">
            <button type="submit" name="confirm_delete" value="yes">Sim, Deletar</button>
            <a href="dashboard.php" class="btn-cancel">Não, Voltar</a>
        </form>
    </div>
</body>
</html>
<?php
    exit(); // Sai do script após exibir a página de confirmação
}

// --- Parte 2: Executar a exclusão após a confirmação via POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
    // 1. Busca o nome da imagem antes de deletar o registro do DB
    $stmt_select_image = $conn->prepare("SELECT imagem FROM veiculos WHERE id = ?");
    if ($stmt_select_image === false) {
        error_log("Erro ao preparar busca de imagem para exclusão: " . $conn->error);
        header("Location: dashboard.php?status=error&message=Erro interno ao buscar imagem.");
        exit();
    }
    $stmt_select_image->bind_param("i", $veiculo_id);
    $stmt_select_image->execute();
    $result_image = $stmt_select_image->get_result();
    $veiculo_data = $result_image->fetch_assoc();
    $stmt_select_image->close();

    $imagem_path = null;
    if ($veiculo_data && !empty($veiculo_data['imagem'])) {
        $imagem_path = "uploads/" . $veiculo_data['imagem'];
    }

    // 2. Deleta o registro do banco de dados
    $stmt_delete = $conn->prepare("DELETE FROM veiculos WHERE id = ?");
    if ($stmt_delete === false) {
        error_log("Erro ao preparar exclusão de veículo: " . $conn->error);
        header("Location: dashboard.php?status=error&message=Erro interno ao deletar.");
        exit();
    }
    $stmt_delete->bind_param("i", $veiculo_id);

    if ($stmt_delete->execute()) {
        // 3. Deleta o arquivo de imagem (se existir)
        if ($imagem_path && file_exists($imagem_path)) {
            if (unlink($imagem_path)) {
                // Imagem deletada com sucesso
            } else {
                error_log("Erro ao deletar arquivo de imagem: " . $imagem_path);
                // Pode exibir um aviso, mas o registro do DB foi deletado
            }
        }
        header("Location: dashboard.php?status=success&message=Veículo deletado com sucesso.");
        exit();
    } else {
        error_log("Erro ao executar exclusão de veículo: " . $stmt_delete->error);
        header("Location: dashboard.php?status=error&message=Erro ao deletar veículo.");
        exit();
    }
    $stmt_delete->close();
}

// Se o formulário não foi submetido via POST de confirmação, redireciona de volta
header("Location: dashboard.php?status=error&message=Requisição inválida.");
exit();
?>