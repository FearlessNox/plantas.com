<?php
// Configurações de Segurança
define('CSRF_TOKEN_NAME', 'plantcare_csrf_token');
define('SESSION_NAME', 'plantcare_session');
define('COOKIE_HTTPONLY', true);
define('COOKIE_SECURE', true);
define('COOKIE_SAMESITE', 'Strict');

// Configurações de Headers de Segurança
define('SECURITY_HEADERS', [
    'X-Frame-Options' => 'DENY',
    'X-XSS-Protection' => '1; mode=block',
    'X-Content-Type-Options' => 'nosniff',
    'Referrer-Policy' => 'strict-origin-when-cross-origin',
    'Content-Security-Policy' => "default-src 'self' https: data:; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://unpkg.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self' https://cdnjs.cloudflare.com;"
]);

// Função para gerar token CSRF
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Função para validar token CSRF
function validate_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('Acesso negado: Token CSRF inválido');
    }
    return true;
}

// Função para sanitizar entrada
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Aplicar headers de segurança
foreach (SECURITY_HEADERS as $header => $value) {
    header("$header: $value");
} 