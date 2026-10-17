<?php
require_once __DIR__ . '/../config/database.php';
require_admin_login();

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_job'])) {
        $pdo->prepare("UPDATE jobs SET is_active = 0, deleted_at = NOW() WHERE id = ?")->execute([(int)$_POST['job_id']]);
        set_flash('success', 'Job deleted successfully.');
        redirect('/admin/jobs');
    }

    if (isset($_POST['save_job'])) {
        $companyName = trim($_POST['company_name']);
        $categoryId = (int)$_POST['category_id'];
        $title = trim($_POST['title']);
        $description = $_POST['description'];
        $responsibilities = $_POST['responsibilities'] ?? '';
        $requirements = $_POST['requirements'] ?? '';
        $qualifications = $_POST['qualifications'] ?? '';
        $benefits = $_POST['benefits'] ?? '';
        $howToApply = $_POST['how_to_apply'] ?? '';
        $applicationUrl = trim($_POST['application_url'] ?? '');
        $applicationEmail = trim($_POST['application_email'] ?? '');
        $province = trim($_POST['province'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $employmentType = trim($_POST['employment_type'] ?? 'Full-time');
        $experienceLevel = trim($_POST['experience_level'] ?? '');
        $educationLevel = trim($_POST['education_level'] ?? '');
        $deadline = $_POST['application_deadline'] ?: null;
        if ($deadline) {
            $deadline = date('Y-m-d H:i:s', strtotime($deadline));
        }
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $isRemote = isset($_POST['is_remote']) ? 1 : 0;
        $remoteEnabled = akazinet_jobs_remote_enabled();
        $status = $_POST['status'] ?? 'draft';
        $editId = (int)($_POST['edit_id'] ?? 0);

        if (empty($title) || empty($companyName) || empty($categoryId) || empty($description)) {
            set_flash('error', 'Please fill in all required fields.');
            redirect('/admin/jobs?action=' . ($editId ? "edit&id=$editId" : 'add'));
        }

        $slug = generate_slug($title);
        if ($editId) {
            $existingSlug = $pdo->prepare("SELECT slug FROM jobs WHERE id = ?");
            $existingSlug->execute([$editId]);
            $slug = $existingSlug->fetch()['slug'];
        } else {
            $checkSlug = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE slug = ?");
            $checkSlug->execute([$slug]);
            if ($checkSlug->fetchColumn() > 0) {
                $slug .= '-' . time();
            }
        }

        $companyLogo = null;
        if (!empty($_FILES['company_logo']['name'])) {
            $upload = upload_image($_FILES['company_logo']);
            if ($upload['success']) {
                $companyLogo = $upload['filename'];
            }
        }

        $supportingDocument = null;
        if (!empty($_FILES['supporting_document']['name'])) {
            $docUpload = upload_document($_FILES['supporting_document']);
            if ($docUpload['success']) {
                $supportingDocument = $docUpload['filename'];
            } else {
                set_flash('error', $docUpload['message']);
                redirect('/admin/jobs?action=' . ($editId ? "edit&id=$editId" : 'add'));
            }
        }

        if ($editId) {
            $sql = "UPDATE jobs SET company_name=?, category_id=?, title=?, slug=?, description=?, responsibilities=?, requirements=?, qualifications=?, benefits=?, how_to_apply=?, application_url=?, application_email=?, province=?, district=?, location=?, employment_type=?, experience_level=?, education_level=?, application_deadline=?, is_featured=?, status=?";
            $params = [$companyName, $categoryId, $title, $slug, $description, $responsibilities, $requirements, $qualifications, $benefits, $howToApply, $applicationUrl, $applicationEmail, $province, $district, $location, $employmentType, $experienceLevel, $educationLevel, $deadline, $isFeatured, $status];
            if ($remoteEnabled) {
                $sql .= ", is_remote=?";
                $params[] = $isRemote;
            }
            if ($companyLogo) {
                $sql .= ", company_logo=?";
                $params[] = $companyLogo;
            }
            if ($supportingDocument) {
                $sql .= ", supporting_document=?";
                $params[] = $supportingDocument;
            }
            $sql .= ", updated_at=NOW() WHERE id=?";
            $params[] = $editId;
            $pdo->prepare($sql)->execute($params);
            set_flash('success', 'Job updated successfully.');
        } else {
            $columns = "company_name, company_logo, category_id, title, slug, description, responsibilities, requirements, qualifications, benefits, how_to_apply, application_url, application_email, supporting_document, province, district, location, employment_type, experience_level, education_level, application_deadline, is_featured, status";
            $placeholders = "?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?";
            $values = [$companyName, $companyLogo, $categoryId, $title, $slug, $description, $responsibilities, $requirements, $qualifications, $benefits, $howToApply, $applicationUrl, $applicationEmail, $supportingDocument, $province, $district, $location, $employmentType, $experienceLevel, $educationLevel, $deadline, $isFeatured, $status];
            if ($remoteEnabled) {
                $columns .= ", is_remote";
                $placeholders .= ",?";
                $values[] = $isRemote;
            }
            $stmt = $pdo->prepare("INSERT INTO jobs ({$columns}) VALUES ({$placeholders})");
            $stmt->execute($values);
            set_flash('success', 'Job created successfully.');
        }
        redirect('/admin/jobs');
    }
}

require_once __DIR__ . '/includes/header.php';

$categories = $pdo->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name")->fetchAll();
$remoteEnabled = akazinet_jobs_remote_enabled();

$editJob = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ?");
    $stmt->execute([$id]);
    $editJob = $stmt->fetch();
    if (!$editJob) redirect('/admin/jobs');
}

if ($action === 'list'):
    $allJobs = $pdo->query("
        SELECT j.*, cat.name as category_name
        FROM jobs j
        JOIN categories cat ON j.category_id = cat.id
        WHERE j.is_active = 1
        ORDER BY (j.application_deadline IS NOT NULL AND j.application_deadline < NOW()) ASC, j.created_at DESC
    ")->fetchAll();
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Manage Jobs</h1>
        <p class="text-gray-500 text-sm"><?= count($allJobs) ?> total jobs</p>
    </div>
    <a href="/admin/jobs?action=add" class="px-4 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors">
        <i class="fas fa-plus mr-1"></i> Add Job
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="px-5 py-3 font-medium text-gray-600">Job Title</th>
                    <th class="px-5 py-3 font-medium text-gray-600">Company</th>
                    <th class="px-5 py-3 font-medium text-gray-600">Category</th>
                    <th class="px-5 py-3 font-medium text-gray-600">Status</th>
                    <th class="px-5 py-3 font-medium text-gray-600">Views</th>
                    <th class="px-5 py-3 font-medium text-gray-600">Deadline</th>
                    <th class="px-5 py-3 font-medium text-gray-600 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($allJobs as $j): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <div class="font-medium text-gray-800"><?= sanitize($j['title']) ?></div>
                        <?php if ($j['is_featured']): ?>
                        <span class="text-[10px] text-accent font-medium"><i class="fas fa-star mr-0.5"></i> Featured</span>
                        <?php endif; ?>
                        <?php if (!empty($j['is_remote'])): ?>
                        <span class="text-[10px] text-blue-600 font-medium ml-2"><i class="fas fa-laptop-house mr-0.5"></i> Remote</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3 text-gray-600"><?= sanitize($j['company_name']) ?></td>
                    <td class="px-5 py-3 text-gray-600"><?= sanitize($j['category_name']) ?></td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 text-[10px] font-medium rounded-full <?= $j['status'] === 'published' ? 'bg-green-100 text-green-700' : ($j['status'] === 'draft' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600') ?>">
                            <?= ucfirst($j['status']) ?>
                        </span>
                    </td>
                    <td class="px-5 py-3 text-gray-500"><?= (int)$j['views'] ?></td>
                    <td class="px-5 py-3 text-gray-500"><?= $j['application_deadline'] ? format_deadline($j['application_deadline']) : 'N/A' ?></td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="/admin/jobs?action=edit&id=<?= $j['id'] ?>" class="px-3 py-1.5 text-xs bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this job?')">
                                <input type="hidden" name="delete_job" value="1">
                                <input type="hidden" name="job_id" value="<?= $j['id'] ?>">
                                <button type="submit" class="px-3 py-1.5 text-xs bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors">
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

<?php elseif ($action === 'add' || $action === 'edit'): ?>
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900"><?= $editJob ? 'Edit Job' : 'Add New Job' ?></h1>
    <a href="/admin/jobs" class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
</div>

<form method="POST" enctype="multipart/form-data" class="space-y-6">
    <input type="hidden" name="save_job" value="1">
    <?php if ($editJob): ?>
    <input type="hidden" name="edit_id" value="<?= $editJob['id'] ?>">
    <?php endif; ?>

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><i class="fas fa-building mr-2 text-primary"></i>Company & Category</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Company <span class="text-red-500">*</span></label>
                <input type="text" name="company_name" value="<?= sanitize($editJob['company_name'] ?? '') ?>" required class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm" placeholder="e.g. Bank of Kigali">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Company Logo</label>
                <input type="file" name="company_logo" accept="image/*" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-primary file:text-white file:text-sm file:cursor-pointer">
                <?php if ($editJob && $editJob['company_logo']): ?>
                <div class="mt-2 flex items-center gap-2">
                    <img src="/assets/uploads/<?= $editJob['company_logo'] ?>" alt="" class="w-10 h-10 object-contain rounded border">
                    <span class="text-xs text-gray-400">Current logo</span>
                </div>
                <?php endif; ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                <select name="category_id" required class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm bg-white">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($editJob['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= sanitize($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                <select name="status" required class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm bg-white">
                    <option value="draft" <?= ($editJob['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= ($editJob['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="archived" <?= ($editJob['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><i class="fas fa-file-alt mr-2 text-primary"></i>Job Details</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Job Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="<?= sanitize($editJob['title'] ?? '') ?>" required class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                <div class="quill-editor" data-quill="description"></div>
                <textarea name="description" id="description" required hidden></textarea>
                <p class="text-xs text-gray-400 mt-1">Use the toolbar to format with bullets, bold, italic, and links.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Responsibilities</label>
                <div class="quill-editor" data-quill="responsibilities"></div>
                <textarea name="responsibilities" id="responsibilities" hidden></textarea>
                <p class="text-xs text-gray-400 mt-1">Use the toolbar to format with bullets, bold, italic, and links.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Requirements</label>
                <div class="quill-editor" data-quill="requirements"></div>
                <textarea name="requirements" id="requirements" hidden></textarea>
                <p class="text-xs text-gray-400 mt-1">Use the toolbar to format with bullets, bold, italic, and links.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Qualifications</label>
                <div class="quill-editor" data-quill="qualifications"></div>
                <textarea name="qualifications" id="qualifications" hidden></textarea>
                <p class="text-xs text-gray-400 mt-1">Use the toolbar to format with bullets, bold, italic, and links.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Benefits</label>
                <div class="quill-editor" data-quill="benefits"></div>
                <textarea name="benefits" id="benefits" hidden></textarea>
                <p class="text-xs text-gray-400 mt-1">Use the toolbar to format with bullets, bold, italic, and links.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">How to Apply</label>
                <div class="quill-editor" data-quill="how_to_apply"></div>
                <textarea name="how_to_apply" id="how_to_apply" hidden></textarea>
                <p class="text-xs text-gray-400 mt-1">Use the toolbar to format with bullets, bold, italic, and links.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Application URL <span class="text-gray-400 text-xs">(Direct link to apply)</span></label>
                <input type="url" name="application_url" value="<?= sanitize($editJob['application_url'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm" placeholder="https://example.com/apply">
                <p class="text-xs text-gray-400 mt-1">If provided, "Apply Now" button opens this link directly.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Application Email <span class="text-gray-400 text-xs">(If no URL above)</span></label>
                <input type="email" name="application_email" value="<?= sanitize($editJob['application_email'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm" placeholder="hr@company.com">
                <p class="text-xs text-gray-400 mt-1">If no URL, clicking "Apply Now" opens email to this address.</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><i class="fas fa-paperclip mr-2 text-primary"></i>Supporting Document</h2>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Advertisement / Source Document</label>
            <input type="file" name="supporting_document" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.webp" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-primary file:text-white file:text-sm file:cursor-pointer">
            <p class="text-xs text-gray-400 mt-1">Optional. Upload the original advertisement or source document (PDF, Word, or image). Max 10MB.</p>
            <?php if ($editJob && $editJob['supporting_document']): ?>
            <div class="mt-2 flex items-center gap-2">
                <a href="/assets/uploads/<?= sanitize($editJob['supporting_document']) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-3 py-1.5 text-xs bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors">
                    <i class="fas fa-file mr-1"></i> View current document
                </a>
                <span class="text-xs text-gray-400">Upload a new file to replace it.</span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><i class="fas fa-map-marker-alt mr-2 text-primary"></i>Location & Type</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                <select name="province" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm bg-white">
                    <option value="">Select Province</option>
                    <option value="Kigali" <?= ($editJob['province'] ?? '') === 'Kigali' ? 'selected' : '' ?>>Kigali</option>
                    <option value="Northern" <?= ($editJob['province'] ?? '') === 'Northern' ? 'selected' : '' ?>>Northern</option>
                    <option value="Southern" <?= ($editJob['province'] ?? '') === 'Southern' ? 'selected' : '' ?>>Southern</option>
                    <option value="Eastern" <?= ($editJob['province'] ?? '') === 'Eastern' ? 'selected' : '' ?>>Eastern</option>
                    <option value="Western" <?= ($editJob['province'] ?? '') === 'Western' ? 'selected' : '' ?>>Western</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">District</label>
                <input type="text" name="district" value="<?= sanitize($editJob['district'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location/Sector</label>
                <input type="text" name="location" value="<?= sanitize($editJob['location'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Employment Type</label>
                <select name="employment_type" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm bg-white">
                    <?php foreach (['Full-time','Part-time','Contract','Internship','Temporary','Freelance'] as $type): ?>
                    <option value="<?= $type ?>" <?= ($editJob['employment_type'] ?? 'Full-time') === $type ? 'selected' : '' ?>><?= $type ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Experience Level</label>
                <input type="text" name="experience_level" value="<?= sanitize($editJob['experience_level'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm" placeholder="e.g. 3+ years">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Education Level</label>
                <input type="text" name="education_level" value="<?= sanitize($editJob['education_level'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm" placeholder="e.g. Bachelor's Degree">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><i class="fas fa-calendar mr-2 text-primary"></i>Deadline & Options</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Application Deadline Date & Time <span class="text-gray-400 text-xs">(Your local time)</span></label>
                <input type="datetime-local" name="application_deadline" value="<?= isset($editJob['application_deadline']) && $editJob['application_deadline'] ? date('Y-m-d\TH:i', strtotime($editJob['application_deadline'])) : '' ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
                <p class="text-xs text-gray-400 mt-1">Set the closing date and time for applications.</p>
            </div>
            <div class="flex items-center">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="is_featured" value="1" <?= ($editJob['is_featured'] ?? 0) ? 'checked' : '' ?> class="w-4 h-4 text-primary rounded border-gray-300 focus:ring-primary">
                    <span class="text-sm font-medium text-gray-700">Featured Job</span>
                </label>
            </div>
            <?php if ($remoteEnabled): ?>
            <div class="flex items-center">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="is_remote" value="1" <?= ($editJob['is_remote'] ?? 0) ? 'checked' : '' ?> class="w-4 h-4 text-primary rounded border-gray-300 focus:ring-primary">
                    <span class="text-sm font-medium text-gray-700">Remote Job</span>
                </label>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex items-center space-x-3">
        <button type="submit" class="px-6 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors">
            <i class="fas fa-save mr-1"></i> <?= $editJob ? 'Update Job' : 'Create Job' ?>
        </button>
        <a href="/admin/jobs" class="px-6 py-2.5 border border-gray-200 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">Cancel</a>
    </div>
</form>

<?php
$quillInitial = [];
foreach (['description', 'responsibilities', 'requirements', 'qualifications', 'benefits', 'how_to_apply'] as $quillField) {
    $quillInitial[$quillField] = $editJob[$quillField] ?? '';
}
?>

<script>
var editorHtml = <?= json_encode($quillInitial, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
document.querySelectorAll('[data-quill]').forEach(function (el) {
    var id = el.getAttribute('data-quill');
    var hidden = document.getElementById(id);
    var quill = new Quill(el, {
        theme: 'snow',
        placeholder: 'Start writing...',
        modules: {
            toolbar: [
                [{ 'header': [2, 3, 4, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ['link']
            ]
        }
    });
    if (hidden && typeof editorHtml[id] === 'string' && editorHtml[id].trim() !== '') {
        quill.clipboard.dangerouslyPasteHTML(editorHtml[id]);
        hidden.value = quill.root.innerHTML;
    }
    if (hidden) {
        quill.on('text-change', function () {
            var html = quill.getText().trim() === '' ? '' : quill.root.innerHTML;
            hidden.value = html;
        });
    }
});
</script>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
