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

    try {
        $conn->begin_transaction();

        // 1. Excluir itens do carrinho associados a este veículo (se houver uma tabela de carrinho separada)
        $sql_carrinho_itens = "DELETE FROM carrinho_itens WHERE veiculo_id = ?";
        if (!($stmt_carrinho_itens = $conn->prepare($sql_carrinho_itens))) {
            throw new mysqli_sql_exception("Erro na preparação da query 'carrinho_itens': " . $conn->error . " Query: " . $sql_carrinho_itens);
        }
        $stmt_carrinho_itens->bind_param("i", $veiculo_id);
        $stmt_carrinho_itens->execute();
        $stmt_carrinho_itens->close();

        // 2. Excluir registros em 'compra_itens' associados a este veículo
        $sql_compra_itens = "DELETE FROM compra_itens WHERE veiculo_id = ?";
        if (!($stmt_compra_itens = $conn->prepare($sql_compra_itens))) {
            throw new mysqli_sql_exception("Erro na preparação da query 'compra_itens': " . $conn->error . " Query: " . $sql_compra_itens);
        }
        $stmt_compra_itens->bind_param("i", $veiculo_id);
        $stmt_compra_itens->execute();
        $stmt_compra_itens->close();

        // 3. Excluir anúncios associados a este veículo (se necessário)
        $sql_anuncios = "DELETE FROM anuncios WHERE veiculo_id = ?";
        if (!($stmt_anuncios = $conn->prepare($sql_anuncios))) {
            throw new mysqli_sql_exception("Erro na preparação da query 'anuncios': " . $conn->error . " Query: " . $sql_anuncios);
        }
        $stmt_anuncios->bind_param("i", $veiculo_id);
        $stmt_anuncios->execute();
        $stmt_anuncios->close();

        // 4. Excluir o veículo
        $sql_veiculo = "DELETE FROM veiculos WHERE id = ?";
        if (!($stmt_veiculo = $conn->prepare($sql_veiculo))) {
            throw new mysqli_sql_exception("Erro na preparação da query 'veiculos': " . $conn->error . " Query: " . $sql_veiculo);
        }
        $stmt_veiculo->bind_param("i", $veiculo_id);

        if ($stmt_veiculo->execute()) {
            $conn->commit();
            header("Location: dashboard.php?status=success&message=" . urlencode("Veículo, itens de compra, e anúncios associados excluídos com sucesso!"));
        } else {
            // Erro na execução da query do veículo
            $conn->rollback();
            header("Location: dashboard.php?status=error&message=" . urlencode("Erro ao executar exclusão do veículo: " . $stmt_veiculo->error));
        }
        $stmt_veiculo->close();

    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        // Logar o erro completo para depuração
        error_log("Erro transacional ao excluir veículo: " . $e->getMessage());
        // Exibir uma mensagem de erro mais detalhada para o usuário durante o desenvolvimento
        header("Location: dashboard.php?status=error&message=" . urlencode("Erro de banco de dados ao excluir veículo. Detalhes: " . $e->getMessage()));
    }

} else {
    header("Location: dashboard.php?status=error&message=" . urlencode("ID do veículo inválido."));
}
exit();
?>