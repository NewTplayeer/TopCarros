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
        $stmt = $conn->prepare("SELECT id, senha FROM usuarios WHERE email = ?");
        
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
                    // Redireciona para o dashboard.php
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Login</title>
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
        // Este script não está fazendo nada útil, pois o texto é sempre 'Entrar'.
        // Se você não planeja mudar o texto no hover via JS, pode remover este script.
        // Se quiser um efeito de hover visual, use CSS (como já está no seu login.css).

        /*
        const loginBtn = document.getElementById('loginBtn');
        
        loginBtn.addEventListener('mouseover', function() {
            this.innerHTML = 'Fazer Login'; // Exemplo: Muda o texto ao passar o mouse
        });
        
        loginBtn.addEventListener('mouseout', function() {
            this.innerHTML = 'Entrar'; // Volta o texto ao tirar o mouse
        });
        */
        // Remova ou modifique o script acima se ele não for necessário ou funcional
    </script>
</body>
</html>