<?php
define('IN_ADMIN', true);
require __DIR__ . '/../includes/bootstrap.php';

if (admin_logged_in()) {
    redirect('dashboard.php');
}

$error    = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $username = clean_text($_POST['username'] ?? '');
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both your username and password.';
    } else {
        $result = attempt_login($username, $password);
        if ($result['ok']) {
            redirect('dashboard.php');
        }
        $error = $result['error'];
    }
}

$darkClass = setting('default_theme', 'dark') === 'light' ? '' : 'dark-mode';
?>
<!DOCTYPE html>
<html lang="en" class="<?= $darkClass ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Login</title>
    <script src="../assets/js/theme-init.js"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <?= accent_style() ?>
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <h1>Admin Login</h1>
        <p class="sub">Sign in to manage the portfolio website.</p>

        <?= render_flash() ?>
        <?php if ($error !== ''): ?>
            <div class="alert alert-error" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php" autocomplete="off">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" maxlength="50" value="<?= e($username) ?>" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" maxlength="200" required>
            </div>

            <button type="submit" class="btn">Log in</button>
        </form>
    </div>
</div>
</body>
</html>
