<?php
/**
 * Seed the Career Guides section with original editorial content.
 *
 * Admin-only. Re-runnable: guides are matched on slug and updated in place, so
 * running it twice will not create duplicates.
 *
 * Usage: open /admin/seed-guides in a browser while logged in, then remove or
 * protect this file. It is safe to keep if your /admin area is password
 * protected, but deleting it after a successful run is cleaner.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/guide-taxonomy.php';
require_admin_login();

$guides = akazinet_seed_guides();

$upsert = $pdo->prepare("
    INSERT INTO guides
        (title, slug, excerpt, content, faq_json, sources_json, category, meta_title, meta_description, is_featured, read_minutes, status, published_at)
    VALUES
        (:title, :slug, :excerpt, :content, :faq_json, :sources_json, :category, :meta_title, :meta_description, :is_featured, :read_minutes, 'published', NOW())
    ON DUPLICATE KEY UPDATE
        title            = VALUES(title),
        excerpt          = VALUES(excerpt),
        content          = VALUES(content),
        faq_json         = VALUES(faq_json),
        sources_json     = VALUES(sources_json),
        category         = VALUES(category),
        meta_title       = VALUES(meta_title),
        meta_description = VALUES(meta_description),
        is_featured      = VALUES(is_featured),
        read_minutes     = VALUES(read_minutes),
        status           = 'published'
");

$created = 0;
$updated = 0;

foreach ($guides as $guide) {
    $exists = $pdo->prepare("SELECT id FROM guides WHERE slug = ?");
    $exists->execute([$guide['slug']]);
    $isNew = !$exists->fetch();

    $faq = [];
    foreach (($guide['faq'] ?? []) as $qa) {
        $question = trim((string)($qa['question'] ?? ''));
        $answer = trim((string)($qa['answer'] ?? ''));
        if ($question !== '' && $answer !== '') {
            $faq[] = ['question' => $question, 'answer' => $answer];
        }
    }

    $sources = [];
    foreach (($guide['sources'] ?? []) as $source) {
        $source = trim((string)$source);
        if ($source !== '') {
            $sources[] = $source;
        }
    }

    $upsert->execute([
        ':title'            => $guide['title'],
        ':slug'             => $guide['slug'],
        ':excerpt'          => $guide['excerpt'],
        ':content'          => $guide['content'],
        ':faq_json'         => $faq ? json_encode($faq, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ':sources_json'     => $sources ? json_encode($sources, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ':category'         => $guide['category'],
        ':meta_title'       => $guide['meta_title'],
        ':meta_description' => $guide['meta_description'],
        ':is_featured'      => $guide['is_featured'] ? 1 : 0,
        ':read_minutes'     => $guide['read_minutes'],
    ]);

    $isNew ? $created++ : $updated++;
}

// Report the spread across the taxonomy. A run where most categories still have
// zero guides is the signal that guides-content-extended.php needs more work,
// and it is visible here rather than only as empty headings on the index.
$perCategory = [];
foreach ($guides as $guide) {
    $slug = akazinet_guide_category_slug((string)($guide['category'] ?? ''));
    $perCategory[$slug] = ($perCategory[$slug] ?? 0) + 1;
}
$emptyCategories = [];
foreach (akazinet_guide_categories() as $entry) {
    if (!isset($perCategory[$entry['slug']])) {
        $emptyCategories[] = $entry['name'];
    }
}

// Make sure the guides index and the nav entry are reachable.
require_once __DIR__ . '/../includes/header.php';
?>
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl border border-brand-border shadow-sm p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Career Guides seeded</h1>
        <p class="text-gray-600 mb-6">
            <?= (int)$created ?> guide(s) created, <?= (int)$updated ?> updated.
        </p>

        <?php if ($created === 0 && $updated === 0): ?>
            <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-4 text-sm">
                Nothing was written. If the <code class="text-xs">guides</code> table does not exist yet, run
                <code class="text-xs">migration17.sql</code> first.
            </div>
        <?php else: ?>
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4 text-sm mb-6">
                Done. View the result at <a class="font-semibold underline" href="/pages/guides" target="_blank">/pages/guides</a>.
                Then delete this file.
            </div>
        <?php endif; ?>

        <ul class="space-y-2 text-sm text-gray-600">
            <?php foreach ($guides as $guide): ?>
            <li class="flex items-center">
                <i class="fas fa-file-alt text-primary mr-2"></i>
                <a class="hover:underline" href="/pages/guide-detail?slug=<?= urlencode($guide['slug']) ?>" target="_blank"><?= sanitize($guide['title']) ?></a>
                <span class="ml-2 text-xs text-gray-400">/ <?= sanitize(akazinet_guide_category_name((string)($guide['category'] ?? ''))) ?></span>
            </li>
            <?php endforeach; ?>
        </ul>

        <h2 class="text-lg font-bold text-gray-900 mt-8 mb-3">Category coverage</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
            <?php foreach (akazinet_guide_categories() as $entry): ?>
            <div class="flex items-center justify-between bg-brand-gray border border-brand-border rounded-lg px-3 py-2">
                <span class="text-gray-700"><?= sanitize($entry['name']) ?></span>
                <span class="font-semibold <?= isset($perCategory[$entry['slug']]) ? 'text-primary' : 'text-gray-400' ?>">
                    <?= (int)($perCategory[$entry['slug']] ?? 0) ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if ($emptyCategories): ?>
        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-4 text-sm mt-4">
            No guide yet for: <?= sanitize(implode(', ', $emptyCategories)) ?>.
            These topics still show on the guides index as "Coming soon".
        </div>
        <?php endif; ?>
    </div>
</div>
<?php
require_once __DIR__ . '/../includes/footer.php';

/**
 * Full seed corpus: the original guides plus the extended file, merged on slug.
 *
 * The merge is deliberately keyed on slug rather than array_merge, because both
 * files are edited by hand and a duplicated slug must never produce two rows
 * fighting over one guide-detail URL. Later files win, which lets a correction
 * in the extended file supersede an original without editing both.
 */
function akazinet_seed_guides()
{
    // Globbed so a new corpus file is picked up without editing this function.
    // Sorted for a stable merge order when a slug ever appears twice.
    $files = glob(__DIR__ . '/guides-content*.php');
    sort($files);

    $merged = [];
    foreach ($files as $file) {
        foreach ((array)require $file as $guide) {
            if (!isset($guide['slug']) || trim((string)$guide['slug']) === '') {
                continue;
            }
            $merged[(string)$guide['slug']] = $guide;
        }
    }

    // Normalise every category to a canonical taxonomy name so the index filter
    // and the card icons resolve, even if a category string drifts in a file.
    foreach ($merged as $guide) {
        $guide['category'] = akazinet_guide_category_name((string)($guide['category'] ?? ''));
        $merged[$guide['slug']] = $guide;
    }

    return array_values($merged);
}