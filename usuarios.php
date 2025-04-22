<?php
require_once 'config/database.php';

// Conexão com o banco
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $nivel_experiencia = $_POST['nivel_experiencia'];

    $sql = "INSERT INTO usuarios (nome, email, telefone, nivel_experiencia) 
            VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nome, $email, $telefone, $nivel_experiencia);
    
    if ($stmt->execute()) {
        header("Location: usuarios.php?success=1");
        exit();
    }
}

// Buscar usuários
$sql = "SELECT * FROM usuarios";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - PlantCare</title>
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
        <section id="usuarios">
            <div class="section-header">
                <!-- <h2><i class="fas fa-users"></i> Gerenciamento de Usuários</h2> -->
                <button id="btnAddUsuario" class="btn-add"><i class="fas fa-plus"></i> Novo Usuário</button>
            </div>
            
            <div class="card-form" id="usuarioFormCard">
                <div class="card-header">
                    <h3>Cadastrar Novo Usuário</h3>
                    <button class="btn-close" id="closeUsuarioForm"><i class="fas fa-times"></i></button>
                </div>
                <form id="usuarioForm" class="form" method="POST" action="usuarios.php">
                    <div class="form-group">
                        <label for="nome">Nome Completo:</label>
                        <input type="text" id="nome" name="nome" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="telefone">Telefone:</label>
                        <input type="tel" id="telefone" name="telefone">
                    </div>
                    <div class="form-group">
                        <label for="nivel_experiencia">Nível de Experiência:</label>
                        <select id="nivel_experiencia" name="nivel_experiencia">
                            <option value="iniciante">Iniciante</option>
                            <option value="intermediario">Intermediário</option>
                            <option value="avancado">Avançado</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Cadastrar</button>
                        <button type="reset" class="btn btn-secondary"><i class="fas fa-undo"></i> Limpar</button>
                    </div>
                </form>
            </div>

            <div class="data-table-container">
                <table class="data-table" id="usuariosTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Telefone</th>
                            <th>Experiência</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['telefone']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nivel_experiencia']); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-delete" data-id="<?php echo $row['id']; ?>" title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="empty-table">Nenhum usuário cadastrado</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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