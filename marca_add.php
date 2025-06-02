<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $stmt = $conn->prepare("INSERT INTO marcas (nome) VALUES (?)");
    $stmt->bind_param("s", $nome);
    $stmt->execute();
    header("Location: marca_list.php");
}
?>
<form method="post">
    <input type="text" name="nome" placeholder="Nome da marca" required>
    <button type="submit">Cadastrar Marca</button>
</form>
