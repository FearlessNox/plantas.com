<?php
// Headers de segurança
define('SECURITY_HEADERS', [
    'X-Frame-Options' => 'SAMEORIGIN',
    'X-XSS-Protection' => '1; mode=block',
    'X-Content-Type-Options' => 'nosniff',
    'Referrer-Policy' => 'same-origin',
    'Content-Security-Policy' => "default-src 'self' https: 'unsafe-inline' 'unsafe-eval';"
]);

// Função para validar dados de entrada
function validate_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Função para sanitizar dados de entrada
function sanitize_input($data) {
    if (is_string($data)) {
        // Remove espaços em branco extras
        $data = trim($data);
        
        // Remove caracteres potencialmente perigosos
        $data = filter_var($data, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        
        // Converte caracteres especiais em entidades HTML
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        
        return $data;
    }
    return false;
}

// Função para validar email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Aplicar headers de segurança
foreach (SECURITY_HEADERS as $header => $value) {
    header("$header: $value");
}