<?php
require __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Contact';
$active    = 'contact';

$errors = [];
$old    = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require_csrf();   // 1. CSRF token

    // 2. Honeypot: real visitors never see this field, bots fill it in.
    if (!empty($_POST['website'])) {
        flash('success', 'Thank you! Your message has been sent.');
        redirect('contact.php');
    }

    $old['name']    = clean_text($_POST['name'] ?? '');
    $old['email']   = clean_text($_POST['email'] ?? '');
    $old['subject'] = clean_text($_POST['subject'] ?? '');
    $old['message'] = clean_text($_POST['message'] ?? '');

    // 3. Server-side validation
    if ($old['name'] === '' || mb_strlen($old['name']) > 100) {
        $errors['name'] = 'Please enter your name (max 100 characters).';
    }
    if ($old['email'] === '') {
        $errors['email'] = 'Please enter your email.';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($old['email']) > 150) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if ($old['subject'] === '' || mb_strlen($old['subject']) > 150) {
        $errors['subject'] = 'Please enter a subject (max 150 characters).';
    }
    if ($old['message'] === '') {
        $errors['message'] = 'Please enter your message.';
    } elseif (mb_strlen($old['message']) < 10) {
        $errors['message'] = 'Message must contain at least 10 characters.';
    } elseif (mb_strlen($old['message']) > 3000) {
        $errors['message'] = 'Message is too long (max 3000 characters).';
    }

    // 4. Spam prevention: too-fast submit and per-IP rate limit
    if (!$errors && time() - (int)($_SESSION['contact_form_time'] ?? 0) < 3) {
        $errors['general'] = 'Please take a moment to review your message, then send it again.';
    }
    if (!$errors) {
        $st = db()->prepare('SELECT COUNT(*) FROM messages WHERE ip_address = ? AND created_at >= ?');
        $st->execute([client_ip(), date('Y-m-d H:i:s', time() - 3600)]);
        if ((int)$st->fetchColumn() >= 5) {
            $errors['general'] = 'You have sent several messages recently. Please try again later.';
        }
    }

    if (!$errors) {
        db()->prepare(
            'INSERT INTO messages (name, email, subject, message, is_read, ip_address, created_at)
             VALUES (?, ?, ?, ?, 0, ?, ?)'
        )->execute([$old['name'], $old['email'], $old['subject'], $old['message'], client_ip(), date('Y-m-d H:i:s')]);

        flash('success', 'Thank you! Your message has been sent.');
        redirect('contact.php');
    }
} else {
    $_SESSION['contact_form_time'] = time();   // when the form was shown (for the speed check)
}

include __DIR__ . '/includes/header.php';
?>

    <main>

        <section class="page-header">
            <div class="container">
                <h1>Contact Me</h1>
                <p>Feel free to get in touch with me.</p>
            </div>
        </section>

        <section class="section">
            <div class="container">

                <div class="contact-grid">

                    <div class="contact-info">
                        <h2>Get In Touch</h2>
                        <p>If you would like to contact me, you can use the information below.</p>

                        <?php if (setting('email') !== ''): ?>
                            <div class="contact-item">
                                <h3>Email</h3>
                                <p><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></p>
                            </div>
                        <?php endif; ?>

                        <?php if (setting('phone') !== ''): ?>
                            <div class="contact-item">
                                <h3>Phone</h3>
                                <p><?= e(setting('phone')) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (setting('address') !== ''): ?>
                            <div class="contact-item">
                                <h3>Address</h3>
                                <p><?= e(setting('address')) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (valid_url(setting('facebook_url')) && setting('facebook_url') !== '' || valid_url(setting('github_url')) && setting('github_url') !== ''): ?>
                            <div class="social-links">
                                <h3>Social Media</h3>
                                <?php if (setting('facebook_url') !== '' && valid_url(setting('facebook_url'))): ?>
                                    <a href="<?= e(setting('facebook_url')) ?>" target="_blank" rel="noopener noreferrer">Facebook</a>
                                <?php endif; ?>
                                <?php if (setting('github_url') !== '' && valid_url(setting('github_url'))): ?>
                                    <a href="<?= e(setting('github_url')) ?>" target="_blank" rel="noopener noreferrer">GitHub</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="contact-form-container">

                        <h2>Send Me a Message</h2>

                        <?= render_flash() ?>
                        <?php if (isset($errors['general'])): ?>
                            <div class="alert alert-error" role="alert"><?= e($errors['general']) ?></div>
                        <?php endif; ?>

                        <form id="contact-form" method="post" action="contact.php" novalidate>
                            <?= csrf_field() ?>

                            <div class="hp-field" aria-hidden="true">
                                <label for="website">Leave this field empty</label>
                                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" id="name" name="name" maxlength="100" placeholder="Enter your name" value="<?= e($old['name']) ?>" required>
                                <small class="error-message" id="name-error"><?= e($errors['name'] ?? '') ?></small>
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" maxlength="150" placeholder="Enter your email" value="<?= e($old['email']) ?>" required>
                                <small class="error-message" id="email-error"><?= e($errors['email'] ?? '') ?></small>
                            </div>

                            <div class="form-group">
                                <label for="subject">Subject</label>
                                <input type="text" id="subject" name="subject" maxlength="150" placeholder="Enter subject" value="<?= e($old['subject']) ?>" required>
                                <small class="error-message" id="subject-error"><?= e($errors['subject'] ?? '') ?></small>
                            </div>

                            <div class="form-group">
                                <label for="message">Message</label>
                                <textarea id="message" name="message" rows="6" maxlength="3000" placeholder="Write your message" required><?= e($old['message']) ?></textarea>
                                <small class="error-message" id="message-error"><?= e($errors['message'] ?? '') ?></small>
                            </div>

                            <button type="submit" class="btn">Send Message</button>
                        </form>

                    </div>

                </div>

            </div>
        </section>

    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
