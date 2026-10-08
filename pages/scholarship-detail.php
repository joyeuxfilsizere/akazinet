<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../components/whatsapp.php';

$type = $_GET['type'] ?? '';
$listingId = (int)($_GET['id'] ?? 0);
$validTypes = ['university', 'ngo'];

if ($listingId) {
    // The expiry rule is applied here as well as on the listing pages, so a
    // shared or bookmarked link to a closed scholarship stops resolving instead
    // of rendering a permanently dead "Application Closed" page.
    $stmt = $pdo->prepare("SELECT sl.*, s.title as category_title, s.icon, s.icon_bg, s.icon_color, s.description as category_description, s.link_url as category_slug FROM scholarship_listings sl JOIN scholarships s ON sl.scholarship_id = s.id WHERE sl.id = ? AND sl.is_active = 1 AND " . akazinet_not_expired_sql('sl', 'deadline'));
    $stmt->execute([$listingId]);
    $listing = $stmt->fetch();
    if (!$listing) redirect('/pages/scholarships');
    $scholarship = [
        'title' => $listing['category_title'],
        'description' => $listing['category_description'],
        'icon' => $listing['icon'],
        'icon_bg' => $listing['icon_bg'],
        'icon_color' => $listing['icon_color'],
        'link_url' => $listing['category_slug'],
    ];
} elseif (in_array($type, $validTypes)) {
    $stmt = $pdo->prepare("SELECT * FROM scholarships WHERE link_url = ? AND is_active = 1");
    $stmt->execute([$type]);
    $scholarship = $stmt->fetch();
    $listing = null;
    if (!$scholarship) redirect('/pages/scholarships');
} else {
    redirect('/pages/scholarships');
}

$pageTitle = $scholarship['title'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<style>
    .scholarship-hero {
        background: linear-gradient(135deg, #172d13 0%, #0D1F0A 100%);
        position: relative;
        overflow: hidden;
    }
    .scholarship-hero::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 500px;
        height: 500px;
        background: rgba(107, 183, 123, 0.08);
        border-radius: 50%;
    }
    .scholarship-banner {
        width: 100%;
        max-height: 500px;
        object-fit: contain;
        background: #f0f0f0;
        display: block;
    }
    .scholarship-content {
        font-size: 17px;
        line-height: 1.8;
        color: #374151;
    }
    .scholarship-content p {
        margin-bottom: 16px;
    }
    .scholarship-section-title {
        font-size: 20px;
        font-weight: 700;
        color: #172d13;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 3px solid #d76f30;
        display: inline-block;
    }
    .scholarship-list {
        list-style: none;
        padding: 0;
    }
    .scholarship-list li {
        padding: 10px 0 10px 32px;
        position: relative;
        font-size: 16px;
        line-height: 1.7;
        color: #374151;
        border-bottom: 1px solid #f3f4f6;
    }
    .scholarship-list li:last-child {
        border-bottom: none;
    }
    .scholarship-list li::before {
        content: '\f00c';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        left: 0;
        color: #172d13;
        font-size: 14px;
        top: 12px;
    }
    .program-list li::before {
        content: '\f19d';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        color: #d76f30;
    }
    .deadline-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        color: white;
        padding: 14px 24px;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 600;
    }
    .deadline-badge.active {
        background: linear-gradient(135deg, #172d13, #0D1F0A);
    }
    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 16px 32px;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }
    .action-btn-primary {
        background: linear-gradient(135deg, #172d13, #0D1F0A);
        color: white;
    }
    .action-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(23, 45, 19, 0.3);
        color: white;
    }
    .action-btn-whatsapp {
        background: linear-gradient(135deg, #25D366, #128C7E);
        color: white;
    }
    .action-btn-whatsapp:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(37, 211, 102, 0.3);
        color: white;
    }
    .info-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #e5e7eb;
        padding: 32px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .org-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #f0fdf4;
        color: #172d13;
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        border: 1px solid #bbf7d0;
    }
</style>

<?php if ($listing): ?>
<!-- SINGLE LISTING VIEW -->
<section class="scholarship-hero py-16 relative z-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <a href="/pages/scholarships" class="inline-flex items-center text-gray-300 hover:text-white text-sm mb-6 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Back to Scholarships
        </a>
        <h1 class="text-3xl md:text-5xl font-bold text-white mb-4 leading-tight"><?= sanitize($listing['title']) ?></h1>
        <div class="flex flex-wrap items-center gap-3 mt-4">
            <span class="org-badge">
                <i class="fas fa-building"></i> <?= sanitize($listing['organization']) ?>
            </span>
            <?php if ($listing['deadline']): ?>
            <span class="deadline-badge <?= strtotime($listing['deadline']) >= time() ? 'active' : '' ?>">
                <i class="fas fa-calendar-alt"></i>
                <?= strtotime($listing['deadline']) < time() ? 'Application Closed' : 'Deadline: ' . date('F d, Y', strtotime($listing['deadline'])) ?>
            </span>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if ($listing['banner_image']): ?>
<div class="bg-white">
    <div class="max-w-6xl mx-auto">
        <img src="/assets/uploads/<?= $listing['banner_image'] ?>" alt="<?= sanitize($listing['title']) ?>" class="scholarship-banner">
    </div>
</div>
<?php endif; ?>

<section class="py-12 md:py-16 bg-brand-gray">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <?php if ($listing['description']): ?>
                <div class="info-card">
                    <h2 class="scholarship-section-title">About This Scholarship</h2>
                    <div class="scholarship-content mt-4">
                        <?= nl2br(sanitize($listing['description'])) ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($listing['eligibility']): ?>
                <div class="info-card">
                    <h2 class="scholarship-section-title">Eligibility & Requirements</h2>
                    <div class="scholarship-content mt-4">
                        <?= nl2br(sanitize($listing['eligibility'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <div class="info-card text-center">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Ready to Apply?</h3>
                    <p class="text-gray-500 text-sm mb-6">Don't miss this opportunity. Apply before the deadline.</p>
                    <?php if ($listing['link_url']): ?>
                    <a href="<?= sanitize($listing['link_url']) ?>" target="_blank" rel="noopener noreferrer" class="action-btn action-btn-primary w-full justify-center mb-3">
                        <i class="fas fa-paper-plane"></i> Apply Now
                    </a>
                    <?php endif; ?>
                    <a href="https://wa.me/250786354884?text=<?= urlencode("Hello,\nI need help applying for this scholarship:\n" . $listing['title'] . "\nOrganization: " . $listing['organization'] . "\nThank you 🙏") ?>" target="_blank" rel="noopener noreferrer" class="action-btn action-btn-whatsapp w-full justify-center">
                        <i class="fab fa-whatsapp text-lg"></i> Get Help on WhatsApp
                    </a>
                </div>

                <div class="info-card">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Scholarship Details</h3>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-building text-primary text-sm"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wide">Organization</p>
                                <p class="text-sm font-semibold text-gray-900"><?= sanitize($listing['organization']) ?></p>
                            </div>
                        </div>
                        <?php if ($listing['deadline']): ?>
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-clock text-red-500 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wide">Deadline</p>
                                <p class="text-sm font-semibold <?= strtotime($listing['deadline']) < time() ? 'text-red-600' : 'text-gray-900' ?>">
                                    <?= $listing['deadline'] ? date('F d, Y', strtotime($listing['deadline'])) : 'Open' ?>
                                    <?php if (strtotime($listing['deadline']) < time()): ?>
                                    <span class="text-xs font-normal text-red-500">(Expired)</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-tag text-green-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wide">Category</p>
                                <p class="text-sm font-semibold text-gray-900"><?= sanitize($scholarship['title']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100/50 border border-green-200 rounded-xl p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fab fa-whatsapp text-white text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900">Need Help?</h4>
                            <p class="text-sm text-gray-600">We'll guide you through the application process.</p>
                        </div>
                    </div>
                    <a href="https://wa.me/250786354884?text=<?= urlencode("Hello,\nI need help applying for this scholarship:\n" . $listing['title'] . "\nThank you 🙏") ?>" target="_blank" rel="noopener noreferrer" class="action-btn action-btn-whatsapp w-full justify-center text-sm">
                        <i class="fab fa-whatsapp"></i> Chat on WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php else: ?>

<!-- CATEGORY LISTINGS VIEW -->
<section class="scholarship-hero py-16 relative z-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <a href="/pages/scholarships" class="inline-flex items-center text-gray-300 hover:text-white text-sm mb-6 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Back to Scholarships
        </a>
        <h1 class="text-3xl md:text-5xl font-bold text-white mb-4"><?= sanitize($scholarship['title']) ?></h1>
        <p class="text-gray-300 text-lg max-w-3xl"><?= sanitize($scholarship['description']) ?></p>
    </div>
</section>

<section class="py-12 md:py-16 bg-brand-gray">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php
        $listings = $pdo->prepare("SELECT * FROM scholarship_listings WHERE scholarship_id = ? AND is_active = 1 ORDER BY sort_order ASC, created_at DESC");
        $listings->execute([$scholarship['id']]);
        $listings = $listings->fetchAll();
        ?>

        <?php if (!empty($listings)): ?>
        <div class="space-y-6">
            <?php foreach ($listings as $ls): ?>
            <div class="info-card hover:shadow-lg transition-all duration-300 overflow-hidden">
                <?php if ($ls['banner_image']): ?>
                <div class="w-full bg-gray-50 flex items-center justify-center" style="max-height:400px; overflow:hidden;">
                    <img src="/assets/uploads/<?= $ls['banner_image'] ?>" alt="<?= sanitize($ls['title']) ?>" class="w-full object-contain" style="max-height:400px;">
                </div>
                <?php endif; ?>
                <div class="p-8">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                        <div class="flex-1">
                            <div class="flex items-start gap-4 mb-4">
                                <div class="w-14 h-14 <?= $scholarship['icon_bg'] ?> rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="<?= $scholarship['icon'] ?> <?= $scholarship['icon_color'] ?> text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900 mb-1"><?= sanitize($ls['title']) ?></h3>
                                    <p class="text-base text-primary font-semibold"><?= sanitize($ls['organization']) ?></p>
                                </div>
                            </div>
                            <div class="scholarship-content mt-4">
                                <?= nl2br(sanitize($ls['description'])) ?>
                            </div>
                            <?php if ($ls['eligibility']): ?>
                            <div class="mt-6">
                                <h4 class="scholarship-section-title">Eligibility</h4>
                                <div class="scholarship-content mt-2">
                                    <?= nl2br(sanitize($ls['eligibility'])) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if ($ls['deadline']): ?>
                            <div class="mt-4">
                                <span class="deadline-badge <?= strtotime($ls['deadline']) >= time() ? 'active' : '' ?> text-sm">
                                    <i class="fas fa-calendar-alt"></i>
                                    <?= strtotime($ls['deadline']) < time() ? 'Application Closed' : 'Deadline: ' . date('F d, Y', strtotime($ls['deadline'])) ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex flex-col gap-3 flex-shrink-0 md:w-56">
                            <?php if ($ls['link_url']): ?>
                            <a href="<?= sanitize($ls['link_url']) ?>" target="_blank" rel="noopener noreferrer" class="action-btn action-btn-primary justify-center">
                                <i class="fas fa-paper-plane"></i> Apply Now
                            </a>
                            <?php endif; ?>
                            <a href="https://wa.me/250786354884?text=<?= urlencode("Hello,\nI need help applying for this scholarship:\n" . $ls['title'] . "\nOrganization: " . $ls['organization'] . "\nThank you 🙏") ?>" target="_blank" rel="noopener noreferrer" class="action-btn action-btn-whatsapp justify-center">
                                <i class="fab fa-whatsapp"></i> Get Help
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-20 bg-white rounded-2xl border border-brand-border">
            <div class="w-20 h-20 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
                <i class="<?= $scholarship['icon'] ?> text-gray-300 text-3xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No scholarships available yet</h3>
            <p class="text-gray-500 mb-8 text-lg">Check back later for new opportunities.</p>
            <a href="/pages/scholarships" class="action-btn action-btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Scholarships
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script src="/assets/js/main.js"></script>
</body>
</html>
