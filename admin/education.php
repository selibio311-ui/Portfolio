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
        $pdo->prepare('DELETE FROM education WHERE id = ?')->execute([post_int('id', 0, PHP_INT_MAX)]);
        flash('success', 'Education record deleted.');
        redirect('education.php');
    }

    if ($action === 'save') {
        $id   = post_int('id', 0, PHP_INT_MAX);
        $data = [
            'school'     => clean_text($_POST['school'] ?? ''),
            'level'      => clean_text($_POST['level'] ?? ''),
            'details'    => clean_text($_POST['details'] ?? ''),
            'period'     => clean_text($_POST['period'] ?? ''),
            'sort_order' => post_int('sort_order', -9999, 9999),
        ];

        if ($data['school'] === '' || mb_strlen($data['school']) > 150) {
            $errors['school'] = 'School is required (max 150 characters).';
        }
        if (mb_strlen($data['level']) > 150) {
            $errors['level'] = 'Level / program must be 150 characters or fewer.';
        }
        if (mb_strlen($data['details']) > 255) {
            $errors['details'] = 'Details must be 255 characters or fewer.';
        }
        if (mb_strlen($data['period']) > 40) {
            $errors['period'] = 'Period must be 40 characters or fewer.';
        }

        if (!$errors) {
            if ($id > 0) {
                $pdo->prepare('UPDATE education SET school=?, level=?, details=?, period=?, sort_order=? WHERE id=?')
                    ->execute([$data['school'], $data['level'], $data['details'], $data['period'], $data['sort_order'], $id]);
                flash('success', 'Education record updated.');
            } else {
                $pdo->prepare('INSERT INTO education (school, level, details, period, sort_order) VALUES (?, ?, ?, ?, ?)')
                    ->execute([$data['school'], $data['level'], $data['details'], $data['period'], $data['sort_order']]);
                flash('success', 'Education record added.');
            }
            redirect('education.php');
        }
        $edit = $data + ['id' => $id];
    }
}

if ($edit === null && isset($_GET['edit'])) {
    $st = $pdo->prepare('SELECT * FROM education WHERE id = ?');
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch() ?: null;
}

$form = $edit ?? ['id' => 0, 'school' => '', 'level' => '', 'details' => '', 'period' => '', 'sort_order' => 0];
$rows = $pdo->query('SELECT * FROM education ORDER BY sort_order, id')->fetchAll();

admin_header('Education', 'education');
?>

<form method="post" action="education.php" class="panel">
    <h2><?= $form['id'] ? 'Edit education record' : 'Add education record' ?></h2>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)$form['id'] ?>">

    <div class="form-grid">
        <?php
        f_text('school', 'School', $form['school'], ['max' => 150, 'required' => true, 'error' => $errors['school'] ?? null]);
        f_text('level', 'Level / program', $form['level'], ['max' => 150, 'error' => $errors['level'] ?? null]);
        f_text('details', 'Details (subjects, grade, honors)', $form['details'], ['max' => 255, 'error' => $errors['details'] ?? null]);
        f_text('period', 'Period (e.g. 2021 – 2022)', $form['period'], ['max' => 40, 'error' => $errors['period'] ?? null]);
        f_text('sort_order', 'Sort order (lower shows first)', $form['sort_order'], ['type' => 'number']);
        ?>
    </div>

    <button type="submit" class="btn"><?= $form['id'] ? 'Update record' : 'Add record' ?></button>
    <?php if ($form['id']): ?><a class="btn-small" href="education.php">Cancel</a><?php endif; ?>
</form>

<div class="panel">
    <h2>All education records (<?= count($rows) ?>)</h2>
    <div class="table-wrap">
        <table class="admin-table">
            <thead><tr><th>School</th><th>Level</th><th>Period</th><th>Order</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['school']) ?></td>
                    <td><?= e($r['level']) ?></td>
                    <td><?= e($r['period']) ?></td>
                    <td><?= (int)$r['sort_order'] ?></td>
                    <td class="actions">
                        <a class="btn-small" href="education.php?edit=<?= (int)$r['id'] ?>">Edit</a>
                        <?php delete_form('Delete', (int)$r['id'], 'Delete the record for "' . $r['school'] . '"?'); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="5" class="muted">No education records yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php admin_footer(); ?>
