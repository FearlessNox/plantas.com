<?php
require_once 'config/database.php';
//PORQUE NOTA 6 PATRICK?? FIZEMOS TUDO CERTO 
// Conexão com o banco
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Buscar próximos cuidados (para os próximos 7 dias)
$sql_cuidados = "SELECT c.*, u.nome as usuario_nome, p.nome_popular as planta_nome,
       DATE_ADD(c.data_cuidado, INTERVAL c.intervalo_dias DAY) as proxima_data
FROM cuidados c 
JOIN usuarios u ON c.usuario_id = u.id 
JOIN plantas p ON c.planta_id = p.id
WHERE DATE_ADD(c.data_cuidado, INTERVAL c.intervalo_dias DAY) 
BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
ORDER BY proxima_data ASC";

$result_proximos = $conn->query($sql_cuidados);

// Buscar estatísticas
$sql_stats = "
    SELECT 
        (SELECT COUNT(*) FROM plantas) as total_plantas,
        (SELECT COUNT(*) FROM usuarios) as total_usuarios,
        (SELECT COUNT(*) 
         FROM cuidados c
         WHERE DATE(DATE_ADD(c.data_cuidado, INTERVAL COALESCE(c.intervalo_dias, 0) DAY)) = CURDATE()
         AND c.intervalo_dias IS NOT NULL
         AND c.data_cuidado < CURDATE()  -- Apenas cuidados registrados antes de hoje
        ) as cuidados_hoje";

$result_stats = $conn->query($sql_stats);
$stats = $result_stats->fetch_assoc();

// Debug - Mostrar cuidados de hoje
$sql_debug = "
    SELECT c.*, u.nome as usuario_nome, p.nome_popular as planta_nome,
           DATE_ADD(c.data_cuidado, INTERVAL c.intervalo_dias DAY) as proxima_data
    FROM cuidados c 
    JOIN usuarios u ON c.usuario_id = u.id 
    JOIN plantas p ON c.planta_id = p.id
    WHERE DATE(DATE_ADD(c.data_cuidado, INTERVAL c.intervalo_dias DAY)) = CURDATE()
    AND c.intervalo_dias IS NOT NULL";
$result_debug = $conn->query($sql_debug);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlantCare - Sistema de Plantas Domésticas</title>
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
                    <li><a href="/plantas.com/index.php" class="active"><i class="fas fa-home"></i> Início</a></li>
                    <li><a href="/plantas.com/plantas.php"><i class="fas fa-seedling"></i> Plantas</a></li>
                    <li><a href="/plantas.com/usuarios.php"><i class="fas fa-users"></i> Usuários</a></li>
                    <li><a href="/plantas.com/cuidados.php"><i class="fas fa-calendar-check"></i> Cuidados</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <section id="dashboard">
            <div class="dashboard-header">
                <h2>Painel de Controle</h2>
                <p>Bem-vindo ao seu sistema de gerenciamento de plantas domésticas</p>
            </div>

            <!-- Estatísticas -->
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-seedling"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Total de Plantas</h4>
                        <p class="stat-number"><?php echo $stats['total_plantas']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Total de Usuários</h4>
                        <p class="stat-number"><?php echo $stats['total_usuarios']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Cuidados Hoje</h4>
                        <p class="stat-number"><?php echo $stats['cuidados_hoje']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Próximos Cuidados -->
            <div class="upcoming-care">
                <h3><i class="fas fa-bell"></i> Próximos Cuidados</h3>
                <div class="care-list">
                    <?php if ($result_proximos->num_rows > 0): ?>
                        <?php while($cuidado = $result_proximos->fetch_assoc()): ?>
                            <div class="care-item">
                                <div class="care-icon">
                                    <?php
                                    $icon = 'tint';
                                    switch($cuidado['tipo_cuidado']) {
                                        case 'poda': $icon = 'cut'; break;
                                        case 'adubacao': $icon = 'prescription-bottle'; break;
                                        case 'transplante': $icon = 'exchange-alt'; break;
                                        case 'controle_pragas': $icon = 'bug'; break;
                                    }
                                    ?>
                                    <i class="fas fa-<?php echo $icon; ?>"></i>
                                </div>
                                <div class="care-info">
                                    <h4><?php echo htmlspecialchars($cuidado['planta_nome']); ?></h4>
                                    <p>
                                        <strong>Cuidado:</strong> <?php echo ucfirst($cuidado['tipo_cuidado']); ?><br>
                                        <strong>Responsável:</strong> <?php echo htmlspecialchars($cuidado['usuario_nome']); ?><br>
                                        <strong>Data:</strong> <?php echo date('d/m/Y', strtotime($cuidado['proxima_data'])); ?>
                                    </p>
                                </div>
                                <div class="care-status">
                                    <?php
                                    $proxima_data = new DateTime($cuidado['proxima_data']);
                                    $hoje = new DateTime();
                                    $hoje->setTime(0, 0, 0); // Zerar as horas para comparar apenas as datas
                                    $proxima_data->setTime(0, 0, 0);
                                    
                                    // Calcula a diferença em dias
                                    $interval = $hoje->diff($proxima_data);
                                    $dias_restantes = $interval->days;

                                    if ($hoje->format('Y-m-d') === $proxima_data->format('Y-m-d')) {
                                        $status = 'Hoje';
                                        $status_class = 'urgent';
                                    } elseif ($dias_restantes === 1) {
                                        $status = 'Amanhã';
                                        $status_class = 'urgent';
                                    } elseif ($dias_restantes <= 3) {
                                        $status = 'Em ' . $dias_restantes . ' dias';
                                        $status_class = 'warning';
                                    } else {
                                        $status = 'Em ' . $dias_restantes . ' dias';
                                        $status_class = 'normal';
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo $status; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="no-care">Não há cuidados programados para os próximos 7 dias.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-icon"><i class="fas fa-seedling"></i></div>
                    <div class="card-info">
                        <h3>Plantas</h3>
                        <p>Gerencie suas plantas</p>
                    </div>
                    <a href="plantas.php" class="card-btn">Acessar</a>
                </div>
                <div class="card">
                    <div class="card-icon"><i class="fas fa-users"></i></div>
                    <div class="card-info">
                        <h3>Usuários</h3>
                        <p>Gerencie usuários</p>
                    </div>
                    <a href="usuarios.php" class="card-btn">Acessar</a>
                </div>
                <div class="card">
                    <div class="card-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="card-info">
                        <h3>Cuidados</h3>
                        <p>Registre cuidados</p>
                    </div>
                    <a href="cuidados.php" class="card-btn">Acessar</a>
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
</body>
</html>
<?php $conn->close(); ?>

