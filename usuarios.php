<?php
require_once 'config/session.php';
require_once 'config/database.php';
require_once 'config/security.php';

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
                                            <button onclick="abrirModalEditar(<?php echo $row['id']; ?>)" class="btn-edit" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="excluirUsuario(<?php echo $row['id']; ?>)" class="btn-delete" title="Excluir">
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

    <!-- Modal de Edição -->
    <div id="modalEditar" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-edit"></i> Editar Usuário</h3>
                <button type="button" class="modal-close" onclick="fecharModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formEditar" method="POST" class="form">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="form-group">
                        <label for="edit_nome">Nome Completo:</label>
                        <input type="text" id="edit_nome" name="nome" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_email">Email:</label>
                        <input type="email" id="edit_email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_telefone">Telefone:</label>
                        <input type="tel" id="edit_telefone" name="telefone">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_nivel_experiencia">Nível de Experiência:</label>
                        <select id="edit_nivel_experiencia" name="nivel_experiencia">
                            <option value="iniciante">Iniciante</option>
                            <option value="intermediario">Intermediário</option>
                            <option value="avancado">Avançado</option>
                        </select>
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
        fetch(`get_usuario.php?id=${id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(usuario => {
                if (usuario.error) {
                    throw new Error(usuario.error);
                }
                
                document.getElementById('edit_id').value = usuario.id;
                document.getElementById('edit_nome').value = usuario.nome;
                document.getElementById('edit_email').value = usuario.email;
                document.getElementById('edit_telefone').value = usuario.telefone;
                document.getElementById('edit_nivel_experiencia').value = usuario.nivel_experiencia;
                
                document.getElementById('modalEditar').classList.add('active');
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarNotificacao('Erro ao carregar os dados do usuário: ' + error.message);
            });
    }

    function fecharModal() {
        document.getElementById('modalEditar').classList.remove('active');
    }

    // Enviar formulário via AJAX
    document.getElementById('formEditar').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('atualizar_usuario.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                mostrarNotificacao('Usuário atualizado com sucesso!', 'success');
                fecharModal();
                setTimeout(() => window.location.reload(), 1000);
            } else {
                throw new Error(data.message || 'Erro ao atualizar usuário');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            mostrarNotificacao(error.message);
        });
    });

    // Fechar modal ao clicar fora
    document.getElementById('modalEditar').addEventListener('click', function(e) {
        if (e.target === this) {
            fecharModal();
        }
    });

    // Fechar modal com ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('modalEditar').classList.contains('active')) {
            fecharModal();
        }
    });

    // Função para excluir usuário
    function excluirUsuario(id) {
        if (!confirm('Tem certeza que deseja excluir este usuário?')) {
            return;
        }

        const formData = new FormData();
        formData.append('id', id);
        fetch('excluir_usuario.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                mostrarNotificacao('Usuário excluído com sucesso!', 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                throw new Error(data.message || 'Erro ao excluir usuário');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            mostrarNotificacao(error.message);
        });
        if (confirm('Tem certeza que deseja excluir este usuário?')) {
            fetch('excluir_usuario.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}`,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarNotificacao('Usuário excluído com sucesso!', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    throw new Error(data.message || 'Erro ao excluir usuário');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarNotificacao(error.message);
            });
        }
    }
    </script>
</body>
</html>
<?php $conn->close(); ?>