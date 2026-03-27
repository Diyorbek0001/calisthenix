<?php

declare(strict_types=1);

require_once __DIR__ . '/../views/header.php';

$page = $_GET['page'] ?? 'home';
$allowedPages = ['home', 'login', 'register', 'dashboard'];

if (!in_array($page, $allowedPages, true)) {
    $page = 'home';
}
?>

<div class="container">
    <?php if ($page === 'home'): ?>
        <section class="hero-section text-center">
            <h1 class="display-5 fw-bold mb-3">Calisthenics Training Tracker</h1>
            <p class="lead text-secondary mb-4">
                Track bodyweight workouts, monitor performance, and build consistency over time.
            </p>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <a href="index.php?page=register" class="btn btn-primary btn-lg px-4">Get Started</a>
                <a href="index.php?page=login" class="btn btn-outline-primary btn-lg px-4">I Already Have an Account</a>
            </div>
        </section>

        <section class="row g-4 mt-4">
            <div class="col-md-4">
                <div class="feature-card h-100">
                    <h3 class="h5">Log Workouts</h3>
                    <p class="mb-0">Record sets, reps, hold times, and weighted movements in a structured way.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card h-100">
                    <h3 class="h5">Track Progress</h3>
                    <p class="mb-0">Review your history and identify trends in strength and training consistency.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card h-100">
                    <h3 class="h5">Stay Organized</h3>
                    <p class="mb-0">Manage exercises and sessions from one dashboard designed for athletes.</p>
                </div>
            </div>
        </section>
    <?php else: ?>
        <section class="container-sm">
            <div class="placeholder-panel text-center">
                <h1 class="h3 mb-3 text-capitalize"><?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?> Page</h1>
                <p class="text-secondary mb-4">
                    This section is prepared in the project foundation and will be fully implemented in the next development steps.
                </p>
                <a href="index.php?page=home" class="btn btn-outline-primary">Back to Home</a>
            </div>
        </section>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../views/footer.php'; ?>
