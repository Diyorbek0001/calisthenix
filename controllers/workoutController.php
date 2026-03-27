<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/authController.php';

/**
 * Reads and caches table columns.
 */
function getTableColumns(PDO $pdo, string $tableName): array
{
    static $cache = [];

    if (isset($cache[$tableName])) {
        return $cache[$tableName];
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM {$tableName}");
    $cache[$tableName] = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    return $cache[$tableName];
}

/**
 * Returns first existing column from candidates.
 */
function resolveColumn(PDO $pdo, string $tableName, array $candidates, string $fallback): string
{
    $columns = getTableColumns($pdo, $tableName);

    foreach ($candidates as $column) {
        if (in_array($column, $columns, true)) {
            return $column;
        }
    }

    return $fallback;
}

function workoutUserIdColumn(PDO $pdo): string
{
    return resolveColumn($pdo, 'workouts', ['user_id', 'userid', 'userId'], 'user_id');
}

function workoutDateColumn(PDO $pdo): string
{
    return resolveColumn($pdo, 'workouts', ['workout_date', 'date', 'workoutDate'], 'workout_date');
}

function logWorkoutIdColumn(PDO $pdo): string
{
    return resolveColumn($pdo, 'workout_logs', ['workout_id', 'workoutid', 'workoutId'], 'workout_id');
}

function logExerciseIdColumn(PDO $pdo): string
{
    return resolveColumn($pdo, 'workout_logs', ['exercise_id', 'exerciseid', 'exerciseId'], 'exercise_id');
}

function logSetsColumn(PDO $pdo): string
{
    return resolveColumn($pdo, 'workout_logs', ['set_number', 'sets', 'setNumber'], 'set_number');
}

function logRepsColumn(PDO $pdo): string
{
    return resolveColumn($pdo, 'workout_logs', ['reps', 'rep_count', 'repetitions'], 'reps');
}

/**
 * Returns all exercises for dropdown selection.
 */
function getExercises(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT id, name, category FROM exercises ORDER BY name ASC');
    return $stmt->fetchAll();
}

/**
 * Inserts workout + workout log row for the currently logged-in user.
 */
function createWorkout(PDO $pdo, int $userId, int $exerciseId, int $sets, int $reps, string $workoutDate): array
{
    $errors = validateWorkoutInput($exerciseId, $sets, $reps, $workoutDate);

    if (!empty($errors)) {
        return $errors;
    }

    $exerciseStmt = $pdo->prepare('SELECT id FROM exercises WHERE id = :id LIMIT 1');
    $exerciseStmt->execute(['id' => $exerciseId]);

    if (!$exerciseStmt->fetch()) {
        return ['Selected exercise does not exist.'];
    }

    $userColumn = workoutUserIdColumn($pdo);
    $dateColumn = workoutDateColumn($pdo);
    $logWorkoutColumn = logWorkoutIdColumn($pdo);
    $logExerciseColumn = logExerciseIdColumn($pdo);
    $logSets = logSetsColumn($pdo);
    $logReps = logRepsColumn($pdo);

    $pdo->beginTransaction();

    try {
        $workoutInsert = sprintf(
            'INSERT INTO workouts (%s, %s) VALUES (:user_id, :workout_date)',
            $userColumn,
            $dateColumn
        );

        $workoutStmt = $pdo->prepare($workoutInsert);
        $workoutStmt->execute([
            'user_id' => $userId,
            'workout_date' => $workoutDate,
        ]);

        $workoutId = (int) $pdo->lastInsertId();

        $logInsert = sprintf(
            'INSERT INTO workout_logs (%s, %s, %s, %s) VALUES (:workout_id, :exercise_id, :sets, :reps)',
            $logWorkoutColumn,
            $logExerciseColumn,
            $logSets,
            $logReps
        );

        $logStmt = $pdo->prepare($logInsert);
        $logStmt->execute([
            'workout_id' => $workoutId,
            'exercise_id' => $exerciseId,
            'sets' => $sets,
            'reps' => $reps,
        ]);

        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return ['Failed to save workout. Please try again.'];
    }

    return [];
}

/**
 * Returns all workout rows for dashboard using JOIN query.
 */
function getUserWorkouts(PDO $pdo, int $userId): array
{
    $userColumn = workoutUserIdColumn($pdo);
    $dateColumn = workoutDateColumn($pdo);
    $logWorkoutColumn = logWorkoutIdColumn($pdo);
    $logExerciseColumn = logExerciseIdColumn($pdo);
    $logSets = logSetsColumn($pdo);
    $logReps = logRepsColumn($pdo);

    $sql = sprintf(
        'SELECT
            w.id AS workout_id,
            w.%s AS workout_date,
            wl.id AS workout_log_id,
            wl.%s AS set_number,
            wl.%s AS reps,
            e.name AS exercise_name
         FROM workouts w
         INNER JOIN workout_logs wl ON wl.%s = w.id
         INNER JOIN exercises e ON e.id = wl.%s
         WHERE w.%s = :user_id
         ORDER BY w.%s DESC, w.id DESC',
        $dateColumn,
        $logSets,
        $logReps,
        $logWorkoutColumn,
        $logExerciseColumn,
        $userColumn,
        $dateColumn
    );

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
}

/**
 * Gets a single workout log row by id for the current user.
 */
function getWorkoutByLogId(PDO $pdo, int $userId, int $workoutLogId): ?array
{
    $userColumn = workoutUserIdColumn($pdo);
    $dateColumn = workoutDateColumn($pdo);
    $logWorkoutColumn = logWorkoutIdColumn($pdo);
    $logExerciseColumn = logExerciseIdColumn($pdo);
    $logSets = logSetsColumn($pdo);
    $logReps = logRepsColumn($pdo);

    $sql = sprintf(
        'SELECT
            w.id AS workout_id,
            w.%s AS workout_date,
            wl.id AS workout_log_id,
            wl.%s AS exercise_id,
            wl.%s AS set_number,
            wl.%s AS reps
         FROM workouts w
         INNER JOIN workout_logs wl ON wl.%s = w.id
         WHERE w.%s = :user_id AND wl.id = :workout_log_id
         LIMIT 1',
        $dateColumn,
        $logExerciseColumn,
        $logSets,
        $logReps,
        $logWorkoutColumn,
        $userColumn
    );

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'user_id' => $userId,
        'workout_log_id' => $workoutLogId,
    ]);

    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * Updates workout date and workout log sets/reps for current user only.
 */
function updateWorkout(PDO $pdo, int $userId, int $workoutLogId, int $exerciseId, int $sets, int $reps, string $workoutDate): array
{
    $errors = validateWorkoutInput($exerciseId, $sets, $reps, $workoutDate);

    if (!empty($errors)) {
        return $errors;
    }

    $currentWorkout = getWorkoutByLogId($pdo, $userId, $workoutLogId);

    if ($currentWorkout === null) {
        return ['Workout not found or access denied.'];
    }

    $exerciseStmt = $pdo->prepare('SELECT id FROM exercises WHERE id = :id LIMIT 1');
    $exerciseStmt->execute(['id' => $exerciseId]);

    if (!$exerciseStmt->fetch()) {
        return ['Selected exercise does not exist.'];
    }

    $userColumn = workoutUserIdColumn($pdo);
    $dateColumn = workoutDateColumn($pdo);
    $logExerciseColumn = logExerciseIdColumn($pdo);
    $logSets = logSetsColumn($pdo);
    $logReps = logRepsColumn($pdo);

    $pdo->beginTransaction();

    try {
        $workoutUpdate = sprintf(
            'UPDATE workouts SET %s = :workout_date WHERE id = :workout_id AND %s = :user_id',
            $dateColumn,
            $userColumn
        );

        $updateWorkoutStmt = $pdo->prepare($workoutUpdate);
        $updateWorkoutStmt->execute([
            'workout_date' => $workoutDate,
            'workout_id' => (int) $currentWorkout['workout_id'],
            'user_id' => $userId,
        ]);

        $logUpdate = sprintf(
            'UPDATE workout_logs SET %s = :exercise_id, %s = :sets, %s = :reps WHERE id = :workout_log_id',
            $logExerciseColumn,
            $logSets,
            $logReps
        );

        $updateLogStmt = $pdo->prepare($logUpdate);
        $updateLogStmt->execute([
            'exercise_id' => $exerciseId,
            'sets' => $sets,
            'reps' => $reps,
            'workout_log_id' => $workoutLogId,
        ]);

        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return ['Failed to update workout. Please try again.'];
    }

    return [];
}

/**
 * Deletes a workout row if it belongs to current user.
 */
function deleteWorkout(PDO $pdo, int $userId, int $workoutId): bool
{
    $userColumn = workoutUserIdColumn($pdo);
    $logWorkoutColumn = logWorkoutIdColumn($pdo);

    $pdo->beginTransaction();

    try {
        // Ensure user owns this workout before deleting anything.
        $ownershipSql = sprintf('SELECT id FROM workouts WHERE id = :id AND %s = :user_id LIMIT 1', $userColumn);
        $ownershipStmt = $pdo->prepare($ownershipSql);
        $ownershipStmt->execute([
            'id' => $workoutId,
            'user_id' => $userId,
        ]);

        if (!$ownershipStmt->fetch()) {
            $pdo->rollBack();
            return false;
        }

        // Delete child logs first for schemas without ON DELETE CASCADE.
        $deleteLogsSql = sprintf('DELETE FROM workout_logs WHERE %s = :workout_id', $logWorkoutColumn);
        $deleteLogsStmt = $pdo->prepare($deleteLogsSql);
        $deleteLogsStmt->execute(['workout_id' => $workoutId]);

        $deleteWorkoutSql = sprintf('DELETE FROM workouts WHERE id = :id AND %s = :user_id', $userColumn);
        $deleteWorkoutStmt = $pdo->prepare($deleteWorkoutSql);
        $deleteWorkoutStmt->execute([
            'id' => $workoutId,
            'user_id' => $userId,
        ]);

        $pdo->commit();
        return $deleteWorkoutStmt->rowCount() > 0;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return false;
    }
}

/**
 * Basic workout input validator.
 */
function validateWorkoutInput(int $exerciseId, int $sets, int $reps, string $workoutDate): array
{
    $errors = [];

    if ($exerciseId <= 0) {
        $errors[] = 'Please select an exercise.';
    }

    if ($sets <= 0 || $sets > 100) {
        $errors[] = 'Sets must be between 1 and 100.';
    }

    if ($reps <= 0 || $reps > 1000) {
        $errors[] = 'Reps must be between 1 and 1000.';
    }

    $date = DateTime::createFromFormat('Y-m-d', $workoutDate);
    $isValidDate = $date && $date->format('Y-m-d') === $workoutDate;

    if (!$isValidDate) {
        $errors[] = 'Please provide a valid workout date.';
    }

    return $errors;
}
