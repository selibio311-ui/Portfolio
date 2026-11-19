<?php
define('IN_ADMIN', true);
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/admin_ui.php';
require_admin();

$pdo    = db();
$errors = [];
$userId = (int)$_SESSION['admin_id'];

$st = $pdo->prepare('SELECT username, password_hash, created_at FROM admins WHERE id = ?');
$st->execute([$userId]);
$admin = $st->fetch();
if (!$admin) {
    admin_logout();
    redirect('login.php');
}

$username = $admin['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $username = clean_text($_POST['username'] ?? '');
    $current  = is_string($_POST['current_password'] ?? null) ? $_POST['current_password'] : '';
    $new      = is_string($_POST['new_password'] ?? null) ? $_POST['new_password'] : '';
    $confirm  = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';

    if (!password_verify($current, $admin['password_hash'])) {
        $errors['current_password'] = 'Your current password is incorrect.';
    }
    if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
        $errors['username'] = 'Username must be 3-50 characters (letters, numbers, dot, dash, underscore).';
    }
    if ($new !== '') {
        if (strlen($new) < 10) {
            $errors['new_password'] = 'New password must be at least 10 characters.';
        } elseif ($new !== $confirm) {
            $errors['confirm_password'] = 'New passwords do not match.';
        }
    }
    if (!$errors && $username !== $admin['username']) {
        $dup = $pdo->prepare('SELECT COUNT(*) FROM admins WHERE username = ? AND id <> ?');
        $dup->execute([$username, $userId]);
        if ((int)$dup->fetchColumn() > 0) {
            $errors['username'] = 'That username is already taken.';
        }
    }

    if (!$errors) {
        $hash = $new !== '' ? password_hash($new, PASSWORD_DEFAULT) : $admin['password_hash'];
        $pdo->prepare('UPDATE admins SET username = ?, password_hash = ? WHERE id = ?')
            ->execute([$username, $hash, $userId]);

        $_SESSION['admin_user'] = $username;
        if ($new !== '') {
            session_regenerate_id(true);   // new session after a password change
        }
        flash('success', 'Account updated.');
        redirect('account.php');
    }
}

admin_header('My Account', 'account');
?>

<form method="post" action="account.php" class="panel" autocomplete="off">
    <h2>Administrator account</h2>
    <p class="muted">Account created: <?= e($admin['created_at']) ?></p><br>
    <?= csrf_field() ?>

    <div class="form-grid">
        <?php
        f_text('username', 'Username', $username, ['max' => 50, 'required' => true, 'error' => $errors['username'] ?? null]);
        echo '<div></div>';
        f_text('new_password', 'New password (leave empty to keep the current one)', '', ['type' => 'password', 'max' => 200, 'hint' => 'At least 10 characters.', 'error' => $errors['new_password'] ?? null]);
        f_text('confirm_password', 'Confirm new password', '', ['type' => 'password', 'max' => 200, 'error' => $errors['confirm_password'] ?? null]);
        f_text('current_password', 'Current password (required to save changes)', '', ['type' => 'password', 'max' => 200, 'required' => true, 'error' => $errors['current_password'] ?? null]);
        ?>
    </div>

    <button type="submit" class="btn">Save account</button>
</form>

<?php admin_footer(); ?>
