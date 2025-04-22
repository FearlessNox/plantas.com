<?php
require_once 'config/database.php';

// Conexão com o banco
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Buscar dados do usuário
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: usuarios.php");
    exit();
}

$usuario = $result->fetch_assoc();

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $nivel_experiencia = $_POST['nivel_experiencia'];

    $sql = "UPDATE usuarios SET 
            nome = ?,
            email = ?,
            telefone = ?,
            nivel_experiencia = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", 
        $nome,
        $email,
        $telefone,
        $nivel_experiencia,
        $id
    );
    
    if ($stmt->execute()) {
        header("Location: usuarios.php?success=1");
        exit();
    }
}
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
                <h2><i class="fas fa-users"></i> Editar Usuário</h2>
                <a href="usuarios.php" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar</a>
            </div>
            
            <div class="card-form">
                <form method="POST" action="editar_usuario.php?id=<?php echo $id; ?>">
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
                        <a href="/plantas.com/usuarios.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
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
<?php $conn->close(); ?> 