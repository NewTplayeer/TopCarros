<?php
require 'config.php';

// Verificação para garantir que o formulário foi submetido via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validação e sanitização básica das entradas
    $marca_id = filter_input(INPUT_POST, 'marca_id', FILTER_VALIDATE_INT);
    $modelo = htmlspecialchars(trim($_POST['modelo']));
    $ano = filter_input(INPUT_POST, 'ano', FILTER_VALIDATE_INT);
    $preco = filter_input(INPUT_POST, 'preco', FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    
    // Verificação de upload de imagem
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $imagem_nome = basename($_FILES['imagem']['name']); // Pega apenas o nome do arquivo
        $target_dir = "uploads/"; // Pasta onde as imagens serão salvas
        $target_file = $target_dir . $imagem_nome;

        // Verifica se a pasta de uploads existe, se não, cria
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Move o arquivo uploaded
        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $target_file)) {
            // Agora, insira no banco de dados
            $stmt = $conn->prepare("INSERT INTO veiculos (marca_id, modelo, ano, preco, imagem) VALUES (?, ?, ?, ?, ?)");
            
            // Verificação de erro na preparação da query
            if ($stmt === false) {
                error_log("Erro na preparação da query de inserção de veículo: " . $conn->error);
                // Em ambiente de produção, não exiba detalhes do erro para o usuário.
                // echo "Erro interno ao cadastrar veículo. Tente novamente mais tarde.";
                header("Location: veiculo_add.php?status=error"); // Redireciona com status de erro
                exit();
            }

            // 'isids' significa: integer, string, integer, double, string
            $stmt->bind_param("isids", $marca_id, $modelo, $ano, $preco, $imagem_nome);
            
            if ($stmt->execute()) {
                // REDIRECIONAMENTO CORRIGIDO: Para o index.php
                header("Location: index.php?status=success");
                exit(); // É crucial usar exit() após header()
            } else {
                error_log("Erro ao executar inserção de veículo: " . $stmt->error);
                // echo "Erro ao cadastrar veículo: " . $stmt->error;
                header("Location: veiculo_add.php?status=error"); // Redireciona com status de erro
                exit();
            }
            $stmt->close();
        } else {
            // Erro ao mover o arquivo
            // echo "Erro ao fazer upload da imagem.";
            header("Location: veiculo_add.php?status=upload_error");
            exit();
        }
    } else {
        // Nenhum arquivo de imagem enviado ou erro no upload
        // echo "Por favor, selecione uma imagem.";
        header("Location: veiculo_add.php?status=no_image");
        exit();
    }
}

// busca marcas para dropdown (fora do IF para que o formulário seja exibido)
$marcas = $conn->query("SELECT id, nome FROM marcas");
if ($marcas === false) {
    error_log("Erro ao buscar marcas: " . $conn->error);
    $marcas = []; // Garante que $marcas seja um array vazio em caso de erro
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Veículo</title>
    </head>
<body>
    <h1>Cadastrar Novo Veículo</h1>
    
    <?php 
    // Exibe mensagens de status após redirecionamento (se houver)
    if (isset($_GET['status'])) {
        if ($_GET['status'] == 'success') {
            echo '<p style="color: green;">Veículo cadastrado com sucesso!</p>';
        } elseif ($_GET['status'] == 'error') {
            echo '<p style="color: red;">Erro ao cadastrar veículo. Tente novamente.</p>';
        } elseif ($_GET['status'] == 'upload_error') {
            echo '<p style="color: red;">Erro ao fazer upload da imagem.</p>';
        } elseif ($_GET['status'] == 'no_image') {
            echo '<p style="color: orange;">Por favor, selecione uma imagem para o veículo.</p>';
        }
    }
    ?>

    <form method="post" enctype="multipart/form-data">
        <label for="marca_id">Marca:</label><br>
        <select name="marca_id" id="marca_id" required>
            <?php while ($marca = $marcas->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($marca['id']) ?>"><?= htmlspecialchars($marca['nome']) ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label for="modelo">Modelo:</label><br>
        <input type="text" name="modelo" id="modelo" placeholder="Modelo" required><br><br>

        <label for="ano">Ano:</label><br>
        <input type="number" name="ano" id="ano" placeholder="Ano" required><br><br>

        <label for="preco">Preço:</label><br>
        <input type="number" step="0.01" name="preco" id="preco" placeholder="Preço" required><br><br>

        <label for="imagem">Imagem:</label><br>
        <input type="file" name="imagem" id="imagem" required><br><br>

        <button type="submit">Cadastrar Veículo</button>
    </form>
    <br>
    <a href="index.php">Voltar ao Catálogo</a>
</body>
</html>