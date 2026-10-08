<?php
/**
 * Career guide card.
 *
 * @param array $guide Row from the guides table
 * @param bool  $compact Render a single-line list item instead of a card
 */
require_once __DIR__ . '/../config/guide-taxonomy.php';

function render_guide_card(array $guide, bool $compact = false)
{
    $url = '/pages/guide-detail?slug=' . rawurlencode($guide['slug']);
    // Display the canonical category name and icon rather than whatever string
    // was stored, so a guide seeded under a legacy name ("Job Search") still
    // reads as "Finding Work" and matches the taxonomy menu.
    $category = akazinet_guide_category_name((string)($guide['category'] ?? ''));
    $categorySlug = akazinet_guide_category_slug((string)($guide['category'] ?? ''));
    $readMinutes = (int)($guide['read_minutes'] ?? 4);
    $published = !empty($guide['published_at']) ? strtotime($guide['published_at']) : strtotime($guide['created_at']);
    $icon = akazinet_guide_category_icon((string)($guide['category'] ?? ''));
    ?>

    <?php if ($compact): ?>
    <a href="<?= $url ?>" class="flex items-start gap-4 group py-4 border-b border-gray-100">
        <span class="flex-shrink-0 w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
            <i class="fas <?= sanitize($icon) ?> text-primary text-sm"></i>
        </span>
        <span class="min-w-0">
            <span class="block font-semibold text-gray-900 group-hover:text-primary transition-colors"><?= sanitize($guide['title']) ?></span>
            <span class="block text-sm text-gray-500 mt-1"><?= sanitize($guide['excerpt']) ?></span>
        </span>
    </a>
    <?php else: ?>
    <article class="bg-white rounded-xl border border-brand-border overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group flex flex-col">
        <div class="h-1 bg-gradient-to-r from-primary" style="height:4px"></div>
        <div class="p-6 flex flex-col flex-1">
            <div class="flex items-center justify-between mb-3">
                <a href="/pages/guides?category=<?= urlencode($categorySlug) ?>"
                   class="inline-flex items-center text-xs font-semibold text-primary bg-primary/5 px-3 py-1 rounded-full">
                    <i class="fas <?= sanitize($icon) ?> mr-2"></i> <?= sanitize($category) ?>
                </a>
                <?php if (!empty($guide['is_featured'])): ?>
                <span class="inline-flex items-center text-xs font-semibold text-accent bg-accent/10 px-3 py-1 rounded-full">
                    <i class="fas fa-star mr-1 text-xs"></i> Featured
                </span>
                <?php endif; ?>
            </div>

            <h3 class="text-lg font-bold text-gray-900 leading-snug mb-2 group-hover:text-primary transition-colors">
                <a href="<?= $url ?>"><?= sanitize($guide['title']) ?></a>
            </h3>

            <p class="text-sm text-gray-500 leading-relaxed mb-4 flex-1"><?= sanitize($guide['excerpt']) ?></p>

            <div class="flex items-center justify-between pt-4 border-t border-gray-100 text-xs text-gray-400">
                <span><i class="fas fa-clock mr-1"></i> <?= (int)$readMinutes ?> min read</span>
                <?php if ($published): ?>
                <span><i class="fas fa-calendar mr-1"></i> <?= date('M d, Y', $published) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </article>
    <?php endif; ?>
<?php
}