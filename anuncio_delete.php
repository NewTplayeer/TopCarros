<?php
// anuncio_delete.php
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php?status=error&message=ID de anúncio inválido.");
    exit();
}

$anuncio_id = (int)$_GET['id'];

// --- Parte 1: Confirmar a exclusão ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Opcional: buscar detalhes do anúncio para exibição na confirmação
    $stmt = $conn->prepare("SELECT a.id, u.nome AS usuario_nome, v.modelo FROM anuncios a JOIN usuarios u ON a.usuario_id = u.id JOIN veiculos v ON a.veiculo_id = v.id WHERE a.id = ?");
    if ($stmt === false) {
        error_log("Erro ao preparar busca de anúncio para exclusão: " . $conn->error);
        header("Location: dashboard.php?status=error&message=Erro interno.");
        exit();
    }
    $stmt->bind_param("i", $anuncio_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $anuncio = $result->fetch_assoc();
    $stmt->close();

    if (!$anuncio) {
        header("Location: dashboard.php?status=error&message=Anúncio não encontrado.");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Confirmar Exclusão de Anúncio</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="container">
        <h2>Confirmar Exclusão de Anúncio</h2>
        <p>Tem certeza que deseja deletar o anúncio do veículo **"<?= htmlspecialchars($anuncio['modelo']) ?>"** feito por **"<?= htmlspecialchars($anuncio['usuario_nome']) ?>"**?</p>
        <form method="post" action="anuncio_delete.php">
            <input type="hidden" name="id" value="<?= $anuncio_id ?>">
            <button type="submit" name="confirm_delete" value="yes">Sim, Deletar</button>
            <a href="dashboard.php" class="btn-cancel">Não, Voltar</a>
        </form>
    </div>
</body>
</html>
<?php
    exit();
}

// --- Parte 2: Executar a exclusão após a confirmação ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
    $stmt = $conn->prepare("DELETE FROM anuncios WHERE id = ?");
    if ($stmt === false) {
        error_log("Erro ao preparar exclusão de anúncio: " . $conn->error);
        header("Location: dashboard.php?status=error&message=Erro interno ao deletar.");
        exit();
    }
    $stmt->bind_param("i", $anuncio_id);

    if ($stmt->execute()) {
        header("Location: dashboard.php?status=success&message=Anúncio deletado com sucesso.");
        exit();
    } else {
        error_log("Erro ao executar exclusão de anúncio: " . $stmt->error);
        header("Location: dashboard.php?status=error&message=Erro ao deletar anúncio.");
        exit();
    }
    $stmt->close();
}

header("Location: dashboard.php?status=error&message=Requisição inválida.");
exit();
?>