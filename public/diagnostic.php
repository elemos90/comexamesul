<?php
// Diagnostic page - check env loading
echo "<h1>Environment Diagnostic</h1>";
echo "<pre>";

echo "=== PHP Info ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Current Working Directory: " . getcwd() . "\n";
echo "__DIR__: " . __DIR__ . "\n";
echo "__FILE__: " . __FILE__ . "\n\n";

echo "=== Path Tests ===\n";
$envPath1 = __DIR__ . '/../.env';
$envPath2 = dirname(__DIR__) . '/.env';
$envPath3 = $_SERVER['DOCUMENT_ROOT'] . '/../.env';

echo "Path 1 (__DIR__/../.env): $envPath1\n";
echo "  Exists: " . (file_exists($envPath1) ? 'YES' : 'NO') . "\n";
echo "  Readable: " . (is_readable($envPath1) ? 'YES' : 'NO') . "\n\n";

echo "Path 2 (dirname(__DIR__)/.env): $envPath2\n";
echo "  Exists: " . (file_exists($envPath2) ? 'YES' : 'NO') . "\n";
echo "  Readable: " . (is_readable($envPath2) ? 'YES' : 'NO') . "\n\n";

echo "Path 3 (DOCUMENT_ROOT/../.env): $envPath3\n";
echo "  Exists: " . (file_exists($envPath3) ? 'YES' : 'NO') . "\n";
echo "  Readable: " . (is_readable($envPath3) ? 'YES' : 'NO') . "\n\n";

echo "=== Reading .env content ===\n";
if (file_exists($envPath1)) {
    $content = file_get_contents($envPath1);
    echo "File size: " . strlen($content) . " bytes\n";
    echo "First 300 chars:\n";
    echo htmlspecialchars(substr($content, 0, 300)) . "\n\n";

    echo "Parsing lines:\n";
    $lines = file($envPath1, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach (array_slice($lines, 0, 10) as $i => $line) {
        $line = trim($line);
        if ($line && !str_starts_with($line, '#')) {
            echo "Line $i: " . htmlspecialchars($line) . "\n";
            if (str_contains($line, '=')) {
                $parts = explode('=', $line, 2);
                echo "  Key: [{$parts[0]}]\n";
                echo "  Value: [" . (isset($parts[1]) ? htmlspecialchars($parts[1]) : 'NONE') . "]\n";
            }
        }
    }
}

echo "\n=== Bootstrap PATH ===\n";
if (defined('BASE_PATH')) {
    echo "BASE_PATH: " . BASE_PATH . "\n";
} else {
    echo "BASE_PATH: NOT DEFINED\n";
}

echo "\n=== Environment Variables ===\n";
echo "APP_NAME from env(): " . (function_exists('env') ? var_export(env('APP_NAME'), true) : 'env() not available') . "\n";
echo "APP_NAME from \$_ENV: " . (isset($_ENV['APP_NAME']) ? $_ENV['APP_NAME'] : 'NOT SET') . "\n";
echo "APP_NAME from \$_SERVER: " . (isset($_SERVER['APP_NAME']) ? $_SERVER['APP_NAME'] : 'NOT SET') . "\n";
echo "APP_NAME from getenv(): " . (getenv('APP_NAME') ?: 'NOT SET') . "\n";

echo "\n=== All \$_ENV keys ===\n";
if (!empty($_ENV)) {
    foreach (array_keys($_ENV) as $key) {
        if (str_starts_with($key, 'APP_') || str_starts_with($key, 'DB_') || str_starts_with($key, 'SESSION_')) {
            echo "  $key\n";
        }
    }
} else {
    echo "  (empty)\n";
}

echo "</pre>";
