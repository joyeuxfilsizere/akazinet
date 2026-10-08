<?php
require_once __DIR__ . '/includes/header.php';

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_company'])) {
        $pdo->prepare("UPDATE companies SET is_active = 0, deleted_at = NOW() WHERE id = ?")->execute([(int)$_POST['company_id']]);
        set_flash('success', 'Company deleted successfully.');
        redirect('/admin/companies');
    }

    if (isset($_POST['save_company'])) {
        $name = sanitize($_POST['name']);
        $description = $_POST['description'] ?? '';
        $website = sanitize($_POST['website'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $location = sanitize($_POST['location'] ?? '');
        $province = sanitize($_POST['province'] ?? '');
        $district = sanitize($_POST['district'] ?? '');
        $editId = (int)($_POST['edit_id'] ?? 0);

        if (empty($name)) {
            set_flash('error', 'Company name is required.');
            redirect('/admin/companies?action=' . ($editId ? "edit&id=$editId" : 'add'));
        }

        $slug = generate_slug($name);
        if ($editId) {
            $existingSlug = $pdo->prepare("SELECT slug FROM companies WHERE id = ?");
            $existingSlug->execute([$editId]);
            $slug = $existingSlug->fetch()['slug'];
        } else {
            $checkSlug = $pdo->prepare("SELECT COUNT(*) FROM companies WHERE slug = ?");
            $checkSlug->execute([$slug]);
            if ($checkSlug->fetchColumn() > 0) $slug .= '-' . time();
        }

        $logoFilename = null;
        if (!empty($_FILES['logo']['name'])) {
            $upload = upload_image($_FILES['logo']);
            if ($upload['success']) {
                $logoFilename = $upload['filename'];
            }
        }

        if ($editId) {
            $sql = "UPDATE companies SET name=?, slug=?, description=?, website=?, email=?, phone=?, location=?, province=?, district=?";
            $params = [$name, $slug, $description, $website, $email, $phone, $location, $province, $district];
            if ($logoFilename) {
                $sql .= ", logo=?";
                $params[] = $logoFilename;
            }
            $sql .= " WHERE id=?";
            $params[] = $editId;
            $pdo->prepare($sql)->execute($params);
            set_flash('success', 'Company updated successfully.');
        } else {
            $pdo->prepare("INSERT INTO companies (name, slug, description, logo, website, email, phone, location, province, district) VALUES (?,?,?,?,?,?,?,?,?,?)")->execute([$name, $slug, $description, $logoFilename, $website, $email, $phone, $location, $province, $district]);
            set_flash('success', 'Company created successfully.');
        }
        redirect('/admin/companies');
    }
}

$editCompany = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
    $stmt->execute([$id]);
    $editCompany = $stmt->fetch();
    if (!$editCompany) redirect('/admin/companies');
}

if ($action === 'list'):
    $allCompanies = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM jobs j WHERE j.company_id = c.id AND j.status = 'published' AND j.is_active = 1) as open_positions FROM companies c WHERE c.is_active = 1 ORDER BY c.name")->fetchAll();
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Companies</h1>
        <p class="text-gray-500 text-sm"><?= count($allCompanies) ?> companies</p>
    </div>
    <a href="/admin/companies?action=add" class="px-4 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors">
        <i class="fas fa-plus mr-1"></i> Add Company
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="px-5 py-3 font-medium text-gray-600">Company</th>
                    <th class="px-5 py-3 font-medium text-gray-600">Location</th>
                    <th class="px-5 py-3 font-medium text-gray-600">Jobs</th>
                    <th class="px-5 py-3 font-medium text-gray-600 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($allCompanies as $c): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-lg overflow-hidden bg-gray-100 border border-gray-200 flex items-center justify-center flex-shrink-0">
                                <?php if ($c['logo']): ?>
                                <img src="/assets/uploads/<?= $c['logo'] ?>" alt="" class="w-full h-full object-contain p-0.5">
                                <?php else: ?>
                                <span class="text-xs font-bold text-primary"><?= strtoupper(substr($c['name'], 0, 2)) ?></span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="font-medium text-gray-800"><?= sanitize($c['name']) ?></div>
                                <?php if ($c['website']): ?>
                                <div class="text-xs text-gray-400 truncate max-w-[200px]"><?= sanitize($c['website']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-gray-600"><?= sanitize($c['location'] ?? 'N/A') ?></td>
                    <td class="px-5 py-3 text-gray-600"><?= $c['open_positions'] ?></td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="/admin/companies?action=edit&id=<?= $c['id'] ?>" class="px-3 py-1.5 text-xs bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors"><i class="fas fa-edit"></i></a>
                            <form method="POST" onsubmit="return confirm('Delete this company?')">
                                <input type="hidden" name="delete_company" value="1">
                                <input type="hidden" name="company_id" value="<?= $c['id'] ?>">
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

<?php elseif ($action === 'add' || $action === 'edit'): ?>
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900"><?= $editCompany ? 'Edit Company' : 'Add Company' ?></h1>
    <a href="/admin/companies" class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"><i class="fas fa-arrow-left mr-1"></i> Back</a>
</div>

<form method="POST" enctype="multipart/form-data" class="space-y-6">
    <input type="hidden" name="save_company" value="1">
    <?php if ($editCompany): ?>
    <input type="hidden" name="edit_id" value="<?= $editCompany['id'] ?>">
    <?php endif; ?>

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Company Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Company Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="<?= sanitize($editCompany['name'] ?? '') ?>" required class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="4" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm resize-none"><?= sanitize($editCompany['description'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                <input type="url" name="website" value="<?= sanitize($editCompany['website'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm" placeholder="https://...">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                <input type="file" name="logo" accept="image/*" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-primary file:text-white file:text-sm file:cursor-pointer">
                <?php if ($editCompany && $editCompany['logo']): ?>
                <div class="mt-2"><img src="/assets/uploads/<?= $editCompany['logo'] ?>" alt="" class="w-12 h-12 object-contain rounded border"></div>
                <?php endif; ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="<?= sanitize($editCompany['email'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="text" name="phone" value="<?= sanitize($editCompany['phone'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                <select name="province" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm bg-white">
                    <option value="">Select</option>
                    <?php foreach (['Kigali','Northern','Southern','Eastern','Western'] as $p): ?>
                    <option value="<?= $p ?>" <?= ($editCompany['province'] ?? '') === $p ? 'selected' : '' ?>><?= $p ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">District</label>
                <input type="text" name="district" value="<?= sanitize($editCompany['district'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <input type="text" name="location" value="<?= sanitize($editCompany['location'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
        </div>
    </div>

    <div class="flex items-center space-x-3">
        <button type="submit" class="px-6 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors"><i class="fas fa-save mr-1"></i> <?= $editCompany ? 'Update' : 'Create' ?></button>
        <a href="/admin/companies" class="px-6 py-2.5 border border-gray-200 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">Cancel</a>
    </div>
</form>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
