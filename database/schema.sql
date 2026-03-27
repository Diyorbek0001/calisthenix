CREATE DATABASE IF NOT EXISTS calisthenics_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE calisthenics_db;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS exercises (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category ENUM('push', 'pull', 'legs', 'core', 'skill', 'full_body') NOT NULL,
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS workouts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    workout_date DATE NOT NULL,
    duration_minutes SMALLINT UNSIGNED DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_workouts_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS workout_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workout_id INT UNSIGNED NOT NULL,
    exercise_id INT UNSIGNED NOT NULL,
    set_number TINYINT UNSIGNED NOT NULL,
    reps SMALLINT UNSIGNED DEFAULT NULL,
    hold_seconds SMALLINT UNSIGNED DEFAULT NULL,
    added_weight_kg DECIMAL(5,2) DEFAULT 0.00,
    rest_seconds SMALLINT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_workout_logs_workout
        FOREIGN KEY (workout_id) REFERENCES workouts(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_workout_logs_exercise
        FOREIGN KEY (exercise_id) REFERENCES exercises(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_workouts_user_id ON workouts(user_id);
CREATE INDEX idx_workouts_workout_date ON workouts(workout_date);
CREATE INDEX idx_workout_logs_workout_id ON workout_logs(workout_id);
CREATE INDEX idx_workout_logs_exercise_id ON workout_logs(exercise_id);
