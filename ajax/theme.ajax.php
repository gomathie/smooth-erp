<?php

session_start();

$validThemes = [
    'skin-blue','skin-blue-light','skin-black','skin-black-light',
    'skin-purple','skin-purple-light','skin-red','skin-red-light',
    'skin-green','skin-green-light','skin-yellow','skin-yellow-light'
];

if (isset($_POST['theme']) && in_array($_POST['theme'], $validThemes, true)) {
    $theme = $_POST['theme'];
    $_SESSION['pos_theme'] = $theme;
    setcookie('pos_theme', $theme, time() + (365 * 24 * 60 * 60), '/', '', false, true);
    echo 'ok';
} else {
    http_response_code(400);
    echo 'invalid';
}
