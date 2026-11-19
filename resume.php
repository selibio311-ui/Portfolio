<?php
require __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Resume';
$active    = 'resume';

$pdo          = db();
$education    = $pdo->query('SELECT * FROM education ORDER BY sort_order, id')->fetchAll();
$experience   = $pdo->query('SELECT * FROM experience ORDER BY sort_order, id')->fetchAll();
$achievements = $pdo->query('SELECT * FROM achievements ORDER BY sort_order, id')->fetchAll();

$st = $pdo->prepare('SELECT * FROM skills WHERE category = ? ORDER BY sort_order, id');
$st->execute(['personal']);
$personal = $st->fetchAll();
$st->execute(['language']);
$languages = $st->fetchAll();

$resumeFile = setting('resume_file', '');
// Only allow a plain file name or relative path to a PDF in this site.
$showResume = (bool)preg_match('#^[A-Za-z0-9_\-./]+\.pdf$#', $resumeFile) && strpos($resumeFile, '..') === false;

include __DIR__ . '/includes/header.php';
?>

    <main>

        <section class="page-header">
            <div class="container">
                <h1>My Resume</h1>
                <p>My education, work experience, skills, and background.</p>
            </div>
        </section>

        <section class="section">
            <div class="container">

                <div class="resume-card">
                    <h2>About Myself</h2>
                    <p><?= nl2br(e(setting('bio'))) ?></p>
                </div>

                <div class="resume-card">
                    <h2>Education</h2>
                    <?php if ($education): ?>
                        <div class="timeline">
                            <?php foreach ($education as $row): ?>
                                <div class="timeline-item">
                                    <h3><?= e($row['school']) ?></h3>
                                    <?php if ($row['level'] !== ''): ?><h4><?= e($row['level']) ?></h4><?php endif; ?>
                                    <?php if ($row['details'] !== ''): ?><p><?= e($row['details']) ?></p><?php endif; ?>
                                    <?php if ($row['period'] !== ''): ?><p><?= e($row['period']) ?></p><?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No education records yet.</p>
                    <?php endif; ?>
                </div>

                <div class="resume-card">
                    <h2>Work Experience</h2>
                    <?php if ($experience): ?>
                        <div class="experience-list">
                            <?php foreach ($experience as $row): ?>
                                <div class="experience-item">
                                    <h3><?= e($row['title']) ?></h3>
                                    <?php if ($row['period'] !== ''): ?><span><?= e($row['period']) ?></span><?php endif; ?>
                                    <p><?= nl2br(e($row['description'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No experience records yet.</p>
                    <?php endif; ?>
                </div>

                <?php if ($achievements): ?>
                <div class="resume-card">
                    <h2>Achievements &amp; Certificates</h2>
                    <div class="experience-list">
                        <?php foreach ($achievements as $row): ?>
                            <div class="experience-item">
                                <h3><?= e($row['title']) ?></h3>
                                <?php $meta = trim($row['issuer'] . ($row['issuer'] !== '' && $row['year'] !== '' ? ' · ' : '') . $row['year']); ?>
                                <?php if ($meta !== ''): ?><span><?= e($meta) ?></span><?php endif; ?>
                                <?php if ($row['description'] !== ''): ?><p><?= e($row['description']) ?></p><?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($personal): ?>
                <div class="resume-card">
                    <h2>Skills</h2>
                    <div class="resume-skills">
                        <?php foreach ($personal as $s): ?>
                            <div class="skill-item">
                                <span><?= e($s['name']) ?></span>
                                <div class="skill-line">
                                    <div class="skill-progress" data-width="<?= (int)$s['level'] ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($languages): ?>
                <div class="resume-card">
                    <h2>Languages</h2>
                    <div class="language-list">
                        <?php foreach ($languages as $s): ?><span><?= e($s['name']) ?></span><?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="resume-card">
                    <h2>Personal Information</h2>
                    <div class="personal-info">
                        <?php if (setting('phone') !== ''): ?><p><strong>Phone:</strong> <?= e(setting('phone')) ?></p><?php endif; ?>
                        <?php if (setting('email') !== ''): ?><p><strong>Email:</strong> <?= e(setting('email')) ?></p><?php endif; ?>
                        <?php if (setting('address') !== ''): ?><p><strong>Address:</strong> <?= e(setting('address')) ?></p><?php endif; ?>
                    </div>
                </div>

                <?php if ($showResume): ?>
                    <div class="resume-download">
                        <a href="<?= e($resumeFile) ?>" class="btn" download>Download Resume</a>
                    </div>
                <?php endif; ?>

            </div>
        </section>

    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
