<?php
require_once __DIR__ . '/functions.php';
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TimeTrim Start Page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="page-shell">
        <div class="hero-card">
            <h1>Welcome to TimeTrim</h1>
            <p>Prioritize your tasks by time, manage your workload, and keep your profile safe with a local account.</p>
            <div class="button-row">
                <a class="primary-btn" href="signup.php">Sign Up</a>
                <a class="secondary-btn" href="login.php">Log In</a>
            </div>
        </div>
        <div class="feature-cards">
            <div class="feature-card">
                <h2>Smart Priority</h2>
                <p>Tasks are ranked by duration and deadlines so you can work smarter.</p>
            </div>
            <div class="feature-card">
                <h2>Profile Dashboard</h2>
                <p>Track completed tasks, average time, and your next focus item.</p>
            </div>
            <div class="feature-card">
                <h2>Local Account</h2>
                <p>Create a sign-in account and keep your task list saved in the browser file store.</p>
            </div>
        </div>
    </div>
</body>
</html>
