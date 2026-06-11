<?php

/**
 * Lightweight fixed-window rate limiter (session-backed).
 *
 * Useful for throttling sensitive actions such as login / password-reset.
 * Example (in the login controller):
 *
 *   if (RateLimitMiddleware::tooMany('login:' . $_SERVER['REMOTE_ADDR'], 5, 300)) {
 *       // 5 attempts / 5 minutes exceeded — reject
 *   }
 *   // on success:
 *   RateLimitMiddleware::reset('login:' . $_SERVER['REMOTE_ADDR']);
 */
class RateLimitMiddleware
{
    /** Returns true once attempts exceed $maxAttempts within $windowSeconds. */
    public static function tooMany(string $key, int $maxAttempts = 5, int $windowSeconds = 300): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $now    = time();
        $bucket = $_SESSION['_rl'][$key] ?? ['count' => 0, 'reset' => $now + $windowSeconds];

        if ($now > $bucket['reset']) {
            $bucket = ['count' => 0, 'reset' => $now + $windowSeconds];
        }

        $bucket['count']++;
        $_SESSION['_rl'][$key] = $bucket;

        if ($bucket['count'] > $maxAttempts) {
            if (class_exists('Logger')) {
                Logger::warning('Rate limit exceeded', ['key' => $key, 'count' => $bucket['count']]);
            }
            return true;
        }
        return false;
    }

    public static function reset(string $key): void
    {
        unset($_SESSION['_rl'][$key]);
    }
}
