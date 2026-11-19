<?php
require __DIR__ . '/includes/bootstrap.php';

$pageTitle = '';
$active    = 'home';
$photo     = setting('profile_image', 'assets/images/profile.jpg');

include __DIR__ . '/includes/header.php';
?>

    <main>

        <section class="hero">

            <div class="hero-content">
                <p class="hero-small-text"><?= e(setting('hero_greeting', 'Hello, I\'m')) ?></p>
                <h1><?= e(setting('full_name')) ?></h1>
                <h2><?= e(setting('tagline')) ?></h2>
                <p><?= e(setting('intro')) ?></p>

                <div class="hero-buttons">
                    <a href="projects.php" class="btn">View My Projects</a>
                    <a href="contact.php" class="btn secondary-btn">Contact Me</a>
                </div>
            </div>

            <div class="hero-image">
                <?php if (img_src($photo) !== ''): ?>
                    <img src="<?= img_src($photo) ?>" alt="Profile photo of <?= e(setting('full_name')) ?>">
                <?php endif; ?>
            </div>

        </section>

        <section class="section">
            <div class="container">

                <h2 class="section-title">Welcome to My Portfolio</h2>
                <p class="section-description"><?= e(setting('welcome_text')) ?></p>

                <div class="cards">

                    <div class="card">
                        <h3>My Background</h3>
                        <p>Learn about my education, work experience, and goals as an Information Technology student.</p>
                        <a href="about.php">Learn More →</a>
                    </div>

                    <div class="card">
                        <h3>My Skills</h3>
                        <p>See the technologies and personal abilities that I continue to develop through my studies and experiences.</p>
                        <a href="skills.php">View Skills →</a>
                    </div>

                    <div class="card">
                        <h3>My Projects</h3>
                        <p>Explore some of my academic and personal projects related to technology and design.</p>
                        <a href="projects.php">View Projects →</a>
                    </div>

                </div>

            </div>
        </section>

    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
