<?php
require __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Skills';
$active    = 'skills';

function skills_by(string $category): array
{
    $st = db()->prepare('SELECT * FROM skills WHERE category = ? ORDER BY sort_order, id');
    $st->execute([$category]);
    return $st->fetchAll();
}

$technical = skills_by('technical');
$tools     = skills_by('tool');
$personal  = skills_by('personal');
$languages = skills_by('language');

include __DIR__ . '/includes/header.php';
?>

    <main>

        <section class="page-header">
            <div class="container">
                <h1>My Skills</h1>
                <p>Technologies and abilities I am currently developing.</p>
            </div>
        </section>

        <section class="section">
            <div class="container">

                <h2 class="section-title">Technical Skills</h2>

                <?php if ($technical): ?>
                    <div class="skills-bars">
                        <?php foreach ($technical as $s): ?>
                            <div class="skill-bar-item">
                                <div class="skill-bar-header">
                                    <h3><?= e($s['name']) ?></h3>
                                    <span><?= (int)$s['level'] ?>%</span>
                                </div>
                                <div class="skill-line">
                                    <div class="skill-progress" data-width="<?= (int)$s['level'] ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="empty-note">No technical skills added yet.</p>
                <?php endif; ?>

            </div>
        </section>

        <?php if ($tools): ?>
        <section class="section alternate-section">
            <div class="container">

                <h2 class="section-title">Technology &amp; Tools</h2>

                <div class="cards">
                    <?php foreach ($tools as $s): ?>
                        <div class="card">
                            <h3><?= $s['icon'] !== '' ? e($s['icon']) . ' ' : '' ?><?= e($s['name']) ?></h3>
                            <p><?= e($s['description']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </section>
        <?php endif; ?>

        <?php if ($personal): ?>
        <section class="section">
            <div class="container">

                <h2 class="section-title">Personal Skills</h2>

                <div class="skill-container">
                    <?php foreach ($personal as $s): ?>
                        <div class="skill-box">
                            <h3><?= e($s['name']) ?></h3>
                            <p><?= e($s['description']) ?></p>
                            <div class="skill-line">
                                <div class="skill-progress" data-width="<?= (int)$s['level'] ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </section>
        <?php endif; ?>

        <?php if ($languages): ?>
        <section class="section alternate-section">
            <div class="container">
                <h2 class="section-title">Languages</h2>
                <div class="language-list">
                    <?php foreach ($languages as $s): ?>
                        <span><?= e($s['name']) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
