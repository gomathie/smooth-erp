<?php

/**
 * Global error / exception handler.
 *
 * Registered once at the top of index.php (and can be used by other entry
 * points). Logs every uncaught error/exception/fatal via Logger, then renders
 * a friendly page — or full detail when APP_DEBUG=true in config/.env.
 *
 * Requires Logger (app/Helpers/Logger.php) to be loaded first.
 */
class Handler
{
    public static function register(): void
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /** Convert PHP warnings/notices into log entries; defer to PHP's default rendering. */
    public static function handleError(int $no, string $str, string $file = '', int $line = 0): bool
    {
        if (!(error_reporting() & $no)) {
            return false; // suppressed via @ or error_reporting() — ignore
        }
        // Log real problems only; skip notices/deprecations to avoid log spam.
        $noise = E_NOTICE | E_USER_NOTICE | E_DEPRECATED | E_USER_DEPRECATED | E_STRICT;
        if (!($no & $noise)) {
            Logger::warning('PHP error: ' . $str, ['file' => $file, 'line' => $line, 'type' => $no]);
        }
        return false; // let the normal PHP handler run too (respects display_errors)
    }

    public static function handleException(\Throwable $e): void
    {
        Logger::error('Uncaught ' . get_class($e) . ': ' . $e->getMessage(), [
            'file' => $e->getFile(), 'line' => $e->getLine(),
        ]);
        self::render($e->getMessage());
    }

    public static function handleShutdown(): void
    {
        $e = error_get_last();
        if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            Logger::critical('Fatal: ' . $e['message'], ['file' => $e['file'], 'line' => $e['line']]);
            self::render($e['message']);
        }
    }

    private static function render(string $detail): void
    {
        if (headers_sent()) {
            return; // output already started (e.g. mid-page) — it's logged; don't corrupt the page
        }
        http_response_code(500);
        $debug = self::debugEnabled();
        echo '<!doctype html><meta charset="utf-8"><title>Server error</title>';
        echo '<div style="font-family:system-ui,sans-serif;max-width:680px;margin:10vh auto;padding:24px;border:1px solid #eee;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.06)">';
        echo '<h2 style="margin:0 0 8px">Something went wrong</h2>';
        echo '<p style="color:#666">The error has been logged. Please try again, or contact your administrator.</p>';
        if ($debug) {
            echo '<pre style="background:#f8f9fa;padding:12px;border-radius:8px;overflow:auto;color:#b00020;white-space:pre-wrap">'
               . htmlspecialchars($detail) . '</pre>';
        }
        echo '</div>';
    }

    /** Read APP_DEBUG straight from config/.env (handler may run before env helpers load). */
    private static function debugEnabled(): bool
    {
        $root = dirname(__DIR__, 2);
        $path = is_file($root . '/config/.env') ? $root . '/config/.env' : $root . '/.env';
        if (!is_file($path)) {
            return false;
        }
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (stripos(trim($line), 'APP_DEBUG') === 0 && str_contains($line, '=')) {
                $v = strtolower(trim(explode('=', $line, 2)[1], " \t\"'"));
                return $v === 'true' || $v === '1';
            }
        }
        return false;
    }
}
