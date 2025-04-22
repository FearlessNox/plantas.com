<?php
require_once 'config/database.php';

// Conexão com o banco
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_POST['usuario_id'];
    $planta_id = $_POST['planta_id'];
    $tipo_cuidado = $_POST['tipo_cuidado'];
    $data_cuidado = $_POST['data_cuidado'];
    $intervalo_dias = $_POST['intervalo_dias'];
    $observacoes = $_POST['observacoes'];

    $sql = "INSERT INTO cuidados (usuario_id, planta_id, tipo_cuidado, data_cuidado, intervalo_dias, observacoes) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissis", $usuario_id, $planta_id, $tipo_cuidado, $data_cuidado, $intervalo_dias, $observacoes);
    
    if ($stmt->execute()) {
        header("Location: cuidados.php?success=1");
        exit();
    }
}

// Buscar usuários e plantas para os selects
$sql_usuarios = "SELECT id, nome FROM usuarios";
$result_usuarios = $conn->query($sql_usuarios);

$sql_plantas = "SELECT id, nome_popular, frequencia_rega FROM plantas";
$result_plantas = $conn->query($sql_plantas);

// Buscar cuidados
$sql = "SELECT c.*, u.nome as usuario_nome, p.nome_popular as planta_nome,
        DATE_ADD(c.data_cuidado, INTERVAL c.intervalo_dias DAY) as proxima_data 
        FROM cuidados c 
        JOIN usuarios u ON c.usuario_id = u.id 
        JOIN plantas p ON c.planta_id = p.id";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuidados - PlantCare</title>
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
        <section id="cuidados">
            <div class="section-header">
                <!-- <h2><i class="fas fa-calendar-check"></i> Registro de Cuidados</h2> -->
                <button id="btnAddCuidado" class="btn-add"><i class="fas fa-plus"></i> Novo Cuidado</button>
            </div>
            
            <div class="card-form" id="cuidadoFormCard">
                <div class="card-header">
                    <h3>Registrar Novo Cuidado</h3>
                    <button class="btn-close" id="closeCuidadoForm"><i class="fas fa-times"></i></button>
                </div>
                <form id="cuidadoForm" class="form" method="POST" action="cuidados.php">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="usuario_id">Usuário:</label>
                            <select id="usuario_id" name="usuario_id" required>
                                <option value="">Selecione um usuário</option>
                                <?php while($row = $result_usuarios->fetch_assoc()): ?>
                                    <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['nome']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="planta_id">Planta:</label>
                            <select id="planta_id" name="planta_id" required>
                                <option value="">Selecione uma planta</option>
                                <?php while($row = $result_plantas->fetch_assoc()): ?>
                                    <option value="<?php echo $row['id']; ?>" data-frequencia-rega="<?php echo $row['frequencia_rega']; ?>">
                                        <?php echo htmlspecialchars($row['nome_popular']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="tipo_cuidado">Tipo de Cuidado:</label>
                        <select id="tipo_cuidado" name="tipo_cuidado">
                            <option value="rega">Rega</option>
                            <option value="poda">Poda</option>
                            <option value="adubacao">Adubação</option>
                            <option value="transplante">Transplante</option>
                            <option value="controle_pragas">Controle de Pragas</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="data_cuidado">Data do Cuidado:</label>
                            <input type="date" id="data_cuidado" name="data_cuidado" required>
                        </div>
                        <div class="form-group">
                            <label for="intervalo_dias">
                                Intervalo (dias)
                                <div class="info-icon">
                                    <i class="fas fa-info"></i>
                                    <div class="tooltip">
                                        Se você registra uma cuidado com intervalo de 7 dias, significa que o cuidado será feito a cada 7 dias
                                    </div>
                                </div>
                            </label>
                            <input type="number" id="intervalo_dias" name="intervalo_dias" min="1" max="365" value="7">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="observacoes">Observações:</label>
                        <textarea id="observacoes" name="observacoes" rows="3"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Registrar</button>
                        <button type="reset" class="btn btn-secondary"><i class="fas fa-undo"></i> Limpar</button>
                    </div>
                </form>
            </div>

            <div class="data-table-container">
                <table class="data-table" id="cuidadosTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuário</th>
                            <th>Planta</th>
                            <th>Tipo</th>
                            <th>Data</th>
                            <th>intervalo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['usuario_nome']); ?></td>
                                    <td><?php echo htmlspecialchars($row['planta_nome']); ?></td>
                                    <td><?php echo htmlspecialchars($row['tipo_cuidado']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['data_cuidado'])); ?></td>
                                    <td><span class="status-badge normal"><?php echo $row['intervalo_dias']; ?> dias</span></td>
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
                                <td colspan="7" class="empty-table">Nenhum cuidado registrado</td>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const plantaSelect = document.getElementById('planta_id');
            const tipoCuidadoSelect = document.getElementById('tipo_cuidado');
            const intervaloInput = document.getElementById('intervalo_dias');

            function atualizarIntervalo() {
                if (tipoCuidadoSelect.value === 'rega') {
                    const plantaSelecionada = plantaSelect.options[plantaSelect.selectedIndex];
                    const frequenciaRega = plantaSelecionada.dataset.frequenciaRega;
                    if (frequenciaRega) {
                        intervaloInput.value = frequenciaRega;
                    }
                }
            }

            function verificarIntervalo() {
                if (tipoCuidadoSelect.value === 'rega') {
                    const plantaSelecionada = plantaSelect.options[plantaSelect.selectedIndex];
                    const frequenciaRega = parseInt(plantaSelecionada.dataset.frequenciaRega);
                    const intervaloEscolhido = parseInt(intervaloInput.value);
                    const nomePlanta = plantaSelecionada.textContent.trim();

                    if (intervaloEscolhido !== frequenciaRega) {
                        let mensagem = `Atenção!\n\n`;
                        mensagem += `O intervalo de rega escolhido (${intervaloEscolhido} dias) é diferente do recomendado para ${nomePlanta}.\n\n`;
                        
                        if (intervaloEscolhido > frequenciaRega) {
                            mensagem += `Este intervalo é MAIOR que o recomendado. `;
                            mensagem += `A planta pode sofrer com falta de água.\n`;
                        } else {
                            mensagem += `Este intervalo é MENOR que o recomendado. `;
                            mensagem += `A planta pode sofrer com excesso de água.\n`;
                        }
                        
                        mensagem += `\nRecomendação: Regar a cada ${frequenciaRega} dias.`;
                        mensagem += `\n\nDeseja manter o intervalo escolhido?`;

                        if (!confirm(mensagem)) {
                            intervaloInput.value = frequenciaRega;
                        }
                    }
                }
            }

            plantaSelect.addEventListener('change', atualizarIntervalo);
            tipoCuidadoSelect.addEventListener('change', atualizarIntervalo);
            intervaloInput.addEventListener('change', verificarIntervalo);
            intervaloInput.addEventListener('blur', verificarIntervalo);
        });
    </script>
</body>
</html>
<?php $conn->close(); ?> 