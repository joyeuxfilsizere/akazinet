<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/components/job-card.php';
require_once __DIR__ . '/components/ad-banner.php';
if (file_exists(__DIR__ . '/components/announcements.php')) {
    require_once __DIR__ . '/components/announcements.php';
}

$search = $_GET['search'] ?? '';
$province = $_GET['province'] ?? '';
$category = $_GET['category'] ?? '';

// featured carries a value, so read the value rather than its presence: the
// previous isset() check treated ?featured=0 as "featured only" and served an
// exact duplicate of ?featured=1 under a self-canonical, which is how the two
// variants ended up competing in Search Console. Values other than 1 are not a
// filter anyone links to, so send them (keeping the rest of the query) to the
// unfiltered listing with a permanent redirect instead of serving a copy.
if (isset($_GET['featured']) && $_GET['featured'] !== '1') {
    $remaining = $_GET;
    unset($remaining['featured']);
    redirect('/jobs' . ($remaining ? '?' . http_build_query($remaining) : ''), true);
}
$featured = (isset($_GET['featured']) && $_GET['featured'] === '1') ? 1 : 0;
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;

// Only published, active vacancies belong in the listing. The previous
// condition also admitted any row whose application_deadline had already
// passed (deadline < NOW()), which re-surfaced archived jobs as cards that
// 404 on click, because job-details.php requires status = 'published'.
//
// The deadline check is repeated here rather than trusted to auto_expire_jobs(),
// which writes to the database and silently ignores failures. auto_expire_jobs()
// is an optimisation for the admin views; this condition is what actually
// guarantees a visitor never sees an expired vacancy.
$where = [
    "j.status = 'published'",
    "j.is_active = 1",
    akazinet_not_expired_sql('j', 'application_deadline'),
];
$params = [];

if (!empty($search)) {
    $where[] = "(j.title LIKE ? OR j.company_name LIKE ? OR j.description LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
if (!empty($province)) {
    $where[] = "j.province = ?";
    $params[] = $province;
}
if (!empty($category)) {
    $where[] = "cat.slug = ?";
    $params[] = $category;
}
if ($featured) {
    $where[] = "j.is_featured = 1";
}

$whereSQL = implode(' AND ', $where);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM jobs j JOIN categories cat ON j.category_id = cat.id WHERE {$whereSQL}");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();

$pagination = paginate($total, $perPage, $page);

// The previous sort pushed rows whose deadline had passed to the back. Now that
// they are excluded by the WHERE clause above, that expression was always 0, so
// it is dropped rather than replaced. Replacing it with a dated-first ordering
// would have quietly changed the ranking.
$stmt = $pdo->prepare("
    SELECT j.*, cat.name as category_name, cat.slug as category_slug
    FROM jobs j
    JOIN categories cat ON j.category_id = cat.id
    WHERE {$whereSQL}
    ORDER BY j.is_featured DESC, j.created_at DESC
    LIMIT {$perPage} OFFSET {$pagination['offset']}
");
$stmt->execute($params);
$jobs = $stmt->fetchAll();

// Every active category, used to resolve the requested category for the page
// title even when that category currently has no vacancies.
$allCategories = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name")->fetchAll();

// Only categories that actually have at least one published, active vacancy
// are rendered as filter links. Linking to the empty ones produced dozens of
// indexable, self-canonical "no jobs found" pages, which reads as thin
// doorway content rather than a useful category browse.
$categories = $pdo->query("
    SELECT c.*
    FROM categories c
    WHERE c.is_active = 1
      AND EXISTS (
          SELECT 1 FROM jobs j
          WHERE j.category_id = c.id
            AND j.status = 'published'
            AND j.is_active = 1
            AND " . akazinet_not_expired_sql('j', 'application_deadline') . "
      )
    ORDER BY c.name
")->fetchAll();

$currentCategory = null;
foreach ($allCategories as $catItem) {
    if ($catItem['slug'] === $category) {
        $currentCategory = $catItem;
        break;
    }
}

// Search result pages are thin, query-built views of the same listings, so they
// are never advertised as indexable: with hits they would otherwise be indexed
// as their own pages, and empty they are duplicates of the empty listing. The
// noindex is delivered on a crawlable 200 (robots.txt no longer blocks these
// URLs) so Google can actually read it. Other filters keep their index only
// while they list something.
if ($search !== '' || ($total === 0 && ($category !== '' || $province !== '' || $featured))) {
    $meta_robots = 'noindex, follow';
}

if ($currentCategory) {
    $categoryName = $currentCategory['name'];
    $pageTitle = $categoryName . ' Jobs in Rwanda';
    $meta_description = "Browse {$total} {$categoryName} " . ($total == 1 ? 'job vacancy' : 'job vacancies')
        . " in Rwanda, with application deadlines, locations and direct apply links on AkaziNet.";
} elseif ($featured) {
    $pageTitle = 'Featured Jobs in Rwanda';
    $meta_description = "Explore {$total} featured job " . ($total == 1 ? 'vacancy' : 'vacancies')
        . ' in Rwanda hand-picked by our editorial team, with deadlines and direct application links on AkaziNet.';
} elseif ($province !== '') {
    $pageTitle = 'Jobs in ' . $province . ', Rwanda';
    $meta_description = "Find {$total} open job " . ($total == 1 ? 'vacancy' : 'vacancies')
        . " in {$province}, Rwanda. Browse current openings with application deadlines and direct apply links on AkaziNet.";
} elseif ($search !== '') {
    $pageTitle = 'Search Results for ' . $search;
    $meta_description = "Search results for \"{$search}\" - {$total} matching job "
        . ($total == 1 ? 'vacancy' : 'vacancies')
        . ' in Rwanda with deadlines and direct application links on AkaziNet.';
} else {
    $pageTitle = 'Browse Jobs in Rwanda';
    $meta_description = "Browse {$total} current job " . ($total == 1 ? 'vacancy' : 'vacancies')
        . ' in Rwanda across accounting, IT, engineering, healthcare and more. Every listing shows its application deadline so you can apply in time.';
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- Hero Section -->
<section class="bg-gradient-to-br from-primary to-primary-dark py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">
                <?php if ($featured): ?>
                    Featured Jobs
                <?php elseif (!empty($search)): ?>
                    Search Results
                <?php else: ?>
                    Browse All Jobs
                <?php endif; ?>
            </h1>
        </div>

        <!-- Search Form -->
        <form action="/jobs" method="GET" class="bg-white rounded-2xl p-3 shadow-2xl max-w-3xl mx-auto">
            <div class="flex flex-col md:flex-row gap-3">
                <div class="flex-1 relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="<?= sanitize($search) ?>" placeholder="Job title, company, or keyword..." class="w-full pl-12 pr-4 py-3.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all text-gray-700">
                </div>
                <div class="flex-1 relative">
                    <i class="fas fa-map-marker-alt absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <select name="province" class="w-full pl-12 pr-4 py-3.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all text-gray-700 appearance-none bg-white">
                        <option value="">All Locations</option>
                        <?php foreach (['Kigali', 'Northern', 'Southern', 'Eastern', 'Western'] as $loc): ?>
                        <option value="<?= $loc ?>" <?= $province === $loc ? 'selected' : '' ?>><?= $loc ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="px-8 py-3.5 bg-accent text-white font-semibold rounded-xl hover:bg-accent-light transition-all duration-300 hover:shadow-lg hover:shadow-accent/30 flex items-center justify-center">
                    <i class="fas fa-search mr-2"></i> Search
                </button>
            </div>
        </form>
    </div>
</section>

<!-- Filters & Results -->
<section class="py-12 bg-brand-gray min-h-[50vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Category Chips -->
        <div class="flex flex-wrap gap-2 mb-8">
            <a href="/jobs" class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition-colors <?= empty($category) ? 'bg-primary text-white' : 'bg-white text-gray-600 border border-brand-border hover:border-primary hover:text-primary' ?>">
                All
            </a>
            <?php foreach ($categories as $cat): ?>
            <a href="/jobs?category=<?= $cat['slug'] ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($province) ? '&province=' . urlencode($province) : '' ?>" class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition-colors <?= $category === $cat['slug'] ? 'bg-primary text-white' : 'bg-white text-gray-600 border border-brand-border hover:border-primary hover:text-primary' ?>">
                <i class="fas fa-<?= $cat['icon'] ?> mr-1.5"></i> <?= sanitize($cat['name']) ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Ad Banner -->
        <?php render_adsense_unit('content', 'Advertisement'); ?>
<?php render_ad_banner('content'); ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            <div class="lg:col-span-2">
                <?php if (!empty($jobs)): ?>
                <!-- Job Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                    <?php foreach ($jobs as $job): ?>
                        <?php render_job_card($job); ?>
                    <?php endforeach; ?>
                </div>

        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
        <nav class="flex items-center justify-center gap-2">
            <?php if ($pagination['has_prev']): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="px-4 py-2 bg-white border border-brand-border rounded-lg text-sm font-medium text-gray-600 hover:border-primary hover:text-primary transition-colors">
                <i class="fas fa-arrow-left mr-1"></i> Prev
            </a>
            <?php endif; ?>

            <?php for ($i = max(1, $page - 2); $i <= min($pagination['total_pages'], $page + 2); $i++): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="w-10 h-10 flex items-center justify-center rounded-lg text-sm font-medium transition-colors <?= $i === $page ? 'bg-primary text-white' : 'bg-white border border-brand-border text-gray-600 hover:border-primary hover:text-primary' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>

            <?php if ($pagination['has_next']): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="px-4 py-2 bg-white border border-brand-border rounded-lg text-sm font-medium text-gray-600 hover:border-primary hover:text-primary transition-colors">
                Next <i class="fas fa-arrow-right ml-1"></i>
            </a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>

        <?php else: ?>
        <!-- Empty State -->
        <div class="text-center py-20">
            <div class="w-20 h-20 mx-auto mb-6 bg-gray-100 rounded-2xl flex items-center justify-center">
                <i class="fas fa-search text-gray-300 text-3xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">No jobs found</h2>
            <p class="text-gray-500 mb-6">Try adjusting your search criteria or browse all jobs.</p>
            <a href="/jobs" class="inline-flex items-center px-6 py-3 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition-colors">
                <i class="fas fa-list mr-2"></i> Browse All Jobs
            </a>
        </div>
                <?php endif; ?>
            </div>
            <aside>
                <?php if (function_exists('render_announcements_sidebar')) { render_announcements_sidebar(5); } ?>
            </aside>
        </div>
    </div>
</section>

<!-- Footer Banner -->
<?php render_ad_banner('footer'); ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script src="/assets/js/main.js?v=4"></script>
</body>
</html>
