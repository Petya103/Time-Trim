<?php
session_start();

define('DB_HOST', getenv('DB_HOST') ?: 'db'); 
define('DB_NAME', getenv('DB_NAME') ?: 'timetask');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'secret_password');

define('DB_DSN', 'mysql:host=' . DB_HOST . ';charset=utf8mb4');
define('DB_DSN_DB', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4');

function getDb(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    try {
        // Свързване без конкретна база данни
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        // Създаване на базата данни, ако не съществува
        $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // Свързване към конкретната база
        $pdo = new PDO(DB_DSN_DB, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return $pdo;
    } catch (PDOException $exception) {
        die('Database connection error: ' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8'));
    }
}

function sanitize(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function getUserByEmail(string $email): ?array
{
    $pdo = getDb();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function getUserById(int $id): ?array
{
    $pdo = getDb();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function createUser(string $name, string $email, string $password): bool
{
    $pdo = getDb();
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
    return $stmt->execute([
        'name' => $name,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
    ]);
}

function getTasksForUser(int $userId): array
{
    $pdo = getDb();
    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE user_id = :user_id ORDER BY created_at DESC');
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
}

function createTaskForUser(int $userId, string $title, string $description, int $duration, ?string $deadline): bool
{
    $pdo = getDb();
    $stmt = $pdo->prepare(
        'INSERT INTO tasks (user_id, title, description, duration, deadline, completed) VALUES (:user_id, :title, :description, :duration, :deadline, 0)'
    );
    return $stmt->execute([
        'user_id' => $userId,
        'title' => $title,
        'description' => $description,
        'duration' => $duration,
        'deadline' => $deadline ?: null,
    ]);
}

function toggleTaskComplete(int $taskId, int $userId): bool
{
    $pdo = getDb();
    $stmt = $pdo->prepare('UPDATE tasks SET completed = NOT completed WHERE id = :id AND user_id = :user_id');
    $stmt->execute(['id' => $taskId, 'user_id' => $userId]);
    return $stmt->rowCount() > 0;
}

function deleteTaskForUser(int $taskId, int $userId): bool
{
    $pdo = getDb();
    $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = :id AND user_id = :user_id');
    $stmt->execute(['id' => $taskId, 'user_id' => $userId]);
    return $stmt->rowCount() > 0;
}

function getCurrentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }

    return getUserById((int)$_SESSION['user_id']);
}

function calculatePriority(array $task): int
{
    $score = (int)$task['duration'];
    if (!empty($task['deadline'])) {
        $deadline = new DateTime($task['deadline']);
        $now = new DateTime();
        $interval = $now->diff($deadline);
        $daysLeft = (int)$interval->format('%r%a');
        if ($daysLeft <= 0) {
            $score += 30;
        } elseif ($daysLeft <= 2) {
            $score += 18;
        } elseif ($daysLeft <= 5) {
            $score += 8;
        }
    }

    return $score;
}

function formatPriority(array $task): string
{
    $score = calculatePriority($task);
    if ($score >= 60) {
        return 'High';
    }
    if ($score >= 30) {
        return 'Medium';
    }
    return 'Low';
}

function sortTasks(array $tasks): array
{
    usort($tasks, function ($a, $b) {
        return calculatePriority($b) <=> calculatePriority($a);
    });
    return $tasks;
}