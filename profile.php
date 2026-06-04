<?php
require_once __DIR__ . '/functions.php';
requireLogin();
$user = getCurrentUser();
$tasks = getTasksForUser($user['id']);
$completed = array_filter($tasks, fn($task) => $task['completed']);
$averageDuration = count($tasks) ? round(array_sum(array_map(fn($task) => $task['duration'], $tasks)) / count($tasks)) : 0;
$profileCreated = new DateTime($user['created_at'] ?? date('c'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - TimeTrim</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="app-shell">
        <header class="app-header">
            <div>
                <h1>Your Profile</h1>
                <p>Account data and performance insights.</p>
            </div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            </div>
        </header>

        <section class="card profile-card">
            <h2><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Member since:</strong> <?= $profileCreated->format('F j, Y') ?></p>
            <div class="profile-stats">
                <div>
                    <strong><?= count($tasks) ?></strong>
                    <span>Total Tasks</span>
                </div>
                <div>
                    <strong><?= count($completed) ?></strong>
                    <span>Completed</span>
                </div>
                <div>
                    <strong><?= $averageDuration ?> min</strong>
                    <span>Average Duration</span>
                </div>
            </div>
        </section>

        <section class="card profile-info">
            <h2>Account Benefits</h2>
            <ul>
                <li>Saved personalized tasks per account</li>
                <li>Task prioritization based on time and urgency</li>
                <li>Progress overview for better planning</li>
                <li>Secure sign in and sign out workflow</li>
            </ul>
        </section>
    </div>
</body>
</html>
