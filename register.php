<?php
require 'config.php';

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT); // senha segura

    $sql = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $nome, $email, $senha);

    if ($stmt->execute()) {
        $mensagem = "<p class='mensagem sucesso'>Cadastro realizado com sucesso! <a href='login.php'>Entrar</a></p>";
    } else {
        $mensagem = "<p class='mensagem erro'>Erro: " . $stmt->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Registrar</title>
    <link rel="stylesheet" href="css/registro.css">
</head>
<body>
    <div class="container">
        <h2>Registrar</h2>
        <?php if (!empty($mensagem)) echo $mensagem; ?>
        <form method="POST">
            Nome: <input type="text" name="nome" required><br>
            Email: <input type="email" name="email" required><br>
            Senha: <input type="password" name="senha" required><br>
            <button type="submit">Cadastrar</button>
        </form>
        <a href="login.php">Já tem conta? Entrar</a>
    </div>
</body>
</html>
