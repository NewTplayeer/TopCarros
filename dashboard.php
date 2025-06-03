<?php
// dashboard.php — Interface única com autenticação de tipo de usuário
session_start();
require 'config.php'; // Certifique-se de que $conn está definida aqui

// 1. Verificação de autenticação e obtenção de dados do usuário
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$usuario_nome = '';
$tipo_usuario = '';

// Buscar informações do usuário logado usando prepared statement
$stmt = $conn->prepare("SELECT nome, tipo FROM usuarios WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result_usuario = $stmt->get_result();

    if ($result_usuario->num_rows === 1) {
        $user_data = $result_usuario->fetch_assoc();
        $usuario_nome = $user_data['nome'];
        $tipo_usuario = $user_data['tipo'];
        // ARMAZENA O TIPO DE USUÁRIO NA SESSÃO
        $_SESSION['tipo_usuario'] = $user_data['tipo'];
    } else {
        // Usuário não encontrado, ou problema. Redirecionar para login.
        session_destroy(); // Destroi a sessão para evitar loops
        header("Location: login.php?status=error&message=Usuário não encontrado.");
        exit;
    }
    $stmt->close();
} else {
    // Erro na preparação da consulta SQL (pode indicar problema de conexão ou sintaxe)
    error_log("Erro na preparação da consulta de usuário: " . $conn->error);
    // Em produção, talvez um erro mais amigável ou redirecionamento
    header("Location: login.php?status=error&message=Erro interno no login."); // Redireciona em caso de erro crítico
    exit;
}

// --- Funções de Listagem (ajustadas para os links de exclusão) ---

function listarMarcas($conn) {
    $sql = "SELECT id, nome FROM marcas";
    $result = $conn->query($sql);

    if (!$result) {
        error_log("Erro ao listar marcas: " . $conn->error);
        echo "<p class='alert alert-danger'>Erro ao carregar marcas.</p>";
        return;
    }
    if ($result->num_rows === 0) {
        echo "<p>Nenhuma marca cadastrada.</p>";
        return;
    }

    echo "<ul class='list-group'>";
    while ($row = $result->fetch_assoc()) {
        $id_escaped = htmlspecialchars($row['id']);
        $nome_escaped = htmlspecialchars($row['nome']);

        echo "<li class='list-group-item d-flex justify-content-between align-items-center'>" .
             "{$nome_escaped}" .
             "<div>" .
             " <a href='marca_edit.php?id={$id_escaped}' class='btn btn-sm btn-info me-2'>Editar</a>" .
             " <a href='marca_delete.php?id={$id_escaped}' class='btn btn-sm btn-danger' onclick='return confirm(\"Tem certeza que deseja excluir esta marca? Isso também excluirá veículos e anúncios associados a ela!\");'>Excluir</a>" .
             "</div>" .
             "</li>";
    }
    echo "</ul>";
}

function listarVeiculos($conn) {
    $sql = "SELECT v.id, m.nome AS marca, v.modelo, v.ano, v.preco FROM veiculos v JOIN marcas m ON v.marca_id = m.id";
    $result = $conn->query($sql);

    if (!$result) {
        error_log("Erro ao listar veículos: " . $conn->error);
        echo "<p class='alert alert-danger'>Erro ao carregar veículos.</p>";
        return;
    }
    if ($result->num_rows === 0) {
        echo "<p>Nenhum veículo cadastrado.</p>";
        return;
    }

    echo "<ul class='list-group'>";
    while ($row = $result->fetch_assoc()) {
        $id_escaped = htmlspecialchars($row['id']);
        $marca_escaped = htmlspecialchars($row['marca']);
        $modelo_escaped = htmlspecialchars($row['modelo']);
        $ano_escaped = htmlspecialchars($row['ano']);
        $preco_escaped = htmlspecialchars(number_format($row['preco'], 2, ',', '.'));

        echo "<li class='list-group-item d-flex justify-content-between align-items-center'>" .
             "{$marca_escaped} {$modelo_escaped} - {$ano_escaped} R$ {$preco_escaped} " .
             "<div>" .
             "<a href='veiculo_edit.php?id={$id_escaped}' class='btn btn-sm btn-info me-2'>Editar</a> " .
             "<a href='veiculo_delete.php?id={$id_escaped}' class='btn btn-sm btn-danger' onclick='return confirm(\"Tem certeza que deseja excluir este veículo? Isso também excluirá todos os anúncios associados a ele!\");'>Excluir</a>" .
             "</div>" .
             "</li>";
    }
    echo "</ul>";
}

function listarAnuncios($conn) {
    $sql = "SELECT a.id, u.nome AS usuario, m.nome AS marca, v.modelo, a.status
             FROM anuncios a
             JOIN usuarios u ON a.usuario_id = u.id
             JOIN veiculos v ON a.veiculo_id = v.id
             JOIN marcas m ON v.marca_id = m.id";
    $result = $conn->query($sql);

    if (!$result) {
        error_log("Erro ao listar anúncios: " . $conn->error);
        echo "<p class='alert alert-danger'>Erro ao carregar anúncios.</p>";
        return;
    }
    if ($result->num_rows === 0) {
        echo "<p>Nenhum anúncio cadastrado.</p>";
        return;
    }

    echo "<ul class='list-group'>";
    while ($a = $result->fetch_assoc()) {
        $id_escaped = htmlspecialchars($a['id']);
        $usuario_escaped = htmlspecialchars($a['usuario']);
        $marca_escaped = htmlspecialchars($a['marca']);
        $modelo_escaped = htmlspecialchars($a['modelo']);
        $status_escaped = htmlspecialchars($a['status']);

        echo "<li class='list-group-item d-flex justify-content-between align-items-center'>" .
             "{$usuario_escaped} anunciou {$marca_escaped} {$modelo_escaped} ({$status_escaped}) " .
             "<div>" .
             // AQUI ESTÁ A MUDANÇA: O botão "Editar" agora leva para anuncio_list.php
             " <a href='anuncio_list.php' class='btn btn-sm btn-info me-2'>Ver e Editar</a> " .
             " <a href='anuncio_delete.php?id={$id_escaped}' class='btn btn-sm btn-danger' onclick='return confirm(\"Tem certeza que deseja excluir este anúncio?\");'>Excluir</a>" .
             "</div>" .
             "</li>";
    }
    echo "</ul>";
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TopCarros</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
<div class="container container-dashboard">
    <h1 class="mb-4">Bem-vindo, <?php echo htmlspecialchars($usuario_nome); ?>!</h1>
    <p class="lead">Tipo de usuário: <strong><?php echo htmlspecialchars($tipo_usuario); ?></strong></p>

    <div class="d-flex justify-content-center mb-4">
        <a href="logout.php" class="btn btn-warning">Sair</a>
        <a href="index.php" class="btn btn-secondary btn-back-to-index">Voltar ao Início</a>
    </div>

    <?php
    // Exibir mensagens de status vindas dos redirecionamentos (ex: após deletar)
    if (isset($_GET['status']) && isset($_GET['message'])) {
        $status_class = ($_GET['status'] == 'success') ? 'alert-success' : 'alert-danger';
        echo '<div class="alert ' . $status_class . ' mt-3" role="alert">';
        echo htmlspecialchars($_GET['message']);
        echo '</div>';
    }
    ?>

    <?php if ($tipo_usuario === 'admin'): ?>
        <hr class="my-4">
        <div class="admin-section">
            <h2>Gestão de Marcas</h2>
            <a href="marca_add.php" class="btn btn-primary mb-3">Adicionar Nova Marca</a>
            <?php listarMarcas($conn); ?>
        </div>

        <hr class="my-4">
        <div class="admin-section">
            <h2>Gestão de Veículos</h2>
            <a href="veiculo_add.php" class="btn btn-primary mb-3">Adicionar Novo Veículo</a>
            <?php listarVeiculos($conn); ?>
        </div>
    <?php endif; ?>

    <hr class="my-4">
    <div class="admin-section">
        <h2>Anúncios</h2>
        <a href="anuncio_add.php" class="btn btn-primary mb-3">Adicionar Novo Anúncio</a>
        <?php listarAnuncios($conn); ?>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>