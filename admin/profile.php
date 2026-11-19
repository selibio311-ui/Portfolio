<?php
define('IN_ADMIN', true);
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/admin_ui.php';
require_admin();

/* key => [label, max length, type, required] */
$fields = [
    'full_name'     => ['Full name',                        100, 'text',     true],
    'hero_greeting' => ['Home page greeting',                60, 'text',     false],
    'tagline'       => ['Tagline / headline',               150, 'text',     false],
    'intro'         => ['Home page introduction',           600, 'textarea', false],
    'welcome_text'  => ['Home "Welcome" description',       400, 'textarea', false],
    'bio'           => ['Biography (About + Resume)',      1500, 'textarea', false],
    'bio_secondary' => ['Biography - second paragraph',     800, 'textarea', false],
    'interests'     => ['Interests',                        400, 'textarea', false],
    'goal'          => ['My goal',                          800, 'textarea', false],
    'email'         => ['Email',                            150, 'email',    false],
    'phone'         => ['Phone',                             40, 'text',     false],
    'address'       => ['Address',                          255, 'text',     false],
    'facebook_url'  => ['Facebook URL',                     255, 'url',      false],
    'github_url'    => ['GitHub URL',                       255, 'url',      false],
    'resume_file'   => ['Resume PDF file name',             120, 'pdf',      false],
];

$errors = [];
$values = [];
foreach ($fields as $key => $_) {
    $values[$key] = setting($key);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    foreach ($fields as $key => [$label, $max, $type, $required]) {
        $val = clean_text($_POST[$key] ?? '');
        $values[$key] = $val;

        if ($required && $val === '') {
            $errors[$key] = "$label is required.";
        } elseif (mb_strlen($val) > $max) {
            $errors[$key] = "$label must be $max characters or fewer.";
        } elseif ($val !== '') {
            if ($type === 'email' && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
                $errors[$key] = 'Enter a valid email address.';
            } elseif ($type === 'url' && !valid_url($val)) {
                $errors[$key] = 'Enter a full http:// or https:// address.';
            } elseif ($type === 'pdf' && (!preg_match('#^[A-Za-z0-9_\-./]+\.pdf$#', $val) || strpos($val, '..') !== false)) {
                $errors[$key] = 'Enter a PDF file name such as TROY-RESUME-Final.pdf.';
            }
        }
    }

    [$newPhoto, $photoError] = handle_image_upload('profile_image', 'profile');
    if ($photoError) {
        $errors['profile_image'] = $photoError;
    }

    if (!$errors) {
        foreach ($values as $key => $val) {
            set_setting($key, $val);
        }
        if ($newPhoto) {
            $old = setting('profile_image');
            set_setting('profile_image', $newPhoto);
            delete_upload($old);
        }
        flash('success', 'Personal information updated.');
        redirect('profile.php');
    } elseif ($newPhoto) {
        delete_upload($newPhoto);   // do not keep an upload when the form failed
    }
}

admin_header('Personal Information', 'profile');
?>

<form method="post" action="profile.php" enctype="multipart/form-data" class="panel">
    <?= csrf_field() ?>

    <div class="form-grid">
        <?php
        f_file('profile_image', 'Profile picture', setting('profile_image'), $errors['profile_image'] ?? null);
        echo '<div></div>';

        foreach ($fields as $key => [$label, $max, $type, $required]) {
            $opts = ['max' => $max, 'required' => $required, 'error' => $errors[$key] ?? null];
            if ($type === 'textarea') {
                echo '<div class="full">';
                f_textarea($key, $label, $values[$key], $opts + ['rows' => $max > 600 ? 6 : 3]);
                echo '</div>';
            } else {
                $opts['type'] = in_array($type, ['email', 'url'], true) ? $type : 'text';
                if ($type === 'pdf') {
                    $opts['hint'] = 'Upload the PDF to the website folder, then enter its name here.';
                }
                f_text($key, $label, $values[$key], $opts);
            }
        }
        ?>
    </div>

    <button type="submit" class="btn">Save changes</button>
</form>

<?php admin_footer(); ?>
