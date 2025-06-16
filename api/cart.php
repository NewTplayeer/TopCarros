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
$action = $_POST['action'] ?? ''; // Obtém a ação a ser realizada (add, update_quantity, remove, get)
$veiculo_id = $_POST['veiculo_id'] ?? null; // ID do veículo para operações (adicionar, remover)
$quantidade = $_POST['quantidade'] ?? 1; // Quantidade para adicionar ou atualizar

// Função auxiliar para obter o ID do carrinho ativo do usuário
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

// Função auxiliar para criar um novo carrinho
function createNewCart($conn, $usuario_id) {
    // Insere uma nova entrada na tabela `compras` com status 'carrinho'
    $stmt = $conn->prepare("INSERT INTO compras (usuario_id, valor_total, status) VALUES (?, 0, 'carrinho')");
    $stmt->bind_param("i", $usuario_id);
    if ($stmt->execute()) {
        return $conn->insert_id; // Retorna o ID da nova compra (carrinho) criada
    }
    return null;
}

// Função auxiliar para atualizar o valor_total na tabela `compras`
function updateCartTotal($conn, $compra_id) {
    // Calcula a soma dos preços unitários * quantidade para todos os itens do carrinho
    $stmt_total = $conn->prepare("SELECT SUM(ci.preco_unitario * ci.quantidade) AS total FROM compra_itens ci WHERE ci.compra_id = ?");
    $stmt_total->bind_param("i", $compra_id);
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();
    $total_row = $result_total->fetch_assoc();
    $new_total = $total_row['total'] ?? 0; // Se não houver itens, o total é 0

    // Atualiza a coluna valor_total na tabela `compras`
    $stmt_update_total = $conn->prepare("UPDATE compras SET valor_total = ? WHERE id = ?");
    $stmt_update_total->bind_param("di", $new_total, $compra_id);
    $stmt_update_total->execute();
    $stmt_update_total->close();
}


// Lógica principal baseada na ação recebida
switch ($action) {
    case 'add':
        // 1. Buscar detalhes do veículo para obter o preço (segurança: não confiar no preço do cliente)
        $stmt_vehicle = $conn->prepare("SELECT preco FROM veiculos WHERE id = ?");
        $stmt_vehicle->bind_param("i", $veiculo_id);
        $stmt_vehicle->execute();
        $result_vehicle = $stmt_vehicle->get_result();
        $vehicle_data = $result_vehicle->fetch_assoc();
        $stmt_vehicle->close();

        if (!$vehicle_data) {
            echo json_encode(['success' => false, 'message' => 'Veículo não encontrado.']);
            exit();
        }
        $preco_unitario = $vehicle_data['preco']; // Preço obtido do banco de dados

        // 2. Obter o ID do carrinho ativo ou criar um novo
        $compra_id = getActiveCartId($conn, $usuario_id);
        if (!$compra_id) {
            $compra_id = createNewCart($conn, $usuario_id);
            if (!$compra_id) {
                echo json_encode(['success' => false, 'message' => 'Erro ao criar novo carrinho.']);
                exit();
            }
        }

        // 3. Verificar se o item já existe no carrinho para o veículo específico
        $stmt_check_item = $conn->prepare("SELECT id, quantidade FROM compra_itens WHERE compra_id = ? AND veiculo_id = ?");
        $stmt_check_item->bind_param("ii", $compra_id, $veiculo_id);
        $stmt_check_item->execute();
        $result_check_item = $stmt_check_item->get_result();

        if ($item_exists = $result_check_item->fetch_assoc()) {
            // Se o item já existe, atualiza a quantidade
            $new_quantity = $item_exists['quantidade'] + $quantidade;
            $stmt_update_item = $conn->prepare("UPDATE compra_itens SET quantidade = ? WHERE id = ?");
            $stmt_update_item->bind_param("ii", $new_quantity, $item_exists['id']);
            $stmt_update_item->execute();
            $stmt_update_item->close();
        } else {
            // Se o item não existe, adiciona como um novo item no carrinho
            $stmt_add_item = $conn->prepare("INSERT INTO compra_itens (compra_id, veiculo_id, preco_unitario, quantidade) VALUES (?, ?, ?, ?)");
            $stmt_add_item->bind_param("iidi", $compra_id, $veiculo_id, $preco_unitario, $quantidade);
            $stmt_add_item->execute();
            $stmt_add_item->close();
        }
        $stmt_check_item->close();
        
        // 4. Atualizar o valor total do carrinho na tabela `compras`
        updateCartTotal($conn, $compra_id);
        echo json_encode(['success' => true, 'message' => 'Item adicionado/atualizado no carrinho.']);
        break;

    case 'update_quantity':
        $item_id = $_POST['item_id'] ?? null; // ID do item específico em `compra_itens`
        $quantidade = max(1, (int)$_POST['quantidade']); // Garante que a quantidade seja pelo menos 1 e um inteiro

        if ($item_id && $quantidade) {
            // 1. Obter o `compra_id` do `item_id` para segurança e para atualizar o total
            $stmt_get_compra_id = $conn->prepare("SELECT compra_id FROM compra_itens WHERE id = ?");
            $stmt_get_compra_id->bind_param("i", $item_id);
            $stmt_get_compra_id->execute();
            $result_get_compra_id = $stmt_get_compra_id->get_result();
            $item_data = $result_get_compra_id->fetch_assoc();
            $stmt_get_compra_id->close();

            if ($item_data) {
                $compra_id = $item_data['compra_id'];

                // 2. Atualizar a quantidade do item no banco de dados
                $stmt = $conn->prepare("UPDATE compra_itens SET quantidade = ? WHERE id = ?");
                $stmt->bind_param("ii", $quantidade, $item_id);
                if ($stmt->execute()) {
                    // 3. Atualizar o valor total do carrinho após a alteração da quantidade
                    updateCartTotal($conn, $compra_id);
                    echo json_encode(['success' => true, 'message' => 'Quantidade atualizada.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar quantidade.']);
                }
                $stmt->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Item do carrinho não encontrado.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos para atualização.']);
        }
        break;

    case 'remove':
        $item_id = $_POST['item_id'] ?? null; // ID do item a ser removido de `compra_itens`

        if ($item_id) {
            // 1. Obter o `compra_id` do `item_id` para posterior atualização do total e verificação de carrinho vazio
            $stmt_get_compra_id = $conn->prepare("SELECT compra_id FROM compra_itens WHERE id = ?");
            $stmt_get_compra_id->bind_param("i", $item_id);
            $stmt_get_compra_id->execute();
            $result_get_compra_id = $stmt_get_compra_id->get_result();
            $item_data = $result_get_compra_id->fetch_assoc();
            $stmt_get_compra_id->close();

            if ($item_data) {
                $compra_id = $item_data['compra_id'];

                // 2. Remover o item da tabela `compra_itens`
                $stmt = $conn->prepare("DELETE FROM compra_itens WHERE id = ?");
                $stmt->bind_param("i", $item_id);
                if ($stmt->execute()) {
                    // 3. Verificar se o carrinho está vazio após a remoção
                    $stmt_check_empty = $conn->prepare("SELECT COUNT(*) FROM compra_itens WHERE compra_id = ?");
                    $stmt_check_empty->bind_param("i", $compra_id);
                    $stmt_check_empty->execute();
                    $result_check_empty = $stmt_check_empty->get_result();
                    $row_count = $result_check_empty->fetch_row()[0];
                    $stmt_check_empty->close();

                    if ($row_count == 0) {
                        // Se o carrinho estiver vazio, exclui a entrada principal da tabela `compras`
                        $stmt_delete_cart = $conn->prepare("DELETE FROM compras WHERE id = ?");
                        $stmt_delete_cart->bind_param("i", $compra_id);
                        $stmt_delete_cart->execute();
                        $stmt_delete_cart->close();
                    } else {
                        // Se não estiver vazio, apenas atualiza o total
                        updateCartTotal($conn, $compra_id);
                    }
                    echo json_encode(['success' => true, 'message' => 'Item removido do carrinho.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erro ao remover item.']);
                }
                $stmt->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Item do carrinho não encontrado.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'ID do item inválido.']);
        }
        break;

    case 'get':
        // 1. Obter o ID do carrinho ativo para o usuário
        $compra_id = getActiveCartId($conn, $usuario_id);
        $carrinho_items = [];
        $cart_total = 0;
        $frete_info = [
            'tipo' => 'A calcular',
            'valor' => 0,
            'prazo' => '-'
        ];

        if ($compra_id) {
            // 2. Buscar todos os itens associados a este carrinho (compra_id)
            $stmt = $conn->prepare("
                SELECT
                    ci.id AS item_id,
                    v.id AS veiculo_id,
                    m.nome AS marca,
                    v.modelo,
                    v.ano,
                    v.preco,
                    v.imagem,
                    ci.quantidade,
                    c.valor_total,
                    c.tipo_frete,
                    c.valor_frete,
                    c.prazo_frete
                FROM compra_itens ci
                JOIN veiculos v ON ci.veiculo_id = v.id
                JOIN marcas m ON v.marca_id = m.id
                JOIN compras c ON ci.compra_id = c.id
                WHERE ci.compra_id = ?
            ");
            $stmt->bind_param("i", $compra_id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $carrinho_items[] = [
                    'item_id' => $row['item_id'], // ID do item na tabela `compra_itens`
                    'veiculo_id' => $row['veiculo_id'], // ID do veículo na tabela `veiculos`
                    'nome' => $row['marca'] . ' ' . $row['modelo'],
                    'marca' => $row['marca'],
                    'modelo' => $row['modelo'],
                    'ano' => $row['ano'],
                    'preco' => (float)$row['preco'], // Converte para float
                    'imagem' => $row['imagem'],
                    'qtd' => (int)$row['quantidade'] // Converte para int
                ];
                // Define o total do carrinho e as informações de frete apenas uma vez (do registro da compra)
                if ($cart_total === 0) {
                    $cart_total = (float)$row['valor_total'];
                    $frete_info = [
                        'tipo' => $row['tipo_frete'] ?? 'A calcular',
                        'valor' => (float)($row['valor_frete'] ?? 0),
                        'prazo' => $row['prazo_frete'] ?? '-'
                    ];
                }
            }
            $stmt->close();
        }
        // Retorna os itens do carrinho, o total do carrinho e as informações de frete
        echo json_encode(['success' => true, 'carrinho' => $carrinho_items, 'cart_total' => $cart_total, 'frete' => $frete_info]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
        break;
}

$conn->close(); // Fecha a conexão com o banco de dados
?>