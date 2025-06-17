<?php
// veiculo_edit.php
session_start();
require 'config.php';

// Redireciona se não for admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$veiculo = null; // Variável para armazenar os dados do veículo a ser editado
$marcas = [];    // Variável para armazenar as marcas para o dropdown
$error_message = '';
$success_message = '';

// --- Lógica para buscar as marcas para o dropdown ---
$sql_marcas = "SELECT id, nome FROM marcas ORDER BY nome ASC";
$result_marcas = $conn->query($sql_marcas);
if ($result_marcas) {
    while ($row = $result_marcas->fetch_assoc()) {
        $marcas[] = $row;
    }
} else {
    $error_message = "Erro ao carregar marcas: " . $conn->error;
    error_log("Erro ao carregar marcas em veiculo_edit.php: " . $conn->error);
}

// --- Lógica para carregar os dados do veículo para edição ---
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $veiculo_id = $_GET['id'];

    $stmt = $conn->prepare("SELECT id, marca_id, modelo, ano, preco, imagem FROM veiculos WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $veiculo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $veiculo = $result->fetch_assoc();
        } else {
            $error_message = "Veículo não encontrado.";
            // Redirecionar se o veículo não existir
            header("Location: dashboard.php?status=error&message=" . urlencode("Veículo não encontrado para edição."));
            exit();
        }
        $stmt->close();
    } else {
        $error_message = "Erro na preparação da consulta de veículo: " . $conn->error;
        error_log("Erro na preparação da consulta de veículo em veiculo_edit.php: " . $conn->error);
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Se não houver ID na URL no primeiro acesso, redireciona
    header("Location: dashboard.php?status=error&message=" . urlencode("ID do veículo não especificado para edição."));
    exit();
}

// --- Lógica para processar a atualização do veículo via POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['veiculo_id'])) {
    $veiculo_id = $_POST['veiculo_id'];
    $marca_id = $_POST['marca_id'];
    $modelo = trim($_POST['modelo']);
    $ano = $_POST['ano'];
    $preco = str_replace(',', '.', trim($_POST['preco'])); // Substitui vírgula por ponto para float

    $imagem_antiga = $_POST['imagem_antiga'] ?? ''; // Pega o nome da imagem antiga
    $nova_imagem_nome = $imagem_antiga; // Mantém a imagem antiga por padrão

    // Validação básica
    if (empty($modelo) || empty($ano) || empty($preco) || !is_numeric($preco) || $preco <= 0) {
        $error_message = "Por favor, preencha todos os campos obrigatórios corretamente.";
    } else {
        // --- Processamento da imagem (se uma nova imagem foi enviada) ---
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "uploads/";
            $imageFileType = strtolower(pathinfo($_FILES["imagem"]["name"], PATHINFO_EXTENSION));
            $nova_imagem_nome = uniqid() . "." . $imageFileType; // Nome único para a nova imagem
            $target_file = $target_dir . $nova_imagem_nome;

            // Checa se o arquivo é uma imagem real
            $check = getimagesize($_FILES["imagem"]["tmp_name"]);
            if ($check === false) {
                $error_message = "O arquivo enviado não é uma imagem válida.";
            } elseif ($_FILES["imagem"]["size"] > 5000000) { // Limite de 5MB
                $error_message = "Desculpe, sua imagem é muito grande (máximo 5MB).";
            } elseif (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
                $error_message = "Desculpe, apenas JPG, JPEG, PNG e GIF são permitidos.";
            } else {
                // Tenta mover o arquivo
                if (!move_uploaded_file($_FILES["imagem"]["tmp_name"], $target_file)) {
                    $error_message = "Desculpe, houve um erro ao fazer upload da sua imagem.";
                } else {
                    // Se a imagem antiga for diferente e existir, tenta deletá-la
                    if (!empty($imagem_antiga) && $imagem_antiga !== 'default.png' && file_exists($target_dir . $imagem_antiga)) {
                        unlink($target_dir . $imagem_antiga);
                    }
                }
            }
        }

        // Se não houve erros no upload, prossegue com a atualização do BD
        if (empty($error_message)) {
            $stmt = $conn->prepare("UPDATE veiculos SET marca_id = ?, modelo = ?, ano = ?, preco = ?, imagem = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("issssi", $marca_id, $modelo, $ano, $preco, $nova_imagem_nome, $veiculo_id);
                if ($stmt->execute()) {
                    $success_message = "Veículo atualizado com sucesso!";
                    // Atualiza a variável $veiculo para refletir as novas informações no formulário
                    $veiculo['marca_id'] = $marca_id;
                    $veiculo['modelo'] = $modelo;
                    $veiculo['ano'] = $ano;
                    $veiculo['preco'] = $preco;
                    $veiculo['imagem'] = $nova_imagem_nome;

                    // Redireciona com mensagem de sucesso
                    header("Location: dashboard.php?status=success&message=" . urlencode($success_message));
                    exit();
                } else {
                    $error_message = "Erro ao atualizar veículo no banco de dados: " . $stmt->error;
                    error_log("Erro ao atualizar veículo em veiculo_edit.php: " . $stmt->error);
                }
                $stmt->close();
            } else {
                $error_message = "Erro na preparação da consulta de atualização: " . $conn->error;
                error_log("Erro na preparação da consulta de atualização em veiculo_edit.php: " . $conn->error);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Veículo - TopCarros</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .preview-imagem {
            max-width: 200px;
            height: auto;
            margin-top: 10px;
            border: 1px solid #ddd;
            padding: 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Editar Veículo</h2>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <?php if ($veiculo): // Somente mostra o formulário se o veículo foi carregado ?>
            <form action="veiculo_edit.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="veiculo_id" value="<?= htmlspecialchars($veiculo['id']) ?>">
                <input type="hidden" name="imagem_antiga" value="<?= htmlspecialchars($veiculo['imagem']) ?>">

                <div class="mb-3">
                    <label for="marca_id" class="form-label">Marca:</label>
                    <select class="form-select" id="marca_id" name="marca_id" required>
                        <?php foreach ($marcas as $marca): ?>
                            <option value="<?= htmlspecialchars($marca['id']) ?>"
                                <?= ($veiculo['marca_id'] == $marca['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($marca['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="modelo" class="form-label">Modelo:</label>
                    <input type="text" class="form-control" id="modelo" name="modelo"
                        value="<?= htmlspecialchars($veiculo['modelo']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="ano" class="form-label">Ano:</label>
                    <input type="number" class="form-control" id="ano" name="ano"
                        value="<?= htmlspecialchars($veiculo['ano']) ?>" required min="1900" max="<?= date('Y') + 1 ?>">
                </div>

                <div class="mb-3">
                    <label for="preco" class="form-label">Preço:</label>
                    <input type="text" class="form-control" id="preco" name="preco"
                        value="<?= htmlspecialchars(number_format($veiculo['preco'], 2, ',', '.')) ?>" required pattern="^\d+(\,\d{2})?$">
                    <small class="form-text text-muted">Use vírgula para centavos (ex: 50.000,00)</small>
                </div>

                <div class="mb-3">
                    <label for="imagem" class="form-label">Imagem Atual:</label><br>
                    <?php if (!empty($veiculo['imagem'])): ?>
                        <img src="uploads/<?= htmlspecialchars($veiculo['imagem']) ?>" alt="Imagem Atual" class="preview-imagem">
                    <?php else: ?>
                        <p>Nenhuma imagem atual.</p>
                    <?php endif; ?>
                    <input type="file" class="form-control mt-2" id="imagem" name="imagem" accept="image/*">
                    <small class="form-text text-muted">Selecione uma nova imagem para substituir a atual.</small>
                </div>

                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancelar e Voltar</a>
            </form>
        <?php else: ?>
            <div class="alert alert-info">
                Carregando dados do veículo ou ID inválido.
            </div>
            <a href="dashboard.php" class="btn btn-secondary mt-3">Voltar ao Dashboard</a>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>