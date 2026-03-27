<?php

declare(strict_types=1);

$host = 'localhost';
$dbName = 'calisthenics_db';
$username = 'root';
$password = 'root';
$charset = 'utf8mb4';

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

$dsnCandidates = [
    "mysql:host={$host};dbname={$dbName};charset={$charset}",
    "mysql:host={$host};port=8889;dbname={$dbName};charset={$charset}",
];

$pdo = null;
$lastException = null;

foreach ($dsnCandidates as $dsn) {
    try {
        $pdo = new PDO($dsn, $username, $password, $options);
        break;
    } catch (PDOException $exception) {
        $lastException = $exception;
    }
}

if (!$pdo instanceof PDO) {
    error_log('Database connection failed: ' . ($lastException ? $lastException->getMessage() : 'Unknown error'));
    http_response_code(500);
    die('Database connection failed. Please check MAMP MySQL and config/db.php settings.');
}
