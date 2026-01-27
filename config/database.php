<?php

$driver = env('DB_CONNECTION', 'mysql');

if ($driver === 'sqlite') {
    $dsn = 'sqlite:' . env('DB_DATABASE', ':memory:');
} else {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        env('DB_HOST', '127.0.0.1'),
        env('DB_PORT', '3306'),
        env('DB_DATABASE', 'comexamesul')
    );
}

return [
    'dsn' => $dsn,
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        // PDO::MYSQL_ATTR_FOUND_ROWS not supported by SQLite
    ] + ($driver === 'mysql' ? [PDO::MYSQL_ATTR_FOUND_ROWS => true] : []),
];

