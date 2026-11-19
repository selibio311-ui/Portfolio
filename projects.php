<?php
require __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Projects';
$active    = 'projects';

$projects = db()->query('SELECT * FROM projects ORDER BY sort_order, id')->fetchAll();

$categoryLabels = ['web' => 'Web', 'system' => 'Systems', 'design' => 'Design'];
$present = array_values(array_unique(array_column($projects, 'category')));

include __DIR__ . '/includes/header.php';
?>

    <main>

        <section class="page-header">
            <div class="container">
                <h1>My Projects</h1>
                <p>Academic and personal projects that demonstrate my interest in technology and design.</p>
            </div>
        </section>

        <section class="section">
            <div class="container">

                <?php if ($projects): ?>

                    <div class="project-filters">
                        <button class="filter-btn active" data-filter="all">All</button>
                        <?php foreach ($categoryLabels as $key => $label): ?>
                            <?php if (in_array($key, $present, true)): ?>
                                <button class="filter-btn" data-filter="<?= e($key) ?>"><?= e($label) ?></button>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <div class="project-grid">
                        <?php foreach ($projects as $p): ?>
                            <article class="project-card" data-category="<?= e($p['category']) ?>">

                                <?php if ($p['image'] !== '' && img_src($p['image']) !== ''): ?>
                                    <img src="<?= img_src($p['image']) ?>" alt="<?= e($p['title']) ?> screenshot">
                                <?php else: ?>
                                    <div class="project-placeholder">No image</div>
                                <?php endif; ?>

                                <div class="project-content">
                                    <span class="project-category"><?= e($p['label'] !== '' ? $p['label'] : ($categoryLabels[$p['category']] ?? $p['category'])) ?></span>
                                    <h2><?= e($p['title']) ?></h2>

                                    <?= paragraphs($p['description']) ?>

                                    <?php if ($p['tech'] !== ''): ?>
                                        <div class="project-tech">
                                            <?php foreach (array_filter(array_map('trim', explode(',', $p['tech']))) as $t): ?>
                                                <span><?= e($t) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($p['project_url'] !== '' && valid_url($p['project_url'])): ?>
                                        <a class="project-link" href="<?= e($p['project_url']) ?>" target="_blank" rel="noopener noreferrer">View project →</a>
                                    <?php endif; ?>
                                </div>

                            </article>
                        <?php endforeach; ?>
                    </div>

                <?php else: ?>
                    <p class="empty-note">No projects have been added yet.</p>
                <?php endif; ?>

            </div>
        </section>

    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
