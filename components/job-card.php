<?php
require_once __DIR__ . '/deadline-countdown.php';

function render_job_card($job) {
    global $pdo;
    $posted = date('M d, Y', strtotime($job['created_at']));
    $logo = !empty($job['company_logo']) ? '/assets/uploads/' . $job['company_logo'] : '/assets/images/default-logo.svg';
    $isExpired = $job['application_deadline'] && strtotime($job['application_deadline']) <= time();
    $whatsappNumber = get_setting('whatsapp_number', '250780092252');
    $waMsg = urlencode(job_help_message($job['title'], $job['company_name'], $job['application_deadline']));
    $waUrl = "https://wa.me/{$whatsappNumber}?text={$waMsg}";
?>
<div class="job-card bg-white rounded-xl border border-brand-border p-6 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 relative group">
    <?php if ($job['is_featured']): ?>
    <span class="absolute top-4 right-4 bg-accent/10 text-accent text-xs font-semibold px-3 py-1 rounded-full flex items-center">
        <i class="fas fa-star mr-1 text-[10px]"></i> Featured
    </span>
    <?php endif; ?>

    <div class="flex items-start space-x-4 mb-4">
        <div class="w-14 h-14 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0 border border-brand-border">
            <img src="<?= $logo ?>" alt="<?= sanitize($job['company_name']) ?> logo" class="w-full h-full object-contain p-1" loading="lazy" onerror="this.src='/assets/images/default-logo.svg'">
        </div>
        <div class="min-w-0 flex-1">
            <h3 class="text-lg font-semibold text-gray-900 truncate group-hover:text-primary transition-colors">
                <a href="/job-details?slug=<?= $job['slug'] ?>"><?= sanitize($job['title']) ?></a>
            </h3>
            <p class="text-sm text-gray-500 truncate"><?= sanitize($job['company_name']) ?></p>
        </div>
    </div>

    <div class="flex flex-wrap gap-2 mb-4">
        <span class="inline-flex items-center text-xs text-gray-500 bg-gray-50 px-2.5 py-1 rounded-md">
            <i class="fas fa-map-marker-alt mr-1.5 text-primary-light"></i> <?= sanitize($job['location'] ?? $job['district'] ?? 'Rwanda') ?>
        </span>
        <span class="inline-flex items-center text-xs text-gray-500 bg-gray-50 px-2.5 py-1 rounded-md">
            <i class="fas fa-briefcase mr-1.5 text-primary-light"></i> <?= sanitize($job['employment_type']) ?>
        </span>
        <?php if ($job['category_name']): ?>
        <span class="inline-flex items-center text-xs text-gray-500 bg-gray-50 px-2.5 py-1 rounded-md">
            <i class="fas fa-tag mr-1.5 text-primary-light"></i> <?= sanitize($job['category_name']) ?>
        </span>
        <?php endif; ?>
    </div>

    <?php render_deadline_countdown($job['application_deadline'] ?? null, 'card'); ?>

    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
        <span class="text-xs text-gray-400"><i class="fas fa-calendar mr-1"></i> Posted <?= $posted ?></span>
    </div>

    <?php
        $applyUrl = !empty($job['application_url']) ? $job['application_url'] : '';
        $detailsHref = "/job-details?slug={$job['slug']}";
    ?>
    <div class="flex gap-2 mt-4">
        <?php if ($isExpired): ?>
        <span class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-gray-100 text-gray-500 text-sm font-medium rounded-lg cursor-not-allowed" aria-disabled="true">
            <i class="fas fa-lock mr-1.5"></i> Application Closed
        </span>
        <?php else: ?>
        <a href="<?= $applyUrl ? sanitize($applyUrl) : $detailsHref ?>" <?= $applyUrl ? 'target="_blank" rel="noopener noreferrer"' : '' ?> class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors">
            <i class="fas fa-paper-plane mr-1.5"></i> Apply Now
        </a>
        <?php endif; ?>
        <a href="<?= $waUrl ?>" target="_blank" rel="noopener noreferrer" class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-green-500 text-white text-sm font-medium rounded-lg hover:bg-green-600 transition-colors">
            <i class="fab fa-whatsapp mr-1.5"></i> Ask for Help
        </a>
    </div>
</div>
<?php
}
?>
