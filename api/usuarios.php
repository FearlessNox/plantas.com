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
            // Buscar um usuário específico
            $sql = "SELECT * FROM usuarios WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                echo json_encode($result->fetch_assoc());
            } else {
                echo json_encode(['error' => 'Usuário não encontrado']);
            }
        } else {
            // Listar todos os usuários
            $sql = "SELECT * FROM usuarios";
            $result = $conn->query($sql);
            
            $usuarios = [];
            while ($row = $result->fetch_assoc()) {
                $usuarios[] = $row;
            }
            
            echo json_encode($usuarios);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        $sql = "INSERT INTO usuarios (nome, email, telefone, nivel_experiencia) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", 
            $data['nome'],
            $data['email'],
            $data['telefone'],
            $data['nivel_experiencia']
        );
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['error' => 'Erro ao cadastrar usuário']);
        }
        break;

    case 'PUT':
        if (!$id) {
            echo json_encode(['error' => 'ID não fornecido']);
            break;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $sql = "UPDATE usuarios SET 
                nome = ?,
                email = ?,
                telefone = ?,
                nivel_experiencia = ?
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", 
            $data['nome'],
            $data['email'],
            $data['telefone'],
            $data['nivel_experiencia'],
            $id
        );
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Erro ao atualizar usuário']);
        }
        break;

    case 'DELETE':
        if (!$id) {
            echo json_encode(['error' => 'ID não fornecido']);
            break;
        }
        
        // Verificar se existem cuidados relacionados
        $sql = "SELECT COUNT(*) as total FROM cuidados WHERE usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['total'] > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Não é possível excluir este usuário pois existem cuidados registrados para ele'
            ]);
            exit();
        }
        
        // Excluir usuário
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Usuário excluído com sucesso'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao excluir usuário: ' . $conn->error
            ]);
        }
        break;

    default:
        echo json_encode(['error' => 'Método não permitido']);
        break;
}

$conn->close();
?> 