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
    $workoutId = (int) ($_POST['delete_workout_id'] ?? 0);

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

$stats = getUserWorkoutStats($pdo, $userId);
$workouts = getUserWorkouts($pdo, $userId);

require_once __DIR__ . '/../views/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Dashboard</h1>
            <p class="text-secondary mb-0">Track progress and manage your training history.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="add_workout.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Add Workout
            </a>
            <a href="logout.php" class="btn btn-outline-danger">
                <i class="bi bi-box-arrow-right me-1"></i>Logout
            </a>
        </div>
    </div>

    <?php if ($feedbackMessage !== ''): ?>
        <div class="alert alert-<?php echo htmlspecialchars($feedbackType, ENT_QUOTES, 'UTF-8'); ?>" role="alert">
            <?php echo htmlspecialchars($feedbackMessage, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-secondary mb-1">Total Workouts</p>
                            <h2 class="h3 mb-0"><?php echo (int) $stats['total_workouts']; ?></h2>
                        </div>
                        <span class="badge bg-primary-subtle text-primary-emphasis p-2">
                            <i class="bi bi-calendar2-week"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-secondary mb-1">Exercises Performed</p>
                            <h2 class="h3 mb-0"><?php echo (int) $stats['total_exercises']; ?></h2>
                        </div>
                        <span class="badge bg-success-subtle text-success-emphasis p-2">
                            <i class="bi bi-list-check"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-secondary mb-1">Total Reps Completed</p>
                            <h2 class="h3 mb-0"><?php echo (int) $stats['total_reps']; ?></h2>
                        </div>
                        <span class="badge bg-warning-subtle text-warning-emphasis p-2">
                            <i class="bi bi-lightning-charge"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h5 mb-0">Workout History</h2>
            <span class="badge text-bg-secondary"><?php echo count($workouts); ?> records</span>
        </div>

        <div class="card-body">
            <?php if (empty($workouts)): ?>
                <div class="text-center py-4">
                    <h3 class="h6 text-secondary mb-2">No workouts yet. Start training!</h3>
                    <a href="add_workout.php" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>Add your first workout
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
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
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-calendar-event me-1"></i><?php echo htmlspecialchars((string) $workout['workout_date'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold"><?php echo htmlspecialchars((string) $workout['exercise_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </td>
                                    <td><span class="badge text-bg-primary"><?php echo (int) $workout['set_number']; ?></span></td>
                                    <td><span class="badge text-bg-success"><?php echo (int) $workout['reps']; ?></span></td>
                                    <td class="text-end">
                                        <a href="edit_workout.php?id=<?php echo (int) $workout['workout_log_id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bi bi-pencil-square me-1"></i>Edit
                                        </a>
                                        <form action="dashboard.php" method="POST" class="d-inline">
                                            <input type="hidden" name="delete_workout_id" value="<?php echo (int) $workout['workout_id']; ?>">
                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Are you sure you want to delete this workout?');"
                                            >
                                                <i class="bi bi-trash me-1"></i>Delete
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

<?php require_once __DIR__ . '/../views/footer.php'; ?>
