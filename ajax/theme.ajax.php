<?php

require_once "../helpers/security.php";

session_start();

$validThemes = [
    'blue','blue-light','black','black-light',
    'purple','purple-light','red','red-light',
    'green','green-light','yellow','yellow-light',
];

csrf_verify();

if (isset($_POST['theme']) && in_array($_POST['theme'], $validThemes, true)) {
    $theme = $_POST['theme'];
    $_SESSION['pos_theme'] = $theme;
    setcookie('pos_theme', $theme, time() + (365 * 24 * 60 * 60), '/', '', false, true);
    echo 'ok';
} else {
    http_response_code(400);
    echo 'invalid';
}
