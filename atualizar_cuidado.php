<?php
require_once 'config/session.php';
require_once 'config/database.php';
require_once 'config/security.php';

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
$usuario_id = isset($_POST['usuario_id']) ? (int)$_POST['usuario_id'] : 0;
$planta_id = isset($_POST['planta_id']) ? (int)$_POST['planta_id'] : 0;

// Verificar se os IDs são válidos
if ($id <= 0 || $usuario_id <= 0 || $planta_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'IDs inválidos']);
    exit;
}

// Verificar campos obrigatórios
if (!isset($_POST['tipo_cuidado']) || !isset($_POST['data_cuidado']) || 
    !isset($_POST['intervalo_dias'])) {
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
    exit;
}
$tipo_cuidado = sanitize_input($_POST['tipo_cuidado']);
$data_cuidado = sanitize_input($_POST['data_cuidado']);
$intervalo_dias = filter_var($_POST['intervalo_dias'], FILTER_VALIDATE_INT, [
    "options" => ["min_range" => 1, "max_range" => 365]
]);
$observacoes = sanitize_input($_POST['observacoes']);

// Validações
if ($intervalo_dias === false) {
    echo json_encode(['success' => false, 'message' => 'Intervalo de dias inválido']);
    exit;
}

// Validar tipo de cuidado
$tipos_permitidos = ['rega', 'poda', 'adubacao', 'outro'];
if (!in_array($tipo_cuidado, $tipos_permitidos)) {
    echo json_encode(['success' => false, 'message' => 'Tipo de cuidado inválido']);
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
    
    // Verificar se o cuidado existe
    $stmt = $pdo->prepare("SELECT id FROM cuidados WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Cuidado não encontrado']);
        exit;
    }
    
    // Verificar se o usuário existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
        exit;
    }
    
    // Verificar se a planta existe
    $stmt = $pdo->prepare("SELECT id FROM plantas WHERE id = ?");
    $stmt->execute([$planta_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Planta não encontrada']);
        exit;
    }

    $sql = "UPDATE cuidados SET 
            usuario_id = ?, 
            planta_id = ?, 
            tipo_cuidado = ?, 
            data_cuidado = ?, 
            intervalo_dias = ?,
            observacoes = ?
            WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $usuario_id,
        $planta_id,
        $tipo_cuidado,
        $data_cuidado,
        $intervalo_dias,
        $observacoes,
        $id
    ]);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar cuidado']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar cuidado']);
}
?>