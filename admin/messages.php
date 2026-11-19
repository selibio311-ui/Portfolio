<?php
define('IN_ADMIN', true);
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/admin_ui.php';
require_admin();

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';
    $id     = post_int('id', 0, PHP_INT_MAX);

    if ($action === 'delete') {
        $pdo->prepare('DELETE FROM messages WHERE id = ?')->execute([$id]);
        flash('success', 'Message deleted.');
    } elseif ($action === 'mark_read') {
        $pdo->prepare('UPDATE messages SET is_read = 1 WHERE id = ?')->execute([$id]);
        flash('success', 'Message marked as read.');
    } elseif ($action === 'mark_unread') {
        $pdo->prepare('UPDATE messages SET is_read = 0 WHERE id = ?')->execute([$id]);
        flash('success', 'Message marked as unread.');
    } elseif ($action === 'mark_all_read') {
        $pdo->exec('UPDATE messages SET is_read = 1 WHERE is_read = 0');
        flash('success', 'All messages marked as read.');
    }
    redirect('messages.php');
}

$rows   = $pdo->query('SELECT * FROM messages ORDER BY created_at DESC, id DESC LIMIT 200')->fetchAll();
$unread = (int)$pdo->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();

admin_header('Messages', 'messages');
?>

<div class="panel">
    <h2>Inbox (<?= count($rows) ?> shown, <?= $unread ?> unread)</h2>

    <?php if ($unread > 0): ?>
        <form method="post" action="messages.php" class="inline-form">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="mark_all_read">
            <button type="submit" class="btn-small">Mark all as read</button>
        </form>
        <br><br>
    <?php endif; ?>

    <div class="table-wrap">
        <table class="admin-table">
            <thead><tr><th>From</th><th>Subject / message</th><th>Received</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $m): ?>
                <tr class="<?= $m['is_read'] ? '' : 'unread' ?>">
                    <td>
                        <?= e($m['name']) ?><br>
                        <a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a>
                    </td>
                    <td>
                        <details class="message-body">
                            <summary><?= e($m['subject']) ?></summary>
                            <p><?= nl2br(e($m['message'])) ?></p>
                        </details>
                    </td>
                    <td><?= e($m['created_at']) ?></td>
                    <td><?= $m['is_read'] ? 'Read' : 'Unread' ?></td>
                    <td class="actions">
                        <form method="post" action="messages.php" class="inline-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                            <input type="hidden" name="action" value="<?= $m['is_read'] ? 'mark_unread' : 'mark_read' ?>">
                            <button type="submit" class="btn-small"><?= $m['is_read'] ? 'Mark unread' : 'Mark read' ?></button>
                        </form>
                        <?php delete_form('Delete', (int)$m['id'], 'Delete this message from ' . $m['name'] . '?'); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="5" class="muted">No messages yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php admin_footer(); ?>
