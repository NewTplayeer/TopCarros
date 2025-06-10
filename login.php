<?php
session_start();
require_once 'config.php';

$erro = ''; // Inicializa a variável de erro

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Validar se email e senha não estão vazios (segurança básica)
    if (empty($email) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        // Altera a query para selecionar também o 'nome' do usuário
        $stmt = $conn->prepare("SELECT id, nome, senha FROM usuarios WHERE email = ?"); 
        
        if ($stmt === false) { // Verifica se a preparação da query falhou
            error_log("Erro na preparação da consulta de login: " . $conn->error);
            $erro = "Ocorreu um erro interno. Tente novamente mais tarde.";
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($user = $result->fetch_assoc()) {
                if (password_verify($senha, $user['senha'])) {
                    $_SESSION['usuario_id'] = $user['id'];
                    $_SESSION['usuario_email'] = $email;
                    $_SESSION['usuario_nome'] = $user['nome']; // <-- Adicionamos esta linha para salvar o nome!

                    // Redireciona para o index.php (ou dashboard.php, se preferir)
                    header("Location: index.php");
                    exit(); // Garante que o script pare após o redirecionamento
                } else {
                    $erro = "Usuário ou senha inválidos."; // Senha incorreta
                }
            } else {
                $erro = "Usuário ou senha inválidos."; // Email não encontrado
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/logins.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (!empty($erro)): ?>
            <div class="erro-danger"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="senha" placeholder="Senha" required><br>
            <button type="submit" id="loginBtn">Entrar</button>
        </form>
        <a href="register.php">Criar conta</a>
    </div>

    <script>
        // O script JavaScript que você tinha para o hover não é funcional como está.
        // Se você quiser um efeito de hover visual, use CSS (como provavelmente já faz em seu 'logins.css').
        // Este bloco de script pode ser removido ou modificado se não for necessário.
    </script>
</body>
</html>