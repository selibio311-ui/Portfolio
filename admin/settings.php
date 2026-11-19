<?php
define('IN_ADMIN', true);
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/admin_ui.php';
require_admin();

$errors = [];
$values = [
    'site_title'     => setting('site_title'),
    'site_logo_text' => setting('site_logo_text'),
    'footer_text'    => setting('footer_text'),
    'default_theme'  => setting('default_theme', 'dark'),
    'accent_color'   => setting('accent_color', '#22d3ee'),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    foreach ($values as $key => $_) {
        $values[$key] = clean_text($_POST[$key] ?? '');
    }

    if ($values['site_title'] === '' || mb_strlen($values['site_title']) > 100) {
        $errors['site_title'] = 'Website title is required (max 100 characters).';
    }
    if (mb_strlen($values['site_logo_text']) > 60) {
        $errors['site_logo_text'] = 'Logo text must be 60 characters or fewer.';
    }
    if (mb_strlen($values['footer_text']) > 120) {
        $errors['footer_text'] = 'Footer text must be 120 characters or fewer.';
    }
    if (!in_array($values['default_theme'], ['dark', 'light'], true)) {
        $errors['default_theme'] = 'Choose dark or light.';
    }
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $values['accent_color'])) {
        $errors['accent_color'] = 'Choose a valid colour.';
    }

    [$newLogo, $logoError] = handle_image_upload('site_logo', 'logo');
    if ($logoError) {
        $errors['site_logo'] = $logoError;
    }

    $removeLogo = !empty($_POST['remove_logo']);

    if (!$errors) {
        foreach ($values as $key => $val) {
            set_setting($key, $val);
        }
        $oldLogo = setting('site_logo');
        if ($newLogo) {
            set_setting('site_logo', $newLogo);
            delete_upload($oldLogo);
        } elseif ($removeLogo) {
            set_setting('site_logo', '');
            delete_upload($oldLogo);
        }
        flash('success', 'Website settings updated.');
        redirect('settings.php');
    } elseif ($newLogo) {
        delete_upload($newLogo);
    }
}

admin_header('Website Settings', 'settings');
?>

<form method="post" action="settings.php" enctype="multipart/form-data" class="panel">
    <?= csrf_field() ?>

    <div class="form-grid">
        <?php
        f_text('site_title', 'Website title (browser tab)', $values['site_title'], ['max' => 100, 'required' => true, 'error' => $errors['site_title'] ?? null]);
        f_text('site_logo_text', 'Logo text (top-left of the menu)', $values['site_logo_text'], ['max' => 60, 'error' => $errors['site_logo_text'] ?? null]);
        f_text('footer_text', 'Footer text (after the © year)', $values['footer_text'], ['max' => 120, 'error' => $errors['footer_text'] ?? null]);
        f_select('default_theme', 'Default theme for new visitors', $values['default_theme'], ['dark' => 'Dark', 'light' => 'Light'], ['error' => $errors['default_theme'] ?? null]);
        f_text('accent_color', 'Accent colour', $values['accent_color'], ['type' => 'color', 'error' => $errors['accent_color'] ?? null]);
        ?>
        <div class="full">
            <?php f_file('site_logo', 'Logo image (optional, shown next to the logo text)', setting('site_logo'), $errors['site_logo'] ?? null); ?>
            <?php if (setting('site_logo') !== ''): ?>
                <label><input type="checkbox" name="remove_logo" value="1"> Remove current logo</label>
            <?php endif; ?>
        </div>
    </div>

    <button type="submit" class="btn">Save settings</button>
</form>

<?php admin_footer(); ?>
