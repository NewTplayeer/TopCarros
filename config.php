<?php
$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "topcarros";

$conn = new mysqli($host, $usuario, $senha, $banco, 3306);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>