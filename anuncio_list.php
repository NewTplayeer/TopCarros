<?php
require 'config.php';
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

$sql = "
    SELECT
        a.id AS anuncio_id,
        v.modelo,
        m.nome AS marca,
        v.ano,
        v.preco,
        v.imagem
    FROM
        anuncios a
    JOIN
        veiculos v ON a.veiculo_id = v.id
    JOIN
        marcas m ON v.marca_id = m.id
    WHERE
        a.usuario_id = ? ORDER BY a.id DESC
";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    // Isso significa que a query falhou. Exiba o erro do MySQL.
    die('Erro na preparação da consulta: ' . $conn->error . ' Query: ' . $sql);
}

$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$anuncios = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Anúncios - TopCarros</title>
    <link rel="stylesheet" href="css/anuncio_list.css">
    </head>
<body>
    <div class="container-anuncio-list">
        <h1>Meus Anúncios</h1>

        <div class="top-actions">
            <a href="anuncio_add.php" class="btn btn-primary">Criar Novo Anúncio</a>
            </div>

        <?php
        // Exibir mensagens de status (sucesso/erro da exclusão ou criação)
        if (isset($_SESSION['mensagem_sucesso'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['mensagem_sucesso']) . '</div>';
            unset($_SESSION['mensagem_sucesso']);
        }
        if (isset($_SESSION['mensagem_erro'])) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['mensagem_erro']) . '</div>';
            unset($_SESSION['mensagem_erro']);
        }
        ?>

        <?php if (count($anuncios) > 0): ?>
            <div class="anuncio-grid">
                <?php foreach ($anuncios as $anuncio): ?>
                    <div class="anuncio-item">
                        <?php
                        // Caminho da imagem
                        $image_path = !empty($anuncio['imagem']) ? 'uploads/' . htmlspecialchars($anuncio['imagem']) : 'assets/placeholder.jpg'; // Usar uma imagem placeholder se não houver
                        // Verificar se o arquivo da imagem existe no servidor antes de exibir
                        if (!file_exists($image_path) || is_dir($image_path)) {
                            $image_path = 'assets/placeholder.jpg'; // Caminho para uma imagem placeholder padrão
                            // Recomendo que você crie uma pasta 'assets' e coloque um 'placeholder.jpg' lá
                        }
                        ?>
                        <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($anuncio['marca'] . ' ' . $anuncio['modelo']) ?>">

                        <div class="anuncio-info">
                            <h3><?= htmlspecialchars($anuncio['marca']) ?> <?= htmlspecialchars($anuncio['modelo']) ?> (<?= htmlspecialchars($anuncio['ano']) ?>)</h3>
                            <p class="preco">R$ <?= number_format($anuncio['preco'], 2, ',', '.') ?></p>
                            <div class="acoes">
                                <a href="anuncio_delete.php?id=<?= $anuncio['anuncio_id'] ?>" class="delete-link" onclick="return confirm('Tem certeza que deseja excluir este anúncio? Esta ação é irreversível.');">Excluir</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-anuncios">Você ainda não possui anúncios. <a href="anunciar.php">Crie seu primeiro anúncio!</a></p>
        <?php endif; ?>
    </div>

    <div class="bottom-links">
        <a href="dashboard.php">Voltar para o Dashboard</a>
    </div>
</body>
</html> 