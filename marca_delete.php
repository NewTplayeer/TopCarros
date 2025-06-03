<?php
// marca_delete.php
session_start();
require 'config.php';

// Redireciona se não for admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $marca_id = $_GET['id'];

    // Prepara e executa a exclusão da marca
    $stmt = $conn->prepare("DELETE FROM marcas WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $marca_id);
        if ($stmt->execute()) {
            // Sucesso
            header("Location: dashboard.php?status=success&message=" . urlencode("Marca excluída com sucesso!"));
        } else {
            // Erro
            header("Location: dashboard.php?status=error&message=" . urlencode("Erro ao excluir marca: " . $stmt->error));
        }
        $stmt->close();
    } else {
        header("Location: dashboard.php?status=error&message=" . urlencode("Erro na preparação da exclusão da marca."));
    }
} else {
    // ID inválido
    header("Location: dashboard.php?status=error&message=" . urlencode("ID da marca inválido."));
}
exit();
?>