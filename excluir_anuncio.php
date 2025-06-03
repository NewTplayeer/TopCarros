<?php
// anuncio_delete.php
require 'config.php';
session_start();

// 1. Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// 2. Verificar se um ID de anúncio foi fornecido via GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['mensagem_erro'] = "ID do anúncio inválido ou não fornecido.";
    header("Location: dashboard.php?status=error&message=" . urlencode($_SESSION['mensagem_erro']));
    exit();
}

$anuncio_id = $_GET['id'];

// 3. Verificar se o anúncio pertence ao usuário logado (SEGURANÇA ESSENCIAL!)
// Apenas um admin (se permitido) ou o próprio usuário pode excluir
$stmt_check = $conn->prepare("SELECT usuario_id FROM anuncios WHERE id = ?");
$stmt_check->bind_param("i", $anuncio_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$anuncio = $result_check->fetch_assoc();
$stmt_check->close();

// Buscar tipo de usuário logado para verificar permissão de admin
$stmt_user_type = $conn->prepare("SELECT tipo FROM usuarios WHERE id = ?");
$stmt_user_type->bind_param("i", $usuario_id);
$stmt_user_type->execute();
$result_user_type = $stmt_user_type->get_result();
$user_data = $result_user_type->fetch_assoc();
$tipo_usuario_logado = $user_data['tipo'];
$stmt_user_type->close();


if (!$anuncio || ($anuncio['usuario_id'] != $usuario_id && $tipo_usuario_logado !== 'admin')) {
    // Anúncio não encontrado OU não pertence ao usuário logado E o usuário não é admin
    $_SESSION['mensagem_erro'] = "Você não tem permissão para excluir este anúncio ou ele não existe.";
    header("Location: dashboard.php?status=error&message=" . urlencode($_SESSION['mensagem_erro']));
    exit();
}

// 4. Se tudo estiver ok, prosseguir com a exclusão
// Para admins, a exclusão é pelo ID. Para usuários comuns, é pelo ID E pelo usuario_id.
$delete_sql = "DELETE FROM anuncios WHERE id = ?";
$params = "i";
$bind_values = [$anuncio_id];

if ($tipo_usuario_logado !== 'admin') {
    $delete_sql .= " AND usuario_id = ?";
    $params .= "i";
    $bind_values[] = $usuario_id;
}

$stmt_delete = $conn->prepare($delete_sql);

// Bind dos parâmetros dinamicamente
if ($params == "i") { // Apenas um ID (para admin)
    $stmt_delete->bind_param($params, $anuncio_id);
} else { // ID e usuario_id (para usuário comum)
    $stmt_delete->bind_param($params, $anuncio_id, $usuario_id);
}


if ($stmt_delete->execute()) {
    $_SESSION['mensagem_sucesso'] = "Anúncio excluído com sucesso!";
    header("Location: dashboard.php?status=success&message=" . urlencode($_SESSION['mensagem_sucesso']));
} else {
    $_SESSION['mensagem_erro'] = "Erro ao excluir anúncio: " . $conn->error;
    header("Location: dashboard.php?status=error&message=" . urlencode($_SESSION['mensagem_erro']));
}
$stmt_delete->close();
exit();
?>