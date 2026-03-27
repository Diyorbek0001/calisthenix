<?php

declare(strict_types=1);

$host = 'localhost';
$dbName = 'calisthenics_db';
$username = 'root';
$password = 'root';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$dbName};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $exception) {
    http_response_code(500);
    die('Database connection failed. Please check your configuration.');
}
