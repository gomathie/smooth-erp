<?php

/**
 * Minimal application logger with daily rotation.
 *
 * Writes to storage/logs/app.log. When the current app.log is from a previous
 * day, it is rotated to app-YYYY-MM-DD.log before today's first entry, and
 * rotated files older than the retention window are pruned.
 *
 * Usage:
 *   Logger::info('User logged in', ['id' => 5]);
 *   Logger::error('Payment failed', ['invoice' => 12]);
 *
 * No namespaces — matches the rest of the codebase (global classes).
 */
class Logger
{
    /** Days to keep rotated logs before pruning. */
    private const RETENTION_DAYS = 14;

    /** storage/logs/ — app/Helpers is two levels under the project root. */
    private static function dir(): string
    {
        return dirname(__DIR__, 2) . '/storage/logs';
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        $dir = self::dir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $file = $dir . '/app.log';
        self::rotateIfStale($file);

        $line = sprintf(
            "[%s] %s: %s%s%s",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            $context ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '',
            PHP_EOL
        );

        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }

    /** If app.log was last written on an earlier day, archive it to app-<that-day>.log. */
    private static function rotateIfStale(string $file): void
    {
        if (!is_file($file)) {
            return;
        }
        $fileDay = date('Y-m-d', filemtime($file));
        if ($fileDay !== date('Y-m-d')) {
            @rename($file, dirname($file) . "/app-{$fileDay}.log");
            self::prune(dirname($file));
        }
    }

    private static function prune(string $dir): void
    {
        $cutoff = time() - (self::RETENTION_DAYS * 86400);
        foreach (glob($dir . '/app-*.log') ?: [] as $old) {
            if (@filemtime($old) < $cutoff) {
                @unlink($old);
            }
        }
    }

    public static function debug(string $m, array $c = []): void    { self::log('debug', $m, $c); }
    public static function info(string $m, array $c = []): void     { self::log('info', $m, $c); }
    public static function warning(string $m, array $c = []): void  { self::log('warning', $m, $c); }
    public static function error(string $m, array $c = []): void    { self::log('error', $m, $c); }
    public static function critical(string $m, array $c = []): void { self::log('critical', $m, $c); }
}
