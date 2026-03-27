<?php

declare(strict_types=1);

require_once __DIR__ . '/../controllers/workoutController.php';

requireAuth();

$userId = (int) $_SESSION['user_id'];
$exercises = getExercises($pdo);
$errors = [];

$exerciseId = 0;
$sets = 3;
$reps = 10;
$workoutDate = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exerciseId = (int) ($_POST['exercise_id'] ?? 0);
    $sets = (int) ($_POST['sets'] ?? 0);
    $reps = (int) ($_POST['reps'] ?? 0);
    $workoutDate = trim($_POST['workout_date'] ?? '');

    $errors = createWorkout($pdo, $userId, $exerciseId, $sets, $reps, $workoutDate);

    if (empty($errors)) {
        header('Location: dashboard.php?workout_added=1');
        exit;
    }
}

require_once __DIR__ . '/../views/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="feature-card">
                <h1 class="h3 mb-4">Add Workout</h1>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (empty($exercises)): ?>
                    <div class="alert alert-warning" role="alert">
                        No exercises found. Please insert exercise rows in the <code>exercises</code> table first.
                    </div>
                <?php else: ?>
                    <form action="add_workout.php" method="POST" novalidate>
                        <div class="mb-3">
                            <label for="exercise_id" class="form-label">Exercise</label>
                            <select id="exercise_id" name="exercise_id" class="form-select" required>
                                <option value="">Select an exercise</option>
                                <?php foreach ($exercises as $exercise): ?>
                                    <option
                                        value="<?php echo (int) $exercise['id']; ?>"
                                        <?php echo $exerciseId === (int) $exercise['id'] ? 'selected' : ''; ?>
                                    >
                                        <?php echo htmlspecialchars($exercise['name'] . ' (' . $exercise['category'] . ')', ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sets" class="form-label">Sets</label>
                                <input
                                    type="number"
                                    min="1"
                                    max="100"
                                    id="sets"
                                    name="sets"
                                    class="form-control"
                                    value="<?php echo (int) $sets; ?>"
                                    required
                                >
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="reps" class="form-label">Reps</label>
                                <input
                                    type="number"
                                    min="1"
                                    max="1000"
                                    id="reps"
                                    name="reps"
                                    class="form-control"
                                    value="<?php echo (int) $reps; ?>"
                                    required
                                >
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="workout_date" class="form-label">Workout Date</label>
                            <input
                                type="date"
                                id="workout_date"
                                name="workout_date"
                                class="form-control"
                                value="<?php echo htmlspecialchars($workoutDate, ENT_QUOTES, 'UTF-8'); ?>"
                                required
                            >
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save Workout</button>
                            <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../views/footer.php'; ?>
