<?php
require_once 'config/database.php';

// Conexão com o banco
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_cientifico = $_POST['nome_cientifico'];
    $nome_popular = $_POST['nome_popular'];
    $tipo_planta = $_POST['tipo_planta'];
    $nivel_luz = $_POST['nivel_luz'];
    $frequencia_rega = $_POST['frequencia_rega'];

    $sql = "INSERT INTO plantas (nome_cientifico, nome_popular, tipo_planta, nivel_luz, frequencia_rega) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $nome_cientifico, $nome_popular, $tipo_planta, $nivel_luz, $frequencia_rega);
    
    if ($stmt->execute()) {
        header("Location: plantas.php?success=1");
        exit();
    }
}

// Buscar plantas
$sql = "SELECT * FROM plantas";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plantas - PlantCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>
    <style>
        .content-wrapper {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding: 20px;
            gap: 20px;
        }
        
        .lottie-container {
            width: 300px;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .table-container {
            flex: 1;
        }
        
        @media (max-width: 1024px) {
            .lottie-container {
                display: none;
            }
        }
    </style>
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
                    <li><a href="/plantas.com/plantas.php" class="active"><i class="fas fa-seedling"></i> Plantas</a></li>
                    <li><a href="/plantas.com/usuarios.php"><i class="fas fa-users"></i> Usuários</a></li>
                    <li><a href="/plantas.com/cuidados.php"><i class="fas fa-calendar-check"></i> Cuidados</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <section id="plantas">
            <div class="section-header">
                <!-- <h2><i class="fas fa-seedling"></i> Gerenciamento de Plantas</h2> -->
                <button id="btnAddPlanta" class="btn-add"><i class="fas fa-plus"></i> Nova Planta</button>
            </div>
            
            <div class="content-wrapper">
                <!-- Lottie à esquerda -->
                <div class="lottie-container">
                    <dotlottie-player
                        src="https://lottie.host/9375c7fb-8050-47e2-9d6b-92b4a1c218f9/wZ6fZjlQDf.lottie"
                        background="transparent"
                        speed="1"
                        style="width: 300px; height: 300px"
                        loop
                        autoplay
                    ></dotlottie-player>
                </div>

                <!-- Tabela no centro -->
                <div class="table-container">
                    <div class="card-form" id="plantaFormCard">
                        <div class="card-header">
                            <h3>Cadastrar Nova Planta</h3>
                            <button class="btn-close" id="closePlantaForm"><i class="fas fa-times"></i></button>
                        </div>
                        <form id="plantaForm" class="form" method="POST" action="plantas.php">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <div class="form-group">
                                <label for="nome_cientifico">Nome Científico:</label>
                                <input type="text" id="nome_cientifico" name="nome_cientifico" required>
                            </div>
                            <div class="form-group">
                                <label for="nome_popular">Nome Popular:</label>
                                <input type="text" id="nome_popular" name="nome_popular" required>
                            </div>
                            <div class="form-group">
                                <label for="tipo_planta">Tipo de Planta:</label>
                                <select id="tipo_planta" name="tipo_planta">
                                    <option value="interior">Planta de Interior</option>
                                    <option value="exterior">Planta de Exterior</option>
                                    <option value="suculenta">Suculenta</option>
                                    <option value="frutifera">Frutífera</option>
                                    <option value="hortalica">Hortaliça</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="nivel_luz">Necessidade de Luz:</label>
                                <select id="nivel_luz" name="nivel_luz">
                                    <option value="baixa">Baixa</option>
                                    <option value="media">Média</option>
                                    <option value="alta">Alta</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="frequencia_rega">Frequência de Rega (dias):</label>
                                <input type="number" id="frequencia_rega" name="frequencia_rega" min="1" max="30" value="7">
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Cadastrar</button>
                                <button type="reset" class="btn btn-secondary"><i class="fas fa-undo"></i> Limpar</button>
                            </div>
                        </form>
                    </div>

                    <div class="data-table-container">
                        <table class="data-table" id="plantasTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome Científico</th>
                                    <th>Nome Popular</th>
                                    <th>Tipo</th>
                                    <th>Luz</th>
                                    <th>Rega (dias)</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($result) > 0): ?>
                                    <?php foreach ($result as $row): ?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td><?php echo htmlspecialchars($row['nome_cientifico']); ?></td>
                                            <td><?php echo htmlspecialchars($row['nome_popular'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['tipo_planta'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['nivel_luz'] ?? ''); ?></td>
                                            <td><?php echo $row['frequencia_rega'] ?? ''; ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn-action btn-delete" data-id="<?php echo $row['id']; ?>" title="Excluir">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="empty-table">Nenhuma planta cadastrada</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Lottie à direita -->
                <div class="lottie-container">
                    <dotlottie-player
                        src="https://lottie.host/9375c7fb-8050-47e2-9d6b-92b4a1c218f9/wZ6fZjlQDf.lottie"
                        background="transparent"
                        speed="1"
                        style="width: 300px; height: 300px"
                        loop
                        autoplay
                    ></dotlottie-player>
                </div>
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
        // Debug do Lottie
        document.addEventListener('DOMContentLoaded', function() {
            const containers = document.querySelectorAll('.lottie-container');
            containers.forEach(container => {
                console.log('Container Lottie encontrado:', container);
                const player = container.querySelector('dotlottie-player');
                if (player) {
                    console.log('Player Lottie encontrado:', player);
                    player.addEventListener('error', function(e) {
                        console.error('Erro no Lottie:', e);
                    });
                    player.addEventListener('ready', function() {
                        console.log('Lottie pronto para reproduzir');
                    });
                    player.addEventListener('load', function() {
                        console.log('Lottie carregado com sucesso');
                    });
                }
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?> 