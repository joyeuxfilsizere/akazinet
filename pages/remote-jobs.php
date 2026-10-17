<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../components/job-card.php';
require_once __DIR__ . '/../components/pagination.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$remoteEnabled = akazinet_jobs_remote_enabled();
$jobs = [];
$total = 0;
$pagination = paginate(0, $perPage, $page);

// The column only exists after migration 11 has been run on the database.
// Before that the page renders its empty state instead of throwing SQL errors.
if ($remoteEnabled) {
    $where = akazinet_open_listing_sql('j') . ' AND j.is_remote = 1';

    $countStmt = $pdo->query("SELECT COUNT(*) FROM jobs j WHERE {$where}");
    $total = (int)$countStmt->fetchColumn();
    $pagination = paginate($total, $perPage, $page);

    $stmt = $pdo->query(
        "SELECT j.*, cat.name AS category_name
         FROM jobs j
         JOIN categories cat ON j.category_id = cat.id
         WHERE {$where}
         ORDER BY j.is_featured DESC, j.created_at DESC
         LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}"
    );
    $jobs = $stmt->fetchAll();
}

$pageTitle = 'Remote Jobs';
$meta_description = $total > 0
    ? "Browse {$total} open remote and work-from-home job vacancies in Rwanda on AkaziNet, with application deadlines and direct apply links."
    : 'Remote and work-from-home job opportunities on AkaziNet. Find remote roles from employers hiring in Rwanda, with deadlines and direct apply links.';
$breadcrumbs = [
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'Remote Jobs'],
];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<section class="bg-gradient-to-br from-primary to-primary-dark py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">Remote Jobs</h1>
        <p class="text-gray-300">Work-from-home and remote roles, all in one place.</p>
        <?php if ($total > 0): ?>
        <p class="mt-3 text-sm text-blue-200">
            <i class="fas fa-laptop-house mr-1"></i> <?= $total ?> open remote <?= $total === 1 ? 'vacancy' : 'vacancies' ?>
        </p>
        <?php endif; ?>
    </div>
</section>

<section class="py-12 md:py-16 bg-brand-gray min-h-[40vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (!empty($jobs)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($jobs as $job): ?>
            <?php render_job_card($job); ?>
            <?php endforeach; ?>
        </div>
        <?php if ($pagination['total_pages'] > 1): ?>
        <div class="mt-10">
            <?php render_pagination($pagination, '/pages/remote-jobs'); ?>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="rounded-2xl p-8 md:p-12 text-center max-w-2xl mx-auto" style="background-color:#eff6ff;border:1px solid #bfdbfe;">
            <div class="w-16 h-16 mx-auto mb-5 rounded-full flex items-center justify-center" style="background-color:#dbeafe;">
                <i class="fas fa-laptop-house text-2xl" style="color:#1d4ed8"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-3">No remote jobs published right now</h2>
            <p class="text-gray-600 leading-relaxed mb-2">Remote vacancies are posted as employers submit them. Check back soon, or browse every open role on the site.</p>
            <p class="text-gray-500 text-sm">In the meantime, <a href="/jobs" class="text-primary font-medium hover:underline">browse all current jobs</a> or <a href="/pages/contact" class="text-primary font-medium hover:underline">get in touch</a>.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script src="/assets/js/main.js"></script>
</body>
</html>
