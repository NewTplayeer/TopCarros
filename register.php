<?php
require 'config.php';

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $nome, $email, $senha);

    if ($stmt->execute()) {
        $mensagem = "<div class='mensagem sucesso'>Cadastro realizado com sucesso! <a href='login.php'>Entrar</a></div>";
    } else {
        $mensagem = "<div class='mensagem erro'>Erro: " . $stmt->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Registrar</title>
    <link rel="stylesheet" href="css/registro.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        @keyframes gradient {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        .container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 400px; /* Aumentei a largura */
            text-align: center;
        }

        .mensagem {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 15px;
        }

        .sucesso {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }

        .erro {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 14px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            margin-top: 15px;
            width: 100%;
        }

        button:hover {
            background-color: #45a049;
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        button:active {
            transform: scale(0.98);
        }

        a {
            display: inline-block;
            margin-top: 20px;
            color: #333;
            text-decoration: none;
            transition: color 0.3s;
            font-size: 15px;
        }

        a:hover {
            color: #4CAF50;
        }

        h2 {
            margin-bottom: 25px;
            color: #333;
            font-size: 24px;
        }
    </style>
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