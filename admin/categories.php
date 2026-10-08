<?php
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_category'])) {
        $pdo->prepare("UPDATE categories SET is_active = 0 WHERE id = ?")->execute([(int)$_POST['cat_id']]);
        set_flash('success', 'Category deleted.');
        redirect('/admin/categories');
    }

    if (isset($_POST['save_category'])) {
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description'] ?? '');
        $icon = sanitize($_POST['icon'] ?? 'briefcase');
        $editId = (int)($_POST['edit_id'] ?? 0);

        if (empty($name)) {
            set_flash('error', 'Category name is required.');
            redirect('/admin/categories');
        }

        $slug = generate_slug($name);
        if ($editId) {
            $slugRow = $pdo->prepare("SELECT slug FROM categories WHERE id = ?");
            $slugRow->execute([$editId]);
            $slug = $slugRow->fetch()['slug'];
            $pdo->prepare("UPDATE categories SET name=?, description=?, icon=? WHERE id=?")->execute([$name, $description, $icon, $editId]);
        } else {
            $check = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = ?");
            $check->execute([$slug]);
            if ($check->fetchColumn() > 0) $slug .= '-' . time();
            $pdo->prepare("INSERT INTO categories (name, slug, description, icon) VALUES (?,?,?,?)")->execute([$name, $slug, $description, $icon]);
        }
        set_flash('success', 'Category saved.');
        redirect('/admin/categories');
    }
}

$allCategories = $pdo->query("
    SELECT cat.*, COUNT(j.id) as job_count
    FROM categories cat
    LEFT JOIN jobs j ON j.category_id = cat.id AND j.status = 'published' AND j.is_active = 1
    WHERE cat.is_active = 1
    GROUP BY cat.id
    ORDER BY cat.name
")->fetchAll();

$editCat = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editCat = $stmt->fetch();
}
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Categories</h1>
        <p class="text-gray-500 text-sm"><?= count($allCategories) ?> categories</p>
    </div>
</div>

<!-- Add/Edit Form -->
<div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm mb-6">
    <h2 class="font-bold text-gray-900 mb-4"><?= $editCat ? 'Edit Category' : 'Add Category' ?></h2>
    <form method="POST" class="flex flex-col sm:flex-row gap-3">
        <input type="hidden" name="save_category" value="1">
        <?php if ($editCat): ?>
        <input type="hidden" name="edit_id" value="<?= $editCat['id'] ?>">
        <?php endif; ?>
        <input type="text" name="name" value="<?= sanitize($editCat['name'] ?? '') ?>" placeholder="Category name" required class="flex-1 px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
        <input type="text" name="icon" value="<?= sanitize($editCat['icon'] ?? '') ?>" placeholder="Font Awesome icon (e.g. code)" class="w-full sm:w-40 px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
        <input type="text" name="description" value="<?= sanitize($editCat['description'] ?? '') ?>" placeholder="Description" class="flex-1 px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
        <button type="submit" class="px-5 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors whitespace-nowrap"><i class="fas fa-save mr-1"></i> <?= $editCat ? 'Update' : 'Add' ?></button>
        <?php if ($editCat): ?>
        <a href="/admin/categories" class="px-4 py-2.5 border border-gray-200 text-gray-600 text-sm rounded-lg hover:bg-gray-50 transition-colors">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<!-- List -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="px-5 py-3 font-medium text-gray-600">Name</th>
                    <th class="px-5 py-3 font-medium text-gray-600">Icon</th>
                    <th class="px-5 py-3 font-medium text-gray-600">Description</th>
                    <th class="px-5 py-3 font-medium text-gray-600">Jobs</th>
                    <th class="px-5 py-3 font-medium text-gray-600 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($allCategories as $cat): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-medium text-gray-800"><?= sanitize($cat['name']) ?></td>
                    <td class="px-5 py-3"><i class="fas fa-<?= $cat['icon'] ?> text-primary"></i> <?= $cat['icon'] ?></td>
                    <td class="px-5 py-3 text-gray-500 text-xs max-w-[200px] truncate"><?= sanitize($cat['description']) ?></td>
                    <td class="px-5 py-3 text-gray-600"><?= $cat['job_count'] ?></td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="/admin/categories?edit=<?= $cat['id'] ?>" class="px-3 py-1.5 text-xs bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors"><i class="fas fa-edit"></i></a>
                            <form method="POST" onsubmit="return confirm('Delete this category?')">
                                <input type="hidden" name="delete_category" value="1">
                                <input type="hidden" name="cat_id" value="<?= $cat['id'] ?>">
                                <button type="submit" class="px-3 py-1.5 text-xs bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
