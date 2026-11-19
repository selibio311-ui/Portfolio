<?php
define('IN_ADMIN', true);
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/admin_ui.php';
require_admin();

$pdo = db();

$categories = [
    'technical' => 'Technical skill (progress bar)',
    'tool'      => 'Technology & tool (card)',
    'personal'  => 'Personal skill (progress bar)',
    'language'  => 'Language',
];

$errors = [];
$edit   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $pdo->prepare('DELETE FROM skills WHERE id = ?')->execute([post_int('id', 0, PHP_INT_MAX)]);
        flash('success', 'Skill deleted.');
        redirect('skills.php');
    }

    if ($action === 'save') {
        $id   = post_int('id', 0, PHP_INT_MAX);
        $data = [
            'name'        => clean_text($_POST['name'] ?? ''),
            'category'    => clean_text($_POST['category'] ?? ''),
            'level'       => post_int('level', 0, 100),
            'icon'        => clean_text($_POST['icon'] ?? ''),
            'description' => clean_text($_POST['description'] ?? ''),
            'sort_order'  => post_int('sort_order', -9999, 9999),
        ];

        if ($data['name'] === '' || mb_strlen($data['name']) > 100) {
            $errors['name'] = 'Name is required (max 100 characters).';
        }
        if (!isset($categories[$data['category']])) {
            $errors['category'] = 'Choose a category.';
        }
        if (mb_strlen($data['icon']) > 16) {
            $errors['icon'] = 'Icon must be 16 characters or fewer.';
        }
        if (mb_strlen($data['description']) > 255) {
            $errors['description'] = 'Description must be 255 characters or fewer.';
        }

        if (!$errors) {
            if ($id > 0) {
                $pdo->prepare('UPDATE skills SET name=?, category=?, level=?, icon=?, description=?, sort_order=? WHERE id=?')
                    ->execute([$data['name'], $data['category'], $data['level'], $data['icon'], $data['description'], $data['sort_order'], $id]);
                flash('success', 'Skill updated.');
            } else {
                $pdo->prepare('INSERT INTO skills (name, category, level, icon, description, sort_order) VALUES (?, ?, ?, ?, ?, ?)')
                    ->execute([$data['name'], $data['category'], $data['level'], $data['icon'], $data['description'], $data['sort_order']]);
                flash('success', 'Skill added.');
            }
            redirect('skills.php');
        }
        $edit = $data + ['id' => $id];
    }
}

if ($edit === null && isset($_GET['edit'])) {
    $st = $pdo->prepare('SELECT * FROM skills WHERE id = ?');
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch() ?: null;
}

$form = $edit ?? ['id' => 0, 'name' => '', 'category' => 'technical', 'level' => 50, 'icon' => '', 'description' => '', 'sort_order' => 0];
$rows = $pdo->query('SELECT * FROM skills ORDER BY category, sort_order, id')->fetchAll();

admin_header('Skills', 'skills');
?>

<form method="post" action="skills.php" class="panel">
    <h2><?= $form['id'] ? 'Edit skill' : 'Add a skill' ?></h2>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)$form['id'] ?>">

    <div class="form-grid">
        <?php
        f_text('name', 'Name', $form['name'], ['max' => 100, 'required' => true, 'error' => $errors['name'] ?? null]);
        f_select('category', 'Category', $form['category'], $categories, ['error' => $errors['category'] ?? null]);
        f_text('level', 'Level (0-100, for progress bars)', $form['level'], ['type' => 'number', 'hint' => 'Used by technical and personal skills.']);
        f_text('sort_order', 'Sort order (lower shows first)', $form['sort_order'], ['type' => 'number']);
        f_text('icon', 'Icon / emoji (for tool cards)', $form['icon'], ['max' => 16, 'error' => $errors['icon'] ?? null]);
        f_text('description', 'Description (cards and personal skills)', $form['description'], ['max' => 255, 'error' => $errors['description'] ?? null]);
        ?>
    </div>

    <button type="submit" class="btn"><?= $form['id'] ? 'Update skill' : 'Add skill' ?></button>
    <?php if ($form['id']): ?><a class="btn-small" href="skills.php">Cancel</a><?php endif; ?>
</form>

<div class="panel">
    <h2>All skills (<?= count($rows) ?>)</h2>
    <div class="table-wrap">
        <table class="admin-table">
            <thead><tr><th>Name</th><th>Category</th><th>Level</th><th>Order</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['icon'] !== '' ? $r['icon'] . ' ' : '') ?><?= e($r['name']) ?></td>
                    <td><?= e($categories[$r['category']] ?? $r['category']) ?></td>
                    <td><?= in_array($r['category'], ['technical', 'personal'], true) ? (int)$r['level'] . '%' : '<span class="muted">-</span>' ?></td>
                    <td><?= (int)$r['sort_order'] ?></td>
                    <td class="actions">
                        <a class="btn-small" href="skills.php?edit=<?= (int)$r['id'] ?>">Edit</a>
                        <?php delete_form('Delete', (int)$r['id'], 'Delete the skill "' . $r['name'] . '"?'); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="5" class="muted">No skills yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php admin_footer(); ?>
