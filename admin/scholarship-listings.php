<?php
require_once __DIR__ . '/../config/database.php';
require_admin_login();

$scholarships = $pdo->query("SELECT * FROM scholarships WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_listing'])) {
        $pdo->prepare("DELETE FROM scholarship_listings WHERE id = ?")->execute([(int)$_POST['listing_id']]);
        set_flash('success', 'Scholarship listing deleted.');
        redirect('/admin/scholarship-listings');
    }

    if (isset($_POST['toggle_listing'])) {
        $pdo->prepare("UPDATE scholarship_listings SET is_active = NOT is_active WHERE id = ?")->execute([(int)$_POST['listing_id']]);
        set_flash('success', 'Listing status updated.');
        redirect('/admin/scholarship-listings');
    }

    if (isset($_POST['save_listing'])) {
        $scholarshipId = (int)$_POST['scholarship_id'];
        $title = sanitize($_POST['title']);
        $organization = sanitize($_POST['organization']);
        $description = sanitize($_POST['description']);
        $eligibility = sanitize($_POST['eligibility'] ?? '');
        $deadline = $_POST['deadline'] ?: null;
        $linkUrl = sanitize($_POST['link_url'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $editId = (int)($_POST['edit_id'] ?? 0);

        if (empty($title) || empty($organization) || empty($description) || empty($scholarshipId)) {
            set_flash('error', 'Title, organization, and description are required.');
            redirect('/admin/scholarship-listings?action=' . ($editId ? "edit&id=$editId" : 'add'));
        }

        $bannerImage = null;
        if (!empty($_FILES['banner_image']['name'])) {
            $upload = upload_image($_FILES['banner_image'], 'banners');
            if ($upload['success']) {
                $bannerImage = $upload['filename'];
            }
        }

        if ($editId) {
            $sql = "UPDATE scholarship_listings SET scholarship_id=?, title=?, organization=?, description=?, eligibility=?, deadline=?, link_url=?, is_active=?, sort_order=?, updated_at=NOW()";
            $params = [$scholarshipId, $title, $organization, $description, $eligibility, $deadline, $linkUrl, $isActive, $sortOrder];
            if ($bannerImage) {
                $sql .= ", banner_image=?";
                $params[] = $bannerImage;
            }
            $sql .= " WHERE id=?";
            $params[] = $editId;
            $pdo->prepare($sql)->execute($params);
            set_flash('success', 'Scholarship listing updated.');
        } else {
            $pdo->prepare("INSERT INTO scholarship_listings (scholarship_id, title, organization, banner_image, description, eligibility, deadline, link_url, is_active, sort_order) VALUES (?,?,?,?,?,?,?,?,?,?)")
                ->execute([$scholarshipId, $title, $organization, $bannerImage, $description, $eligibility, $deadline, $linkUrl, $isActive, $sortOrder]);
            set_flash('success', 'Scholarship listing created.');
        }
        redirect('/admin/scholarship-listings');
    }
}

require_once __DIR__ . '/includes/header.php';

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$listing = null;

if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM scholarship_listings WHERE id = ?");
    $stmt->execute([$id]);
    $listing = $stmt->fetch();
    if (!$listing) redirect('/admin/scholarship-listings');
}

if ($action === 'add' || $action === 'edit'):
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <a href="/admin/scholarship-listings" class="text-sm text-gray-500 hover:text-primary mb-1 inline-flex items-center">
            <i class="fas fa-arrow-left mr-1"></i> Back to Listings
        </a>
        <h1 class="text-2xl font-bold text-gray-900"><?= $listing ? 'Edit Scholarship' : 'Add Scholarship' ?></h1>
    </div>
</div>

<form method="POST" enctype="multipart/form-data" class="space-y-6">
    <input type="hidden" name="edit_id" value="<?= $listing['id'] ?? '' ?>">

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><i class="fas fa-graduation-cap mr-2 text-primary"></i>Scholarship Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                <select name="scholarship_id" required class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm bg-white">
                    <option value="">Select category</option>
                    <?php foreach ($scholarships as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= ($listing['scholarship_id'] ?? '') == $s['id'] ? 'selected' : '' ?>><?= $s['title'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="<?= $listing['sort_order'] ?? 0 ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                <input type="text" name="title" value="<?= sanitize($listing['title'] ?? '') ?>" required class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Organization *</label>
                <input type="text" name="organization" value="<?= sanitize($listing['organization'] ?? '') ?>" required class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Banner Image</label>
                <input type="file" name="banner_image" accept="image/*" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-primary file:text-white file:text-sm file:cursor-pointer">
                <?php if ($listing && $listing['banner_image']): ?>
                <div class="mt-2 flex items-center gap-2">
                    <img src="/assets/uploads/<?= $listing['banner_image'] ?>" alt="" class="w-20 h-12 object-cover rounded border">
                    <span class="text-xs text-gray-400">Current banner</span>
                </div>
                <?php endif; ?>
                <p class="text-xs text-gray-400 mt-1">Recommended size: 1200x600px. Max 5MB.</p>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                <textarea name="description" rows="4" required class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm resize-none"><?= sanitize($listing['description'] ?? '') ?></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Eligibility</label>
                <textarea name="eligibility" rows="2" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm resize-none"><?= sanitize($listing['eligibility'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
                <input type="date" name="deadline" value="<?= $listing['deadline'] ?? '' ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Application URL</label>
                <input type="url" name="link_url" value="<?= sanitize($listing['link_url'] ?? '') ?>" placeholder="https://example.com/apply" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
        </div>

        <div class="mt-4">
            <label class="flex items-center space-x-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" <?= ($listing['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 text-primary rounded border-gray-300 focus:ring-primary">
                <span class="text-sm font-medium text-gray-700">Active</span>
            </label>
        </div>
    </div>

    <button type="submit" name="save_listing" class="px-6 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors">
        <i class="fas fa-save mr-1"></i> Save Scholarship
    </button>
</form>

<?php else: ?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Scholarship Listings</h1>
        <p class="text-gray-500 text-sm">Manage individual scholarship listings</p>
    </div>
    <a href="/admin/scholarship-listings?action=add" class="px-4 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors">
        <i class="fas fa-plus mr-1"></i> Add Scholarship
    </a>
</div>

<?php
$allListings = $pdo->prepare("
    SELECT sl.*, s.title as category_title, s.icon, s.icon_bg, s.icon_color
    FROM scholarship_listings sl
    JOIN scholarships s ON sl.scholarship_id = s.id
    ORDER BY sl.sort_order ASC, sl.created_at DESC
");
$allListings->execute();
$allListings = $allListings->fetchAll();
?>

<?php if (empty($allListings)): ?>
<div class="bg-white rounded-xl border border-gray-200 p-12 text-center shadow-sm">
    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
        <i class="fas fa-graduation-cap text-gray-300 text-2xl"></i>
    </div>
    <h3 class="text-lg font-semibold text-gray-900 mb-2">No scholarships yet</h3>
    <p class="text-gray-500 mb-4">Add your first scholarship listing.</p>
    <a href="/admin/scholarship-listings?action=add" class="inline-flex items-center px-5 py-2.5 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark transition-colors">
        <i class="fas fa-plus mr-1"></i> Add Scholarship
    </a>
</div>
<?php else: ?>
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Title</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Category</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Organization</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Deadline</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($allListings as $l): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center <?= $l['icon_bg'] ?>">
                            <i class="<?= $l['icon'] ?> <?= $l['icon_color'] ?> text-sm"></i>
                        </div>
                        <span class="font-medium text-gray-900 text-sm"><?= sanitize($l['title']) ?></span>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500"><?= sanitize($l['category_title']) ?></td>
                <td class="px-6 py-4 text-sm text-gray-500"><?= sanitize($l['organization']) ?></td>
                <td class="px-6 py-4 text-sm <?= $l['deadline'] && strtotime($l['deadline']) < time() ? 'text-red-500' : 'text-gray-500' ?>">
                    <?= $l['deadline'] ? date('M d, Y', strtotime($l['deadline'])) : '—' ?>
                </td>
                <td class="px-6 py-4">
                    <form method="POST" class="inline">
                        <input type="hidden" name="listing_id" value="<?= $l['id'] ?>">
                        <button type="submit" name="toggle_listing" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer <?= $l['is_active'] ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                            <?= $l['is_active'] ? 'Active' : 'Inactive' ?>
                        </button>
                    </form>
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end space-x-2">
                        <a href="/admin/scholarship-listings?action=edit&id=<?= $l['id'] ?>" class="p-2 text-gray-400 hover:text-primary rounded-lg hover:bg-gray-100 transition-colors" title="Edit">
                            <i class="fas fa-pen text-sm"></i>
                        </a>
                        <form method="POST" data-confirm="Delete this scholarship listing?" class="inline">
                            <input type="hidden" name="listing_id" value="<?= $l['id'] ?>">
                            <button type="submit" name="delete_listing" class="p-2 text-gray-400 hover:text-red-500 rounded-lg hover:bg-gray-100 transition-colors" title="Delete">
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
