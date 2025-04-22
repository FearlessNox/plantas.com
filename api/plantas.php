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
            // Buscar uma planta específica
            $sql = "SELECT * FROM plantas WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                echo json_encode($result->fetch_assoc());
            } else {
                echo json_encode(['error' => 'Planta não encontrada']);
            }
        } else {
            // Listar todas as plantas
            $sql = "SELECT * FROM plantas";
            $result = $conn->query($sql);
            
            $plantas = [];
            while ($row = $result->fetch_assoc()) {
                $plantas[] = $row;
            }
            
            echo json_encode($plantas);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        $sql = "INSERT INTO plantas (nome_cientifico, nome_popular, tipo_planta, nivel_luz, frequencia_rega) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", 
            $data['nome_cientifico'],
            $data['nome_popular'],
            $data['tipo_planta'],
            $data['nivel_luz'],
            $data['frequencia_rega']
        );
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['error' => 'Erro ao cadastrar planta']);
        }
        break;

    case 'PUT':
        if (!$id) {
            echo json_encode(['error' => 'ID não fornecido']);
            break;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $sql = "UPDATE plantas SET 
                nome_cientifico = ?,
                nome_popular = ?,
                tipo_planta = ?,
                nivel_luz = ?,
                frequencia_rega = ?
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssii", 
            $data['nome_cientifico'],
            $data['nome_popular'],
            $data['tipo_planta'],
            $data['nivel_luz'],
            $data['frequencia_rega'],
            $id
        );
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Erro ao atualizar planta']);
        }
        break;

    case 'DELETE':
        if (!$id) {
            echo json_encode(['error' => 'ID não fornecido']);
            break;
        }
        
        // Verificar se existem cuidados relacionados
        $sql = "SELECT COUNT(*) as total FROM cuidados WHERE planta_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['total'] > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Não é possível excluir esta planta pois existem cuidados registrados para ela'
            ]);
            exit();
        }
        
        // Excluir planta
        $sql = "DELETE FROM plantas WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Planta excluída com sucesso'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao excluir planta: ' . $conn->error
            ]);
        }
        break;

    default:
        echo json_encode(['error' => 'Método não permitido']);
        break;
}

$conn->close();
?> 