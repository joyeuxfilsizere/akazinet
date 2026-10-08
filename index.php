<?php
$pageTitle = 'Home';
$meta_description = 'AkaziNet lists job vacancies in Rwanda with live application deadline countdowns, so you always know how long you have to apply. Browse jobs by category, province and employer.';
require_once __DIR__ . '/includes/header.php';

$featuredJobs = $pdo->query("
    SELECT j.*, cat.name as category_name, cat.slug as category_slug
    FROM jobs j
    JOIN categories cat ON j.category_id = cat.id
    WHERE j.status = 'published' AND j.is_featured = 1 AND j.is_active = 1 AND (j.application_deadline IS NULL OR j.application_deadline >= NOW())
    ORDER BY j.created_at DESC LIMIT 6
")->fetchAll();

$latestJobs = $pdo->query("
    SELECT j.*, cat.name as category_name, cat.slug as category_slug
    FROM jobs j
    JOIN categories cat ON j.category_id = cat.id
    WHERE j.status = 'published' AND j.is_active = 1
    ORDER BY j.created_at DESC LIMIT 6
")->fetchAll();

        // Only categories with at least one published, active vacancy are
        // linked from the homepage, so we never point visitors or crawlers at
        // an empty "no jobs found" category page.
        $categories = $pdo->query("
            SELECT c.*
            FROM categories c
            WHERE c.is_active = 1
              AND EXISTS (
                  SELECT 1 FROM jobs j
                  WHERE j.category_id = c.id
                    AND j.status = 'published'
                    AND j.is_active = 1
              )
            ORDER BY c.name
        ")->fetchAll();

$totalJobs = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'published' AND is_active = 1")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories WHERE is_active = 1")->fetchColumn();
$whatsappGroupLink = get_setting('whatsapp_group_link', '');

$scholarshipListings = $pdo->query("
    SELECT sl.*, s.title as category_title
    FROM scholarship_listings sl
    JOIN scholarships s ON sl.scholarship_id = s.id
    WHERE sl.is_active = 1
    ORDER BY sl.sort_order ASC, sl.created_at DESC
    LIMIT 3
")->fetchAll();
$totalScholarships = $pdo->query("SELECT COUNT(*) FROM scholarship_listings WHERE is_active = 1")->fetchColumn();

require_once __DIR__ . '/components/job-card.php';
require_once __DIR__ . '/components/ad-banner.php';
if (file_exists(__DIR__ . '/components/announcements.php')) {
    require_once __DIR__ . '/components/announcements.php';
}
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- Hero Section -->
<section class="relative bg-gradient-to-br from-primary via-primary to-primary-dark overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-20 left-10 w-72 h-72 bg-accent rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 right-20 w-96 h-96 bg-secondary-light rounded-full blur-3xl"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-32">
        <div class="text-center max-w-4xl mx-auto">
            <h1 class="text-4xl md:text-6xl font-extrabold text-white mb-6 leading-tight">
                Find Your Dream Job<br>
                <span class="text-accent">in Rwanda</span>
            </h1>
            <p class="text-lg md:text-xl text-gray-300 mb-10 max-w-2xl mx-auto leading-relaxed">
                Connecting talented Rwandans with career opportunities from employers across the nation, each reviewed before publication.
            </p>

            <!-- Search Form -->
            <form action="/jobs" method="GET" class="bg-white rounded-2xl p-3 shadow-2xl max-w-3xl mx-auto">
                <div class="flex flex-col md:flex-row gap-3">
                    <div class="flex-1 relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" placeholder="Job title, company, or keyword..." class="w-full pl-12 pr-4 py-3.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all text-gray-700">
                    </div>
                    <div class="flex-1 relative">
                        <i class="fas fa-map-marker-alt absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <select name="province" class="w-full pl-12 pr-4 py-3.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all text-gray-700 appearance-none bg-white">
                            <option value="">All Locations</option>
                            <option value="Kigali">Kigali</option>
                            <option value="Northern">Northern</option>
                            <option value="Southern">Southern</option>
                            <option value="Eastern">Eastern</option>
                            <option value="Western">Western</option>
                        </select>
                    </div>
                    <button type="submit" class="px-8 py-3.5 bg-accent text-white font-semibold rounded-xl hover:bg-accent-light transition-all duration-300 hover:shadow-lg hover:shadow-accent/30 flex items-center justify-center">
                        <i class="fas fa-search mr-2"></i> Search Jobs
                    </button>
                </div>
            </form>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-6 mt-12 max-w-md mx-auto">
                <div class="text-center">
                    <div class="text-2xl md:text-3xl font-bold text-white"><?= $totalJobs ?>+</div>
                    <div class="text-sm text-gray-400">Active Jobs</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl md:text-3xl font-bold text-white"><?= $totalCategories ?>+</div>
                    <div class="text-sm text-gray-400">Categories</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Hero Banner -->
<?php render_ad_banner('hero'); ?>

<!-- Featured Jobs -->
<?php if (!empty($featuredJobs)): ?>
<section class="py-16 md:py-20 bg-brand-gray">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
            <div>
                <span class="text-accent font-semibold text-sm uppercase tracking-wider">Featured</span>
                <h2 class="text-3xl font-bold text-gray-900 mt-1">Featured Job Vacancies</h2>
                <p class="text-gray-500 mt-2">Hand-picked opportunities from top employers</p>
            </div>
            <a href="/jobs?featured=1" class="mt-4 md:mt-0 text-primary font-medium hover:text-accent transition-colors flex items-center">
                View All Featured <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($featuredJobs as $job): ?>
                <?php render_job_card($job); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Advertisement -->
<?php render_adsense_unit('content', 'Advertisement'); ?>
<?php render_ad_banner(['content', 'sidebar']); ?>

<!-- Job Categories -->
<section class="py-16 md:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="text-accent font-semibold text-sm uppercase tracking-wider">Explore</span>
            <h2 class="text-3xl font-bold text-gray-900 mt-1">Job Categories</h2>
            <p class="text-gray-500 mt-2 max-w-2xl mx-auto">Browse jobs by category to find roles that match your skills and expertise</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <?php foreach (array_slice($categories, 0, 5) as $cat): ?>
            <a href="/jobs?category=<?= $cat['slug'] ?>" class="group bg-white border border-brand-border rounded-xl p-5 text-center hover:border-primary hover:shadow-md transition-all duration-300">
                <div class="w-12 h-12 mx-auto mb-3 bg-primary/5 rounded-xl flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-all duration-300">
                    <i class="fas fa-<?= $cat['icon'] ?> text-primary text-xl group-hover:text-white transition-colors"></i>
                </div>
                <h3 class="text-sm font-semibold text-gray-800 group-hover:text-primary transition-colors"><?= $cat['name'] ?></h3>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-8">
            <a href="/pages/categories" class="inline-flex items-center px-6 py-3 border-2 border-primary text-primary font-semibold rounded-xl hover:bg-primary hover:text-white transition-all duration-300">
                View All Categories <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Latest Jobs -->
<?php if (!empty($latestJobs)): ?>
<section class="py-16 md:py-20 bg-brand-gray">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
            <div>
                <span class="text-accent font-semibold text-sm uppercase tracking-wider">Latest</span>
                <h2 class="text-3xl font-bold text-gray-900 mt-1">Latest Job Vacancies</h2>
                <p class="text-gray-500 mt-2">Recently posted opportunities across Rwanda</p>
            </div>
            <a href="/jobs" class="mt-4 md:mt-0 text-primary font-medium hover:text-accent transition-colors flex items-center">
                View All Jobs <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            <div class="lg:col-span-2">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($latestJobs as $job): ?>
                        <?php render_job_card($job); ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <aside>
                <?php if (function_exists('render_announcements_sidebar')) { render_announcements_sidebar(5); } ?>
            </aside>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Scholarships -->
<?php if (!empty($scholarshipListings)): ?>
<section class="py-16 md:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
            <div>
                <span class="text-primary font-semibold text-sm uppercase tracking-wider"><i class="fas fa-graduation-cap mr-1"></i> Scholarships</span>
                <h2 class="text-3xl font-bold text-gray-900 mt-1">Scholarships Are Also Available</h2>
                <p class="text-gray-500 mt-2">Educational opportunities for students and professionals in Rwanda â€” not just jobs!</p>
            </div>
            <a href="/pages/scholarships" class="mt-4 md:mt-0 text-primary font-medium hover:text-accent transition-colors flex items-center">
                View All <?= (int)$totalScholarships ?> Scholarships <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($scholarshipListings as $ls): ?>
            <a href="/pages/scholarship-detail?id=<?= (int)$ls['id'] ?>" class="group bg-white rounded-2xl border border-brand-border p-6 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex flex-col">
                <div class="flex items-start space-x-4 mb-4">
                    <div class="w-14 h-14 bg-primary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-graduation-cap text-primary text-xl"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 truncate group-hover:text-primary transition-colors"><?= sanitize($ls['title']) ?></h3>
                        <p class="text-sm text-primary font-medium truncate"><?= sanitize($ls['organization']) ?></p>
                    </div>
                </div>
                <p class="text-sm text-gray-500 leading-relaxed line-clamp-2 mb-4 flex-1"><?= sanitize(mb_strimwidth($ls['description'], 0, 180, '...')) ?></p>
                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <span class="relative z-10 inline-flex items-center text-xs font-semibold px-3 py-1.5 rounded-full <?= $ls['deadline'] && strtotime($ls['deadline']) < time() ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-700' ?>">
                        <i class="fas fa-calendar-alt mr-1.5"></i>
                        <?= $ls['deadline'] ? (strtotime($ls['deadline']) < time() ? 'Closed' : 'Deadline: ' . date('M d, Y', strtotime($ls['deadline']))) : 'Open' ?>
                    </span>
                    <span class="inline-flex items-center text-xs font-medium text-primary">
                        View Details <i class="fas fa-arrow-right ml-1 group-hover:translate-x-1 transition-transform"></i>
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Why Choose AkaziNet -->
<section class="py-16 md:py-20 bg-brand-gray">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="text-accent font-semibold text-sm uppercase tracking-wider">Why Us</span>
            <h2 class="text-3xl font-bold text-gray-900 mt-1">Why Choose AkaziNet?</h2>
            <p class="text-gray-500 mt-2 max-w-2xl mx-auto">We are committed to connecting Rwandans with the best career opportunities</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl p-8 text-center hover:shadow-lg transition-all duration-300 border border-brand-border">
                <div class="w-16 h-16 mx-auto mb-5 bg-primary/10 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-shield-alt text-primary text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Reviewed Before Publishing</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Every job posting is reviewed by our team before it goes live, which catches incomplete and suspicious listings. Review is not a guarantee of authenticity, so confirm with the employer before you send documents or pay anything.</p>
            </div>
            <div class="bg-white rounded-2xl p-8 text-center hover:shadow-lg transition-all duration-300 border border-brand-border">
                <div class="w-16 h-16 mx-auto mb-5 bg-accent/10 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-bolt text-accent text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Fast & Simple</h3>
                <p class="text-gray-500 text-sm leading-relaxed">No account required to browse jobs. Find and apply for opportunities quickly and effortlessly.</p>
            </div>
            <div class="bg-white rounded-2xl p-8 text-center hover:shadow-lg transition-all duration-300 border border-brand-border">
                <div class="w-16 h-16 mx-auto mb-5 bg-green-100 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-handshake text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">WhatsApp Support</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Have questions? Get instant assistance through WhatsApp to help you navigate your job search.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Banner -->
<section class="py-16 md:py-20 bg-gradient-to-r from-primary to-primary-dark relative overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute -top-20 -right-20 w-80 h-80 bg-accent rounded-full blur-3xl"></div>
    </div>
    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Ready to Find Your Next Opportunity?</h2>
        <p class="text-gray-300 text-lg mb-8 max-w-2xl mx-auto">Browse through hundreds of job vacancies across Rwanda, each reviewed before publication, and take the next step in your career.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/jobs" class="inline-flex items-center px-8 py-4 bg-accent text-white font-semibold rounded-xl hover:bg-accent-light transition-all duration-300 hover:shadow-lg hover:shadow-accent/30">
                <i class="fas fa-search mr-2"></i> Browse Jobs
            </a>
            <a href="/pages/contact" class="inline-flex items-center px-8 py-4 bg-white/10 text-white font-semibold rounded-xl hover:bg-white/20 transition-all duration-300 border border-white/20">
                <i class="fas fa-envelope mr-2"></i> Contact Us
            </a>
            <?php if (!empty($whatsappGroupLink)): ?>
            <a href="<?= sanitize($whatsappGroupLink) ?>" target="_blank" rel="noopener" class="inline-flex items-center px-8 py-4 bg-green-500 text-white font-semibold rounded-xl hover:bg-green-600 transition-all duration-300 hover:shadow-lg hover:shadow-green-500/25">
                <i class="fab fa-whatsapp mr-2 text-lg"></i> Join WhatsApp Group
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Footer Banner -->
<?php render_ad_banner('footer'); ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script src="/assets/js/main.js?v=4"></script>
</body>
</html>
