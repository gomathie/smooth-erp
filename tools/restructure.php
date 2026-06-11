<?php
/**
 * One-time, idempotent structure migration.
 *
 * Creates the new folders and moves existing files (.env, .env.example, the
 * stray output-*.txt) into their new homes. Safe to run repeatedly — it only
 * acts when a file is still in its old location. Touches NO database or business
 * data; it only reorganizes files.
 *
 * Run from the project root:   php tools/restructure.php
 *
 * CLI-only: refuses to execute over HTTP.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This migration script runs from the command line only.');
}

$root = dirname(__DIR__);
$did  = [];

function ensureDir(string $path, array &$did): void {
    if (!is_dir($path)) { mkdir($path, 0775, true); $did[] = "created dir  $path"; }
}

function moveIfPresent(string $from, string $to, array &$did): void {
    if (is_file($from) && !is_file($to)) {
        if (!is_dir(dirname($to))) { mkdir(dirname($to), 0775, true); }
        rename($from, $to);
        $did[] = "moved        $from  ->  $to";
    }
}

// 1) Folders
foreach ([
    'storage/logs', 'storage/cache', 'storage/uploads', 'storage/exports',
    'config', 'middleware', 'routes', 'app/Exceptions', 'app/Helpers',
] as $d) {
    ensureDir($root . '/' . $d, $did);
}

// 2) Configuration files -> config/
moveIfPresent($root . '/.env',          $root . '/config/.env',          $did);
moveIfPresent($root . '/.env.example',  $root . '/config/.env.example',  $did);

// 3) Stray generated exports -> storage/exports/
foreach (['output-products.txt', 'output-sales.txt'] as $f) {
    moveIfPresent($root . '/' . $f,        $root . '/storage/exports/' . $f, $did);
    moveIfPresent($root . '/ajax/' . $f,   $root . '/storage/exports/' . $f, $did);
}

// 4) Report
echo "Restructure complete.\n";
echo $did ? "  - " . implode("\n  - ", $did) . "\n" : "  (nothing to do — already migrated)\n";
echo "\nReminder: keep config/.env out of source control and ensure the web server\n";
echo "denies access to config/, storage/, models/, controllers/, helpers/, app/,\n";
echo "routes/, middleware/, database/ (handled by the root .htaccess).\n";
