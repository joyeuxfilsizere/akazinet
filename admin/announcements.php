<?php
require_once __DIR__ . '/../config/database.php';
require_admin_login();

/**
 * The announcements table is defined in database/migration15.sql. Create it on
 * first use so this page fails gracefully instead of fatal-erroring when the
 * migration has not been run against the live database yet.
 */
function ensure_announcements_table() {
    global $pdo;

    try {
        $pdo->query('SELECT 1 FROM announcements LIMIT 1');
        return true;
    } catch (PDOException $exception) {
        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS announcements (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(200) NOT NULL,
                    body TEXT NOT NULL,
                    announcement_type ENUM('update','news','communication') NOT NULL DEFAULT 'update',
                    link_url VARCHAR(500) DEFAULT NULL,
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    sort_order INT NOT NULL DEFAULT 0,
                    published_at DATETIME DEFAULT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_announcements_public (is_active, published_at, sort_order, created_at)
                ) ENGINE=InnoDB
            ");
            return true;
        } catch (PDOException $createException) {
            return false;
        }
    }
}

$announcementsTableReady = ensure_announcements_table();

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!is_string($csrfToken) || !verify_csrf($csrfToken)) {
        set_flash('error', 'Your session has expired. Please try again.');
        redirect('/admin/announcements');
    }

    if (!$announcementsTableReady) {
        set_flash('error', 'The announcements table is missing and could not be created automatically. Please run database/migration15.sql in phpMyAdmin.');
        redirect('/admin/announcements');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_announcement'])) {
        $announcementId = (int)($_POST['announcement_id'] ?? 0);
        $pdo->prepare("DELETE FROM announcements WHERE id = ?")->execute([$announcementId]);
        set_flash('success', 'Announcement deleted successfully.');
        redirect('/admin/announcements');
    }

    if (isset($_POST['toggle_announcement'])) {
        $announcementId = (int)($_POST['announcement_id'] ?? 0);
        $pdo->prepare("UPDATE announcements SET is_active = NOT is_active WHERE id = ?")->execute([$announcementId]);
        set_flash('success', 'Announcement status updated.');
        redirect('/admin/announcements');
    }

    if (isset($_POST['save_announcement'])) {
        $title = trim($_POST['title'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $type = $_POST['announcement_type'] ?? 'update';
        $type = in_array($type, ['update', 'news', 'communication'], true) ? $type : 'update';
        $linkUrl = trim($_POST['link_url'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $publishedAtInput = trim($_POST['published_at'] ?? '');
        $publishedAt = null;
        $editId = (int)($_POST['edit_id'] ?? 0);

        if ($publishedAtInput !== '') {
            $publishedTimestamp = strtotime($publishedAtInput);
            if ($publishedTimestamp === false) {
                set_flash('error', 'Please enter a valid publication date and time.');
                redirect('/admin/announcements?action=' . ($editId ? "edit&id={$editId}" : 'add'));
            }
            $publishedAt = date('Y-m-d H:i:s', $publishedTimestamp);
        }

        if ($linkUrl !== '' && !preg_match('~^(https?://|mailto:|/)~i', $linkUrl)) {
            set_flash('error', 'The link must start with https://, http://, mailto:, or /.');
            redirect('/admin/announcements?action=' . ($editId ? "edit&id={$editId}" : 'add'));
        }

        if ($title === '' || $body === '') {
            set_flash('error', 'Title and message are required.');
            redirect('/admin/announcements?action=' . ($editId ? "edit&id={$editId}" : 'add'));
        }

        if ($editId) {
            $stmt = $pdo->prepare("
                UPDATE announcements
                SET title = ?, body = ?, announcement_type = ?, link_url = ?, is_active = ?, sort_order = ?, published_at = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$title, $body, $type, $linkUrl ?: null, $isActive, $sortOrder, $publishedAt, $editId]);
            set_flash('success', 'Announcement updated successfully.');
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO announcements (title, body, announcement_type, link_url, is_active, sort_order, published_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $body, $type, $linkUrl ?: null, $isActive, $sortOrder, $publishedAt]);
            set_flash('success', 'Announcement created successfully.');
        }

        redirect('/admin/announcements');
    }
}

require_once __DIR__ . '/includes/header.php';

$announcement = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM announcements WHERE id = ?");
    $stmt->execute([$id]);
    $announcement = $stmt->fetch();
    if (!$announcement) {
        redirect('/admin/announcements');
    }
}

$typeOptions = [
    'update' => 'Update',
    'news' => 'News',
    'communication' => 'Communication',
];

$typeStyles = [
    'update' => 'bg-accent/10 text-accent',
    'news' => 'bg-blue-100 text-blue-700',
    'communication' => 'bg-green-100 text-green-700',
];

if ($action === 'add' || $action === 'edit'):
    $publishedAtValue = '';
    if (!empty($announcement['published_at'])) {
        $publishedAtValue = date('Y-m-d\TH:i', strtotime($announcement['published_at']));
    }
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <a href="/admin/announcements" class="text-sm text-gray-500 hover:text-primary mb-1 inline-flex items-center">
            <i class="fas fa-arrow-left mr-1"></i> Back to Updates &amp; News
        </a>
        <h1 class="text-2xl font-bold text-gray-900"><?= $announcement ? 'Edit Announcement' : 'Add Announcement' ?></h1>
    </div>
</div>

<form method="POST" class="space-y-6">
    <input type="hidden" name="csrf_token" value="<?= sanitize(csrf_token()) ?>">
    <input type="hidden" name="save_announcement" value="1">
    <input type="hidden" name="edit_id" value="<?= (int)($announcement['id'] ?? 0) ?>">

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><i class="fas fa-bullhorn mr-2 text-primary"></i>Announcement Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="<?= sanitize($announcement['title'] ?? '') ?>" required maxlength="200" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm" placeholder="e.g. AkaziNet launches new job alerts">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Message <span class="text-red-500">*</span></label>
                <textarea name="body" rows="7" required maxlength="10000" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm resize-y" placeholder="Write the update, news, or communication you want visitors to see."><?= sanitize($announcement['body'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="announcement_type" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm bg-white">
                    <?php foreach ($typeOptions as $type => $label): ?>
                    <option value="<?= $type ?>" <?= ($announcement['announcement_type'] ?? 'update') === $type ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Link URL <span class="text-gray-400 text-xs">(optional)</span></label>
                <input type="text" name="link_url" value="<?= sanitize($announcement['link_url'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm" placeholder="https://example.com/news">
                <p class="text-xs text-gray-400 mt-1">Use an absolute URL, an internal path, or a mailto link.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Publish Date &amp; Time <span class="text-gray-400 text-xs">(optional)</span></label>
                <input type="datetime-local" name="published_at" value="<?= $publishedAtValue ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
                <p class="text-xs text-gray-400 mt-1">Leave blank to publish immediately.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="<?= (int)($announcement['sort_order'] ?? 0) ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
                <p class="text-xs text-gray-400 mt-1">Lower numbers appear first.</p>
            </div>
        </div>
        <label class="flex items-center space-x-2 cursor-pointer mt-5">
            <input type="checkbox" name="is_active" value="1" <?= ($announcement['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 text-primary rounded border-gray-300 focus:ring-primary">
            <span class="text-sm font-medium text-gray-700">Active</span>
        </label>
    </div>

    <div class="flex items-center space-x-3">
        <button type="submit" class="px-6 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors">
            <i class="fas fa-save mr-1"></i> <?= $announcement ? 'Update Announcement' : 'Publish Announcement' ?>
        </button>
        <a href="/admin/announcements" class="px-6 py-2.5 border border-gray-200 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">Cancel</a>
    </div>
</form>

<?php else:
    $announcements = [];
    try {
        $announcements = $pdo->query("
            SELECT *
            FROM announcements
            ORDER BY sort_order ASC, COALESCE(published_at, created_at) DESC, id DESC
        ")->fetchAll();
    } catch (PDOException $exception) {
        $announcements = [];
    }
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Updates &amp; News</h1>
        <p class="text-gray-500 text-sm">Publish updates, news, and communications in the website sidebar.</p>
    </div>
    <a href="/admin/announcements?action=add" class="px-4 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors">
        <i class="fas fa-plus mr-1"></i> Add Announcement
    </a>
</div>

<?php if (empty($announcements)): ?>
<div class="bg-white rounded-xl border border-gray-200 p-12 text-center shadow-sm">
    <div class="w-16 h-16 mx-auto mb-4 bg-primary/10 rounded-full flex items-center justify-center">
        <i class="fas fa-bullhorn text-primary text-2xl"></i>
    </div>
    <h3 class="text-lg font-semibold text-gray-900 mb-2">No announcements yet</h3>
    <p class="text-gray-500 mb-4">Create your first update to keep visitors informed.</p>
    <a href="/admin/announcements?action=add" class="inline-flex items-center px-5 py-2.5 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark transition-colors">
        <i class="fas fa-plus mr-1"></i> Add Announcement
    </a>
</div>
<?php else: ?>
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="px-5 py-3 font-medium text-gray-600">Announcement</th>
                    <th class="px-5 py-3 font-medium text-gray-600">Type</th>
                    <th class="px-5 py-3 font-medium text-gray-600">Published</th>
                    <th class="px-5 py-3 font-medium text-gray-600">Order</th>
                    <th class="px-5 py-3 font-medium text-gray-600">Status</th>
                    <th class="px-5 py-3 font-medium text-gray-600 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($announcements as $item): ?>
                <?php
                $itemType = $typeOptions[$item['announcement_type']] ?? 'Update';
                $itemTypeStyle = $typeStyles[$item['announcement_type']] ?? $typeStyles['update'];
                $itemDate = !empty($item['published_at']) ? strtotime($item['published_at']) : strtotime($item['created_at']);
                $itemDate = $itemDate ? date('M d, Y H:i', $itemDate) : 'Not scheduled';
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <div class="font-medium text-gray-800"><?= sanitize($item['title']) ?></div>
                        <div class="text-xs text-gray-500 mt-1 line-clamp-2"><?= sanitize($item['body']) ?></div>
                    </td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium <?= $itemTypeStyle ?>"><?= sanitize($itemType) ?></span>
                    </td>
                    <td class="px-5 py-3 text-gray-500"><?= sanitize($itemDate) ?></td>
                    <td class="px-5 py-3 text-gray-500"><?= (int)$item['sort_order'] ?></td>
                    <td class="px-5 py-3">
                        <form method="POST" class="inline">
                            <input type="hidden" name="csrf_token" value="<?= sanitize(csrf_token()) ?>">
                            <input type="hidden" name="announcement_id" value="<?= (int)$item['id'] ?>">
                            <button type="submit" name="toggle_announcement" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer <?= $item['is_active'] ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                                <?= $item['is_active'] ? 'Active' : 'Inactive' ?>
                            </button>
                        </form>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="/admin/announcements?action=edit&id=<?= (int)$item['id'] ?>" class="px-3 py-1.5 text-xs bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this announcement?')">
                                <input type="hidden" name="csrf_token" value="<?= sanitize(csrf_token()) ?>">
                                <input type="hidden" name="announcement_id" value="<?= (int)$item['id'] ?>">
                                <button type="submit" name="delete_announcement" class="px-3 py-1.5 text-xs bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
