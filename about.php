<?php
require __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'About';
$active    = 'about';
$photo     = setting('profile_image', 'assets/images/profile.jpg');

$education  = db()->query('SELECT * FROM education ORDER BY sort_order, id')->fetchAll();
$experience = db()->query('SELECT * FROM experience ORDER BY sort_order, id')->fetchAll();

include __DIR__ . '/includes/header.php';
?>

    <main>

        <section class="page-header">
            <div class="container">
                <h1>About Me</h1>
                <p>Learn more about my background, education, and goals as an aspiring IT professional.</p>
            </div>
        </section>

        <section class="section">
            <div class="container">

                <div class="contact-grid">

                    <div class="contact-info">
                        <div class="hero-image about-avatar">
                            <?php if (img_src($photo) !== ''): ?>
                                <img src="<?= img_src($photo) ?>" alt="Profile photo of <?= e(setting('full_name')) ?>">
                            <?php endif; ?>
                        </div>

                        <h2><?= e(setting('full_name')) ?></h2>
                        <p><?= e(setting('tagline')) ?></p>
                    </div>

                    <div>
                        <div class="resume-card">
                            <h2>Who I Am</h2>
                            <p><?= nl2br(e(setting('bio'))) ?></p>
                            <?php if (setting('bio_secondary') !== ''): ?>
                                <p class="about-subtext"><?= nl2br(e(setting('bio_secondary'))) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

                <div class="cards cards-spaced">

                    <div class="card">
                        <h3>Education</h3>
                        <?php if ($education): ?>
                            <ul class="card-list">
                                <?php foreach ($education as $row): ?>
                                    <li>
                                        <?= e($row['school']) ?><?= $row['level'] !== '' ? ' – ' . e($row['level']) : '' ?>
                                        <?= $row['period'] !== '' ? '(' . e($row['period']) . ')' : '' ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>No education records yet.</p>
                        <?php endif; ?>
                        <a href="resume.php">View full resume →</a>
                    </div>

                    <div class="card">
                        <h3>Work Experience</h3>
                        <?php if ($experience): ?>
                            <ul class="card-list">
                                <?php foreach ($experience as $row): ?>
                                    <li><?= e($row['title']) ?><?= $row['period'] !== '' ? ' (' . e($row['period']) . ')' : '' ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>No experience records yet.</p>
                        <?php endif; ?>
                        <a href="resume.php">View experience →</a>
                    </div>

                    <div class="card">
                        <h3>Interests</h3>
                        <p><?= e(setting('interests')) ?></p>
                        <a href="skills.php">View skills →</a>
                    </div>

                </div>

                <?php if (setting('goal') !== ''): ?>
                    <div class="resume-card cards-spaced">
                        <h2>My Goal</h2>
                        <p><?= nl2br(e(setting('goal'))) ?></p>
                    </div>
                <?php endif; ?>

            </div>
        </section>

    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
