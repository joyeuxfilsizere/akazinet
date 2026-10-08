<?php
function get_public_announcements($limit = 5) {
    global $pdo;

    $limit = max(1, min(20, (int) $limit));

    try {
        $stmt = $pdo->prepare("
            SELECT id, title, body, announcement_type, link_url, published_at, created_at
            FROM announcements
            WHERE is_active = 1
              AND (published_at IS NULL OR published_at <= NOW())
            ORDER BY sort_order ASC, COALESCE(published_at, created_at) DESC, id DESC
            LIMIT {$limit}
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $exception) {
        return [];
    }
}

function site_announcement_type_details($type) {
    $types = [
        'news' => [
            'label' => 'News',
            'icon' => 'fas fa-newspaper',
        ],
        'update' => [
            'label' => 'Update',
            'icon' => 'fas fa-bullhorn',
        ],
        'communication' => [
            'label' => 'Communication',
            'icon' => 'fas fa-comment-dots',
        ],
    ];

    return $types[$type] ?? $types['update'];
}

function site_announcement_url($url) {
    $url = trim((string) $url);
    if ($url === '' || !preg_match('~^(https?://|mailto:|/)~i', $url)) {
        return '';
    }
    return $url;
}

function render_announcements_sidebar($limit = 5) {
    $announcements = get_public_announcements($limit);
?>
<aside class="announcement-sidebar bg-white rounded-xl border border-brand-border shadow-sm overflow-hidden lg:sticky lg:top-28">
    <div class="bg-primary px-5 py-5 text-white">
        <div class="flex items-center gap-2 text-accent-light text-xs font-semibold uppercase tracking-wider mb-2">
            <i class="fas fa-bullhorn" aria-hidden="true"></i>
            Stay Updated
        </div>
        <h2 class="text-xl font-bold">Updates &amp; News</h2>
        <p class="text-gray-300 text-sm mt-1">Important news and communication from AkaziNet.</p>
    </div>

    <?php if (!empty($announcements)): ?>
    <div class="divide-y divide-gray-100">
        <?php foreach ($announcements as $announcement): ?>
        <?php
        $typeDetails = site_announcement_type_details($announcement['announcement_type']);
        $announcementDate = !empty($announcement['published_at']) ? strtotime($announcement['published_at']) : strtotime($announcement['created_at']);
        $announcementDate = $announcementDate ? date('M d, Y', $announcementDate) : 'Recently';
        $announcementLink = site_announcement_url($announcement['link_url'] ?? '');
        ?>
        <article class="p-5">
            <div class="flex items-center justify-between gap-3 mb-3">
                <span class="announcement-type announcement-type--<?= sanitize($announcement['announcement_type'] ?? 'update') ?>">
                    <i class="<?= $typeDetails['icon'] ?>" aria-hidden="true"></i>
                    <?= sanitize($typeDetails['label']) ?>
                </span>
                <time class="text-xs text-gray-400"><?= sanitize($announcementDate) ?></time>
            </div>
            <h3 class="font-semibold text-gray-900 leading-snug"><?= sanitize($announcement['title']) ?></h3>
            <p class="text-sm text-gray-600 leading-relaxed mt-2"><?= nl2br(sanitize($announcement['body'])) ?></p>
            <?php if ($announcementLink): ?>
            <a href="<?= sanitize($announcementLink) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center text-primary text-sm font-semibold mt-3 hover:text-accent transition-colors">
                Read more <i class="fas fa-arrow-right ml-1.5 text-xs" aria-hidden="true"></i>
            </a>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="p-6 text-center">
        <div class="w-11 h-11 mx-auto mb-3 rounded-xl bg-primary/10 flex items-center justify-center">
            <i class="fas fa-bell text-primary" aria-hidden="true"></i>
        </div>
        <p class="text-sm text-gray-500">No updates have been published yet.</p>
    </div>
    <?php endif; ?>
</aside>
<?php
}
?>
