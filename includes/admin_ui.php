<?php
/* Shared layout + form helpers for admin pages. */

function admin_header(string $title, string $active = ''): void
{
    $unread = (int)db()->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();

    $items = [
        'dashboard'    => ['dashboard.php',    'Dashboard'],
        'profile'      => ['profile.php',      'Personal Info'],
        'skills'       => ['skills.php',       'Skills'],
        'projects'     => ['projects.php',     'Projects'],
        'education'    => ['education.php',    'Education'],
        'experience'   => ['experience.php',   'Experience'],
        'achievements' => ['achievements.php', 'Achievements'],
        'messages'     => ['messages.php',     'Messages'],
        'settings'     => ['settings.php',     'Website Settings'],
        'account'      => ['account.php',      'My Account'],
    ];
    $darkClass = setting('default_theme', 'dark') === 'light' ? '' : 'dark-mode';
    ?>
<!DOCTYPE html>
<html lang="en" class="<?= $darkClass ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> | Admin</title>
    <script src="../assets/js/theme-init.js"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <?= accent_style() ?>
</head>
<body class="admin-body">

<div class="admin-layout">

    <aside class="admin-sidebar" id="admin-sidebar">
        <a href="dashboard.php" class="admin-brand">Admin Panel</a>
        <nav>
            <?php foreach ($items as $key => [$href, $label]): ?>
                <a href="<?= $href ?>"<?= $active === $key ? ' class="active"' : '' ?>>
                    <?= e($label) ?>
                    <?php if ($key === 'messages' && $unread > 0): ?><span class="badge"><?= $unread ?></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="admin-sidebar-footer">
            <a href="../index.php" target="_blank" rel="noopener">View website ↗</a>
            <form method="post" action="logout.php">
                <?= csrf_field() ?>
                <button type="submit" class="btn-link">Log out (<?= e($_SESSION['admin_user'] ?? '') ?>)</button>
            </form>
        </div>
    </aside>

    <div class="admin-main">
        <div class="admin-topbar">
            <button class="menu-toggle admin-menu-toggle" id="admin-menu-toggle" aria-label="Toggle admin menu">☰</button>
            <h1><?= e($title) ?></h1>
        </div>
        <div class="admin-content">
            <?= render_flash() ?>
<?php
}

function admin_footer(): void
{
    ?>
        </div>
    </div>
</div>

<script src="../assets/js/admin.js"></script>
</body>
</html>
<?php
}

/* ---------- Form field helpers (all output is escaped) ---------- */

function f_text(string $name, string $label, $value, array $o = []): void
{
    $type = $o['type'] ?? 'text';
    $max  = isset($o['max']) ? ' maxlength="' . (int)$o['max'] . '"' : '';
    $req  = !empty($o['required']) ? ' required' : '';
    $hint = isset($o['hint']) ? '<small class="hint">' . e($o['hint']) . '</small>' : '';
    $err  = isset($o['error']) ? '<small class="error-message">' . e($o['error']) . '</small>' : '';
    echo '<div class="form-group"><label for="f_' . e($name) . '">' . e($label) . '</label>'
        . '<input type="' . e($type) . '" id="f_' . e($name) . '" name="' . e($name) . '" value="' . e($value) . '"' . $max . $req . '>'
        . $hint . $err . '</div>';
}

function f_textarea(string $name, string $label, $value, array $o = []): void
{
    $max  = isset($o['max']) ? ' maxlength="' . (int)$o['max'] . '"' : '';
    $rows = (int)($o['rows'] ?? 5);
    $hint = isset($o['hint']) ? '<small class="hint">' . e($o['hint']) . '</small>' : '';
    $err  = isset($o['error']) ? '<small class="error-message">' . e($o['error']) . '</small>' : '';
    echo '<div class="form-group"><label for="f_' . e($name) . '">' . e($label) . '</label>'
        . '<textarea id="f_' . e($name) . '" name="' . e($name) . '" rows="' . $rows . '"' . $max . '>' . e($value) . '</textarea>'
        . $hint . $err . '</div>';
}

function f_select(string $name, string $label, $value, array $options, array $o = []): void
{
    $err = isset($o['error']) ? '<small class="error-message">' . e($o['error']) . '</small>' : '';
    echo '<div class="form-group"><label for="f_' . e($name) . '">' . e($label) . '</label><select id="f_' . e($name) . '" name="' . e($name) . '">';
    foreach ($options as $val => $text) {
        echo '<option value="' . e($val) . '"' . ((string)$val === (string)$value ? ' selected' : '') . '>' . e($text) . '</option>';
    }
    echo '</select>' . $err . '</div>';
}

function f_file(string $name, string $label, ?string $currentPath = null, ?string $error = null): void
{
    echo '<div class="form-group"><label for="f_' . e($name) . '">' . e($label) . '</label>';
    if ($currentPath && img_src($currentPath) !== '') {
        echo '<img class="current-image" src="' . img_src($currentPath) . '" alt="Current image">';
    }
    echo '<input type="file" id="f_' . e($name) . '" name="' . e($name) . '" accept="image/jpeg,image/png,image/webp,image/gif">'
        . '<small class="hint">JPG, PNG, WEBP or GIF - max 2 MB. Leave empty to keep the current image.</small>';
    if ($error) {
        echo '<small class="error-message">' . e($error) . '</small>';
    }
    echo '</div>';
}

function delete_form(string $label, int $id, string $confirm = 'Delete this record? This cannot be undone.'): void
{
    echo '<form method="post" class="inline-form" data-confirm="' . e($confirm) . '">'
        . csrf_field()
        . '<input type="hidden" name="action" value="delete">'
        . '<input type="hidden" name="id" value="' . (int)$id . '">'
        . '<button type="submit" class="btn-small btn-danger">' . e($label) . '</button></form>';
}


/** Read an integer from POST, clamped to a range. */
function post_int(string $key, int $min = 0, int $max = 9999, int $default = 0): int
{
    $v = filter_var($_POST[$key] ?? null, FILTER_VALIDATE_INT);
    if ($v === false || $v === null) {
        return $default;
    }
    return max($min, min($max, $v));
}
