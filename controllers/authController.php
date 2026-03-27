<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Reads and caches users table columns.
 */
function getUsersTableColumns(PDO $pdo): array
{
    static $cachedColumns = null;

    if ($cachedColumns !== null) {
        return $cachedColumns;
    }

    $columnsStmt = $pdo->query('SHOW COLUMNS FROM users');
    $cachedColumns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN, 0);

    return $cachedColumns;
}

/**
 * Returns the display-name column used by users table.
 */
function getUserNameColumn(PDO $pdo): string
{
    $columns = getUsersTableColumns($pdo);

    if (in_array('full_name', $columns, true)) {
        return 'full_name';
    }

    if (in_array('username', $columns, true)) {
        return 'username';
    }

    return 'full_name';
}

/**
 * Returns the password-hash column used by users table.
 */
function getUserPasswordColumn(PDO $pdo): string
{
    $columns = getUsersTableColumns($pdo);

    if (in_array('password_hash', $columns, true)) {
        return 'password_hash';
    }

    if (in_array('password', $columns, true)) {
        return 'password';
    }

    return 'password_hash';
}

/**
 * Registers a new user after validating basic input rules.
 */
function registerUser(PDO $pdo, string $username, string $email, string $password): array
{
    $errors = [];

    $username = trim($username);
    $email = trim($email);

    if ($username === '' || $email === '' || $password === '') {
        $errors[] = 'All fields are required.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    if (!empty($errors)) {
        return $errors;
    }

    $checkStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $checkStmt->execute(['email' => $email]);

    if ($checkStmt->fetch()) {
        return ['This email is already registered.'];
    }

    // Always store hashed password; this works for both `password_hash` and `password` columns.
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $nameColumn = getUserNameColumn($pdo);
    $passwordColumn = getUserPasswordColumn($pdo);

    $insertSql = sprintf(
        'INSERT INTO users (%s, email, %s) VALUES (:name, :email, :password_value)',
        $nameColumn,
        $passwordColumn
    );

    $insertStmt = $pdo->prepare($insertSql);
    $insertStmt->execute([
        'name' => $username,
        'email' => $email,
        'password_value' => $passwordHash,
    ]);

    return [];
}

/**
 * Attempts user login and stores user id in session on success.
 */
function loginUser(PDO $pdo, string $email, string $password): array
{
    $email = trim($email);

    if ($email === '' || $password === '') {
        return ['Email and password are required.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['Please provide a valid email address.'];
    }

    $nameColumn = getUserNameColumn($pdo);
    $passwordColumn = getUserPasswordColumn($pdo);

    $selectSql = sprintf(
        'SELECT id, %s AS full_name, %s AS password_hash FROM users WHERE email = :email LIMIT 1',
        $nameColumn,
        $passwordColumn
    );

    $stmt = $pdo->prepare($selectSql);
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, (string) $user['password_hash'])) {
        return ['Invalid email or password.'];
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_name'] = (string) $user['full_name'];

    return [];
}

/**
 * Returns current logged-in user row or null.
 */
function getCurrentUser(PDO $pdo): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $nameColumn = getUserNameColumn($pdo);

    $selectSql = sprintf(
        'SELECT id, %s AS full_name, email, created_at FROM users WHERE id = :id LIMIT 1',
        $nameColumn
    );

    $stmt = $pdo->prepare($selectSql);
    $stmt->execute(['id' => (int) $_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_name'] = (string) $user['full_name'];
    }

    return $user ?: null;
}

/**
 * Redirects to login page if user is not authenticated.
 */
function requireAuth(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Logs out current user and clears session data.
 */
function logoutUser(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}
