<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nome, $email, $senha);

    if ($stmt->execute()) {
        echo "Usuário cadastrado com sucesso.";
    } else {
        echo "Erro ao cadastrar: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Registrar</title>
    <link rel="stylesheet" href="css/registros.css">
</head>
<body>
    <div class="container">
        <h2>Registrar</h2>
        <?php if (!empty($mensagem)) echo $mensagem; ?>
        <form method="POST">
            <input type="text" name="nome" placeholder="Nome completo" required><br>
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="senha" placeholder="Senha" required><br>
            <button type="submit" id="registerBtn">Cadastrar</button>
        </form>
        <a href="login.php">Já tem conta? Entrar</a>
    </div>

    <script>
        const registerBtn = document.getElementById('registerBtn');
        
        registerBtn.addEventListener('mouseover', function() {
            this.innerHTML = 'Cadastrar';
        });
        
        registerBtn.addEventListener('mouseout', function() {
            this.innerHTML = 'Cadastrar';
        });
    </script>
</body>
</html>