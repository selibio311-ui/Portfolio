<?php
define('IN_ADMIN', true);
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/admin_ui.php';
require_admin();

$pdo    = db();
$errors = [];
$edit   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $pdo->prepare('DELETE FROM experience WHERE id = ?')->execute([post_int('id', 0, PHP_INT_MAX)]);
        flash('success', 'Experience deleted.');
        redirect('experience.php');
    }

    if ($action === 'save') {
        $id   = post_int('id', 0, PHP_INT_MAX);
        $data = [
            'title'       => clean_text($_POST['title'] ?? ''),
            'period'      => clean_text($_POST['period'] ?? ''),
            'description' => clean_text($_POST['description'] ?? ''),
            'sort_order'  => post_int('sort_order', -9999, 9999),
        ];

        if ($data['title'] === '' || mb_strlen($data['title']) > 150) {
            $errors['title'] = 'Job title is required (max 150 characters).';
        }
        if (mb_strlen($data['period']) > 40) {
            $errors['period'] = 'Period must be 40 characters or fewer.';
        }
        if (mb_strlen($data['description']) > 1000) {
            $errors['description'] = 'Description must be 1000 characters or fewer.';
        }

        if (!$errors) {
            if ($id > 0) {
                $pdo->prepare('UPDATE experience SET title=?, period=?, description=?, sort_order=? WHERE id=?')
                    ->execute([$data['title'], $data['period'], $data['description'], $data['sort_order'], $id]);
                flash('success', 'Experience updated.');
            } else {
                $pdo->prepare('INSERT INTO experience (title, period, description, sort_order) VALUES (?, ?, ?, ?)')
                    ->execute([$data['title'], $data['period'], $data['description'], $data['sort_order']]);
                flash('success', 'Experience added.');
            }
            redirect('experience.php');
        }
        $edit = $data + ['id' => $id];
    }
}

if ($edit === null && isset($_GET['edit'])) {
    $st = $pdo->prepare('SELECT * FROM experience WHERE id = ?');
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch() ?: null;
}

$form = $edit ?? ['id' => 0, 'title' => '', 'period' => '', 'description' => '', 'sort_order' => 0];
$rows = $pdo->query('SELECT * FROM experience ORDER BY sort_order, id')->fetchAll();

admin_header('Work Experience', 'experience');
?>

<form method="post" action="experience.php" class="panel">
    <h2><?= $form['id'] ? 'Edit experience' : 'Add experience' ?></h2>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)$form['id'] ?>">

    <div class="form-grid">
        <?php
        f_text('title', 'Job title / role', $form['title'], ['max' => 150, 'required' => true, 'error' => $errors['title'] ?? null]);
        f_text('period', 'Period (e.g. 2022 – 2024)', $form['period'], ['max' => 40, 'error' => $errors['period'] ?? null]);
        f_text('sort_order', 'Sort order (lower shows first)', $form['sort_order'], ['type' => 'number']);
        echo '<div class="full">';
        f_textarea('description', 'Description', $form['description'], ['max' => 1000, 'rows' => 4, 'error' => $errors['description'] ?? null]);
        echo '</div>';
        ?>
    </div>

    <button type="submit" class="btn"><?= $form['id'] ? 'Update experience' : 'Add experience' ?></button>
    <?php if ($form['id']): ?><a class="btn-small" href="experience.php">Cancel</a><?php endif; ?>
</form>

<div class="panel">
    <h2>All experience (<?= count($rows) ?>)</h2>
    <div class="table-wrap">
        <table class="admin-table">
            <thead><tr><th>Title</th><th>Period</th><th>Order</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['title']) ?></td>
                    <td><?= e($r['period']) ?></td>
                    <td><?= (int)$r['sort_order'] ?></td>
                    <td class="actions">
                        <a class="btn-small" href="experience.php?edit=<?= (int)$r['id'] ?>">Edit</a>
                        <?php delete_form('Delete', (int)$r['id'], 'Delete "' . $r['title'] . '"?'); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="4" class="muted">No experience yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php admin_footer(); ?>
