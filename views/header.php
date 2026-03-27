<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$currentPage = $_GET['page'] ?? 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calisthenics Training Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php?page=home">CalisthenixTracker</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'home' ? 'active' : ''; ?>" href="index.php?page=home">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'login' ? 'active' : ''; ?>" href="index.php?page=login">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'register' ? 'active' : ''; ?>" href="index.php?page=register">Register</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>" href="index.php?page=dashboard">Dashboard</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<main class="py-5">
