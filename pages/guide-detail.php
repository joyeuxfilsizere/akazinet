<?php
/**
 * Single career guide.
 *
 * URL: /pages/guide-detail?slug=...
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../components/ad-banner.php';
require_once __DIR__ . '/../components/guide-card.php';
require_once __DIR__ . '/../config/guide-taxonomy.php';
require_once __DIR__ . '/../components/job-card.php';
// Loaded before header.php because $extraSchema is built before <head> renders.
require_once __DIR__ . '/../includes/schema.php';

$slug = trim((string)($_GET['slug'] ?? ''));

if ($slug === '') {
    redirect('/pages/guides');
}

try {
    $stmt = $pdo->prepare("
        SELECT * FROM guides
        WHERE slug = ? AND status = 'published'
    ");
    $stmt->execute([$slug]);
    $guide = $stmt->fetch();
} catch (PDOException $e) {
    $guide = false;
}

if (!$guide) {
    redirect('/pages/guides');
}

$pdo->prepare("UPDATE guides SET views = views + 1 WHERE id = ?")->execute([$guide['id']]);

$pageTitle = !empty($guide['meta_title']) ? $guide['meta_title'] : $guide['title'];
$meta_description = !empty($guide['meta_description'])
    ? $guide['meta_description']
    : $guide['excerpt'];

// The index filters by taxonomy slug, so every link back into /pages/guides has
// to carry the slug. The stored category may be a legacy name ("Job Search"),
// so resolve the slug through the taxonomy rather than lowercasing the name.
//
// Read from $guide, not a free-floating $category: akazinet_guide_category_slug()
// declares a non-nullable string parameter, so passing an undefined variable
// raises a TypeError, which is a fatal and renders the page as a blank 500.
$categorySlug = akazinet_guide_category_slug((string)($guide['category'] ?? ''));
$category     = akazinet_guide_category_name((string)($guide['category'] ?? ''));
$readMinutes = (int)($guide['read_minutes'] ?? 4);
$published = !empty($guide['published_at']) ? strtotime($guide['published_at']) : strtotime($guide['created_at']);

$breadcrumbs = [
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'Career Guides', 'url' => '/pages/guides'],
    ['name' => $category, 'url' => '/pages/guides?category=' . urlencode($categorySlug)],
    ['name' => $guide['title'], 'url' => '/pages/guide-detail?slug=' . rawurlencode($guide['slug'])],
];

// FAQ and sources are stored as JSON columns so the same data drives both the
// visible page and the FAQPage schema. Parsing them back out of the HTML body
// would be fragile and would mean editing content to change schema.
$faqItems = [];
if (!empty($guide['faq_json'])) {
    $decoded = json_decode((string)$guide['faq_json'], true);
    if (is_array($decoded)) {
        foreach ($decoded as $qa) {
            $question = trim((string)($qa['question'] ?? ''));
            $answer = trim((string)($qa['answer'] ?? ''));
            if ($question !== '' && $answer !== '') {
                $faqItems[] = ['question' => $question, 'answer' => $answer];
            }
        }
    }
}

$sourceItems = [];
if (!empty($guide['sources_json'])) {
    $decoded = json_decode((string)$guide['sources_json'], true);
    if (is_array($decoded)) {
        foreach ($decoded as $source) {
            $source = trim((string)$source);
            if ($source !== '') {
                $sourceItems[] = $source;
            }
        }
    }
}

// Article and FAQPage are emitted as one @graph so a single ld+json block
// carries both, rather than two separate scripts competing for the same node.
$faqSchema = akazinet_faq($faqItems);
if (!empty($faqSchema)) {
    $extraSchema = [
        '@context' => 'https://schema.org',
        '@graph'   => [akazinet_article($guide), $faqSchema],
    ];
} else {
    $extraSchema = akazinet_article($guide);
}

// Related guides, same category first.
//
// Match on every stored name that maps to this taxonomy entry, not just the
// canonical one. A guide seeded under a legacy name ("Job Search") should still
// surface its siblings, and the sibling query must also exclude legacy names -
// otherwise the "same category" and "different category" branches disagree and
// the same guide can be returned twice.
$categoryNames = [$category];
foreach (akazinet_guide_category_aliases() as $legacyName => $legacySlug) {
    if ($legacySlug === $categorySlug) {
        $categoryNames[] = $legacyName;
    }
}
$categoryPlaceholders = implode(',', array_fill(0, count($categoryNames), '?'));

try {
    $relatedStmt = $pdo->prepare("
        SELECT * FROM guides
        WHERE status = 'published' AND category IN ($categoryPlaceholders) AND id != ?
        ORDER BY published_at DESC
        LIMIT 3
    ");
    $relatedStmt->execute(array_merge($categoryNames, [$guide['id']]));
    $relatedGuides = $relatedStmt->fetchAll();

    if (count($relatedGuides) < 3) {
        $moreStmt = $pdo->prepare("
            SELECT * FROM guides
            WHERE status = 'published' AND category NOT IN ($categoryPlaceholders) AND id != ?
            ORDER BY is_featured DESC, published_at DESC
            LIMIT ?
        ");
        $moreStmt->execute(array_merge($categoryNames, [$guide['id'], 3 - count($relatedGuides)]));
        $relatedGuides = array_merge($relatedGuides, $moreStmt->fetchAll());
    }
} catch (PDOException $e) {
    $relatedGuides = [];
}

try {
    $jobsStmt = $pdo->query("
        SELECT j.*, cat.name AS category_name
        FROM jobs j
        JOIN categories cat ON j.category_id = cat.id
        WHERE j.status = 'published' AND j.is_active = 1 AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
        ORDER BY j.is_featured DESC, j.created_at DESC
        LIMIT 3
    ");
    $relatedJobs = $jobsStmt->fetchAll();
} catch (PDOException $e) {
    $relatedJobs = [];
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<article class="bg-white">
    <header class="bg-gradient-to-br from-primary to-primary-dark py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav aria-label="Breadcrumb" class="mb-5">
                <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-300">
                    <li><a href="/" class="hover:text-white transition-colors">Home</a></li>
                    <li aria-hidden="true">/</li>
                    <li><a href="/pages/guides" class="hover:text-white transition-colors">Career Guides</a></li>
                    <li aria-hidden="true">/</li>
                    <li><a href="/pages/guides?category=<?= urlencode($categorySlug) ?>" class="hover:text-white transition-colors"><?= sanitize($category) ?></a></li>
                </ol>
            </nav>

            <span class="inline-flex items-center text-xs font-semibold text-accent bg-white/10 px-3 py-1 rounded-full mb-4">
                <i class="fas fa-book-open mr-1.5"></i> <?= sanitize($category) ?>
            </span>

            <h1 class="text-3xl md:text-4xl font-bold text-white leading-tight"><?= sanitize($guide['title']) ?></h1>

            <?php if (!empty($guide['excerpt'])): ?>
            <p class="text-gray-300 mt-4 text-lg leading-relaxed"><?= sanitize($guide['excerpt']) ?></p>
            <?php endif; ?>

            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 mt-6 text-sm text-gray-300">
                <span><i class="fas fa-clock mr-1.5"></i> <?= (int)$readMinutes ?> min read</span>
                <?php if ($published): ?>
                <span><i class="fas fa-calendar mr-1.5"></i> Published <?= date('F d, Y', $published) ?></span>
                <?php endif; ?>
                <span><i class="fas fa-user-tie mr-1.5"></i> AkaziNet Editorial</span>
            </div>
        </div>
    </header>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <div class="prose prose-gray max-w-none akazinet-prose">
            <?= render_rich_text($guide['content']) ?>
        </div>

        <?php if (!empty($sourceItems)): ?>
        <aside class="mt-10 p-5 bg-brand-gray rounded-xl border border-gray-200" aria-labelledby="guide-sources-heading">
            <h2 id="guide-sources-heading" class="text-sm font-bold uppercase tracking-wider text-gray-500 flex items-center gap-2">
                <i class="fas fa-book text-primary"></i> Sources
            </h2>
            <p class="text-sm text-gray-500 mt-2 mb-3">
                Figures and legal references in this guide come from the following. Salary and market ranges are indicative and should be confirmed against the specific employer.
            </p>
            <ul class="space-y-2 text-sm text-gray-700">
                <?php foreach ($sourceItems as $source): ?>
                <li class="flex gap-2">
                    <i class="fas fa-angle-right text-accent mt-1 shrink-0"></i>
                    <span><?= sanitize($source) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </aside>
        <?php endif; ?>

        <?php if (!empty($faqItems)): ?>
        <section class="mt-12" aria-labelledby="guide-faq-heading">
            <h2 id="guide-faq-heading" class="text-2xl font-bold text-gray-900 mb-2">Frequently asked questions</h2>
            <p class="text-gray-600 mb-6">Short answers to the questions readers ask most about this topic.</p>

            <div class="space-y-3">
                <?php foreach ($faqItems as $i => $faqItem): ?>
                <details class="group border border-gray-200 rounded-lg bg-white open:shadow-sm open:border-gray-300 transition-shadow"
                         <?= $i === 0 ? 'open' : '' ?>>
                    <summary class="flex items-start justify-between gap-4 cursor-pointer list-none px-5 py-4 font-semibold text-gray-900 hover:text-primary transition-colors">
                        <span><?= sanitize($faqItem['question']) ?></span>
                        <i class="fas fa-chevron-down text-gray-400 mt-1 shrink-0 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <div class="px-5 pb-4 text-gray-700 leading-relaxed">
                        <?= render_rich_text($faqItem['answer']) ?>
                    </div>
                </details>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <div class="mt-12 pt-8 border-t border-gray-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"></command>
            <p class="text-sm text-gray-500">
                Found something useful? Share it with someone job hunting.
            </p>
            <div class="flex flex-wrap gap-2">
                <button type="button" data-copy-url="<?= SITE_URL . $_SERVER['REQUEST_URI'] ?>" class="copy-link-btn inline-flex items-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-link mr-2"></i> Copy link
                </button>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL . $_SERVER['REQUEST_URI']) ?>" target="_blank" rel="noopener" class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fab fa-facebook-f mr-2"></i> Facebook
                </a>
                <a href="https://wa.me/?text=<?= urlencode($guide['title'] . "\n" . SITE_URL . $_SERVER['REQUEST_URI']) ?>" target="_blank" rel="noopener" class="inline-flex items-center px-4 py-2.5 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fab fa-whatsapp mr-2"></i> WhatsApp
                </a>
            </div>
        </div>

        <?php render_adsense_unit('content', 'Advertisement'); ?>
    </div>
</article>

<section class="py-14 bg-brand-gray">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <?php if (!empty($relatedJobs)): ?>
        <div class="mb-14">
            <div class="flex items-end justify-between mb-6">
                <div>
                    <span class="text-accent font-semibold text-sm uppercase tracking-wider">Ready to apply?</span>
                    <h2 class="text-2xl font-bold text-gray-900 mt-1">Live vacancies in Rwanda</h2>
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

        <?php if (!empty($relatedGuides)): ?>
        <div>
            <div class="flex items-end justify-between mb-6">
                <div>
                    <span class="text-accent font-semibold text-sm uppercase tracking-wider">Keep reading</span>
                    <h2 class="text-2xl font-bold text-gray-900 mt-1">More career guides</h2>
                </div>
                <a href="/pages/guides" class="text-primary font-medium hover:text-accent transition-colors flex items-center">
                    All Guides <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($relatedGuides as $relatedGuide): ?>
                <?php render_guide_card($relatedGuide); ?>
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