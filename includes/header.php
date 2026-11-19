<?php
/* Public page header. Set $pageTitle and $active before including. */
$pageTitle = $pageTitle ?? '';
$active    = $active ?? '';
$siteTitle = setting('site_title', 'Portfolio');
$fullTitle = $pageTitle !== '' ? $pageTitle . ' | ' . $siteTitle : $siteTitle;
$logoImg   = setting('site_logo', '');
$logoText  = setting('site_logo_text', setting('full_name', 'Portfolio'));
$darkClass = setting('default_theme', 'dark') === 'light' ? '' : 'dark-mode';

$nav = [
    'home'     => ['index.php',    'Home'],
    'about'    => ['about.php',    'About'],
    'skills'   => ['skills.php',   'Skills'],
    'projects' => ['projects.php', 'Projects'],
    'resume'   => ['resume.php',   'Resume'],
    'contact'  => ['contact.php',  'Contact'],
];
?>
<!DOCTYPE html>
<html lang="en" class="<?= $darkClass ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($fullTitle) ?></title>
    <script src="assets/js/theme-init.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <?= accent_style() ?>
</head>
<body>

    <header class="header">
        <nav class="navbar">

            <a href="index.php" class="logo">
                <?php if ($logoImg !== '' && img_src($logoImg) !== ''): ?>
                    <img class="logo-img" src="<?= img_src($logoImg) ?>" alt="">
                <?php endif; ?>
                <?= e($logoText) ?>
            </a>

            <button class="menu-toggle" id="menu-toggle" aria-label="Toggle navigation menu">☰</button>

            <ul class="nav-links" id="nav-links">
                <?php foreach ($nav as $key => [$href, $label]): ?>
                    <li><a href="<?= $href ?>"<?= $active === $key ? ' class="active"' : '' ?>><?= $label ?></a></li>
                <?php endforeach; ?>
            </ul>

            <button id="theme-toggle" class="theme-btn" aria-label="Toggle dark and light mode">🌙</button>

        </nav>
    </header>
