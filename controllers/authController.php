<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Registers a new user after validating basic input rules.
 * Returns an array with validation errors, if any.
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

    // Hash password before saving to database.
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $insertStmt = $pdo->prepare(
        'INSERT INTO users (full_name, email, password_hash) VALUES (:full_name, :email, :password_hash)'
    );

    $insertStmt->execute([
        'full_name' => $username,
        'email' => $email,
        'password_hash' => $passwordHash,
    ]);

    return [];
}

/**
 * Attempts user login and stores user id in session on success.
 */
function loginUser(PDO $pdo, string $email, string $password): array
{
    $errors = [];

    $email = trim($email);

    if ($email === '' || $password === '') {
        return ['Email and password are required.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['Please provide a valid email address.'];
    }

    $stmt = $pdo->prepare('SELECT id, full_name, password_hash FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['Invalid email or password.'];
    }

    // Prevent session fixation by generating a new id after login.
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];

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

    $stmt = $pdo->prepare('SELECT id, full_name, email, created_at FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => (int) $_SESSION['user_id']]);
    $user = $stmt->fetch();

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
