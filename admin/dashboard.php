<?php
define('IN_ADMIN', true);
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/admin_ui.php';
require_admin();

$pdo = db();

function count_rows(PDO $pdo, string $sql): int
{
    return (int)$pdo->query($sql)->fetchColumn();
}

$stats = [
    ['Projects',         count_rows($pdo, 'SELECT COUNT(*) FROM projects')],
    ['Skills',           count_rows($pdo, 'SELECT COUNT(*) FROM skills')],
    ['Experience',       count_rows($pdo, 'SELECT COUNT(*) FROM experience')],
    ['Education',        count_rows($pdo, 'SELECT COUNT(*) FROM education')],
    ['Achievements',     count_rows($pdo, 'SELECT COUNT(*) FROM achievements')],
    ['Messages',         count_rows($pdo, 'SELECT COUNT(*) FROM messages')],
    ['Unread messages',  count_rows($pdo, 'SELECT COUNT(*) FROM messages WHERE is_read = 0')],
];

$recent = $pdo->query('SELECT id, name, subject, is_read, created_at FROM messages ORDER BY created_at DESC, id DESC LIMIT 5')->fetchAll();

admin_header('Dashboard', 'dashboard');
?>

<div class="stats-grid">
    <?php foreach ($stats as [$label, $number]): ?>
        <div class="stat-card">
            <div class="stat-number"><?= (int)$number ?></div>
            <div class="stat-label"><?= e($label) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="panel">
    <h2>Welcome, <?= e($_SESSION['admin_user']) ?></h2>
    <p class="muted">Use the menu to update your personal information, skills, projects, education, experience and achievements. Changes appear on the public website immediately.</p>
</div>

<div class="panel">
    <h2>Recent messages</h2>
    <?php if ($recent): ?>
        <div class="table-wrap">
            <table class="admin-table">
                <thead><tr><th>From</th><th>Subject</th><th>Received</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($recent as $m): ?>
                    <tr class="<?= $m['is_read'] ? '' : 'unread' ?>">
                        <td><?= e($m['name']) ?></td>
                        <td><?= e($m['subject']) ?></td>
                        <td><?= e($m['created_at']) ?></td>
                        <td><?= $m['is_read'] ? 'Read' : 'Unread' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p style="margin-top:14px"><a class="btn-small" href="messages.php">Open inbox</a></p>
    <?php else: ?>
        <p class="muted">No messages yet.</p>
    <?php endif; ?>
</div>

<?php admin_footer(); ?>
