<?php

declare(strict_types=1);

require_once __DIR__ . '/../views/header.php';
?>

<div class="container">
    <section class="hero-section text-center">
        <h1 class="display-5 fw-bold mb-3">Calisthenics Training Tracker</h1>
        <p class="lead text-secondary mb-4">
            Track bodyweight workouts, monitor performance, and build consistency over time.
        </p>
        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="btn btn-primary btn-lg px-4">Go to Dashboard</a>
                <a href="logout.php" class="btn btn-outline-danger btn-lg px-4">Logout</a>
            <?php else: ?>
                <a href="register.php" class="btn btn-primary btn-lg px-4">Get Started</a>
                <a href="login.php" class="btn btn-outline-primary btn-lg px-4">I Already Have an Account</a>
            <?php endif; ?>
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
</div>

<?php require_once __DIR__ . '/../views/footer.php'; ?>
