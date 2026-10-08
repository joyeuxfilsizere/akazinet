<?php
/**
 * Skeleton (shimmer placeholder) component.
 *
 * Two jobs on this site:
 *
 *  1. Reserve space for ad units. AdSense markup renders asynchronously and
 *     its final height is not known when the page is built, so an unfilled
 *     slot collapses to zero and every ad that arrives shoves the page
 *     content down. Reserving the slot's height up front removes that
 *     layout shift entirely, and keeps the "Advertisement" placement visible
 *     rather than leaving a hole.
 *
 *  2. Cover content that arrives late — images decoding, and job cards painted
 *     by any client-side rendering.
 *
 * Shimmer is CSS only, so nothing here depends on JavaScript to be readable.
 */

if (!defined('SKELETON_AD_HEIGHTS')) {
    define('SKELETON_AD_HEIGHTS', [
        'header'  => 90,
        'content' => 280,
        'sidebar' => 600,
        'footer'  => 90,
    ]);
}

if (!function_exists('skeleton_ad_height')) {
    function skeleton_ad_height(string $slotKey): int
    {
        return (int)(SKELETON_AD_HEIGHTS[$slotKey] ?? 250);
    }
}

if (!function_exists('skeleton_ad_height_css')) {
    /** Height as a CSS length, so it can be used inline. */
    function skeleton_ad_height_css(string $slotKey): string
    {
        return skeleton_ad_height($slotKey) . 'px';
    }
}

if (!function_exists('render_skeleton_lines')) {
    /**
     * @param int $count   Number of text lines to draw
     * @param int $lastPct Width percentage of the final line, so the block
     *                     ends ragged like real text instead of as a solid bar
     */
    function render_skeleton_lines(int $count = 3, int $lastPct = 62)
    {
        for ($i = 0; $i < $count; $i++) {
            $isLast = ($i === $count - 1);
            $width = $isLast ? max(28, min(100, $lastPct)) : 100;
            echo '<span class="skeleton skeleton-line" style="width:' . $width . '%" aria-hidden="true"></span>';
        }
    }
}

if (!function_exists('render_skeleton_block')) {
    function render_skeleton_block(string $height = '1rem', string $class = '')
    {
        echo '<span class="skeleton ' . sanitize($class) . '" style="height:' . sanitize($height) . '" aria-hidden="true"></span>';
    }
}

if (!function_exists('render_skeleton_circle')) {
    function render_skeleton_circle(string $size = '3rem')
    {
        echo '<span class="skeleton skeleton-circle" style="width:' . sanitize($size) . ';height:' . sanitize($size) . '" aria-hidden="true"></span>';
    }
}

if (!function_exists('render_ad_placeholder')) {
    /**
     * Reserve the height of an ad slot before the ad itself loads.
     *
     * The <ins> tag is nested inside so the ad lands in a box that already has
     * its final dimensions. assets/js/main.js watches for the ad filling the
     * box and drops the shimmer.
     *
     * @param string $slotKey One of the SKELETON_AD_HEIGHTS keys
     * @param string $label   Visible placement label
     */
    function render_ad_placeholder(string $slotKey = 'content', string $label = 'Advertisement', string $extraClass = '')
    {
        $height = skeleton_ad_height($slotKey);
        ?>
        <aside class="adsense-slot adsense-slot--reserved is-loading <?= sanitize($extraClass) ?>"
               data-ad-reserve="<?= sanitize($slotKey) ?>"
               style="min-height:<?= skeleton_ad_height_css($slotKey) ?>"
               aria-label="<?= sanitize($label) ?>">
            <span class="adsense-slot-label"><?= sanitize($label) ?></span>
            <div class="adsense-slot-frame" style="min-height:<?= skeleton_ad_height_css($slotKey) ?>">
                <span class="skeleton skeleton-block adsense-slot-shimmer" aria-hidden="true"></span>
            </div>
        </aside>
        <?php
    }
}

if (!function_exists('render_job_card_skeleton')) {
    /**
     * Placeholder shaped like job-card.php, for client-side rendered results.
     */
    function render_job_card_skeleton()
    {
        ?>
        <div class="job-card bg-white rounded-xl border border-brand-border p-6" aria-hidden="true">
            <div class="flex items-start space-x-4 mb-4">
                <?php render_skeleton_circle('3.5rem'); ?>
                <div class="flex-1 min-w-0 pt-1">
                    <?php render_skeleton_lines(2, 70); ?>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 mb-4">
                <?php
                $chipWidths = ['4.5rem', '5.5rem', '4rem'];
                foreach ($chipWidths as $chipWidth):
                ?>
                <span class="skeleton skeleton-chip" style="width:<?= sanitize($chipWidth) ?>"></span>
                <?php endforeach; ?>
            </div>
            <?php render_skeleton_lines(3, 80); ?>
        </div>
        <?php
    }
}

if (!function_exists('render_job_grid_skeleton')) {
    function render_job_grid_skeleton(int $cards = 6)
    {
        echo '<div class="grid grid-cols-1 md:grid-cols-2 gap-6" data-skeleton-grid>';
        for ($i = 0; $i < $cards; $i++) {
            render_job_card_skeleton();
        }
        echo '</div>';
    }
}

if (!function_exists('render_article_card_skeleton')) {
    function render_article_card_skeleton()
    {
        ?>
        <div class="bg-white rounded-xl border border-brand-border overflow-hidden" aria-hidden="true">
            <?php render_skeleton_block('11rem'); ?>
            <div class="p-6">
                <span class="skeleton skeleton-chip mb-3" style="width:5rem"></span>
                <?php render_skeleton_lines(2, 90); ?>
                <div class="mt-4"><?php render_skeleton_lines(3, 75); ?></div>
            </div>
        </div>
        <?php
    }
}