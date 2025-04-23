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

// Validar token CSRF
if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
    exit;
}

// Validar dados recebidos
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$usuario_id = isset($_POST['usuario_id']) ? (int)$_POST['usuario_id'] : 0;
$planta_id = isset($_POST['planta_id']) ? (int)$_POST['planta_id'] : 0;
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