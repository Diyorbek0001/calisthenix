<?php

declare(strict_types=1);

require_once __DIR__ . '/../controllers/authController.php';

requireAuth();
$user = getCurrentUser($pdo);

if ($user === null) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../views/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="feature-card">
                <h1 class="h3 mb-3">Dashboard</h1>
                <p class="mb-1"><strong>Welcome,</strong> <?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?>!</p>
                <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="mb-4"><strong>Member since:</strong> <?php echo htmlspecialchars((string) $user['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>

                <div class="d-flex gap-2 flex-wrap">
                    <a href="index.php" class="btn btn-outline-primary">Home</a>
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../views/footer.php'; ?>
