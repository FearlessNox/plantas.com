<?php
require_once 'config/database.php';

// Conexão com o banco
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Buscar dados do cuidado
$sql = "SELECT * FROM cuidados WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: cuidados.php");
    exit();
}

$cuidado = $result->fetch_assoc();

// Buscar usuários e plantas para os selects
$sql_usuarios = "SELECT id, nome FROM usuarios";
$result_usuarios = $conn->query($sql_usuarios);

$sql_plantas = "SELECT id, nome_popular, frequencia_rega FROM plantas";
$result_plantas = $conn->query($sql_plantas);

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_POST['usuario_id'];
    $planta_id = $_POST['planta_id'];
    $tipo_cuidado = $_POST['tipo_cuidado'];
    $data_cuidado = $_POST['data_cuidado'];
    $intervalo_dias = $_POST['intervalo_dias'];
    $observacoes = $_POST['observacoes'];

    $sql = "UPDATE cuidados SET 
            usuario_id = ?,
            planta_id = ?,
            tipo_cuidado = ?,
            data_cuidado = ?,
            intervalo_dias = ?,
            observacoes = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissisi", 
        $usuario_id,
        $planta_id,
        $tipo_cuidado,
        $data_cuidado,
        $intervalo_dias,
        $observacoes,
        $id
    );
    
    if ($stmt->execute()) {
        header("Location: cuidados.php?success=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cuidado - PlantCare</title>
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
                    <li><a href="/plantas.com/usuarios.php"><i class="fas fa-users"></i> Usuários</a></li>
                    <li><a href="/plantas.com/cuidados.php" class="active"><i class="fas fa-calendar-check"></i> Cuidados</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <section id="editar-cuidado">
            <div class="section-header">
                <h2><i class="fas fa-edit"></i> Editar Cuidado</h2>
                <a href="cuidados.php" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar</a>
            </div>
            
            <div class="card-form">
                <form method="POST" action="editar_cuidado.php?id=<?php echo $id; ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="usuario_id">Usuário:</label>
                            <select id="usuario_id" name="usuario_id" required>
                                <option value="">Selecione um usuário</option>
                                <?php while($row = $result_usuarios->fetch_assoc()): ?>
                                    <option value="<?php echo $row['id']; ?>" <?php echo $row['id'] == $cuidado['usuario_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($row['nome']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="planta_id">Planta:</label>
                            <select id="planta_id" name="planta_id" required>
                                <option value="">Selecione uma planta</option>
                                <?php while($row = $result_plantas->fetch_assoc()): ?>
                                    <option value="<?php echo $row['id']; ?>" 
                                            data-frequencia-rega="<?php echo $row['frequencia_rega']; ?>"
                                            <?php echo $row['id'] == $cuidado['planta_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($row['nome_popular']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="tipo_cuidado">Tipo de Cuidado:</label>
                        <select id="tipo_cuidado" name="tipo_cuidado">
                            <option value="rega" <?php echo $cuidado['tipo_cuidado'] === 'rega' ? 'selected' : ''; ?>>Rega</option>
                            <option value="poda" <?php echo $cuidado['tipo_cuidado'] === 'poda' ? 'selected' : ''; ?>>Poda</option>
                            <option value="adubacao" <?php echo $cuidado['tipo_cuidado'] === 'adubacao' ? 'selected' : ''; ?>>Adubação</option>
                            <option value="transplante" <?php echo $cuidado['tipo_cuidado'] === 'transplante' ? 'selected' : ''; ?>>Transplante</option>
                            <option value="controle_pragas" <?php echo $cuidado['tipo_cuidado'] === 'controle_pragas' ? 'selected' : ''; ?>>Controle de Pragas</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="data_cuidado">Data do Cuidado:</label>
                            <input type="date" id="data_cuidado" name="data_cuidado" value="<?php echo $cuidado['data_cuidado']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="intervalo_dias">Intervalo (dias):</label>
                            <input type="number" id="intervalo_dias" name="intervalo_dias" min="1" max="365" value="<?php echo $cuidado['intervalo_dias']; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="observacoes">Observações:</label>
                        <textarea id="observacoes" name="observacoes" rows="3"><?php echo htmlspecialchars($cuidado['observacoes']); ?></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar</button>
                        <a href="cuidados.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
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