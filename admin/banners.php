<?php
require_once __DIR__ . '/../config/database.php';
require_admin_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_banner'])) {
        $bannerId = (int)$_POST['banner_id'];
        $existing = $pdo->prepare("SELECT image_path FROM ad_banners WHERE id = ?");
        $existing->execute([$bannerId]);
        $row = $existing->fetch();
        if ($row && $row['image_path'] && file_exists(__DIR__ . '/../assets/uploads/' . $row['image_path'])) {
            unlink(__DIR__ . '/../assets/uploads/' . $row['image_path']);
        }
        $pdo->prepare("DELETE FROM ad_banners WHERE id = ?")->execute([$bannerId]);
        set_flash('success', 'Banner deleted.');
        redirect('/admin/banners');
    }

    if (isset($_POST['toggle_banner'])) {
        $bannerId = (int)$_POST['banner_id'];
        $pdo->prepare("UPDATE ad_banners SET is_active = NOT is_active WHERE id = ?")->execute([$bannerId]);
        set_flash('success', 'Banner status updated.');
        redirect('/admin/banners');
    }

    if (isset($_POST['save_banner'])) {
        $title = sanitize($_POST['title']);
        $type = $_POST['type'] === 'image' ? 'image' : 'adsense';
        $adCode = $type === 'adsense' ? $_POST['ad_code'] : null;
        $linkUrl = sanitize($_POST['link_url'] ?? '');
        $position = sanitize($_POST['position'] ?? 'content');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $editId = (int)($_POST['edit_id'] ?? 0);

        $imagePath = null;
        if ($type === 'image') {
            if (!empty($_FILES['banner_image']['name'])) {
                $upload = upload_image($_FILES['banner_image'], 'banners');
                if ($upload['success']) {
                    $imagePath = $upload['filename'];
                } else {
                    set_flash('error', $upload['message'] ?? 'Failed to upload image.');
                    redirect('/admin/banners?action=' . ($editId ? "edit&id=$editId" : 'add'));
                }
            } elseif ($editId) {
                $existing = $pdo->prepare("SELECT image_path FROM ad_banners WHERE id = ?");
                $existing->execute([$editId]);
                $row = $existing->fetch();
                $imagePath = $row ? $row['image_path'] : null;
            }
        }

        if (empty($title)) {
            set_flash('error', 'Title is required.');
            redirect('/admin/banners?action=' . ($editId ? "edit&id=$editId" : 'add'));
        }

        if ($editId) {
            $sql = "UPDATE ad_banners SET title=?, type=?, ad_code=?, image_path=?, link_url=?, position=?, is_active=?, sort_order=?, updated_at=NOW() WHERE id=?";
            $params = [$title, $type, $adCode, $imagePath, $linkUrl, $position, $isActive, $sortOrder, $editId];
            $pdo->prepare($sql)->execute($params);
            set_flash('success', 'Banner updated.');
        } else {
            $pdo->prepare("INSERT INTO ad_banners (title, type, ad_code, image_path, link_url, position, is_active, sort_order) VALUES (?,?,?,?,?,?,?,?)")
                ->execute([$title, $type, $adCode, $imagePath, $linkUrl, $position, $isActive, $sortOrder]);
            set_flash('success', 'Banner created.');
        }
        redirect('/admin/banners');
    }
}

require_once __DIR__ . '/includes/header.php';

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$banner = null;

if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM ad_banners WHERE id = ?");
    $stmt->execute([$id]);
    $banner = $stmt->fetch();
    if (!$banner) redirect('/admin/banners');
}

$positions = [
    'hero' => 'Hero — Homepage top (below navigation)',
    'content' => 'Content — Homepage & Jobs page (between sections)',
    'sidebar' => 'Sidebar — Job details page (right column)',
    'footer' => 'Footer — All pages (above footer)'
];

if ($action === 'add' || $action === 'edit'):
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <a href="/admin/banners" class="text-sm text-gray-500 hover:text-primary mb-1 inline-flex items-center">
            <i class="fas fa-arrow-left mr-1"></i> Back to Banners
        </a>
        <h1 class="text-2xl font-bold text-gray-900"><?= $banner ? 'Edit Banner' : 'Add Banner' ?></h1>
    </div>
</div>

<form method="POST" enctype="multipart/form-data" class="space-y-6">
    <input type="hidden" name="edit_id" value="<?= $banner['id'] ?? '' ?>">

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><i class="fas fa-ad mr-2 text-primary"></i>Banner Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                <input type="text" name="title" value="<?= sanitize($banner['title'] ?? '') ?>" required class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="type" id="bannerType" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm bg-white">
                    <option value="adsense" <?= ($banner['type'] ?? 'adsense') === 'adsense' ? 'selected' : '' ?>>Google AdSense</option>
                    <option value="image" <?= ($banner['type'] ?? '') === 'image' ? 'selected' : '' ?>>Image Banner</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                <select name="position" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm bg-white">
                    <?php foreach ($positions as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($banner['position'] ?? 'content') === $key ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="<?= $banner['sort_order'] ?? 0 ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
        </div>

        <!-- AdSense Section -->
        <div id="adsenseSection" class="mt-4" style="<?= ($banner['type'] ?? 'adsense') !== 'adsense' ? 'display:none' : '' ?>">
            <label class="block text-sm font-medium text-gray-700 mb-1">AdSense Code</label>
            <textarea name="ad_code" rows="6" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm font-mono resize-none" placeholder='Paste your AdSense ad unit code here...'><?= sanitize($banner['ad_code'] ?? '') ?></textarea>
        </div>

        <!-- Image Section -->
        <div id="imageSection" class="mt-4" style="<?= ($banner['type'] ?? 'adsense') !== 'image' ? 'display:none' : '' ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Banner Image</label>
                    <input type="file" name="banner_image" accept="image/*" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
                    <p class="text-xs text-gray-400 mt-1">Recommended: 1200x200px for content banners, 300x250 for sidebar.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Link URL (optional)</label>
                    <input type="url" name="link_url" value="<?= sanitize($banner['link_url'] ?? '') ?>" placeholder="https://example.com" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
                </div>
            </div>
            <?php if (!empty($banner['image_path'])): ?>
            <div class="mt-3 rounded-lg overflow-hidden border border-gray-200 max-w-md">
                <img src="/assets/uploads/<?= $banner['image_path'] ?>" alt="Current banner" class="w-full h-auto">
            </div>
            <?php endif; ?>
        </div>

        <div class="mt-4">
            <label class="flex items-center space-x-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" <?= ($banner['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 text-primary rounded border-gray-300 focus:ring-primary">
                <span class="text-sm font-medium text-gray-700">Active</span>
            </label>
        </div>
    </div>

    <button type="submit" name="save_banner" class="px-6 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors">
        <i class="fas fa-save mr-1"></i> Save Banner
    </button>
</form>

<script>
document.getElementById('bannerType').addEventListener('change', function() {
    document.getElementById('adsenseSection').style.display = this.value === 'adsense' ? '' : 'none';
    document.getElementById('imageSection').style.display = this.value === 'image' ? '' : 'none';
});
</script>

<?php else: ?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Ad Banners</h1>
        <p class="text-gray-500 text-sm">Manage advertisement banners across the site</p>
    </div>
    <a href="/admin/banners?action=add" class="px-4 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors">
        <i class="fas fa-plus mr-1"></i> Add Banner
    </a>
</div>

<?php
$banners = $pdo->query("SELECT * FROM ad_banners ORDER BY sort_order ASC, created_at DESC")->fetchAll();
?>

<?php if (empty($banners)): ?>
<div class="bg-white rounded-xl border border-gray-200 p-12 text-center shadow-sm">
    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
        <i class="fas fa-ad text-gray-300 text-2xl"></i>
    </div>
    <h3 class="text-lg font-semibold text-gray-900 mb-2">No banners yet</h3>
    <p class="text-gray-500 mb-4">Create your first ad banner to display across the site.</p>
    <a href="/admin/banners?action=add" class="inline-flex items-center px-5 py-2.5 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark transition-colors">
        <i class="fas fa-plus mr-1"></i> Add Banner
    </a>
</div>
<?php else: ?>
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Title</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Position</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Order</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($banners as $b): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div class="flex items-center space-x-3">
                        <?php if ($b['type'] === 'image' && $b['image_path']): ?>
                        <img src="/assets/uploads/<?= $b['image_path'] ?>" class="w-16 h-10 object-cover rounded border border-gray-200">
                        <?php else: ?>
                        <div class="w-16 h-10 bg-blue-50 rounded border border-blue-200 flex items-center justify-center">
                            <i class="fab fa-google text-blue-500 text-xs"></i>
                        </div>
                        <?php endif; ?>
                        <span class="font-medium text-gray-900 text-sm"><?= sanitize($b['title']) ?></span>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    <?php if ($b['type'] === 'adsense'): ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700"><i class="fab fa-google mr-1"></i> AdSense</span>
                    <?php else: ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700"><i class="fas fa-image mr-1"></i> Image</span>
                    <?php endif; ?>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500"><?= $positions[$b['position']] ?? $b['position'] ?></td>
                <td class="px-6 py-4 text-sm text-gray-500"><?= $b['sort_order'] ?></td>
                <td class="px-6 py-4">
                    <form method="POST" class="inline">
                        <input type="hidden" name="banner_id" value="<?= $b['id'] ?>">
                        <button type="submit" name="toggle_banner" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer <?= $b['is_active'] ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                            <?= $b['is_active'] ? 'Active' : 'Inactive' ?>
                        </button>
                    </form>
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end space-x-2">
                        <a href="/admin/banners?action=edit&id=<?= $b['id'] ?>" class="p-2 text-gray-400 hover:text-primary rounded-lg hover:bg-gray-100 transition-colors" title="Edit">
                            <i class="fas fa-pen text-sm"></i>
                        </a>
                        <form method="POST" data-confirm="Delete this banner?" class="inline">
                            <input type="hidden" name="banner_id" value="<?= $b['id'] ?>">
                            <button type="submit" name="delete_banner" class="p-2 text-gray-400 hover:text-red-500 rounded-lg hover:bg-gray-100 transition-colors" title="Delete">
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
