<?php
require_once 'config.php';

$mensagem = ''; // Inicializa a variável mensagem para não dar erro se não houver POST

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha']; // Pega a senha diretamente do POST

    // Validação básica para evitar campos vazios
    if (empty($nome) || empty($email) || empty($senha)) {
        $mensagem = "<p class='error-message'>Por favor, preencha todos os campos.</p>";
    } else {
        // Valida se o e-mail já existe
        $stmt_check_email = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        if ($stmt_check_email) {
            $stmt_check_email->bind_param("s", $email);
            $stmt_check_email->execute();
            $stmt_check_email->store_result();

            if ($stmt_check_email->num_rows > 0) {
                $mensagem = "<p class='error-message'>Este e-mail já está cadastrado. Tente outro ou <a href='login.php'>faça login</a>.</p>";
            } else {
                // Se o e-mail não existe, procede com o cadastro
                $senha_hashed = password_hash($senha, PASSWORD_DEFAULT); // Agora sim, faz o hash da senha

                // O tipo_usuario padrão será 'cliente'. Você pode ajustar isso.
                // Certifique-se de que sua tabela 'usuarios' tenha uma coluna 'tipo_usuario'
                $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, 'cliente')"); 
                
                if ($stmt) {
                    $stmt->bind_param("sss", $nome, $email, $senha_hashed);

                    if ($stmt->execute()) {
                        // Cadastro com sucesso: define mensagem e redireciona
                        // Use header() para um redirecionamento limpo
                        header("Location: login.php?status=success&message=" . urlencode("Cadastro realizado com sucesso! Faça login."));
                        exit(); // Importante para parar a execução do script após o redirecionamento
                    } else {
                        // Erro na execução da inserção
                        $mensagem = "<p class='error-message'>Erro ao cadastrar usuário: " . $stmt->error . "</p>";
                        error_log("Erro ao cadastrar usuário: " . $stmt->error); // Loga o erro para depuração
                    }
                    $stmt->close();
                } else {
                    // Erro na preparação da query de inserção
                    $mensagem = "<p class='error-message'>Erro interno ao preparar cadastro: " . $conn->error . "</p>";
                    error_log("Erro na preparação da query de cadastro: " . $conn->error);
                }
            }
            $stmt_check_email->close();
        } else {
            // Erro na preparação da query de verificação de e-mail
            $mensagem = "<p class='error-message'>Erro interno ao verificar e-mail: " . $conn->error . "</p>";
            error_log("Erro na preparação da query de verificação de e-mail: " . $conn->error);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Registrar - TopCarros</title>
    <link rel="stylesheet" href="css/registros.css">
    <style>
        /* Estilos básicos para as mensagens de feedback */
        .success-message {
            color: green;
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
        }
        .error-message {
            color: red;
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Registrar</h2>
        <?php 
        // Exibe a mensagem gerada pelo PHP (sucesso ou erro)
        if (!empty($mensagem)) {
            echo $mensagem;
        }
        ?>
        <form method="POST">
            <input type="text" name="nome" placeholder="Nome completo" required><br>
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="senha" placeholder="Senha" required><br>
            <button type="submit" id="registerBtn">Cadastrar</button>
        </form>
        <a href="login.php">Já tem conta? Entrar</a>
    </div>

    <script>
        // O script JavaScript para mouseover/mouseout no botão é redundante,
        // pois ele já define o mesmo texto. Se a intenção é mudar o texto
        // para "Click para Cadastrar" ou algo assim, você pode ajustar.
        // Se a intenção era apenas manter "Cadastrar", pode remover este JS.
        const registerBtn = document.getElementById('registerBtn');
        
        registerBtn.addEventListener('mouseover', function() {
            this.innerHTML = 'Cadastrar'; // Já é Cadastrar, então não há mudança visível
        });
        
        registerBtn.addEventListener('mouseout', function() {
            this.innerHTML = 'Cadastrar'; // Já é Cadastrar, então não há mudança visível
        });
    </script>
</body>
</html>