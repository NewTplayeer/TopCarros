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
