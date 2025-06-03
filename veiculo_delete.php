<?php
// veiculo_delete.php
session_start();
require 'config.php';

// Redireciona se não for admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $veiculo_id = $_GET['id'];

    // Cuidado: Excluir um veículo pode causar problemas de integridade referencial
    // se houver anúncios associados a ele. Considere:
    // 1. Excluir os anúncios relacionados primeiro.
    // 2. Definir o veiculo_id dos anúncios como NULL (se a coluna permitir).
    // 3. Usar CASCADE DELETE na foreign key (definido no SQL do BD).
    // Para simplificar, vou excluir os anúncios primeiro.

    try {
        $conn->begin_transaction();

        // 1. Excluir anúncios associados a este veículo
        $stmt_anuncios = $conn->prepare("DELETE FROM anuncios WHERE veiculo_id = ?");
        $stmt_anuncios->bind_param("i", $veiculo_id);
        $stmt_anuncios->execute();
        $stmt_anuncios->close();

        // 2. Excluir o veículo
        $stmt_veiculo = $conn->prepare("DELETE FROM veiculos WHERE id = ?");
        $stmt_veiculo->bind_param("i", $veiculo_id);
        if ($stmt_veiculo->execute()) {
            $conn->commit();
            header("Location: dashboard.php?status=success&message=" . urlencode("Veículo e anúncios associados excluídos com sucesso!"));
        } else {
            $conn->rollback();
            header("Location: dashboard.php?status=error&message=" . urlencode("Erro ao excluir veículo: " . $stmt_veiculo->error));
        }
        $stmt_veiculo->close();

    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        error_log("Erro transacional ao excluir veículo: " . $e->getMessage());
        header("Location: dashboard.php?status=error&message=" . urlencode("Erro de banco de dados ao excluir veículo."));
    }

} else {
    header("Location: dashboard.php?status=error&message=" . urlencode("ID do veículo inválido."));
}
exit();
?>