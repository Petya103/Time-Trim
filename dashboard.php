<?php
require_once __DIR__ . '/functions.php';
requireLogin();
$user = getCurrentUser();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_task') {
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $duration = (int)($_POST['duration'] ?? 0);
        $deadline = $_POST['deadline'] ? sanitize($_POST['deadline']) : null;

        if ($title === '' || $description === '' || $duration <= 0) {
            $message = 'Title, description, and a positive duration are required.';
        } else {
            createTaskForUser($user['id'], $title, $description, $duration, $deadline);
            $message = 'Task added successfully.';
        }
    }
    if (isset($_POST['action']) && $_POST['action'] === 'toggle_complete') {
        $taskId = (int)($_POST['task_id'] ?? 0);
        toggleTaskComplete($taskId, $user['id']);
    }
    if (isset($_POST['action']) && $_POST['action'] === 'delete_task') {
        $taskId = (int)($_POST['task_id'] ?? 0);
        deleteTaskForUser($taskId, $user['id']);
    }
    $user = getCurrentUser();
}

$tasks = sortTasks(getTasksForUser($user['id']));
$taskCount = count($tasks);
$completeCount = count(array_filter($tasks, fn($task) => $task['completed']));
$averageDuration = $taskCount ? round(array_sum(array_map(fn($task) => $task['duration'], $tasks)) / $taskCount) : 0;
$nextTask = null;
foreach ($tasks as $task) {
    if (!$task['completed']) {
        $nextTask = $task;
        break;
    }
}
if (!$nextTask && $taskCount) {
    $nextTask = $tasks[0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TimeTrim</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="app-shell">
        <header class="app-header">
            <div>
                <h1>Hi, <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></h1>
                <p>Manage tasks and priorities for your account.</p>
            </div>
            <div class="nav-links">
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            </div>
        </header>

        <?php if ($message): ?>
            <p class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <section class="stats-panel">
            <div class="stat-card">
                <h2><?= $taskCount ?></h2>
                <p>Total tasks</p>
            </div>
            <div class="stat-card">
                <h2><?= $completeCount ?></h2>
                <p>Completed</p>
            </div>
            <div class="stat-card">
                <h2><?= $averageDuration ?> min</h2>
                <p>Avg duration</p>
            </div>
        </section>

        <section class="grid-two">
            <div class="card">
                <h2>Add a Task</h2>
                <form method="post" action="dashboard.php">
                    <input type="hidden" name="action" value="add_task">
                    <label for="title">Title</label>
                    <input id="title" name="title" type="text" required>

                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" required></textarea>

                    <label for="duration">Duration (minutes)</label>
                    <input id="duration" name="duration" type="number" min="5" value="30" required>

                    <label for="deadline">Deadline (optional)</label>
                    <input id="deadline" name="deadline" type="date">

                    <button class="primary-btn" type="submit">Add Task</button>
                </form>
            </div>

            <div class="card highlight-card">
                <h2>Next Focus Task</h2>
                <?php if ($nextTask): ?>
                    <p class="focus-title"><?= htmlspecialchars($nextTask['title'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><?= htmlspecialchars($nextTask['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Duration:</strong> <?= $nextTask['duration'] ?> minutes</p>
                    <?php if ($nextTask['deadline']): ?>
                        <p><strong>Deadline:</strong> <?= htmlspecialchars($nextTask['deadline'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                    <p><strong>Priority:</strong> <?= formatPriority($nextTask) ?></p>
                <?php else: ?>
                    <p>Add a task to see your next recommended priority.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="card task-list-card">
            <div class="card-header">
                <h2>Your Tasks</h2>
                <span><?= $taskCount ?> total</span>
            </div>
            <?php if ($taskCount === 0): ?>
                <p>No tasks yet. Use the form to create one.</p>
            <?php else: ?>
                <div class="task-list">
                    <?php foreach ($tasks as $task): ?>
                        <div class="task-card">
                            <div class="task-card-top">
                                <h3><?= htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                                <span class="priority <?= strtolower(formatPriority($task)) ?>"><?= formatPriority($task) ?></span>
                            </div>
                            <p><?= htmlspecialchars($task['description'], ENT_QUOTES, 'UTF-8') ?></p>
                            <div class="task-meta">
                                <span><?= $task['duration'] ?> min</span>
                                <span><?= $task['deadline'] ?: 'No deadline' ?></span>
                                <span><?= $task['completed'] ? 'Completed' : 'Pending' ?></span>
                            </div>
                            <div class="task-actions">
                                <form method="post" action="dashboard.php" class="inline-form">
                                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                    <input type="hidden" name="action" value="toggle_complete">
                                    <button class="secondary-btn" type="submit"><?= $task['completed'] ? 'Mark Unfinished' : 'Mark Done' ?></button>
                                </form>
                                <form method="post" action="dashboard.php" class="inline-form">
                                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                    <input type="hidden" name="action" value="delete_task">
                                    <button class="danger-btn" type="submit">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
