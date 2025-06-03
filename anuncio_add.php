<?php
require 'config.php';
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $veiculo_id = filter_input(INPUT_POST, 'veiculo_id', FILTER_VALIDATE_INT);

    if ($veiculo_id) {
        $stmt = $conn->prepare("INSERT INTO anuncios (usuario_id, veiculo_id) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param("ii", $usuario_id, $veiculo_id);
            if ($stmt->execute()) {
                $_SESSION['mensagem_sucesso'] = "Anúncio criado com sucesso!";
            } else {
                $_SESSION['mensagem_erro'] = "Erro ao criar anúncio: " . $conn->error;
            }
            $stmt->close();
        } else {
            $_SESSION['mensagem_erro'] = "Erro na preparação da consulta de anúncio: " . $conn->error;
        }
    } else {
        $_SESSION['mensagem_erro'] = "Seleção de veículo inválida.";
    }
    header("Location: anuncio_list.php"); // Redireciona para a lista de anúncios
    exit();
}

// busca veículos para dropdown
// Adicione um ORDER BY para a lista ser mais organizada
$veiculos = $conn->query("SELECT v.id, m.nome AS marca, v.modelo FROM veiculos v JOIN marcas m ON v.marca_id = m.id ORDER BY m.nome, v.modelo");

// Verifica se a consulta de veículos retornou erro
if ($veiculos === false) {
    error_log("Erro ao buscar veículos para anúncio: " . $conn->error);
    $veiculos = []; // Garante que $veiculos seja um array vazio em caso de erro
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Anúncio - TopCarros</title>
    <link rel="stylesheet" href="css/anunciar.css">
    </head>
<body>
    <div class="container-anunciar">
        <h1>Criar Novo Anúncio</h1>

        <?php
        // Exibir mensagens de status (opcional, mas recomendado)
        if (isset($_SESSION['mensagem_sucesso'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['mensagem_sucesso']) . '</div>';
            unset($_SESSION['mensagem_sucesso']);
        }
        if (isset($_SESSION['mensagem_erro'])) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['mensagem_erro']) . '</div>';
            unset($_SESSION['mensagem_erro']);
        }
        ?>

        <form method="post">
            <label for="veiculo_id">Selecione o Veículo para Anunciar:</label>
            <select name="veiculo_id" id="veiculo_id" required>
                <?php if (!empty($veiculos) && $veiculos->num_rows > 0): ?>
                    <?php while ($v = $veiculos->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($v['id']) ?>"><?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?></option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <option value="">Nenhum veículo disponível para anúncio.</option>
                <?php endif; ?>
            </select>
            <button type="submit">Criar Anúncio</button>
        </form>
        <a href="dashboard.php" class="btn-back">Voltar para o Dashboard</a>
    </div>
</body>
</html>