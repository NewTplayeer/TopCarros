<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $conn->prepare("SELECT id, senha FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($senha, $user['senha'])) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_email'] = $email;
            header("Location: dashboard.php");
            exit();
        }
    }
    echo "Usuário ou senha inválidos.";
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (!empty($erro)): ?>
            <div class="erro-danger"><?php echo $erro; ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="senha" placeholder="Senha" required><br>
            <button type="submit" id="loginBtn">Entrar</button>
        </form>
        <a href="register.php">Criar conta</a>
    </div>

    <script>
        const loginBtn = document.getElementById('loginBtn');
        
        loginBtn.addEventListener('mouseover', function() {
            this.innerHTML = 'Entrar';
        });
        
        loginBtn.addEventListener('mouseout', function() {
            this.innerHTML = 'Entrar';
        });
    </script>
</body>
</html>