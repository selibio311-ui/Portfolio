<?php
define('IN_ADMIN', true);
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/admin_ui.php';
require_admin();

$pdo = db();

$categories = ['web' => 'Web', 'system' => 'Systems', 'design' => 'Design'];

$errors = [];
$edit   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = post_int('id', 0, PHP_INT_MAX);
        $st = $pdo->prepare('SELECT image FROM projects WHERE id = ?');
        $st->execute([$id]);
        $img = $st->fetchColumn();

        $pdo->prepare('DELETE FROM projects WHERE id = ?')->execute([$id]);
        delete_upload($img ?: null);

        flash('success', 'Project deleted.');
        redirect('projects.php');
    }

    if ($action === 'save') {
        $id   = post_int('id', 0, PHP_INT_MAX);
        $data = [
            'title'       => clean_text($_POST['title'] ?? ''),
            'category'    => clean_text($_POST['category'] ?? ''),
            'label'       => clean_text($_POST['label'] ?? ''),
            'description' => clean_text($_POST['description'] ?? ''),
            'tech'        => clean_text($_POST['tech'] ?? ''),
            'project_url' => clean_text($_POST['project_url'] ?? ''),
            'sort_order'  => post_int('sort_order', -9999, 9999),
        ];

        if ($data['title'] === '' || mb_strlen($data['title']) > 150) {
            $errors['title'] = 'Title is required (max 150 characters).';
        }
        if (!isset($categories[$data['category']])) {
            $errors['category'] = 'Choose a category.';
        }
        if (mb_strlen($data['label']) > 60) {
            $errors['label'] = 'Label must be 60 characters or fewer.';
        }
        if ($data['description'] === '' || mb_strlen($data['description']) > 2000) {
            $errors['description'] = 'Description is required (max 2000 characters).';
        }
        if (mb_strlen($data['tech']) > 255) {
            $errors['tech'] = 'Technologies must be 255 characters or fewer.';
        }
        if (mb_strlen($data['project_url']) > 255 || !valid_url($data['project_url'])) {
            $errors['project_url'] = 'Enter a full http:// or https:// address, or leave empty.';
        }

        [$newImage, $imageError] = handle_image_upload('image', 'project');
        if ($imageError) {
            $errors['image'] = $imageError;
        }

        $current = '';
        if ($id > 0) {
            $st = $pdo->prepare('SELECT image FROM projects WHERE id = ?');
            $st->execute([$id]);
            $current = (string)$st->fetchColumn();
        }

        if (!$errors) {
            $image = $newImage ?: $current;

            if ($id > 0) {
                $pdo->prepare('UPDATE projects SET title=?, category=?, label=?, description=?, tech=?, image=?, project_url=?, sort_order=? WHERE id=?')
                    ->execute([$data['title'], $data['category'], $data['label'], $data['description'], $data['tech'], $image, $data['project_url'], $data['sort_order'], $id]);
                if ($newImage) {
                    delete_upload($current);
                }
                flash('success', 'Project updated.');
            } else {
                $pdo->prepare('INSERT INTO projects (title, category, label, description, tech, image, project_url, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
                    ->execute([$data['title'], $data['category'], $data['label'], $data['description'], $data['tech'], $image, $data['project_url'], $data['sort_order']]);
                flash('success', 'Project added.');
            }
            redirect('projects.php');
        }

        if ($newImage) {
            delete_upload($newImage);   // form failed: discard the new upload
        }
        $edit = $data + ['id' => $id, 'image' => $current];
    }
}

if ($edit === null && isset($_GET['edit'])) {
    $st = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch() ?: null;
}

$form = $edit ?? ['id' => 0, 'title' => '', 'category' => 'web', 'label' => '', 'description' => '', 'tech' => '', 'image' => '', 'project_url' => '', 'sort_order' => 0];
$rows = $pdo->query('SELECT * FROM projects ORDER BY sort_order, id')->fetchAll();

admin_header('Projects', 'projects');
?>

<form method="post" action="projects.php" enctype="multipart/form-data" class="panel">
    <h2><?= $form['id'] ? 'Edit project' : 'Add a project' ?></h2>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)$form['id'] ?>">

    <div class="form-grid">
        <?php
        f_text('title', 'Title', $form['title'], ['max' => 150, 'required' => true, 'error' => $errors['title'] ?? null]);
        f_select('category', 'Category (filter)', $form['category'], $categories, ['error' => $errors['category'] ?? null]);
        f_text('label', 'Badge label (optional)', $form['label'], ['max' => 60, 'hint' => 'Shown above the title, e.g. "Animation". Defaults to the category.', 'error' => $errors['label'] ?? null]);
        f_text('sort_order', 'Sort order (lower shows first)', $form['sort_order'], ['type' => 'number']);
        echo '<div class="full">';
        f_textarea('description', 'Description', $form['description'], ['max' => 2000, 'rows' => 6, 'hint' => 'Leave a blank line between paragraphs.', 'error' => $errors['description'] ?? null]);
        echo '</div>';
        f_text('tech', 'Technologies (comma separated)', $form['tech'], ['max' => 255, 'error' => $errors['tech'] ?? null]);
        f_text('project_url', 'Project link (optional)', $form['project_url'], ['max' => 255, 'type' => 'url', 'error' => $errors['project_url'] ?? null]);
        echo '<div class="full">';
        f_file('image', 'Project image / screenshot', $form['image'], $errors['image'] ?? null);
        echo '</div>';
        ?>
    </div>

    <button type="submit" class="btn"><?= $form['id'] ? 'Update project' : 'Add project' ?></button>
    <?php if ($form['id']): ?><a class="btn-small" href="projects.php">Cancel</a><?php endif; ?>
</form>

<div class="panel">
    <h2>All projects (<?= count($rows) ?>)</h2>
    <div class="table-wrap">
        <table class="admin-table">
            <thead><tr><th>Image</th><th>Title</th><th>Category</th><th>Order</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?php if (img_src($r['image']) !== ''): ?><img class="thumb" src="<?= img_src($r['image']) ?>" alt=""><?php else: ?><span class="muted">-</span><?php endif; ?></td>
                    <td><?= e($r['title']) ?></td>
                    <td><?= e($categories[$r['category']] ?? $r['category']) ?></td>
                    <td><?= (int)$r['sort_order'] ?></td>
                    <td class="actions">
                        <a class="btn-small" href="projects.php?edit=<?= (int)$r['id'] ?>">Edit</a>
                        <?php delete_form('Delete', (int)$r['id'], 'Delete the project "' . $r['title'] . '"?'); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="5" class="muted">No projects yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php admin_footer(); ?>
