<?php
require 'config.php';
session_start();
$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $veiculo_id = $_POST['veiculo_id'];
    $stmt = $conn->prepare("INSERT INTO anuncios (usuario_id, veiculo_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $usuario_id, $veiculo_id);
    $stmt->execute();
    header("Location: anuncio_list.php");
}

// busca veículos para dropdown
$veiculos = $conn->query("SELECT v.id, m.nome AS marca, v.modelo FROM veiculos v JOIN marcas m ON v.marca_id = m.id");
?>
<form method="post">
    <select name="veiculo_id">
        <?php while ($v = $veiculos->fetch_assoc()): ?>
            <option value="<?= $v['id'] ?>"><?= $v['marca'] . ' ' . $v['modelo'] ?></option>
        <?php endwhile; ?>
    </select>
    <button type="submit">Criar Anúncio</button>
</form>
