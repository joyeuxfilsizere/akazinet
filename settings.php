<?php
require_once __DIR__ . '/../config/database.php';
require_admin_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['site_name', 'site_tagline', 'site_email', 'site_phone', 'whatsapp_number', 'whatsapp_group_link', 'site_description', 'facebook_url', 'twitter_url', 'linkedin_url', 'instagram_url', 'footer_text'];
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $val = $_POST[$field];
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$val, $field]);
        }
    }

    if (isset($_POST['ad_code'])) {
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'ad_code'");
        $stmt->execute([$_POST['ad_code']]);
    }

    set_flash('success', 'Settings saved successfully.');
    redirect('/admin/settings');
}

require_once __DIR__ . '/includes/header.php';

$settings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
foreach ($stmt->fetchAll() as $s) {
    $settings[$s['setting_key']] = $s['setting_value'];
}
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
    <p class="text-gray-500 text-sm">Manage platform configuration</p>
</div>

<form method="POST" class="space-y-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><i class="fas fa-globe mr-2 text-primary"></i>General</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Site Name</label>
                <input type="text" name="site_name" value="<?= sanitize($settings['site_name'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tagline</label>
                <input type="text" name="site_tagline" value="<?= sanitize($settings['site_tagline'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="site_email" value="<?= sanitize($settings['site_email'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="text" name="site_phone" value="<?= sanitize($settings['site_phone'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp Number (with country code)</label>
                <input type="text" name="whatsapp_number" value="<?= sanitize($settings['whatsapp_number'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp Group Link</label>
                <input type="url" name="whatsapp_group_link" value="<?= sanitize($settings['whatsapp_group_link'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm" placeholder="https://chat.whatsapp.com/...">
                <p class="text-xs text-gray-400 mt-1">Displayed in the footer for visitors to join your WhatsApp group.</p>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Site Description</label>
                <textarea name="site_description" rows="3" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm resize-none"><?= sanitize($settings['site_description'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><i class="fas fa-share-alt mr-2 text-primary"></i>Social Media</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Facebook URL</label>
                <input type="url" name="facebook_url" value="<?= sanitize($settings['facebook_url'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Twitter URL</label>
                <input type="url" name="twitter_url" value="<?= sanitize($settings['twitter_url'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">LinkedIn URL</label>
                <input type="url" name="linkedin_url" value="<?= sanitize($settings['linkedin_url'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Instagram URL</label>
                <input type="url" name="instagram_url" value="<?= sanitize($settings['instagram_url'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><i class="fas fa-file-alt mr-2 text-primary"></i>Footer</h2>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Footer Text</label>
            <textarea name="footer_text" rows="2" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm resize-none"><?= sanitize($settings['footer_text'] ?? '') ?></textarea>
        </div>
    </div>

    <button type="submit" class="px-6 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors">
        <i class="fas fa-save mr-1"></i> Save Settings
    </button>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
