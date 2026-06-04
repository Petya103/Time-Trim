<?php
require_once __DIR__ . '/functions.php';

$email = '';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $message = 'Enter both email and password.';
    } else {
        $user = getUserByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            header('Location: dashboard.php');
            exit;
        }
        $message = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - TimeTrim</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="form-shell">
        <h1>Log In</h1>
        <?php if ($message): ?>
            <p class="alert"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <form method="post" action="login.php">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required>

            <label for="password">Password</label>
            <input id="password" name="password" type="password" required>

            <button class="primary-btn" type="submit">Sign In</button>
        </form>
        <p class="small-text">Don&rsquo;t have an account? <a href="signup.php">Sign up here.</a></p>
    </div>
</body>
</html>
