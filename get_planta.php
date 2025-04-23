<?php
require_once 'config/session.php';
require_once 'config/database.php';
require_once 'config/security.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID não fornecido']);
    exit;
}

$id = (int)$_GET['id'];

try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);

    $stmt = $pdo->prepare("SELECT * FROM plantas WHERE id = ?");
    $stmt->execute([$id]);
    $planta = $stmt->fetch();

    if ($planta) {
        echo json_encode($planta);
    } else {
        echo json_encode(['error' => 'Planta não encontrada']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro ao buscar planta']);
}
?> 