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

// Validar token CSRF
if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
    exit;
}

// Validar dados recebidos
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nome_cientifico = sanitize_input($_POST['nome_cientifico']);
$nome_popular = sanitize_input($_POST['nome_popular']);
$tipo_planta = sanitize_input($_POST['tipo_planta']);
$nivel_luz = sanitize_input($_POST['nivel_luz']);
$frequencia_rega = filter_var($_POST['frequencia_rega'], FILTER_VALIDATE_INT, [
    "options" => ["min_range" => 1, "max_range" => 30]
]);

// Validações
if ($frequencia_rega === false) {
    echo json_encode(['success' => false, 'message' => 'Frequência de rega inválida']);
    exit;
}

// Validar tipo de planta
$tipos_permitidos = ['interior', 'exterior', 'suculenta', 'frutifera', 'hortalica'];
if (!in_array($tipo_planta, $tipos_permitidos)) {
    echo json_encode(['success' => false, 'message' => 'Tipo de planta inválido']);
    exit;
}

// Validar nível de luz
$niveis_luz_permitidos = ['baixa', 'media', 'alta'];
if (!in_array($nivel_luz, $niveis_luz_permitidos)) {
    echo json_encode(['success' => false, 'message' => 'Nível de luz inválido']);
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

    $sql = "UPDATE plantas SET 
            nome_cientifico = ?, 
            nome_popular = ?, 
            tipo_planta = ?, 
            nivel_luz = ?, 
            frequencia_rega = ?
            WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $nome_cientifico,
        $nome_popular,
        $tipo_planta,
        $nivel_luz,
        $frequencia_rega,
        $id
    ]);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar planta']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar planta']);
}
?> 