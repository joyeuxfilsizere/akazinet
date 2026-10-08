<?php
$pageTitle = 'Job Categories';
$meta_description = 'Explore all job categories on AkaziNet, from accounting and finance to IT, engineering, healthcare and teaching. Find vacancies in the field you want to work in.';
require_once __DIR__ . '/../config/database.php';

$categories = $pdo->query("
    SELECT cat.*, COUNT(j.id) as active_jobs
    FROM categories cat
    LEFT JOIN jobs j ON j.category_id = cat.id AND j.status = 'published' AND j.is_active = 1 AND " . akazinet_not_expired_sql('j', 'application_deadline') . "
    WHERE cat.is_active = 1
    GROUP BY cat.id
    ORDER BY cat.name
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<section class="bg-gradient-to-br from-primary to-primary-dark py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">Job Categories</h1>
        <p class="text-gray-300 text-lg">Explore <?= count($categories) ?> categories of job opportunities</p>
    </div>
</section>

<section class="py-12 md:py-20 bg-brand-gray">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($categories as $cat):
                // Categories with no published vacancy are rendered as inert
                // cards rather than links. A link would only lead to an empty
                // "no jobs found" page, which is thin content for both the
                // visitor and a crawler.
                $hasJobs = (int)$cat['active_jobs'] > 0;
                $tag = $hasJobs ? 'a' : 'div';
                $attrs = $hasJobs
                    ? ' href="/jobs?category=' . sanitize($cat['slug']) . '"'
                    : ' aria-disabled="true"';
                $cardClass = 'bg-white rounded-xl p-6 border border-brand-border'
                    . ($hasJobs
                        ? ' hover:shadow-lg hover:-translate-y-1 hover:border-primary transition-all duration-300 group'
                        : ' opacity-60');
                $desc = trim((string)($cat['description'] ?? ''));
                $descHtml = $desc !== '' ? sanitize(mb_strimwidth($desc, 0, 80, '...')) : '';
            ?>
            <<?= $tag . $attrs ?> class="<?= $cardClass ?>">
                <div class="w-14 h-14 mb-4 bg-primary/5 rounded-xl flex items-center justify-center group-hover:bg-primary transition-all duration-300">
                    <i class="fas fa-<?= sanitize($cat['icon']) ?> text-primary text-xl group-hover:text-white transition-colors"></i>
                </div>
                <h3 class="font-bold text-gray-900 group-hover:text-primary transition-colors mb-1"><?= sanitize($cat['name']) ?></h3>
                <p class="text-sm text-gray-500 mb-3 line-clamp-2"><?= $descHtml ?></p>
                <span class="inline-flex items-center text-xs font-medium text-primary bg-primary/5 px-3 py-1 rounded-full">
                    <?= (int)$cat['active_jobs'] ?> job<?= (int)$cat['active_jobs'] !== 1 ? 's' : '' ?>
                </span>
            </<?= $tag ?>>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script src="/assets/js/main.js"></script>
</body>
</html>
