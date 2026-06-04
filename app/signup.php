<?php
require_once __DIR__ . '/functions.php';

$name = '';
$email = '';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $message = 'Please fill in all fields.';
    } elseif (getUserByEmail($email)) {
        $message = 'This email is already registered.';
    } else {
        if (createUser($name, $email, $password)) {
            $user = getUserByEmail($email);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            header('Location: dashboard.php');
            exit;
        }
        $message = 'Unable to create account, please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - TimeTrim</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="form-shell">
        <h1>Sign Up</h1>
        <?php if ($message): ?>
            <p class="alert"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <form method="post" action="signup.php">
            <label for="name">Name</label>
            <input id="name" name="name" type="text" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" required>

            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required>

            <label for="password">Password</label>
            <input id="password" name="password" type="password" required>

            <button class="primary-btn" type="submit">Create Account</button>
        </form>
        <p class="small-text">Already have an account? <a href="login.php">Log in here.</a></p>
    </div>
</body>
</html>
