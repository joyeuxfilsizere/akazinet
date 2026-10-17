<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/components/whatsapp.php';
require_once __DIR__ . '/components/ad-banner.php';
require_once __DIR__ . '/components/job-card.php';
require_once __DIR__ . '/components/deadline-countdown.php';
require_once __DIR__ . '/components/job-context.php';
// Loaded before header.php because $extraSchema is built before <head> renders.
require_once __DIR__ . '/includes/schema.php';
if (file_exists(__DIR__ . '/components/announcements.php')) {
    require_once __DIR__ . '/components/announcements.php';
}

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    redirect('/jobs');
}

$stmt = $pdo->prepare("
    SELECT j.*, cat.name as category_name, cat.slug as category_slug
    FROM jobs j
    JOIN categories cat ON j.category_id = cat.id
    WHERE j.slug = ? AND j.status = 'published' AND j.is_active = 1 AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
");
$stmt->execute([$slug]);
$job = $stmt->fetch();

if (!$job) {
    redirect('/jobs');
}

$pdo->prepare("UPDATE jobs SET views = views + 1 WHERE id = ?")->execute([$job['id']]);

$isExpired = $job['application_deadline'] && strtotime($job['application_deadline']) < time();
$deadline = format_deadline($job['application_deadline']);

$whatsappNumber = get_setting('whatsapp_number', '250780092252');

$relatedJobs = $pdo->prepare("
    SELECT j.*, cat.name as category_name
    FROM jobs j
    JOIN categories cat ON j.category_id = cat.id
    WHERE j.category_id = ? AND j.id != ? AND j.status = 'published' AND j.is_active = 1 AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
    ORDER BY j.created_at DESC LIMIT 3
");
$relatedJobs->execute([$job['category_id'], $job['id']]);
$relatedJobs = $relatedJobs->fetchAll();

$pageTitle = $job['title'];

$metaLocation = !empty($job['province']) ? $job['province'] : ($job['district'] ?? '');
$metaWhere = $metaLocation !== '' ? ' in ' . $metaLocation . ', Rwanda' : ' in Rwanda';
$metaCategory = !empty($job['category_name']) ? ' ' . strtolower($job['category_name']) . ' role' : ' role';
$metaDescription = 'Apply for the ' . $job['title'] . ' job vacancy' . $metaWhere
    . '. See requirements, responsibilities and the application deadline on AkaziNet.';

if (strlen($metaDescription) > 160) {
    $metaDescription = 'Apply for the ' . $job['title'] . ' job vacancy' . $metaWhere
        . '. View requirements and the application deadline on AkaziNet.';
}

$meta_description = $metaDescription;

$breadcrumbs = [
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'Jobs', 'url' => '/jobs'],
];
if (!empty($job['category_slug'])) {
    $breadcrumbs[] = ['name' => $job['category_name'], 'url' => '/jobs?category=' . rawurlencode($job['category_slug'])];
}
$breadcrumbs[] = ['name' => $job['title'], 'url' => '/job-details?slug=' . rawurlencode($job['slug'])];

$extraSchema = akazinet_job_posting($job);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<section class="bg-gradient-to-br from-primary to-primary-dark py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="/jobs" class="inline-flex items-center text-gray-300 hover:text-white text-sm mb-4 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Back to Jobs
        </a>
    </div>
</section>

<section class="py-8 md:py-12 bg-brand-gray">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Job Header -->
                <div class="bg-white rounded-xl p-6 md:p-8 border border-brand-border shadow-sm">
                    <div class="flex items-start space-x-5">
                        <div class="w-16 h-16 rounded-xl overflow-hidden bg-gray-50 border border-brand-border flex-shrink-0 flex items-center justify-center">
                            <?php $jobLogo = !empty($job['company_logo']) ? '/assets/uploads/' . $job['company_logo'] : '/assets/images/default-logo.svg'; ?>
                            <img src="<?= $jobLogo ?>" alt="<?= sanitize($job['company_name']) ?>" class="w-full h-full object-contain p-1" onerror="this.src='/assets/images/default-logo.svg'">
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div>
                                    <?php if ($job['is_featured']): ?>
                                    <span class="inline-flex items-center text-xs font-semibold text-accent bg-accent/10 px-2.5 py-1 rounded-full mb-2">
                                        <i class="fas fa-star mr-1"></i> Featured
                                    </span>
                                    <?php endif; ?>
                                    <?php if (!empty($job['is_remote'])): ?>
                                    <span class="inline-flex items-center text-xs font-semibold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-full mb-2 ml-2">
                                        <i class="fas fa-laptop-house mr-1"></i> Remote
                                    </span>
                                    <?php endif; ?>
                                    <?php if ($isExpired): ?>
                                    <span class="inline-flex items-center text-xs font-semibold text-red-600 bg-red-50 px-2.5 py-1 rounded-full mb-2 ml-2">
                                        Expired
                                    </span>
                                    <?php endif; ?>
                                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900"><?= sanitize($job['title']) ?></h1>
                                    <span class="text-primary font-medium"><?= sanitize($job['company_name']) ?></span>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2 mt-4">
                                <span class="inline-flex items-center text-xs text-gray-500 bg-gray-50 px-3 py-1.5 rounded-lg">
                                    <i class="fas fa-briefcase mr-1.5 text-primary-light"></i> <?= sanitize($job['employment_type']) ?>
                                </span>
                                <span class="inline-flex items-center text-xs text-gray-500 bg-gray-50 px-3 py-1.5 rounded-lg">
                                    <i class="fas fa-tag mr-1.5 text-primary-light"></i> <?= sanitize($job['category_name']) ?>
                                </span>
                                <?php if ($job['experience_level']): ?>
                                <span class="inline-flex items-center text-xs text-gray-500 bg-gray-50 px-3 py-1.5 rounded-lg">
                                    <i class="fas fa-layer-group mr-1.5 text-primary-light"></i> <?= sanitize($job['experience_level']) ?>
                                </span>
                                <?php endif; ?>
                                <?php if ($job['education_level']): ?>
                                <span class="inline-flex items-center text-xs text-gray-500 bg-gray-50 px-3 py-1.5 rounded-lg">
                                    <i class="fas fa-graduation-cap mr-1.5 text-primary-light"></i> <?= sanitize($job['education_level']) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <?php if ($job['description']): ?>
                <div class="bg-white rounded-xl p-6 md:p-8 border border-brand-border shadow-sm">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-file-alt text-primary text-sm"></i>
                        </span>
                        Job Description
                    </h2>
                    <div class="text-gray-600 leading-relaxed"><?= render_rich_text($job['description']) ?></div>
                </div>
                <?php endif; ?>

                <?php if ($job['responsibilities']): ?>
                <div class="bg-white rounded-xl p-6 md:p-8 border border-brand-border shadow-sm">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-accent/10 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-tasks text-accent text-sm"></i>
                        </span>
                        Responsibilities
                    </h2>
                    <?php if (contains_html($job['responsibilities'])): ?>
                    <div class="text-gray-600 leading-relaxed"><?= render_rich_text($job['responsibilities']) ?></div>
                    <?php else: ?>
                    <ul class="space-y-2">
                        <?php foreach (explode("\n", $job['responsibilities']) as $item): ?>
                        <?php if (trim($item)): ?>
                        <li class="flex items-start text-gray-600 text-sm font-semibold">
                            <i class="fas fa-check-circle text-secondary-light mt-0.5 mr-3 flex-shrink-0"></i>
                            <?= sanitize(trim($item)) ?>
                        </li>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ($job['requirements']): ?>
                <div class="bg-white rounded-xl p-6 md:p-8 border border-brand-border shadow-sm">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-clipboard-list text-blue-500 text-sm"></i>
                        </span>
                        Requirements
                    </h2>
                    <?php if (contains_html($job['requirements'])): ?>
                    <div class="text-gray-600 leading-relaxed"><?= render_rich_text($job['requirements']) ?></div>
                    <?php else: ?>
                    <ul class="space-y-2">
                        <?php foreach (explode("\n", $job['requirements']) as $item): ?>
                        <?php if (trim($item)): ?>
                        <li class="flex items-start text-gray-600 text-sm font-semibold">
                            <i class="fas fa-check-circle text-secondary-light mt-0.5 mr-3 flex-shrink-0"></i>
                            <?= sanitize(trim($item)) ?>
                        </li>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ($job['qualifications']): ?>
                <div class="bg-white rounded-xl p-6 md:p-8 border border-brand-border shadow-sm">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-award text-purple-500 text-sm"></i>
                        </span>
                        Qualifications
                    </h2>
                    <?php if (contains_html($job['qualifications'])): ?>
                    <div class="text-gray-600 leading-relaxed"><?= render_rich_text($job['qualifications']) ?></div>
                    <?php else: ?>
                    <ul class="space-y-2">
                        <?php foreach (explode("\n", $job['qualifications']) as $item): ?>
                        <?php if (trim($item)): ?>
                        <li class="flex items-start text-gray-600 text-sm font-semibold">
                            <i class="fas fa-check-circle text-secondary-light mt-0.5 mr-3 flex-shrink-0"></i>
                            <?= sanitize(trim($item)) ?>
                        </li>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ($job['benefits']): ?>
                <div class="bg-white rounded-xl p-6 md:p-8 border border-brand-border shadow-sm">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-gift text-green-500 text-sm"></i>
                        </span>
                        Benefits
                    </h2>
                    <?php if (contains_html($job['benefits'])): ?>
                    <div class="text-gray-600 leading-relaxed"><?= render_rich_text($job['benefits']) ?></div>
                    <?php else: ?>
                    <ul class="space-y-2">
                        <?php foreach (explode("\n", $job['benefits']) as $item): ?>
                        <?php if (trim($item)): ?>
                        <li class="flex items-start text-gray-600 text-sm font-semibold">
                            <i class="fas fa-check-circle text-secondary-light mt-0.5 mr-3 flex-shrink-0"></i>
                            <?= sanitize(trim($item)) ?>
                        </li>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ($job['how_to_apply']): ?>
                <div class="bg-white rounded-xl p-6 md:p-8 border border-brand-border shadow-sm">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-paper-plane text-orange-500 text-sm"></i>
                        </span>
                        How to Apply
                    </h2>
                    <div class="text-gray-600 leading-relaxed"><?= render_rich_text($job['how_to_apply']) ?></div>
                </div>
                <?php endif; ?>

<?php if (!empty($job['supporting_document'])): ?>
                <div class="bg-white rounded-xl p-6 md:p-8 border border-brand-border shadow-sm">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-paperclip text-orange-500 text-sm"></i>
                        </span>
                        Supporting Document
                    </h2>
                    <p class="text-gray-500 text-sm mb-4">View or download the original advertisement / source document for this vacancy.</p>
                    <a href="/assets/uploads/<?= sanitize($job['supporting_document']) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-5 py-2.5 bg-primary text-white font-semibold rounded-lg hover:bg-primary-dark transition-colors">
                        <i class="fas fa-file-download mr-2"></i> View Advertisement Document
                    </a>
                </div>
                <?php endif; ?>

                <!-- Application checklist -->
                <?php render_job_apply_tips($job, $isExpired); ?>

                <!-- Share -->
                <div class="bg-white rounded-xl p-6 border border-brand-border shadow-sm">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <h3 class="font-semibold text-gray-900"><i class="fas fa-share-alt mr-2 text-primary"></i>Share This Vacancy</h3>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" data-copy-url="<?= SITE_URL . $_SERVER['REQUEST_URI'] ?>" class="copy-link-btn w-10 h-10 rounded-lg bg-gray-800 text-white flex items-center justify-center hover:bg-gray-700 transition-colors" aria-label="Copy link" title="Copy link">
                                <i class="fas fa-link"></i>
                            </button>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL . $_SERVER['REQUEST_URI']) ?>" target="_blank" class="w-10 h-10 rounded-lg bg-blue-600 text-white flex items-center justify-center hover:bg-blue-700 transition-colors" aria-label="Share on Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="https://www.instagram.com/" target="_blank" class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 via-pink-500 to-orange-400 text-white flex items-center justify-center hover:opacity-90 transition-colors" aria-label="Share on Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode(SITE_URL . $_SERVER['REQUEST_URI']) ?>&title=<?= urlencode($job['title']) ?>" target="_blank" class="w-10 h-10 rounded-lg bg-blue-700 text-white flex items-center justify-center hover:bg-blue-800 transition-colors" aria-label="Share on LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                            <a href="https://wa.me/?text=<?= urlencode($job['title'] . ' at ' . $job['company_name'] . "\n" . SITE_URL . $_SERVER['REQUEST_URI']) ?>" target="_blank" class="w-10 h-10 rounded-lg bg-green-500 text-white flex items-center justify-center hover:bg-green-600 transition-colors" aria-label="Share on WhatsApp"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>
                </div>

                <!-- WhatsApp -->
                <?php render_whatsapp_button($job['title'], $job['company_name'], $whatsappNumber, $job['application_deadline']); ?>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <?php render_deadline_countdown($job['application_deadline'] ?? null, 'detail'); ?>

                <!-- Job Info -->
                <div class="bg-white rounded-xl p-6 border border-brand-border shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4">Job Information</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Company</span>
                            <span class="font-medium text-gray-800"><?= sanitize($job['company_name']) ?></span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Posted</span>
                            <span class="font-medium text-gray-800"><?= date('M d, Y', strtotime($job['created_at'])) ?></span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Deadline</span>
                            <span class="font-medium <?= $isExpired ? 'text-red-500' : 'text-gray-800' ?>"><?= $isExpired ? 'Expired' : $deadline ?></span>
                        </div>
                        <?php if (is_admin_logged_in()): ?>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Views</span>
                            <span class="font-medium text-gray-800"><?= $job['views'] ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (function_exists('render_announcements_sidebar')) { render_announcements_sidebar(5); } ?>

                <!-- Advertisement -->
                <?php render_adsense_unit('content', 'Advertisement'); ?>
<?php render_adsense_unit('sidebar', 'Advertisement'); ?>
<?php render_ad_banner(['sidebar', 'content']); ?>

                <?php if (!$isExpired): ?>
                <?php
                    $applyUrl = !empty($job['application_url']) ? $job['application_url'] : '';
                    $applyEmail = !empty($job['application_email']) ? $job['application_email'] : '';
                    $waMsg = urlencode(job_help_message($job['title'], $job['company_name'], $job['application_deadline']));
                    $waUrl = "https://wa.me/{$whatsappNumber}?text={$waMsg}";
                ?>

                <!-- Apply Now -->
                <?php if ($applyUrl): ?>
                <a href="<?= sanitize($applyUrl) ?>" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-full py-4 bg-primary text-white text-center font-bold text-lg rounded-xl hover:bg-primary-dark transition-all duration-300 hover:shadow-lg">
                    <i class="fas fa-external-link-alt mr-2"></i> Apply Now
                </a>
                <?php elseif ($applyEmail): ?>
                <a href="mailto:<?= sanitize($applyEmail) ?>" class="flex items-center justify-center w-full py-4 bg-primary text-white text-center font-bold text-lg rounded-xl hover:bg-primary-dark transition-all duration-300 hover:shadow-lg">
                    <i class="fas fa-envelope mr-2"></i> Apply Now
                </a>
                <?php endif; ?>

                <!-- Ask for Help -->
                <a href="<?= $waUrl ?>" target="_blank" class="flex items-center justify-center w-full py-4 mt-3 bg-green-500 text-white text-center font-bold text-lg rounded-xl hover:bg-green-600 transition-all duration-300 hover:shadow-lg hover:shadow-green-500/25">
                    <i class="fab fa-whatsapp mr-2 text-xl"></i> Ask for Help
                </a>

                <!-- Application Email - Prominent Display -->
                <?php if ($applyEmail): ?>
                <div class="mt-4 bg-blue-50 border border-blue-200 rounded-xl p-5 text-center">
                    <div class="flex items-center justify-center mb-2">
                        <span class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                            <i class="fas fa-envelope text-blue-600 text-lg"></i>
                        </span>
                        <span class="text-sm font-semibold text-blue-800 uppercase tracking-wide">Send your CV to</span>
                    </div>
                    <a href="mailto:<?= sanitize($applyEmail) ?>" class="inline-block text-xl md:text-2xl font-extrabold text-blue-700 break-all hover:underline">
                        <?= sanitize($applyEmail) ?>
                    </a>
                    <p class="text-xs text-blue-500 mt-2">Click the email address to open your mail app</p>
                </div>
                <?php endif; ?>

                <?php endif; ?>
            </div>
        </div>

        <!-- Related Jobs -->
        <?php if (!empty($relatedJobs)): ?>
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Jobs</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($relatedJobs as $rjob): ?>
                <?php render_job_card($rjob); ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Trust: let job seekers flag this listing. Pre-fills the slug and
             title so the reporter does not have to retype them. -->
        <div class="mt-10 pt-6 border-t border-gray-200 text-center">
            <p class="text-sm text-gray-500 mb-2">
                Never asked to pay for a job? This advert may be fake.
            </p>
            <a href="/pages/report-job?slug=<?= urlencode((string)$job['slug']) ?>&amp;title=<?= urlencode((string)$job['title']) ?>"
                class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-red-600 transition-colors">
                <i class="fas fa-flag mr-2"></i> Report this listing
            </a>
        </div>
    </div>
</section>

<!-- Footer Banner -->
<?php render_ad_banner('footer'); ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script src="/assets/js/main.js?v=4"></script>
</body>
</html>
