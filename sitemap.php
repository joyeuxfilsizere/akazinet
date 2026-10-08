<?php
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/xml; charset=utf-8');

$baseUrl = rtrim(SITE_URL, '/');
$today = date('Y-m-d');

$urls = [];

$staticPages = [
    ['loc' => '/', 'priority' => '1.0', 'freq' => 'daily'],
    ['loc' => '/jobs', 'priority' => '0.9', 'freq' => 'daily'],
    ['loc' => '/pages/guides', 'priority' => '0.8', 'freq' => 'weekly'],
    ['loc' => '/pages/categories', 'priority' => '0.8', 'freq' => 'weekly'],
    ['loc' => '/pages/about', 'priority' => '0.6', 'freq' => 'monthly'],
    ['loc' => '/pages/report-job', 'priority' => '0.5', 'freq' => 'monthly'],
    ['loc' => '/pages/contact', 'priority' => '0.5', 'freq' => 'monthly'],
    ['loc' => '/pages/disclaimer', 'priority' => '0.3', 'freq' => 'yearly'],
    ['loc' => '/pages/privacy', 'priority' => '0.3', 'freq' => 'yearly'],
    ['loc' => '/pages/terms', 'priority' => '0.3', 'freq' => 'yearly'],
];

try {
    $scholarshipCount = (int)$pdo->query("SELECT COUNT(*) FROM scholarship_listings sl WHERE sl.is_active = 1 AND (sl.deadline IS NULL OR sl.deadline >= CURDATE())")->fetchColumn();
} catch (PDOException $e) {
    $scholarshipCount = 0;
}

// Only advertise the scholarship hub once it has listings, so we never submit
// an empty page to Google as an indexable URL.
if ($scholarshipCount > 0) {
    $staticPages[] = ['loc' => '/pages/scholarships', 'priority' => '0.7', 'freq' => 'weekly'];
}

foreach ($staticPages as $page) {
    $urls[] = $page + ['lastmod' => $today];
}

try {
    $jobs = $pdo->query("
        SELECT slug, created_at, updated_at
        FROM jobs
        WHERE status = 'published' AND is_active = 1 AND (application_deadline IS NULL OR application_deadline >= CURDATE())
        ORDER BY updated_at DESC
    ")->fetchAll();

    foreach ($jobs as $job) {
        $lastmod = !empty($job['updated_at']) ? $job['updated_at'] : $job['created_at'];
        $urls[] = [
            'loc' => '/job-details?slug=' . rawurlencode($job['slug']),
            'lastmod' => $lastmod ? date('Y-m-d', strtotime($lastmod)) : $today,
            'priority' => '0.8',
            'freq' => 'weekly',
        ];
    }

    $listings = $pdo->query("SELECT sl.id, sl.updated_at FROM scholarship_listings sl WHERE sl.is_active = 1 AND (sl.deadline IS NULL OR sl.deadline >= CURDATE()) ORDER BY updated_at DESC")->fetchAll();

    foreach ($listings as $listing) {
        $lastmod = !empty($listing['updated_at']) ? $listing['updated_at'] : null;
        $urls[] = [
            'loc' => '/pages/scholarship-detail?id=' . (int)$listing['id'],
            'lastmod' => $lastmod ? date('Y-m-d', strtotime($lastmod)) : $today,
            'priority' => '0.6',
            'freq' => 'weekly',
        ];
    }

    // Only list category pages that actually contain open vacancies, so the
    // sitemap never points Google at a filter with no results.
    $categories = $pdo->query("
        SELECT cat.slug, cat.updated_at, COUNT(j.id) AS job_count
        FROM categories cat
        JOIN jobs j ON j.category_id = cat.id
        WHERE cat.is_active = 1
          AND j.is_active = 1
    AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
          AND j.status = 'published'
        GROUP BY cat.id, cat.slug, cat.updated_at
        HAVING job_count > 0
        ORDER BY cat.updated_at DESC
    ")->fetchAll();

    foreach ($categories as $category) {
        $lastmod = !empty($category['updated_at']) ? $category['updated_at'] : null;
        $urls[] = [
            'loc' => '/jobs?category=' . rawurlencode($category['slug']),
            'lastmod' => $lastmod ? date('Y-m-d', strtotime($lastmod)) : $today,
            'priority' => '0.6',
            'freq' => 'weekly',
        ];
    }

    // Editorial guides are the site's original content, so they get a priority
    // above the listing pages and a real lastmod from the editorial timestamps.
    // Only published rows are advertised; drafts must never leak here.
    $guides = $pdo->query("
        SELECT slug, published_at, updated_at
        FROM guides
        WHERE status = 'published'
        ORDER BY is_featured DESC, published_at DESC
    ")->fetchAll();

    foreach ($guides as $guide) {
        $lastmod = !empty($guide['updated_at']) ? $guide['updated_at'] : $guide['published_at'];
        $urls[] = [
            'loc' => '/pages/guide-detail?slug=' . rawurlencode($guide['slug']),
            'lastmod' => $lastmod ? date('Y-m-d', strtotime($lastmod)) : $today,
            'priority' => '0.7',
            'freq' => 'monthly',
        ];
    }
} catch (PDOException $e) {
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $url): ?>
    <url>
        <loc><?= htmlspecialchars($baseUrl . $url['loc'], ENT_XML1, 'UTF-8') ?></loc>
        <lastmod><?= $url['lastmod'] ?></lastmod>
        <changefreq><?= $url['freq'] ?></changefreq>
        <priority><?= $url['priority'] ?></priority>
    </url>
<?php endforeach; ?>
</urlset>
