<?php

/**
 * Reusable authentication / authorization guards.
 *
 * Built on the existing session + Permission + Tenant helpers so it matches the
 * current behaviour exactly (the inline `if (!Permission::has($k)) { redirect }`
 * pattern used across the views). Adopt incrementally — e.g. at the top of a
 * module:  AuthMiddleware::requirePermission('sales');
 */
class AuthMiddleware
{
    public static function check(): bool
    {
        return isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === 'ok';
    }

    public static function requireLogin(string $redirect = 'login'): void
    {
        if (!self::check()) {
            self::redirect($redirect);
        }
    }

    public static function requirePermission(string $key, string $redirect = 'home'): void
    {
        if (!self::check() || !class_exists('Permission') || !Permission::has($key)) {
            self::redirect($redirect);
        }
    }

    public static function requireSuperAdmin(string $redirect = 'home'): void
    {
        if (!class_exists('Tenant') || !Tenant::isSuperAdmin()) {
            self::redirect($redirect);
        }
    }

    private static function redirect(string $route): void
    {
        if (!headers_sent()) {
            header('Location: index.php?route=' . $route);
        } else {
            echo '<script>window.location = "' . htmlspecialchars($route, ENT_QUOTES) . '";</script>';
        }
        exit;
    }
}
