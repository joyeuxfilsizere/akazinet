<?php
/**
 * Career Guides index.
 *
 * The editorial half of the site. AdSense reviewers (and Google) need a body of
 * original, ad-supported content, not just listing pages.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/guide-taxonomy.php';
require_once __DIR__ . '/../components/ad-banner.php';
require_once __DIR__ . '/../components/guide-card.php';
require_once __DIR__ . '/../components/job-card.php';
// Loaded before header.php because $extraSchema is built before <head> renders.
require_once __DIR__ . '/../includes/schema.php';

// The filter key is a taxonomy slug, not the raw guides.category value. A guide
// seeded before the taxonomy existed carries a legacy name ("Job Search"),
// which must still land in the "finding-work" bucket, so the slug is expanded
// back to every stored name that maps to it.
$categorySlug = trim((string)($_GET['category'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9;

$categoryNames = [];
if ($categorySlug !== '') {
    $categoryMeta = akazinet_guide_category($categorySlug);
    if ($categoryMeta !== null) {
        $categoryNames[] = $categoryMeta['name'];
    }
    foreach (akazinet_guide_category_aliases() as $legacy => $slug) {
        // array_unique: an alias can share a name with the canonical entry, and
        // duplicating the placeholder is harmless but sloppy.
        if ($slug === $categorySlug && !in_array($legacy, $categoryNames, true)) {
            $categoryNames[] = $legacy;
        }
    }
    // An unknown slug means no guide can match, but the query must stay valid
    // and must not leak results from every category.
    if (!$categoryNames) {
        $categoryNames[] = '__no_such_category__';
    }
}

$where = "status = 'published'";
$params = [];
if ($categoryNames) {
    $where .= ' AND category IN (' . implode(',', array_fill(0, count($categoryNames), '?')) . ')';
    $params = $categoryNames;
}

// Resolved once, up front, so the heading, the meta tags, the empty state and
// the related-guide queries all agree on which topic is being viewed.
$categoryMeta = $categorySlug !== '' ? akazinet_guide_category($categorySlug) : null;
$category = $categoryMeta['name'] ?? ($categorySlug !== '' ? 'Other Guides' : '');

try {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM guides WHERE $where");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $pagination = paginate($total, $perPage, $page);

    $stmt = $pdo->prepare("
        SELECT * FROM guides
        WHERE $where
        ORDER BY is_featured DESC, published_at DESC, id DESC
        LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}
    ");
    $stmt->execute($params);
    $guides = $stmt->fetchAll();
} catch (PDOException $e) {
    $guides = [];
    $pagination = paginate(0, $perPage, 1);
}

// Counts per taxonomy bucket, so a category can show "(4)" next to its name
// without a second round trip per category. Derived from the full published
// set, not from the current page, otherwise the counts would change as the
// reader paginates.
$categoryCounts = [];
try {
    $countRows = $pdo->query("SELECT category, COUNT(*) AS total FROM guides WHERE status = 'published' GROUP BY category")->fetchAll();
    foreach ($countRows as $row) {
        $slug = akazinet_guide_category_slug((string)$row['category']);
        $categoryCounts[$slug] = ($categoryCounts[$slug] ?? 0) + (int)$row['total'];
    }
} catch (PDOException $e) {
    $categoryCounts = [];
}

$totalPublished = array_sum($categoryCounts);

try {
    $featuredStmt = $pdo->query("
        SELECT * FROM guides
        WHERE status = 'published' AND is_featured = 1
        ORDER BY published_at DESC
        LIMIT 3
    ");
    $featuredGuides = $featuredStmt->fetchAll();
} catch (PDOException $e) {
    $featuredGuides = [];
}

// Pair with relevant live vacancies so a guide page sends readers into the
// job listings rather than dead-ending them.
try {
    $relatedJobs = $pdo->query("
        SELECT j.*, cat.name AS category_name
        FROM jobs j
        JOIN categories cat ON j.category_id = cat.id
        WHERE j.status = 'published' AND j.is_active = 1 AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
        ORDER BY j.is_featured DESC, j.created_at DESC
        LIMIT 3
    ")->fetchAll();
} catch (PDOException $e) {
    $relatedJobs = [];
}

if ($categoryMeta !== null) {
    $pageTitle = $categoryMeta['name'];
    $meta_description = akazinet_trim_meta(
        $categoryMeta['description'] . ' Free guides for Rwandan job seekers, with no signup and no cost.'
    );
} else {
    $pageTitle = 'Career Guides';
    $meta_description = 'Free, practical career guides for job seekers in Rwanda: CVs, applications, interviews, salaries, certifications, and how to spot fake job adverts.';
}

$breadcrumbs = [
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'Career Guides', 'url' => '/pages/guides'],
];
if ($categoryMeta !== null) {
    $breadcrumbs[] = ['name' => $categoryMeta['name'], 'url' => '/pages/guides?category=' . urlencode($categoryMeta['slug'])];
}

$extraSchema = akazinet_item_list(
    array_map(static function ($g) {
        return ['name' => $g['title'], 'url' => '/pages/guide-detail?slug=' . rawurlencode($g['slug'])];
    }, $guides),
    $category !== '' ? $category . ' career guides' : 'Career guides'
);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<section class="bg-gradient-to-br from-primary to-primary-dark py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav aria-label="Breadcrumb" class="mb-4">
            <ol class="flex items-center gap-2 text-sm text-gray-300">
                <li><a href="/" class="hover:text-white transition-colors">Home</a></li>
                <li aria-hidden="true">/</li>
                <li class="text-white font-medium">Career Guides</li>
            </ol>
        </nav>
        <span class="text-accent font-semibold text-sm uppercase tracking-wider">Free resources</span>
        <h1 class="text-3xl md:text-4xl font-bold text-white mt-1"><?= sanitize($pageTitle) ?></h1>
        <p class="text-gray-300 mt-3 max-w-2xl leading-relaxed">
            <?= sanitize($meta_description) ?>
        </p>
        <?php if ($totalPublished > 0): ?>
        <p class="text-gray-400 text-sm mt-4">
            <?= (int)$totalPublished ?> guide<?= $totalPublished === 1 ? '' : 's' ?> across
            <?= count($categoryCounts) ?> categor<?= count($categoryCounts) === 1 ? 'y' : 'ies' ?>. No signup, no cost.
        </p>
        <?php endif; ?>
    </div>
</section>

<section class="py-12 md:py-16 bg-brand-gray">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <?php if ($categorySlug === ''): ?>
        <!-- Topic map. Replaces the old bare chip row, which was built from
             GROUP BY category and therefore only ever listed topics that
             already had a published guide. This lists every topic the section
             covers, with a sentence of context for each, so a reader can see
             where their question belongs before they start reading. -->
        <div class="mb-12">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <span class="text-accent font-semibold text-sm uppercase tracking-wider">Browse by topic</span>
                    <h2 class="text-2xl font-bold text-gray-900 mt-1">What would you like help with?</h2>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach (akazinet_guide_categories() as $entry): ?>
                <?php $count = (int)($categoryCounts[$entry['slug']] ?? 0); ?>
                <a href="/pages/guides?category=<?= urlencode($entry['slug']) ?>"
                   class="group flex flex-col bg-white rounded-xl border p-5 transition-all <?= $count > 0 ? 'border-brand-border hover:border-primary hover:shadow-md' : 'border-gray-200' ?>"
                   style="<?= $count > 0 ? '' : 'border-style:dashed;' ?>">
                    <span class="flex items-center justify-between mb-2">
                        <span class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                            <i class="fas <?= sanitize($entry['icon']) ?> text-primary text-sm"></i>
                        </span>
                        <span class="text-xs text-gray-400"><?= $count > 0 ? $count . ' guide' . ($count === 1 ? '' : 's') : 'Coming soon' ?></span>
                    </span>
                    <span class="font-semibold text-gray-900 group-hover:text-primary transition-colors"><?= sanitize($entry['name']) ?></span>
                    <span class="text-sm text-gray-500 leading-relaxed mt-1"><?= sanitize($entry['description']) ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="mb-8 flex flex-wrap gap-2">
            <a href="/pages/guides" class="px-4 py-2 rounded-full text-sm font-medium transition-colors <?= $category === '' ? 'bg-primary text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-brand-border' ?>">
                All guides
            </a>
            <?php foreach (akazinet_guide_categories() as $entry): ?>
            <?php if ((int)($categoryCounts[$entry['slug']] ?? 0) === 0) { continue; } ?>
            <a href="/pages/guides?category=<?= urlencode($entry['slug']) ?>" class="px-4 py-2 rounded-full text-sm font-medium transition-colors <?= $categorySlug === $entry['slug'] ? 'bg-primary text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-brand-border' ?>">
                <?= sanitize($entry['name']) ?>
                <span class="text-gray-400">(<?= (int)$categoryCounts[$entry['slug']] ?>)</span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (empty($guides)): ?>
        <div class="bg-white rounded-xl border border-brand-border p-8 text-center">
            <i class="fas fa-book-open text-4xl text-gray-300 mb-4"></i>
            <?php if ($categoryMeta !== null): ?>
            <h2 class="text-xl font-bold text-gray-900 mb-2">Nothing published in <?= sanitize($categoryMeta['name']) ?> yet</h2>
            <p class="text-gray-600 max-w-2xl mx-auto mb-6"><?= sanitize($categoryMeta['description']) ?> This topic is part of the guide plan but the articles are still being written.</p>
            <?php else: ?>
            <h2 class="text-xl font-bold text-gray-900 mb-2">Guides are being written</h2>
            <p class="text-gray-500 mb-6">In the meantime, browse the live vacancies.</p>
            <?php endif; ?>
            <a href="/pages/guides" class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-800 font-semibold rounded-xl hover:bg-gray-100 transition-colors mr-3">
                See all topics
            </a>
            <a href="/jobs" class="inline-flex items-center px-6 py-3 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition-colors">
                Browse Jobs <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        <?php else: ?>

        <?php if ($category === '' && $page === 1 && !empty($featuredGuides)): ?>
        <div class="mb-12">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <span class="text-accent font-semibold text-sm uppercase tracking-wider">Start here</span>
                    <h2 class="text-2xl font-bold text-gray-900 mt-1">Featured Guides</h2>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($featuredGuides as $featured): ?>
                <?php render_guide_card($featured); ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($guides as $guide): ?>
            <?php render_guide_card($guide); ?>
            <?php endforeach; ?>
        </div>

        <?php require_once __DIR__ . '/../components/pagination.php'; ?>
        <?php render_pagination($pagination, '/pages/guides' . ($category !== '' ? '?category=' . urlencode($category) : '')); ?>
        <?php endif; ?>

        <?php render_adsense_unit('content', 'Advertisement'); ?>

        <?php if (!empty($relatedJobs)): ?>
        <div class="mt-12">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <span class="text-accent font-semibold text-sm uppercase tracking-wider">Live now</span>
                    <h2 class="text-2xl font-bold text-gray-900 mt-1">Open vacancies in Rwanda</h2>
                </div>
                <a href="/jobs" class="text-primary font-medium hover:text-accent transition-colors flex items-center">
                    View All <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($relatedJobs as $relatedJob): ?>
                <?php render_job_card($relatedJob); ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script src="/assets/js/main.js?v=4"></script>
</body>
</html>