<?php
require_once '../config/database.php';

header('Content-Type: application/json');

// Conexão com o banco
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Erro na conexão: ' . $conn->connect_error
    ]));
}

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($method) {
    case 'GET':
        if ($id) {
            // Buscar um cuidado específico
            $sql = "SELECT c.*, u.nome as usuario_nome, p.nome_popular as planta_nome 
                    FROM cuidados c 
                    JOIN usuarios u ON c.usuario_id = u.id 
                    JOIN plantas p ON c.planta_id = p.id
                    WHERE c.id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                echo json_encode($result->fetch_assoc());
            } else {
                echo json_encode(['error' => 'Cuidado não encontrado']);
            }
        } else {
            // Listar todos os cuidados
            $sql = "SELECT c.*, u.nome as usuario_nome, p.nome_popular as planta_nome 
                    FROM cuidados c 
                    JOIN usuarios u ON c.usuario_id = u.id 
                    JOIN plantas p ON c.planta_id = p.id";
            $result = $conn->query($sql);
            
            $cuidados = [];
            while ($row = $result->fetch_assoc()) {
                $cuidados[] = $row;
            }
            
            echo json_encode($cuidados);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        $sql = "INSERT INTO cuidados (usuario_id, planta_id, tipo_cuidado, data_cuidado, frequencia, observacoes) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissis", 
            $data['usuario_id'],
            $data['planta_id'],
            $data['tipo_cuidado'],
            $data['data_cuidado'],
            $data['frequencia'],
            $data['observacoes']
        );
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['error' => 'Erro ao cadastrar cuidado']);
        }
        break;

    case 'PUT':
        if (!$id) {
            echo json_encode(['error' => 'ID não fornecido']);
            break;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $sql = "UPDATE cuidados SET 
                usuario_id = ?,
                planta_id = ?,
                tipo_cuidado = ?,
                data_cuidado = ?,
                frequencia = ?,
                observacoes = ?
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissisi", 
            $data['usuario_id'],
            $data['planta_id'],
            $data['tipo_cuidado'],
            $data['data_cuidado'],
            $data['frequencia'],
            $data['observacoes'],
            $id
        );
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Erro ao atualizar cuidado']);
        }
        break;

    case 'DELETE':
        if (!$id) {
            echo json_encode(['error' => 'ID não fornecido']);
            break;
        }
        
        $sql = "DELETE FROM cuidados WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Registro de cuidado excluído com sucesso'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao excluir registro de cuidado: ' . $conn->error
            ]);
        }
        break;

    default:
        echo json_encode(['error' => 'Método não permitido']);
        break;
}

$conn->close();
?> 