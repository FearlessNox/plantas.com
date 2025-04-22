<?php
require_once 'config/database.php';

// Conexão com o banco
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Buscar cuidados para os próximos 3 dias
$sql = "
    SELECT c.*, u.nome as usuario_nome, u.email as usuario_email,
           p.nome_popular as planta_nome,
           DATE_ADD(c.data_cuidado, INTERVAL c.frequencia DAY) as proxima_data
    FROM cuidados c 
    JOIN usuarios u ON c.usuario_id = u.id 
    JOIN plantas p ON c.planta_id = p.id
    WHERE DATE_ADD(c.data_cuidado, INTERVAL c.frequencia DAY) 
    BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
    ORDER BY proxima_data ASC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($cuidado = $result->fetch_assoc()) {
        $dias_restantes = floor((strtotime($cuidado['proxima_data']) - time()) / (60 * 60 * 24));
        
        // Preparar mensagem de email
        $to = $cuidado['usuario_email'];
        $subject = "Lembrete de Cuidado - PlantCare";
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2e7d32; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { text-align: center; padding: 20px; color: #666; }
                .button { display: inline-block; padding: 10px 20px; background: #2e7d32; color: white; text-decoration: none; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Lembrete de Cuidado</h1>
                </div>
                <div class='content'>
                    <p>Olá {$cuidado['usuario_nome']},</p>
                    <p>Este é um lembrete sobre um cuidado programado:</p>
                    <ul>
                        <li><strong>Planta:</strong> {$cuidado['planta_nome']}</li>
                        <li><strong>Tipo de Cuidado:</strong> " . ucfirst($cuidado['tipo_cuidado']) . "</li>
                        <li><strong>Data:</strong> " . date('d/m/Y', strtotime($cuidado['proxima_data'])) . "</li>
                        <li><strong>Quando:</strong> " . 
                            ($dias_restantes == 0 ? "Hoje" : 
                            ($dias_restantes == 1 ? "Amanhã" : 
                            "Em {$dias_restantes} dias")) . "</li>
                    </ul>
                    <p>Observações: {$cuidado['observacoes']}</p>
                    <p><a href='http://localhost/plantas.com/cuidados.php' class='button'>Ver Detalhes</a></p>
                </div>
                <div class='footer'>
                    <p>Este é um email automático do sistema PlantCare.</p>
                </div>
            </div>
        </body>
        </html>";
        
        // Headers para envio de email HTML
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: PlantCare <noreply@plantcare.com>" . "\r\n";
        
        // Enviar email
        mail($to, $subject, $message, $headers);
        
        // Registrar notificação enviada
        $sql_log = "INSERT INTO notificacoes_log (cuidado_id, usuario_id, data_envio) 
                   VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($sql_log);
        $stmt->bind_param("ii", $cuidado['id'], $cuidado['usuario_id']);
        $stmt->execute();
    }
}

$conn->close();

// Se executado via linha de comando, mostrar mensagem
if (php_sapi_name() === 'cli') {
    echo "Notificações enviadas com sucesso!\n";
}
?> 