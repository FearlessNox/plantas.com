<?php
require_once 'config/database.php';
require_once 'config/security.php';

// Conexão com o banco usando PDO para maior segurança
try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    die("Erro na conexão: " . htmlspecialchars($e->getMessage()));
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Buscar dados da planta
try {
    $sql = "SELECT * FROM plantas WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $planta = $stmt->fetch();

    if (!$planta) {
        header("Location: plantas.php");
        exit();
    }
} catch (PDOException $e) {
    die("Erro ao buscar planta: " . htmlspecialchars($e->getMessage()));
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        die('Token CSRF inválido');
    }

    // Sanitizar e validar entradas
    $nome_cientifico = sanitize_input($_POST['nome_cientifico']);
    $nome_popular = sanitize_input($_POST['nome_popular']);
    $tipo_planta = sanitize_input($_POST['tipo_planta']);
    $nivel_luz = sanitize_input($_POST['nivel_luz']);
    $frequencia_rega = filter_var($_POST['frequencia_rega'], FILTER_VALIDATE_INT, [
        "options" => ["min_range" => 1, "max_range" => 30]
    ]);

    if ($frequencia_rega === false) {
        die('Frequência de rega inválida');
    }

    // Validar tipo de planta
    $tipos_permitidos = ['interior', 'exterior', 'suculenta', 'frutifera', 'hortalica'];
    if (!in_array($tipo_planta, $tipos_permitidos)) {
        die('Tipo de planta inválido');
    }

    // Validar nível de luz
    $niveis_luz_permitidos = ['baixa', 'media', 'alta'];
    if (!in_array($nivel_luz, $niveis_luz_permitidos)) {
        die('Nível de luz inválido');
    }

    try {
        $sql = "UPDATE plantas SET 
                nome_cientifico = ?, 
                nome_popular = ?, 
                tipo_planta = ?, 
                nivel_luz = ?, 
                frequencia_rega = ?
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome_cientifico, $nome_popular, $tipo_planta, $nivel_luz, $frequencia_rega, $id]);
        
        header("Location: plantas.php?success=1");
        exit();
    } catch (PDOException $e) {
        die("Erro ao atualizar: " . htmlspecialchars($e->getMessage()));
    }
}

// Gerar novo token CSRF para o formulário
$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Planta - PlantCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <i class="fas fa-leaf"></i>
                <h1>PlantCare</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-home"></i> Início</a></li>
                    <li><a href="plantas.php" class="active"><i class="fas fa-seedling"></i> Plantas</a></li>
                    <li><a href="usuarios.php"><i class="fas fa-users"></i> Usuários</a></li>
                    <li><a href="cuidados.php"><i class="fas fa-calendar-check"></i> Cuidados</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <section id="editar-planta">
            <div class="section-header">
                <h2><i class="fas fa-edit"></i> Editar Planta</h2>
                <a href="plantas.php" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar</a>
            </div>
            
            <div class="card-form">
                <form method="POST" action="editar_planta.php?id=<?php echo $id; ?>" class="form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <div class="form-group">
                        <label for="nome_cientifico">Nome Científico:</label>
                        <input type="text" id="nome_cientifico" name="nome_cientifico" value="<?php echo htmlspecialchars($planta['nome_cientifico']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="nome_popular">Nome Popular:</label>
                        <input type="text" id="nome_popular" name="nome_popular" value="<?php echo htmlspecialchars($planta['nome_popular']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="tipo_planta">Tipo de Planta:</label>
                        <select id="tipo_planta" name="tipo_planta">
                            <option value="interior" <?php echo $planta['tipo_planta'] === 'interior' ? 'selected' : ''; ?>>Planta de Interior</option>
                            <option value="exterior" <?php echo $planta['tipo_planta'] === 'exterior' ? 'selected' : ''; ?>>Planta de Exterior</option>
                            <option value="suculenta" <?php echo $planta['tipo_planta'] === 'suculenta' ? 'selected' : ''; ?>>Suculenta</option>
                            <option value="frutifera" <?php echo $planta['tipo_planta'] === 'frutifera' ? 'selected' : ''; ?>>Frutífera</option>
                            <option value="hortalica" <?php echo $planta['tipo_planta'] === 'hortalica' ? 'selected' : ''; ?>>Hortaliça</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="nivel_luz">Necessidade de Luz:</label>
                        <select id="nivel_luz" name="nivel_luz">
                            <option value="baixa" <?php echo $planta['nivel_luz'] === 'baixa' ? 'selected' : ''; ?>>Baixa</option>
                            <option value="media" <?php echo $planta['nivel_luz'] === 'media' ? 'selected' : ''; ?>>Média</option>
                            <option value="alta" <?php echo $planta['nivel_luz'] === 'alta' ? 'selected' : ''; ?>>Alta</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="frequencia_rega">Frequência de Rega (dias):</label>
                        <input type="number" id="frequencia_rega" name="frequencia_rega" min="1" max="30" value="<?php echo $planta['frequencia_rega']; ?>">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar</button>
                        <a href="plantas.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 PlantCare - Sistema de Gerenciamento de Plantas Domésticas</p>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html> 