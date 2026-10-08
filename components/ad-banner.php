<?php
require_once __DIR__ . '/skeleton.php';

if (!defined('ADSENSE_CLIENT')) {
    define('ADSENSE_CLIENT', 'ca-pub-1376469203299537');
}

function adsense_slot_id($key) {
    $value = trim((string)get_setting('adsense_slot_' . $key, ''));
    return preg_match('/^\d{6,20}$/', $value) ? $value : '';
}

function adsense_slots_configured() {
    foreach (['header', 'content', 'sidebar', 'footer'] as $k) {
        if (adsense_slot_id($k) !== '') {
            return true;
        }
    }
    return false;
}

function render_auto_ads_bootstrap() {
    if (adsense_slots_configured()) {
        return;
    }
?>
<script>(adsbygoogle = window.adsbygoogle || []).push({google_ad_client: '<?= ADSENSE_CLIENT ?>', enable_page_level_ads: true});</script>
<?php
}

function render_adsense_unit($slotKey = 'content', $label = 'Advertisement', $extraClass = '') {
    $slot = adsense_slot_id($slotKey);
    if ($slot === '') {
        return;
    }

    // The slot's height is reserved up front so the ad landing later cannot
    // push the page content down. See components/skeleton.php.
    $reservedHeight = skeleton_ad_height_css($slotKey);
?>
<aside class="adsense-slot adsense-slot--reserved is-loading w-full my-6 <?= sanitize($extraClass) ?>"
       data-ad-reserve="<?= sanitize($slotKey) ?>"
       style="min-height:<?= $reservedHeight ?>"
       aria-label="<?= sanitize($label) ?>">
    <span class="adsense-slot-label"><?= sanitize($label) ?></span>
    <div class="adsense-slot-frame" style="min-height:<?= $reservedHeight ?>">
        <span class="skeleton skeleton-block adsense-slot-shimmer" aria-hidden="true"></span>
        <ins class="adsbygoogle"
             style="display:block"
             data-ad-client="<?= ADSENSE_CLIENT ?>"
             data-ad-slot="<?= sanitize($slot) ?>"
             data-ad-format="auto"
             data-full-width-responsive="true"></ins>
    </div>
    <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
</aside>
<?php
}

function render_ad_banner($position = 'content') {
    global $pdo;

    if (is_array($position)) {
        $placeholders = implode(',', array_fill(0, count($position), '?'));
        $stmt = $pdo->prepare("SELECT * FROM ad_banners WHERE is_active = 1 AND position IN ($placeholders) ORDER BY sort_order ASC");
        $stmt->execute($position);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM ad_banners WHERE is_active = 1 AND position = ? ORDER BY sort_order ASC");
        $stmt->execute([$position]);
    }

    $banners = $stmt->fetchAll();
    if (empty($banners)) return;

    foreach ($banners as $banner):
        if ($banner['type'] === 'adsense'):
            if (empty($banner['ad_code'])) continue;
?>
<div class="w-full my-4">
    <?= $banner['ad_code'] ?>
</div>
<?php
        else:
            if (empty($banner['image_path'])) continue;
            $tag = !empty($banner['link_url']) ? 'a' : 'div';
            $href = !empty($banner['link_url']) ? ' href="' . sanitize($banner['link_url']) . '" target="_blank" rel="noopener noreferrer"' : '';
?>
<div class="w-full my-4">
    <<?= $tag . $href ?> class="block relative rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300 group">
        <img src="/assets/uploads/<?= sanitize($banner['image_path']) ?>" alt="<?= sanitize($banner['title']) ?>" class="w-full h-auto max-h-40 object-cover group-hover:scale-[1.01] transition-transform duration-300">
        <span class="absolute top-2 right-2 bg-black/40 text-white text-[10px] font-medium px-2 py-0.5 rounded-full">Ad</span>
    <<?= $tag ?>>
</div>
<?php
        endif;
    endforeach;
}
