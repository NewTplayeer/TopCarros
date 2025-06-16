<?php
require_once '../config.php'; // Inclui o arquivo de configuração do banco de dados
session_start(); // Inicia a sessão

header('Content-Type: application/json'); // Define o cabeçalho para retornar JSON

// 1. Verificação de autenticação
if (!isset($_SESSION['usuario_id'])) { // Verifica se o ID do usuário está na sessão
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit();
}

$usuario_id = $_SESSION['usuario_id']; // Obtém o ID do usuário logado
$data = json_decode(file_get_contents('php://input'), true); // Obtém os dados JSON enviados pelo frontend

$frete_tipo = $data['frete_tipo'] ?? null; // Tipo de frete selecionado
$frete_valor = $data['frete_valor'] ?? 0; // Valor do frete
$frete_prazo = $data['frete_prazo'] ?? null; // Prazo do frete
$valor_total_carrinho = $data['valor_total_carrinho'] ?? 0; // Subtotal dos itens no carrinho (sem frete)

// Função auxiliar para obter o ID do carrinho ativo do usuário (mesma de cart.php)
function getActiveCartId($conn, $usuario_id) {
    $stmt = $conn->prepare("SELECT id FROM compras WHERE usuario_id = ? AND status = 'carrinho'");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['id'];
    }
    return null;
}

$compra_id = getActiveCartId($conn, $usuario_id); // Obtém o ID do carrinho ativo

if (!$compra_id) {
    echo json_encode(['success' => false, 'message' => 'Nenhum carrinho ativo para finalizar.']);
    exit();
}

// Validação básica dos dados de frete
if (empty($frete_tipo) || !is_numeric($frete_valor) || empty($frete_prazo)) {
    echo json_encode(['success' => false, 'message' => 'Dados de frete incompletos.']);
    exit();
}

// Iniciar transação para garantir atomicidade da operação
$conn->begin_transaction();

try {
    // 1. Calcular o total final (subtotal + frete)
    $total_final = $valor_total_carrinho + $frete_valor;

    // 2. Atualizar a entrada `compras` no banco de dados
    //    - Mudar o status de 'carrinho' para 'pago' (ou 'pendente', se houver um gateway de pagamento real)
    //    - Salvar o valor_total atualizado, tipo_frete, valor_frete, prazo_frete e a data da compra.
    $stmt_update_compra = $conn->prepare("UPDATE compras SET status = 'pago', valor_total = ?, tipo_frete = ?, valor_frete = ?, prazo_frete = ?, data_compra = NOW() WHERE id = ? AND usuario_id = ?");
    $stmt_update_compra->bind_param("dsdsii", $total_final, $frete_tipo, $frete_valor, $frete_prazo, $compra_id, $usuario_id);
    
    if (!$stmt_update_compra->execute()) {
        throw new Exception("Erro ao finalizar compra: " . $stmt_update_compra->error);
    }
    $stmt_update_compra->close();

    // 3. Confirmar a transação
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Compra finalizada com sucesso!', 'compra_id' => $compra_id, 'total_final' => $total_final]);

} catch (Exception $e) {
    // 4. Em caso de erro, reverter a transação
    $conn->rollback();
    error_log("Erro no checkout: " . $e->getMessage()); // Registrar o erro para depuração
    echo json_encode(['success' => false, 'message' => 'Erro ao finalizar a compra. Tente novamente mais tarde.']);
}

$conn->close(); // Fecha a conexão com o banco de dados
?>