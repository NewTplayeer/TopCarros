<?php
// dashboard.php — Interface única com autenticação de tipo de usuário
session_start();
require 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$usuario_nome = $_SESSION['usuario_nome'];
$tipo_usuario = $_SESSION['usuario_tipo'];

function listarMarcas($conn) {
    $result = $conn->query("SELECT * FROM marcas");
    while ($row = $result->fetch_assoc()) {
        echo $row['nome'] .
             " <a href='marca_edit.php?id={$row['id']}'>Editar</a>" .
             " <a href='marca_delete.php?id={$row['id']}'>Excluir</a><br>";
    }
}

function listarVeiculos($conn) {
    $sql = "SELECT v.id, m.nome AS marca, v.modelo, v.ano, v.preco FROM veiculos v JOIN marcas m ON v.marca_id = m.id";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        echo "{$row['marca']} {$row['modelo']} - {$row['ano']} R$ {$row['preco']} ".
             "<a href='veiculo_edit.php?id={$row['id']}'>Editar</a> ".
             "<a href='veiculo_delete.php?id={$row['id']}'>Excluir</a><br>";
    }
}

function listarAnuncios($conn) {
    $sql = "SELECT a.id, u.nome AS usuario, m.nome AS marca, v.modelo, a.status
            FROM anuncios a
            JOIN usuarios u ON a.usuario_id = u.id
            JOIN veiculos v ON a.veiculo_id = v.id
            JOIN marcas m ON v.marca_id = m.id";
    $result = $conn->query($sql);
    while ($a = $result->fetch_assoc()) {
        echo "{$a['usuario']} anunciou {$a['marca']} {$a['modelo']} ({$a['status']}) ".
             "<a href='anuncio_edit.php?id={$a['id']}'>Editar</a> ".
             "<a href='anuncio_delete.php?id={$a['id']}'>Excluir</a><br>";
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
        .admin-only { background: #f8f8f8; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>
<h1>Bem-vindo, <?php echo htmlspecialchars($usuario_nome); ?>!</h1>
<p>Tipo de usuário: <strong><?php echo $tipo_usuario; ?></strong></p>
<a href="logout.php">Sair</a>

<?php if ($tipo_usuario === 'admin'): ?>
    <h2>Gestão de Marcas</h2>
    <div class="admin-only"><?php listarMarcas($conn); ?></div>

    <h2>Gestão de Veículos</h2>
    <div class="admin-only"><?php listarVeiculos($conn); ?></div>
<?php endif; ?>

<h2>Anúncios</h2>
<div><?php listarAnuncios($conn); ?></div>

</body>
</html>
