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
    } else {
        // Usuário não encontrado, ou problema. Redirecionar para login.
        session_destroy(); // Destroi a sessão para evitar loops
        header("Location: login.php");
        exit;
    }
    $stmt->close();
} else {
    // Erro na preparação da consulta SQL (pode indicar problema de conexão ou sintaxe)
    error_log("Erro na preparação da consulta de usuário: " . $conn->error);
    // Em produção, talvez um erro mais amigável ou redirecionamento
    header("Location: login.php"); // Redireciona em caso de erro crítico
    exit;
}

// REMOVIDA: A linha abaixo estava incorreta e sem sentido aqui.
// $_SESSION['usuario_id'] = $user['id'];

// --- Funções de Listagem (melhoradas com prepared statements como boa prática) ---

function listarMarcas($conn) {
    // Para consultas simples sem parâmetros, prepared statements são menos críticos, mas boa prática.
    // O foco aqui é na segurança contra injeção quando há parâmetros.
    $sql = "SELECT id, nome FROM marcas";
    $result = $conn->query($sql);

    if (!$result) {
        error_log("Erro ao listar marcas: " . $conn->error);
        echo "<p>Erro ao carregar marcas.</p>";
        return;
    }

    while ($row = $result->fetch_assoc()) {
        // Escapar saída para HTML
        $id_escaped = htmlspecialchars($row['id']);
        $nome_escaped = htmlspecialchars($row['nome']);

        echo $nome_escaped .
             " <a href='marca_edit.php?id={$id_escaped}'>Editar</a>" .
             " <a href='marca_delete.php?id={$id_escaped}'>Excluir</a><br>";
    }
}

function listarVeiculos($conn) {
    $sql = "SELECT v.id, m.nome AS marca, v.modelo, v.ano, v.preco FROM veiculos v JOIN marcas m ON v.marca_id = m.id";
    $result = $conn->query($sql);

    if (!$result) {
        error_log("Erro ao listar veículos: " . $conn->error);
        echo "<p>Erro ao carregar veículos.</p>";
        return;
    }

    while ($row = $result->fetch_assoc()) {
        // Escapar saída para HTML
        $id_escaped = htmlspecialchars($row['id']);
        $marca_escaped = htmlspecialchars($row['marca']);
        $modelo_escaped = htmlspecialchars($row['modelo']);
        $ano_escaped = htmlspecialchars($row['ano']);
        $preco_escaped = htmlspecialchars(number_format($row['preco'], 2, ',', '.')); // Formatar preço

        echo "{$marca_escaped} {$modelo_escaped} - {$ano_escaped} R$ {$preco_escaped} ".
             "<a href='veiculo_edit.php?id={$id_escaped}'>Editar</a> ".
             "<a href='veiculo_delete.php?id={$id_escaped}'>Excluir</a><br>";
    }
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
        echo "<p>Erro ao carregar anúncios.</p>";
        return;
    }

    while ($a = $result->fetch_assoc()) {
        // Escapar saída para HTML
        $id_escaped = htmlspecialchars($a['id']);
        $usuario_escaped = htmlspecialchars($a['usuario']);
        $marca_escaped = htmlspecialchars($a['marca']);
        $modelo_escaped = htmlspecialchars($a['modelo']);
        $status_escaped = htmlspecialchars($a['status']);

        echo "{$usuario_escaped} anunciou {$marca_escaped} {$modelo_escaped} ({$status_escaped}) ".
             "<a href='anuncio_edit.php?id={$id_escaped}'>Editar</a> ".
             "<a href='anuncio_delete.php?id={$id_escaped}'>Excluir</a><br>";
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h2 { margin-top: 40px; }
        .admin-only { background: #f8f8f8; padding: 10px; margin: 10px 0; border-left: 5px solid #ccc; }
        a { text-decoration: none; color: #007bff; margin-right: 10px; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<h1>Bem-vindo, <?php echo htmlspecialchars($usuario_nome); ?>!</h1>
<p>Tipo de usuário: <strong><?php echo htmlspecialchars($tipo_usuario); ?></strong></p>
<a href="logout.php">Sair</a>

<?php if ($tipo_usuario === 'admin'): ?>
    <hr>
    <h2>Gestão de Marcas</h2>
    <a href="marca_add.php">Adicionar Nova Marca</a><br><br>
    <div class="admin-only"><?php listarMarcas($conn); ?></div>

    <hr>
    <h2>Gestão de Veículos</h2>
    <a href="veiculo_add.php">Adicionar Novo Veículo</a><br><br>
    <div class="admin-only"><?php listarVeiculos($conn); ?></div>
<?php endif; ?>

<hr>
<h2>Anúncios</h2>
<a href="anuncio_add.php">Adicionar Novo Anúncio</a><br><br>
<div><?php listarAnuncios($conn); ?></div>

</body>
</html>