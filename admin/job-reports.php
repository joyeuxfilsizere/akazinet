<?php
/**
 * Review public reports on suspicious listings.
 *
 * Backs /pages/report-job. Every report is read by a human; confirmed scams get
 * the underlying job archived, which is the trust signal the page promises.
 */

require_once __DIR__ . '/../config/database.php';
require_admin_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!is_string($csrfToken) || !verify_csrf($csrfToken)) {
        set_flash('error', 'Your session expired. Please try again.');
        redirect('/admin/job-reports');
    }

    $reportId = (int)($_POST['report_id'] ?? 0);
    $action = (string)($_POST['report_action'] ?? '');
    $note = trim((string)($_POST['admin_note'] ?? ''));

    $validStatuses = ['new', 'reviewing', 'resolved', 'dismissed'];
    if ($note !== '') {
        $pdo->prepare("UPDATE job_reports SET admin_note = ? WHERE id = ?")->execute([$note, $reportId]);
    }

    if ($action === 'archive_job') {
        $jobId = (int)($_POST['job_id'] ?? 0);
        if ($jobId > 0) {
            $pdo->prepare("UPDATE jobs SET status = 'archived', is_active = 0 WHERE id = ?")->execute([$jobId]);
            $pdo->prepare("UPDATE job_reports SET status = 'resolved' WHERE id = ?")->execute([$reportId]);
            set_flash('success', 'Listing archived and report resolved.');
        } else {
            set_flash('error', 'That report is not linked to a listing.');
        }
        redirect('/admin/job-reports');
    }

    if (in_array($action, $validStatuses, true)) {
        $pdo->prepare("UPDATE job_reports SET status = ? WHERE id = ?")->execute([$action, $reportId]);
        set_flash('success', 'Report marked as ' . $action . '.');
        redirect('/admin/job-reports');
    }
}

$statusFilter = (string)($_GET['status'] ?? '');
$allowedFilters = ['new', 'reviewing', 'resolved', 'dismissed', ''];
if (!in_array($statusFilter, $allowedFilters, true)) {
    $statusFilter = '';
}

$sql = "
    SELECT r.*, j.title AS job_title, j.company_name, j.status AS job_status, j.is_active AS job_active
    FROM job_reports r
    LEFT JOIN jobs j ON j.id = r.job_id
";
$params = [];
if ($statusFilter !== '') {
    $sql .= " WHERE r.status = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY FIELD(r.status, 'new', 'reviewing', 'resolved', 'dismissed'), r.created_at DESC LIMIT 200";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();

$counts = [];
foreach (['new', 'reviewing', 'resolved', 'dismissed'] as $s) {
    $c = $pdo->prepare("SELECT COUNT(*) FROM job_reports WHERE status = ?");
    $c->execute([$s]);
    $counts[$s] = (int)$c->fetchColumn();
}
$counts['all'] = array_sum($counts);

$reportLabels = [
    'fake_job' => 'Fake job or does not exist',
    'scam_payment' => 'Asked me to pay money',
    'misleading' => 'Misleading description',
    'expired' => 'Vacancy closed or filled',
    'duplicate' => 'Duplicate listing',
    'inappropriate' => 'Discriminatory content',
    'wrong_info' => 'Incorrect information',
    'other' => 'Something else',
];

require_once __DIR__ . '/includes/header.php';
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Job Reports</h1>
        <p class="text-sm text-gray-500 mt-1">
            Suspicious listings reported from <a href="/pages/report-job" target="_blank" class="text-primary hover:underline">/pages/report-job</a>.
            <?= (int)$counts['new'] ?> awaiting first review.
        </p>
    </div>
</div>

<div class="flex flex-wrap gap-2 mb-6">
    <?php
    $tabs = ['' => 'All (' . $counts['all'] . ')'];
    foreach (['new' => 'New', 'reviewing' => 'Reviewing', 'resolved' => 'Resolved', 'dismissed' => 'Dismissed'] as $key => $label) {
        $tabs[$key] = $label . ' (' . $counts[$key] . ')';
    }
    foreach ($tabs as $key => $label):
    ?>
    <a href="/admin/job-reports<?= $key !== '' ? '?status=' . $key : '' ?>"
        class="px-4 py-2 text-sm rounded-lg transition-colors <?= $statusFilter === $key ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
        <?= sanitize($label) ?>
    </a>
    <?php endforeach; ?>
</div>

<?php if (!$reports): ?>
<div class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-500">
    <i class="fas fa-flag text-3xl text-gray-300 mb-3"></i>
    <p>No reports<?= $statusFilter !== '' ? ' with this status' : '' ?>.</p>
</div>
<?php endif; ?>

<?php foreach ($reports as $report): ?>
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-4">
    <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
        <div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full
                    <?= $report['status'] === 'new' ? 'bg-red-100 text-red-700' : ($report['status'] === 'reviewing' ? 'bg-yellow-100 text-yellow-700' : ($report['status'] === 'resolved' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600')) ?>">
                    <?= sanitize(ucfirst($report['status'])) ?>
                </span>
                <span class="text-sm font-semibold text-gray-900">
                    <?= sanitize($reportLabels[$report['report_type']] ?? $report['report_type']) ?>
                </span>
            </div>
            <p class="text-xs text-gray-500 mt-1">
                <?= date('M d, Y H:i', strtotime($report['created_at'])) ?>
                <?php if (!empty($report['ip_address'])): ?>
                    &middot; IP <?= sanitize($report['ip_address']) ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="text-right text-xs text-gray-500">
            <?php if (!empty($report['reporter_email'])): ?>
                <div><i class="fas fa-envelope mr-1"></i><?= sanitize($report['reporter_email']) ?></div>
            <?php endif; ?>
            <?php if (!empty($report['reporter_phone'])): ?>
                <div><i class="fas fa-phone mr-1"></i><?= sanitize($report['reporter_phone']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($report['job_title'])): ?>
    <div class="bg-gray-50 rounded-lg p-3 mb-3 text-sm">
        <div class="font-medium text-gray-800">
            <?= sanitize($report['job_title']) ?>
            <?php if (!empty($report['company_name'])): ?>
                <span class="text-gray-500">&middot; <?= sanitize($report['company_name']) ?></span>
            <?php endif; ?>
        </div>
        <?php if (!empty($report['job_slug'])): ?>
        <a href="/job-details?slug=<?= urlencode($report['job_slug']) ?>" target="_blank" class="text-primary text-xs hover:underline">
            open listing
        </a>
        <?php if ($report['job_status'] !== null): ?>
            <span class="text-xs text-gray-400 ml-2">current status: <?= sanitize((string)$report['job_status']) ?><?= (int)$report['job_active'] === 0 ? ' (inactive)' : '' ?></span>
        <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <p class="text-sm text-gray-700 leading-relaxed mb-4 whitespace-pre-line"><?= sanitize($report['details']) ?></p>

    <?php if (!empty($report['admin_note'])): ?>
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
        <p class="text-xs font-semibold text-blue-800 mb-1">Internal note</p>
        <p class="text-sm text-blue-900"><?= sanitize($report['admin_note']) ?></p>
    </div>
    <?php endif; ?>

    <form method="POST" class="flex flex-wrap items-end gap-3">
        <input type="hidden" name="csrf_token" value="<?= sanitize(csrf_token()) ?>">
        <input type="hidden" name="report_id" value="<?= (int)$report['id'] ?>">
        <?php if (!empty($report['job_id'])): ?>
        <input type="hidden" name="job_id" value="<?= (int)$report['job_id'] ?>">
        <?php endif; ?>

        <div class="flex-1 min-w-[220px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Internal note (not shown publicly)</label>
            <input type="text" name="admin_note" value="<?= sanitize($report['admin_note'] ?? '') ?>"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
        </div>

        <?php if ($report['status'] !== 'reviewing'): ?>
        <button type="submit" name="report_action" value="reviewing" class="px-4 py-2 text-sm bg-yellow-50 text-yellow-700 rounded-lg hover:bg-yellow-100 transition-colors">
            Mark reviewing
        </button>
        <?php endif; ?>

        <?php if (!empty($report['job_id'])): ?>
        <button type="submit" name="report_action" value="archive_job"
            onclick="return confirm('Archive this listing? It will be removed from public pages immediately.')"
            class="px-4 py-2 text-sm bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors">
            <i class="fas fa-ban mr-1"></i> Archive listing
        </button>
        <?php endif; ?>

        <button type="submit" name="report_action" value="dismissed" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
            Dismiss
        </button>

        <?php if ($report['status'] !== 'resolved'): ?>
        <button type="submit" name="report_action" value="resolved" class="px-4 py-2 text-sm bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition-colors">
            Resolve
        </button>
        <?php endif; ?>
    </form>
</div>
<?php endforeach; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
