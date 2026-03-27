<?php

declare(strict_types=1);

require_once __DIR__ . '/../controllers/workoutController.php';

requireAuth();

$user = getCurrentUser($pdo);
$userId = (int) $_SESSION['user_id'];

if ($user === null) {
    header('Location: login.php');
    exit;
}

$feedbackMessage = '';
$feedbackType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_workout_id'])) {
    $workoutId = (int) $_POST['delete_workout_id'];

    if ($workoutId > 0) {
        $deleted = deleteWorkout($pdo, $userId, $workoutId);

        if ($deleted) {
            header('Location: dashboard.php?workout_deleted=1');
            exit;
        }

        $feedbackMessage = 'Workout not found or access denied.';
        $feedbackType = 'danger';
    }
}

if (isset($_GET['workout_added']) && $_GET['workout_added'] === '1') {
    $feedbackMessage = 'Workout added successfully.';
}

if (isset($_GET['workout_updated']) && $_GET['workout_updated'] === '1') {
    $feedbackMessage = 'Workout updated successfully.';
}

if (isset($_GET['workout_deleted']) && $_GET['workout_deleted'] === '1') {
    $feedbackMessage = 'Workout deleted successfully.';
}

if (isset($_GET['not_found']) && $_GET['not_found'] === '1') {
    $feedbackMessage = 'Requested workout was not found.';
    $feedbackType = 'warning';
}

$workouts = getUserWorkouts($pdo, $userId);

require_once __DIR__ . '/../views/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="feature-card mb-4">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                    <div>
                        <h1 class="h3 mb-1">Dashboard</h1>
                        <p class="mb-0 text-secondary">
                            Welcome, <?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="add_workout.php" class="btn btn-primary">Add Workout</a>
                        <a href="logout.php" class="btn btn-outline-danger">Logout</a>
                    </div>
                </div>

                <p class="mb-0"><strong>Email:</strong> <?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>

            <?php if ($feedbackMessage !== ''): ?>
                <div class="alert alert-<?php echo htmlspecialchars($feedbackType, ENT_QUOTES, 'UTF-8'); ?>" role="alert">
                    <?php echo htmlspecialchars($feedbackMessage, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <div class="feature-card">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h2 class="h4 mb-0">Your Workouts</h2>
                    <span class="text-secondary small">Total: <?php echo count($workouts); ?></span>
                </div>

                <?php if (empty($workouts)): ?>
                    <p class="text-secondary mb-0">No workouts yet. Click <strong>Add Workout</strong> to create your first entry.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">Date</th>
                                    <th scope="col">Exercise</th>
                                    <th scope="col">Sets</th>
                                    <th scope="col">Reps</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($workouts as $workout): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($workout['workout_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($workout['exercise_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo (int) $workout['set_number']; ?></td>
                                        <td><?php echo (int) $workout['reps']; ?></td>
                                        <td class="text-end">
                                            <a
                                                href="edit_workout.php?id=<?php echo (int) $workout['workout_log_id']; ?>"
                                                class="btn btn-sm btn-outline-primary"
                                            >
                                                Edit
                                            </a>
                                            <form action="dashboard.php" method="POST" class="d-inline">
                                                <input type="hidden" name="delete_workout_id" value="<?php echo (int) $workout['workout_id']; ?>">
                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Are you sure you want to delete this workout?');"
                                                >
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../views/footer.php'; ?>
