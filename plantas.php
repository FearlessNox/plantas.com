<?php
require_once 'config/db_config.php';  // Corrigido de database.php para db_config.php
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
                                    <th>Nome Popular</th>
                                    <th>Nome Científico</th>
                                    <th>Tipo</th>
                                    <th>Luz</th>
                                    <th>Rega (dias)</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['nome_popular']); ?></td>
                                            <td><em><?php echo htmlspecialchars($row['nome_cientifico']); ?></em></td>
                                            <td><?php echo ucfirst(htmlspecialchars($row['tipo_planta'])); ?></td>
                                            <td><?php echo ucfirst(htmlspecialchars($row['nivel_luz'])); ?></td>
                                            <td><?php echo htmlspecialchars($row['frequencia_rega']); ?></td>
                                            <td class="actions">
                                                <button onclick="abrirModalEditar(<?php echo $row['id']; ?>)" class="btn-edit" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="excluirPlanta(<?php echo $row['id']; ?>)" class="btn-delete" title="Excluir">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
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

    <!-- Modal de Edição -->
    <div id="modalEditar" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Editar Planta</h3>
                <button type="button" class="modal-close" onclick="fecharModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formEditar" method="POST" class="form">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="form-group">
                        <label for="edit_nome_cientifico">Nome Científico:</label>
                        <input type="text" id="edit_nome_cientifico" name="nome_cientifico" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_nome_popular">Nome Popular:</label>
                        <input type="text" id="edit_nome_popular" name="nome_popular" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_tipo_planta">Tipo de Planta:</label>
                        <select id="edit_tipo_planta" name="tipo_planta">
                            <option value="interior">Planta de Interior</option>
                            <option value="exterior">Planta de Exterior</option>
                            <option value="suculenta">Suculenta</option>
                            <option value="frutifera">Frutífera</option>
                            <option value="hortalica">Hortaliça</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_nivel_luz">Necessidade de Luz:</label>
                        <select id="edit_nivel_luz" name="nivel_luz">
                            <option value="baixa">Baixa</option>
                            <option value="media">Média</option>
                            <option value="alta">Alta</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_frequencia_rega">Frequência de Rega (dias):</label>
                        <input type="number" id="edit_frequencia_rega" name="frequencia_rega" min="1" max="30">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar</button>
                        <button type="button" class="btn btn-secondary" onclick="fecharModal()"><i class="fas fa-times"></i> Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function mostrarNotificacao(mensagem, tipo = 'error') {
        const notification = document.createElement('div');
        notification.className = `notification ${tipo}`;
        notification.innerHTML = `
            <div class="notification-content">
                <div class="notification-message">${mensagem}</div>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
            </div>
        `;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('closing');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }

    function abrirModalEditar(id) {
        // Buscar dados da planta via AJAX
        fetch(`get_planta.php?id=${id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(planta => {
                if (planta.error) {
                    throw new Error(planta.error);
                }
                
                // Preencher o formulário com os dados
                document.getElementById('edit_id').value = planta.id;
                document.getElementById('edit_nome_cientifico').value = planta.nome_cientifico;
                document.getElementById('edit_nome_popular').value = planta.nome_popular;
                document.getElementById('edit_tipo_planta').value = planta.tipo_planta;
                document.getElementById('edit_nivel_luz').value = planta.nivel_luz;
                document.getElementById('edit_frequencia_rega').value = planta.frequencia_rega;
                
                // Mostrar o modal
                document.getElementById('modalEditar').classList.add('active');
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarNotificacao('Erro ao carregar os dados da planta: ' + error.message);
            });
    }

    function fecharModal() {
        document.getElementById('modalEditar').classList.remove('active');
    }

    // Enviar formulário via AJAX
    document.getElementById('formEditar').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('atualizar_planta.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin' // Importante para enviar cookies de sessão
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                mostrarNotificacao('Planta atualizada com sucesso!', 'success');
                fecharModal();
                setTimeout(() => window.location.reload(), 1000);
            } else {
                throw new Error(data.message || 'Erro ao atualizar planta');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            mostrarNotificacao(error.message);
        });
    });

    // Fechar modal ao clicar fora dele
    document.getElementById('modalEditar').addEventListener('click', function(e) {
        if (e.target === this) {
            fecharModal();
        }
    });

    // Fechar modal com tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('modalEditar').classList.contains('active')) {
            fecharModal();
        }
    });

    // Função para excluir planta
    function excluirPlanta(id) {
        if (confirm('Tem certeza que deseja excluir esta planta?')) {
            fetch('excluir_planta.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}`,
                credentials: 'same-origin'
            })
            .then(response => {

                return response.json();
            })
            .then(data => {
                if (data.success) {
                    mostrarNotificacao('Planta excluída com sucesso!', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    throw new Error(data.message || 'Erro ao excluir planta');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarNotificacao(error.message, 'error');
            });
        }
    }
    </script>
</body>
</html>
<?php 
// Removida a linha $conn->close(); pois não é necessária com PDO
?>