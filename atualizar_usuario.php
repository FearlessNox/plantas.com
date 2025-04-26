<?php
require_once 'config/session.php';
require_once 'config/database.php';
require_once 'config/security.php';

// Permitir acesso via AJAX
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}



// Validar dados recebidos
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// Verificar se o ID é válido
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID do usuário inválido']);
    exit;
}

// Verificar campos obrigatórios
if (!isset($_POST['nome']) || !isset($_POST['email']) || 
    !isset($_POST['nivel_experiencia'])) {
    echo json_encode(['success' => false, 'message' => 'Nome, email e nível de experiência são obrigatórios']);
    exit;
}

$nome = sanitize_input($_POST['nome']);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$telefone = isset($_POST['telefone']) ? sanitize_input($_POST['telefone']) : '';
$nivel_experiencia = sanitize_input($_POST['nivel_experiencia']);

// Validar campos vazios após sanitização
if (empty($nome) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Nome e email não podem estar vazios']);
    exit;
}

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
}

// Validar nível de experiência
$niveis_permitidos = ['iniciante', 'intermediario', 'avancado'];
if (!in_array($nivel_experiencia, $niveis_permitidos)) {
    echo json_encode(['success' => false, 'message' => 'Nível de experiência inválido']);
    exit;
}

try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);

    // Verificar se o usuário existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
        exit;
    }

    // Verificar se o email já está em uso por outro usuário
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
    $stmt->execute([$email, $id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Este email já está em uso por outro usuário']);
        exit;
    }

    $sql = "UPDATE usuarios SET 
            nome = ?, 
            email = ?, 
            telefone = ?, 
            nivel_experiencia = ?
            WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $nome,
        $email,
        $telefone,
        $nivel_experiencia,
        $id
    ]);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar usuário']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar usuário']);
}
?>