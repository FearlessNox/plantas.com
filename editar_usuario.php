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

// Buscar dados do usuário
try {
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        header("Location: usuarios.php");
        exit();
    }
} catch (PDOException $e) {
    die("Erro ao buscar usuário: " . htmlspecialchars($e->getMessage()));
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        die('Token CSRF inválido');
    }

    // Sanitizar e validar entradas
    $nome = sanitize_input($_POST['nome']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $telefone = sanitize_input($_POST['telefone']);
    $nivel_experiencia = sanitize_input($_POST['nivel_experiencia']);

    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('Email inválido');
    }

    // Validar nível de experiência
    $niveis_permitidos = ['iniciante', 'intermediario', 'avancado'];
    if (!in_array($nivel_experiencia, $niveis_permitidos)) {
        die('Nível de experiência inválido');
    }

    try {
        $sql = "UPDATE usuarios SET 
                nome = ?,
                email = ?,
                telefone = ?,
                nivel_experiencia = ?
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $email, $telefone, $nivel_experiencia, $id]);
        
        header("Location: usuarios.php?success=1");
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
    <title>Editar Usuário - PlantCare</title>
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
                    <li><a href="/plantas.com/index.php"><i class="fas fa-home"></i> Início</a></li>
                    <li><a href="/plantas.com/plantas.php"><i class="fas fa-seedling"></i> Plantas</a></li>
                    <li><a href="/plantas.com/usuarios.php" class="active"><i class="fas fa-users"></i> Usuários</a></li>
                    <li><a href="/plantas.com/cuidados.php"><i class="fas fa-calendar-check"></i> Cuidados</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <section id="editar-usuario">
            <div class="section-header">
                <h2><i class="fas fa-user-edit"></i> Editar Usuário</h2>
                <a href="usuarios.php" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar</a>
            </div>
            
            <div class="card-form visible">
                <div class="card-header">
                    <h3>Editar Usuário</h3>
                </div>
                <form method="POST" action="editar_usuario.php?id=<?php echo $id; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <div class="form-group">
                        <label for="nome">Nome Completo:</label>
                        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="telefone">Telefone:</label>
                        <input type="tel" id="telefone" name="telefone" value="<?php echo htmlspecialchars($usuario['telefone']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="nivel_experiencia">Nível de Experiência:</label>
                        <select id="nivel_experiencia" name="nivel_experiencia">
                            <option value="iniciante" <?php echo $usuario['nivel_experiencia'] === 'iniciante' ? 'selected' : ''; ?>>Iniciante</option>
                            <option value="intermediario" <?php echo $usuario['nivel_experiencia'] === 'intermediario' ? 'selected' : ''; ?>>Intermediário</option>
                            <option value="avancado" <?php echo $usuario['nivel_experiencia'] === 'avancado' ? 'selected' : ''; ?>>Avançado</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar</button>
                        <a href="usuarios.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
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