<?php
$host = "localhost";
$usuario = "root";
$senha = "root";
$banco = "topcarros";

$conn = new mysqli($host, $usuario, $senha, $banco, 3307);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>

<!-- Padrão sem senha e sem porta 3307 -->
<!-- <?php
$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "topcarros";

$conn = new mysqli($host, $usuario, $senha, $banco, 3306);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?> -->
