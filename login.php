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
            header("Location: ../dashboard.php");
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
            width: 400px; /* Aumentado para 400px */
            text-align: center;
        }

        .erro-danger {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 15px;
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