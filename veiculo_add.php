<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marca_id = $_POST['marca_id'];
    $modelo = $_POST['modelo'];
    $ano = $_POST['ano'];
    $preco = $_POST['preco'];
    $imagem = $_FILES['imagem']['name'];
    move_uploaded_file($_FILES['imagem']['tmp_name'], "uploads/$imagem");

    $stmt = $conn->prepare("INSERT INTO veiculos (marca_id, modelo, ano, preco, imagem) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isids", $marca_id, $modelo, $ano, $preco, $imagem);
    $stmt->execute();
    header("Location: veiculo_list.php");
}

// busca marcas para dropdown
$marcas = $conn->query("SELECT * FROM marcas");
?>
<form method="post" enctype="multipart/form-data">
    <select name="marca_id">
        <?php while ($marca = $marcas->fetch_assoc()): ?>
            <option value="<?= $marca['id'] ?>"><?= $marca['nome'] ?></option>
        <?php endwhile; ?>
    </select>
    <input type="text" name="modelo" placeholder="Modelo" required>
    <input type="number" name="ano" placeholder="Ano" required>
    <input type="number" step="0.01" name="preco" placeholder="Preço" required>
    <input type="file" name="imagem" required>
    <button type="submit">Cadastrar Veículo</button>
</form>
