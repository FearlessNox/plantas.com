<?php
require 'vendor/autoload.php';
require 'config/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Configurações do email
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'vitorfigueiredl2005@gmail.com'; // Substitua pelo seu email
$mail->Password = 'sua-senha-app'; // Substitua pela sua senha de app
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
$mail->CharSet = 'UTF-8';

// Email de teste
$destinatario = 'email-vitorfigueiredl2005@gmail.com'; // Substitua pelo email de teste
$assunto = 'Teste de Email - Sistema de Plantas';
$corpo = 'Este é um email de teste do Sistema de Gerenciamento de Plantas.';

try {
    // Configurar email
    $mail->setFrom($mail->Username, 'Sistema de Plantas');
    $mail->addAddress($destinatario);
    $mail->Subject = $assunto;
    $mail->Body = $corpo;

    // Tentar enviar o email
    $mail->send();
    $status = 'Sucesso';
    
} catch (Exception $e) {
    $status = 'Erro: ' . $mail->ErrorInfo;
}

// Registrar resultado no banco de dados
$sql = "INSERT INTO email_test_log (destinatario, status, data_teste) VALUES (?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $destinatario, $status);

if ($stmt->execute()) {
    echo "Teste de email realizado e registrado com sucesso.<br>";
    echo "Status: " . $status;
} else {
    echo "Erro ao registrar o teste de email: " . $conn->error;
}

$stmt->close();
$conn->close(); 