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
                header("Location: veiculo_add.php?status=error"); // Redireciona com status de erro
                exit();
            }

            // 'isids' significa: integer, string, integer, double, string
            $stmt->bind_param("isids", $marca_id, $modelo, $ano, $preco, $imagem_nome);

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: dashboard.php?status=success&message=Veículo cadastrado com sucesso!"); // Redireciona para o dashboard com sucesso
                exit(); // É crucial usar exit() após header()
            } else {
                error_log("Erro ao executar inserção de veículo: " . $stmt->error);
                $stmt->close();
                header("Location: veiculo_add.php?status=error"); // Redireciona com status de erro
                exit();
            }
        } else {
            // Erro ao mover o arquivo
            header("Location: veiculo_add.php?status=upload_error");
            exit();
        }
    } else {
        // Nenhum arquivo de imagem enviado ou erro no upload
        header("Location: veiculo_add.php?status=no_image");
        exit();
    }
}

// busca marcas para dropdown (fora do IF para que o formulário seja exibido)
$marcas = $conn->query("SELECT id, nome FROM marcas ORDER BY nome ASC"); // Ordena as marcas
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
    <link rel="stylesheet" href="css/veiculo_add.css">
    </head>
<body>
    <div class="container-veiculo-add">
        <h1>Cadastrar Novo Veículo</h1>

        <?php
        // Exibe mensagens de status após redirecionamento (se houver)
        if (isset($_GET['status'])) {
            $message = '';
            $alert_class = '';
            switch ($_GET['status']) {
                case 'success':
                    $message = 'Veículo cadastrado com sucesso!';
                    $alert_class = 'alert-success';
                    break;
                case 'error':
                    $message = 'Erro ao cadastrar veículo. Tente novamente.';
                    $alert_class = 'alert-danger';
                    break;
                case 'upload_error':
                    $message = 'Erro ao fazer upload da imagem. Verifique o tamanho e o formato.';
                    $alert_class = 'alert-danger';
                    break;
                case 'no_image':
                    $message = 'Por favor, selecione uma imagem para o veículo.';
                    $alert_class = 'alert-warning';
                    break;
                default:
                    $message = '';
                    $alert_class = '';
            }
            if (!empty($message)) {
                echo '<div class="alert ' . $alert_class . '">' . htmlspecialchars($message) . '</div>';
            }
        }
        ?>

        <form method="post" enctype="multipart/form-data">
            <label for="marca_id">Marca:</label>
            <select name="marca_id" id="marca_id" required>
                <?php if (!empty($marcas) && $marcas->num_rows > 0): ?>
                    <?php while ($marca = $marcas->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($marca['id']) ?>"><?= htmlspecialchars($marca['nome']) ?></option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <option value="">Nenhuma marca cadastrada.</option>
                <?php endif; ?>
            </select>

            <label for="modelo">Modelo:</label>
            <input type="text" name="modelo" id="modelo" placeholder="Ex: Onix, Hilux" required>

            <label for="ano">Ano:</label>
            <input type="number" name="ano" id="ano" placeholder="Ex: 2023" required min="1900" max="<?= date('Y') + 1 ?>">

            <label for="preco">Preço (R$):</label>
            <input type="number" step="0.01" name="preco" id="preco" placeholder="Ex: 50000.00" required min="0">

            <label for="imagem">Imagem do Veículo:</label>
            <input type="file" name="imagem" id="imagem" accept="image/*" required>

            <button type="submit">Cadastrar Veículo</button>
        </form>

        <a href="dashboard.php" class="btn-back">Voltar para o Dashboard</a>
    </div>
</body>
</html>