<?php
// anuncio_edit.php
session_start();
require 'config.php';

// Redireciona se não estiver logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$tipo_usuario_logado = '';

// Obter tipo de usuário para permissões
$stmt_user_type = $conn->prepare("SELECT tipo FROM usuarios WHERE id = ?");
$stmt_user_type->bind_param("i", $usuario_id);
$stmt_user_type->execute();
$result_user_type = $stmt_user_type->get_result();
$user_data = $result_user_type->fetch_assoc();
$tipo_usuario_logado = $user_data['tipo'];
$stmt_user_type->close();


// Lógica para carregar o anúncio para edição, processar o formulário POST, etc.
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $anuncio_id = $_GET['id'];

    // Buscar dados do anúncio e verificar permissão
    $stmt_anuncio = $conn->prepare("SELECT usuario_id, veiculo_id, status FROM anuncios WHERE id = ?");
    $stmt_anuncio->bind_param("i", $anuncio_id);
    $stmt_anuncio->execute();
    $result_anuncio = $stmt_anuncio->get_result();
    $anuncio_data = $result_anuncio->fetch_assoc();
    $stmt_anuncio->close();

    if (!$anuncio_data || ($anuncio_data['usuario_id'] != $usuario_id && $tipo_usuario_logado !== 'admin')) {
        $_SESSION['mensagem_erro'] = "Você não tem permissão para editar este anúncio ou ele não existe.";
        header("Location: dashboard.php?status=error&message=" . urlencode($_SESSION['mensagem_erro']));
        exit();
    }

    // Você também precisaria buscar os detalhes do veículo para mostrar
    // e as marcas para o dropdown (se a edição permitir mudar o veículo)
    $veiculo_id_selecionado = $anuncio_data['veiculo_id'];
    $anuncio_status = $anuncio_data['status'];

    // Exemplo: buscar veículos para um dropdown (se permitir mudar o veículo)
    $veiculos = $conn->query("SELECT v.id, m.nome AS marca, v.modelo FROM veiculos v JOIN marcas m ON v.marca_id = m.id ORDER BY m.nome, v.modelo");

} else {
    $_SESSION['mensagem_erro'] = "ID do anúncio inválido para edição.";
    header("Location: dashboard.php?status=error&message=" . urlencode($_SESSION['mensagem_erro']));
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING); // Exemplo: permitir mudar status
    $new_veiculo_id = filter_input(INPUT_POST, 'veiculo_id', FILTER_VALIDATE_INT); // Se for editável

    // Lógica de atualização aqui
    // Exemplo: Atualizar apenas o status (simplificado)
    $update_sql = "UPDATE anuncios SET status = ?";
    $update_params = "s";
    $update_values = [$new_status];

    // Se a edição permitir mudar o veículo
    if ($new_veiculo_id && $new_veiculo_id !== $veiculo_id_selecionado) {
        $update_sql .= ", veiculo_id = ?";
        $update_params .= "i";
        $update_values[] = $new_veiculo_id;
    }

    $update_sql .= " WHERE id = ?";
    $update_params .= "i";
    $update_values[] = $anuncio_id;

    // Adicionar condição de segurança para o usuário comum
    if ($tipo_usuario_logado !== 'admin') {
        $update_sql .= " AND usuario_id = ?";
        $update_params .= "i";
        $update_values[] = $usuario_id;
    }

    $stmt_update = $conn->prepare($update_sql);
    if ($stmt_update) {
        // Usa call_user_func_array para bind_param com array de valores
        call_user_func_array(array($stmt_update, 'bind_param'), array_merge([$update_params], $update_values));

        if ($stmt_update->execute()) {
            $_SESSION['mensagem_sucesso'] = "Anúncio atualizado com sucesso!";
        } else {
            $_SESSION['mensagem_erro'] = "Erro ao atualizar anúncio: " . $stmt_update->error;
        }
        $stmt_update->close();
    } else {
        $_SESSION['mensagem_erro'] = "Erro na preparação da atualização do anúncio: " . $conn->error;
    }
    header("Location: dashboard.php?status=" . (isset($_SESSION['mensagem_sucesso']) ? 'success' : 'error') . "&message=" . urlencode(isset($_SESSION['mensagem_sucesso']) ? $_SESSION['mensagem_sucesso'] : $_SESSION['mensagem_erro']));
    exit();
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Anúncio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/veiculo_add.css"> <style>
        .container-form { /* Estilo similar aos seus outros formulários */
            background-color: var(--white);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            max-width: 500px;
            width: 100%;
            text-align: center;
            margin-top: 30px; /* Para não colar no topo */
        }
        .container-form h2 {
            color: var(--primary-color);
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-group select, .form-group input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
        }
        .btn-update {
            margin-top: 20px;
            background-color: var(--success-color);
            color: var(--white);
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn-update:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container-form">
        <h2>Editar Anúncio</h2>

        <?php
        // Exibir mensagens de status
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
            <div class="form-group">
                <label for="veiculo_id">Veículo Anunciado:</label>
                <select name="veiculo_id" id="veiculo_id" <?php echo ($tipo_usuario_logado === 'admin' ? '' : 'disabled'); ?>>
                    <?php if (!empty($veiculos) && $veiculos->num_rows > 0): ?>
                        <?php while ($v = $veiculos->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($v['id']) ?>"
                                <?= ($v['id'] == $veiculo_id_selecionado) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?>
                            </option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="">Nenhum veículo disponível.</option>
                    <?php endif; ?>
                </select>
                <?php if ($tipo_usuario_logado !== 'admin'): ?>
                    <small class="form-text text-muted">Apenas administradores podem alterar o veículo de um anúncio.</small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="status">Status do Anúncio:</label>
                <select name="status" id="status" required>
                    <option value="ativo" <?= ($anuncio_status == 'ativo' ? 'selected' : '') ?>>Ativo</option>
                    <option value="vendido" <?= ($anuncio_status == 'vendido' ? 'selected' : '') ?>>Vendido</option>
                </select>
            </div>

            <button type="submit" class="btn btn-update">Atualizar Anúncio</button>
        </form>
        <a href="dashboard.php" class="btn-back">Voltar para o Dashboard</a>
    </div>
</body>
</html>