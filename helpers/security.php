<?php

function csrf_verify() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $stored   = $_SESSION['csrf_token'] ?? '';
    $incoming = $_POST['_csrf']
        ?? $_GET['_csrf']
        ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!$stored || !hash_equals($stored, $incoming)) {
        http_response_code(403);
        die('CSRF token mismatch');
    }
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
