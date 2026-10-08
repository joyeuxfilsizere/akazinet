<?php
require_once __DIR__ . '/../config/database.php';
require_admin_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_scholarship'])) {
        $pdo->prepare("DELETE FROM scholarships WHERE id = ?")->execute([(int)$_POST['scholarship_id']]);
        set_flash('success', 'Scholarship deleted.');
        redirect('/admin/scholarships');
    }

    if (isset($_POST['toggle_scholarship'])) {
        $pdo->prepare("UPDATE scholarships SET is_active = NOT is_active WHERE id = ?")->execute([(int)$_POST['scholarship_id']]);
        set_flash('success', 'Scholarship status updated.');
        redirect('/admin/scholarships');
    }

    if (isset($_POST['save_scholarship'])) {
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $icon = sanitize($_POST['icon'] ?? 'fas fa-graduation-cap');
        $iconBg = sanitize($_POST['icon_bg'] ?? 'bg-primary/10');
        $iconColor = sanitize($_POST['icon_color'] ?? 'text-primary');
        $linkText = sanitize($_POST['link_text'] ?? 'Learn More');
        $linkUrl = sanitize($_POST['link_url'] ?? '#');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $editId = (int)($_POST['edit_id'] ?? 0);

        if (empty($title) || empty($description)) {
            set_flash('error', 'Title and description are required.');
            redirect('/admin/scholarships?action=' . ($editId ? "edit&id=$editId" : 'add'));
        }

        if ($editId) {
            $pdo->prepare("UPDATE scholarships SET title=?, description=?, icon=?, icon_bg=?, icon_color=?, link_text=?, link_url=?, is_active=?, sort_order=?, updated_at=NOW() WHERE id=?")
                ->execute([$title, $description, $icon, $iconBg, $iconColor, $linkText, $linkUrl, $isActive, $sortOrder, $editId]);
            set_flash('success', 'Scholarship updated.');
        } else {
            $pdo->prepare("INSERT INTO scholarships (title, description, icon, icon_bg, icon_color, link_text, link_url, is_active, sort_order) VALUES (?,?,?,?,?,?,?,?,?)")
                ->execute([$title, $description, $icon, $iconBg, $iconColor, $linkText, $linkUrl, $isActive, $sortOrder]);
            set_flash('success', 'Scholarship created.');
        }
        redirect('/admin/scholarships');
    }
}

require_once __DIR__ . '/includes/header.php';

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$scholarship = null;

if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM scholarships WHERE id = ?");
    $stmt->execute([$id]);
    $scholarship = $stmt->fetch();
    if (!$scholarship) redirect('/admin/scholarships');
}

$iconPresets = [
    'fas fa-university|bg-blue-50|text-blue-500' => 'University (blue)',
    'fas fa-hand-holding-heart|bg-green-100|text-green-600' => 'Heart (green)',
    'fas fa-graduation-cap|bg-primary/10|text-primary' => 'Graduation (primary)',
    'fas fa-globe|bg-accent/10|text-accent' => 'Globe (accent)',
    'fas fa-book|bg-purple-50|text-purple-600' => 'Book (purple)',
    'fas fa-award|bg-yellow-50|text-yellow-600' => 'Award (yellow)',
    'fas fa-landmark|bg-red-50|text-red-600' => 'Landmark (red)',
];

if ($action === 'add' || $action === 'edit'):
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <a href="/admin/scholarships" class="text-sm text-gray-500 hover:text-primary mb-1 inline-flex items-center">
            <i class="fas fa-arrow-left mr-1"></i> Back to Scholarships
        </a>
        <h1 class="text-2xl font-bold text-gray-900"><?= $scholarship ? 'Edit Scholarship' : 'Add Scholarship' ?></h1>
    </div>
</div>

<form method="POST" class="space-y-6">
    <input type="hidden" name="edit_id" value="<?= $scholarship['id'] ?? '' ?>">

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><i class="fas fa-graduation-cap mr-2 text-primary"></i>Scholarship Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                <input type="text" name="title" value="<?= sanitize($scholarship['title'] ?? '') ?>" required class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                <textarea name="description" rows="3" required class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm resize-none"><?= sanitize($scholarship['description'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Icon Style</label>
                <select name="icon_preset" id="iconPreset" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm bg-white">
                    <option value="custom">Custom</option>
                    <?php foreach ($iconPresets as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($scholarship['icon'] ?? '') . '|' . ($scholarship['icon_bg'] ?? '') . '|' . ($scholarship['icon_color'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="<?= $scholarship['sort_order'] ?? 0 ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4" id="customIconFields">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Icon Class</label>
                <input type="text" name="icon" value="<?= sanitize($scholarship['icon'] ?? 'fas fa-graduation-cap') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm font-mono text-xs">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Icon Background</label>
                <input type="text" name="icon_bg" value="<?= sanitize($scholarship['icon_bg'] ?? 'bg-primary/10') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm font-mono text-xs">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Icon Color</label>
                <input type="text" name="icon_color" value="<?= sanitize($scholarship['icon_color'] ?? 'text-primary') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm font-mono text-xs">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Link Text</label>
                <input type="text" name="link_text" value="<?= sanitize($scholarship['link_text'] ?? 'Learn More') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Link URL</label>
                <input type="url" name="link_url" value="<?= sanitize($scholarship['link_url'] ?? '#') ?>" placeholder="https://example.com" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
        </div>

        <div class="mt-4">
            <label class="flex items-center space-x-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" <?= ($scholarship['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 text-primary rounded border-gray-300 focus:ring-primary">
                <span class="text-sm font-medium text-gray-700">Active</span>
            </label>
        </div>
    </div>

    <button type="submit" name="save_scholarship" class="px-6 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors">
        <i class="fas fa-save mr-1"></i> Save Scholarship
    </button>
</form>

<script>
document.getElementById('iconPreset').addEventListener('change', function() {
    if (this.value !== 'custom') {
        var parts = this.value.split('|');
        document.querySelector('input[name="icon"]').value = parts[0];
        document.querySelector('input[name="icon_bg"]').value = parts[1];
        document.querySelector('input[name="icon_color"]').value = parts[2];
    }
});
</script>

<?php else: ?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Scholarships</h1>
        <p class="text-gray-500 text-sm">Manage scholarship listings displayed on the site</p>
    </div>
    <a href="/admin/scholarships?action=add" class="px-4 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors">
        <i class="fas fa-plus mr-1"></i> Add Scholarship
    </a>
</div>

<?php
$allScholarships = $pdo->query("SELECT * FROM scholarships ORDER BY sort_order ASC, created_at DESC")->fetchAll();
?>

<?php if (empty($allScholarships)): ?>
<div class="bg-white rounded-xl border border-gray-200 p-12 text-center shadow-sm">
    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
        <i class="fas fa-graduation-cap text-gray-300 text-2xl"></i>
    </div>
    <h3 class="text-lg font-semibold text-gray-900 mb-2">No scholarships yet</h3>
    <p class="text-gray-500 mb-4">Add your first scholarship listing.</p>
    <a href="/admin/scholarships?action=add" class="inline-flex items-center px-5 py-2.5 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark transition-colors">
        <i class="fas fa-plus mr-1"></i> Add Scholarship
    </a>
</div>
<?php else: ?>
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Title</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Link</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Order</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($allScholarships as $s): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center <?= $s['icon_bg'] ?>">
                            <i class="<?= $s['icon'] ?> <?= $s['icon_color'] ?> text-sm"></i>
                        </div>
                        <div>
                            <span class="font-medium text-gray-900 text-sm"><?= sanitize($s['title']) ?></span>
                            <p class="text-xs text-gray-400 mt-0.5 line-clamp-1"><?= sanitize($s['description']) ?></p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500 max-w-[200px] truncate">
                    <a href="<?= sanitize($s['link_url']) ?>" target="_blank" class="hover:text-primary transition-colors"><?= sanitize($s['link_text']) ?></a>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500"><?= $s['sort_order'] ?></td>
                <td class="px-6 py-4">
                    <form method="POST" class="inline">
                        <input type="hidden" name="scholarship_id" value="<?= $s['id'] ?>">
                        <button type="submit" name="toggle_scholarship" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer <?= $s['is_active'] ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                            <?= $s['is_active'] ? 'Active' : 'Inactive' ?>
                        </button>
                    </form>
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end space-x-2">
                        <a href="/admin/scholarships?action=edit&id=<?= $s['id'] ?>" class="p-2 text-gray-400 hover:text-primary rounded-lg hover:bg-gray-100 transition-colors" title="Edit">
                            <i class="fas fa-pen text-sm"></i>
                        </a>
                        <form method="POST" data-confirm="Delete this scholarship?" class="inline">
                            <input type="hidden" name="scholarship_id" value="<?= $s['id'] ?>">
                            <button type="submit" name="delete_scholarship" class="p-2 text-gray-400 hover:text-red-500 rounded-lg hover:bg-gray-100 transition-colors" title="Delete">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
