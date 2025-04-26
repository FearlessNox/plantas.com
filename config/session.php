<?php
// Configurações de Sessão
session_name('plantcare_session');

// Configurações de cookie da sessão
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Iniciar a sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}