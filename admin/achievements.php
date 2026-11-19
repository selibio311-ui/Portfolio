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
        $pdo->prepare('DELETE FROM achievements WHERE id = ?')->execute([post_int('id', 0, PHP_INT_MAX)]);
        flash('success', 'Achievement deleted.');
        redirect('achievements.php');
    }

    if ($action === 'save') {
        $id   = post_int('id', 0, PHP_INT_MAX);
        $data = [
            'title'       => clean_text($_POST['title'] ?? ''),
            'issuer'      => clean_text($_POST['issuer'] ?? ''),
            'year'        => clean_text($_POST['year'] ?? ''),
            'description' => clean_text($_POST['description'] ?? ''),
            'sort_order'  => post_int('sort_order', -9999, 9999),
        ];

        if ($data['title'] === '' || mb_strlen($data['title']) > 150) {
            $errors['title'] = 'Title is required (max 150 characters).';
        }
        if (mb_strlen($data['issuer']) > 150) {
            $errors['issuer'] = 'Issuer must be 150 characters or fewer.';
        }
        if (mb_strlen($data['year']) > 20) {
            $errors['year'] = 'Year must be 20 characters or fewer.';
        }
        if (mb_strlen($data['description']) > 500) {
            $errors['description'] = 'Description must be 500 characters or fewer.';
        }

        if (!$errors) {
            if ($id > 0) {
                $pdo->prepare('UPDATE achievements SET title=?, issuer=?, year=?, description=?, sort_order=? WHERE id=?')
                    ->execute([$data['title'], $data['issuer'], $data['year'], $data['description'], $data['sort_order'], $id]);
                flash('success', 'Achievement updated.');
            } else {
                $pdo->prepare('INSERT INTO achievements (title, issuer, year, description, sort_order) VALUES (?, ?, ?, ?, ?)')
                    ->execute([$data['title'], $data['issuer'], $data['year'], $data['description'], $data['sort_order']]);
                flash('success', 'Achievement added.');
            }
            redirect('achievements.php');
        }
        $edit = $data + ['id' => $id];
    }
}

if ($edit === null && isset($_GET['edit'])) {
    $st = $pdo->prepare('SELECT * FROM achievements WHERE id = ?');
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch() ?: null;
}

$form = $edit ?? ['id' => 0, 'title' => '', 'issuer' => '', 'year' => '', 'description' => '', 'sort_order' => 0];
$rows = $pdo->query('SELECT * FROM achievements ORDER BY sort_order, id')->fetchAll();

admin_header('Achievements & Certificates', 'achievements');
?>

<form method="post" action="achievements.php" class="panel">
    <h2><?= $form['id'] ? 'Edit achievement' : 'Add achievement or certificate' ?></h2>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)$form['id'] ?>">

    <div class="form-grid">
        <?php
        f_text('title', 'Title', $form['title'], ['max' => 150, 'required' => true, 'error' => $errors['title'] ?? null]);
        f_text('issuer', 'Issued by', $form['issuer'], ['max' => 150, 'error' => $errors['issuer'] ?? null]);
        f_text('year', 'Year / date', $form['year'], ['max' => 20, 'error' => $errors['year'] ?? null]);
        f_text('sort_order', 'Sort order (lower shows first)', $form['sort_order'], ['type' => 'number']);
        echo '<div class="full">';
        f_textarea('description', 'Description', $form['description'], ['max' => 500, 'rows' => 3, 'error' => $errors['description'] ?? null]);
        echo '</div>';
        ?>
    </div>

    <button type="submit" class="btn"><?= $form['id'] ? 'Update achievement' : 'Add achievement' ?></button>
    <?php if ($form['id']): ?><a class="btn-small" href="achievements.php">Cancel</a><?php endif; ?>
</form>

<div class="panel">
    <h2>All achievements (<?= count($rows) ?>)</h2>
    <div class="table-wrap">
        <table class="admin-table">
            <thead><tr><th>Title</th><th>Issued by</th><th>Year</th><th>Order</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['title']) ?></td>
                    <td><?= e($r['issuer']) ?></td>
                    <td><?= e($r['year']) ?></td>
                    <td><?= (int)$r['sort_order'] ?></td>
                    <td class="actions">
                        <a class="btn-small" href="achievements.php?edit=<?= (int)$r['id'] ?>">Edit</a>
                        <?php delete_form('Delete', (int)$r['id'], 'Delete "' . $r['title'] . '"?'); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="5" class="muted">No achievements yet. Add certificates or awards here and they will appear on your Resume page.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php admin_footer(); ?>
