<?php
// marca_add.php
require 'config.php'; // Inclui a conexão com o banco de dados

$mensagem = ''; // Variável para armazenar mensagens de status

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitização e Validação do Nome da Marca
    $nome_marca = htmlspecialchars(trim($_POST['nome']));

    if (empty($nome_marca)) {
        $mensagem = "<div class='alert alert-warning'>O nome da marca não pode ser vazio.</div>";
    } else {
        // 2. Verifica se a marca já existe no banco de dados
        $stmt_check = $conn->prepare("SELECT id FROM marcas WHERE nome = ?");
        if ($stmt_check === false) {
            error_log("Erro na preparação da consulta de verificação de marca: " . $conn->error);
            $mensagem = "<div class='alert alert-danger'>Erro interno ao verificar marca existente.</div>";
        } else {
            $stmt_check->bind_param("s", $nome_marca);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                $mensagem = "<div class='alert alert-info'>A marca '{$nome_marca}' já existe.</div>";
            } else {
                // 3. Insere a nova marca
                $stmt_insert = $conn->prepare("INSERT INTO marcas (nome) VALUES (?)");
                if ($stmt_insert === false) {
                    error_log("Erro na preparação da consulta de inserção de marca: " . $conn->error);
                    $mensagem = "<div class='alert alert-danger'>Erro interno ao cadastrar marca.</div>";
                } else {
                    $stmt_insert->bind_param("s", $nome_marca);
                    if ($stmt_insert->execute()) {
                        // 4. Redirecionamento com mensagem de sucesso
                        header("Location: dashboard.php?status=success&message=" . urlencode("Marca '{$nome_marca}' cadastrada com sucesso!"));
                        exit();
                    } else {
                        // 5. Tratamento de erro na execução da inserção
                        error_log("Erro ao executar inserção de marca: " . $stmt_insert->error);
                        $mensagem = "<div class='alert alert-danger'>Erro ao cadastrar marca: " . htmlspecialchars($stmt_insert->error) . "</div>";
                    }
                    $stmt_insert->close();
                }
            }
            $stmt_check->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Marca</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/form_pages.css"> <style>
        /* Estilos básicos para centralizar o formulário */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f8f9fa; /* Um fundo claro padrão do Bootstrap */
            font-family: Arial, sans-serif;
        }
        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        .form-container h2 {
            margin-bottom: 25px;
            color: #343a40;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Cadastrar Nova Marca</h2>
        <?php if (!empty($mensagem)): ?>
            <?php echo $mensagem; ?>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label for="nome_marca" class="form-label visually-hidden">Nome da Marca</label>
                <input type="text" class="form-control" id="nome_marca" name="nome" placeholder="Nome da marca" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Cadastrar Marca</button>
        </form>
        <a href="dashboard.php" class="btn btn-secondary mt-3 w-100">Voltar ao Dashboard</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>