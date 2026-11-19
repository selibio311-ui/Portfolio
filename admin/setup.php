<?php
/*
 * ONE-TIME administrator setup.
 *  - Works only while the admins table is empty.
 *  - Requires ADMIN_SETUP_KEY from includes/config.php (so a stranger cannot claim the account).
 *  - DELETE THIS FILE after creating the administrator.
 */
define('IN_ADMIN', true);
require __DIR__ . '/../includes/bootstrap.php';

$adminCount = (int)db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
$keyConfigured = defined('ADMIN_SETUP_KEY') && ADMIN_SETUP_KEY !== '';

if ($adminCount > 0) {
    http_response_code(403);
    exit('Setup is already complete. Please delete admin/setup.php.');
}
if (!$keyConfigured) {
    http_response_code(403);
    exit('Setup is disabled. Set ADMIN_SETUP_KEY in includes/config.php to a long random value to enable it.');
}

$errors   = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $key      = is_string($_POST['setup_key'] ?? null) ? $_POST['setup_key'] : '';
    $username = clean_text($_POST['username'] ?? '');
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $confirm  = is_string($_POST['confirm'] ?? null) ? $_POST['confirm'] : '';

    if (!hash_equals(ADMIN_SETUP_KEY, $key)) {
        $errors[] = 'Incorrect setup key.';
    }
    if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
        $errors[] = 'Username must be 3-50 characters (letters, numbers, dot, dash, underscore).';
    }
    if (strlen($password) < 10) {
        $errors[] = 'Password must be at least 10 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        db()->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)')
            ->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
        flash('success', 'Administrator created. Delete admin/setup.php, clear ADMIN_SETUP_KEY, then log in.');
        redirect('login.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark-mode">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Setup</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <h1>Create Administrator</h1>
        <p class="sub">One-time setup. Delete this file when finished.</p>

        <?php foreach ($errors as $err): ?>
            <div class="alert alert-error" role="alert"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form method="post" action="setup.php" autocomplete="off">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="setup_key">Setup key (from config.php)</label>
                <input type="password" id="setup_key" name="setup_key" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" maxlength="50" value="<?= e($username) ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password (min. 10 characters)</label>
                <input type="password" id="password" name="password" maxlength="200" required>
            </div>
            <div class="form-group">
                <label for="confirm">Confirm password</label>
                <input type="password" id="confirm" name="confirm" maxlength="200" required>
            </div>
            <button type="submit" class="btn">Create administrator</button>
        </form>
    </div>
</div>
</body>
</html>
