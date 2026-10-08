<?php
$pageTitle = 'Scholarships';
$meta_description = 'Find scholarships and grants for university, college and exchange students in Rwanda and abroad, with eligibility details, deadlines and application links.';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$allListings = $pdo->prepare("
    SELECT sl.*, s.title as category_title, s.icon, s.icon_bg, s.icon_color, s.link_url as category_slug
    FROM scholarship_listings sl
    JOIN scholarships s ON sl.scholarship_id = s.id
    WHERE sl.is_active = 1
      AND " . akazinet_not_expired_sql('sl', 'deadline') . "
    ORDER BY sl.sort_order ASC, sl.created_at DESC
");
$allListings->execute();
$allListings = $allListings->fetchAll();
?>

<style>
    .sch-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .sch-card:hover {
        box-shadow: 0 12px 40px rgba(0,0,0,0.08);
        transform: translateY(-4px);
    }
    .sch-banner {
        width: 100%;
        height: 240px;
        object-fit: contain;
        background: #f8f9fa;
        display: block;
    }
    .sch-deadline {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 600;
    }
    .sch-deadline.active { background: #f0fdf4; color: #16a34a; }
    .sch-deadline.expired { background: #fef2f2; color: #dc2626; }
    .sch-cta {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
    }
    .sch-cta-green { background: #172d13; color: white; }
    .sch-cta-green:hover { background: #0D1F0A; color: white; transform: translateY(-1px); }
    .sch-cta-wa { background: #25D366; color: white; }
    .sch-cta-wa:hover { background: #128C7E; color: white; transform: translateY(-1px); }
</style>

<section class="scholarship-hero py-16" style="background: linear-gradient(135deg, #172d13 0%, #0D1F0A 100%);">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <h1 class="text-3xl md:text-5xl font-bold text-white mb-4">Scholarships</h1>
        <p class="text-gray-300 text-lg max-w-2xl mx-auto">Discover scholarship opportunities for Rwandan students and professionals. Your future starts here.</p>
    </div>
</section>

<section class="py-12 md:py-16 bg-brand-gray">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <?php if (!empty($allListings)): ?>
        <div class="space-y-8">
            <?php foreach ($allListings as $ls): ?>
            <a href="/pages/scholarship-detail?id=<?= $ls['id'] ?>" class="sch-card block">
                <?php if ($ls['banner_image']): ?>
                <img src="/assets/uploads/<?= $ls['banner_image'] ?>" alt="<?= sanitize($ls['title']) ?>" class="sch-banner">
                <?php endif; ?>
                <div class="p-8">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="sch-deadline <?= $ls['deadline'] && strtotime($ls['deadline']) >= time() ? 'active' : 'expired' ?>">
                                    <i class="fas fa-calendar-alt"></i>
                                    <?= $ls['deadline'] ? (strtotime($ls['deadline']) < time() ? 'Closed' : 'Deadline: ' . date('M d, Y', strtotime($ls['deadline']))) : 'Open' ?>
                                </span>
                                <span class="text-xs text-gray-400 bg-gray-100 px-3 py-1 rounded-full"><?= sanitize($ls['category_title']) ?></span>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-2"><?= sanitize($ls['title']) ?></h2>
                            <p class="text-primary font-semibold text-base mb-3"><?= sanitize($ls['organization']) ?></p>
                            <p class="text-gray-500 text-base leading-relaxed line-clamp-3"><?= sanitize(mb_strimwidth($ls['description'], 0, 250, '...')) ?></p>
                        </div>
                        <div class="flex flex-col gap-2 flex-shrink-0">
                            <span class="sch-cta sch-cta-green justify-center">
                                <i class="fas fa-arrow-right"></i> View Details
                            </span>
                            <span class="sch-cta sch-cta-wa justify-center" onclick="event.stopPropagation(); event.preventDefault(); window.open('https://wa.me/250786354884?text=<?= urlencode("Hello,\nI need help applying for this scholarship:\n" . $ls['title'] . "\nThank you 🙏") ?>','_blank')">
                                <i class="fab fa-whatsapp"></i> Get Help
                            </span>
                        </div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php else: ?>
        <div class="bg-white rounded-2xl border border-brand-border p-8 md:p-12 mb-12">
            <div class="text-center mb-10">
                <div class="w-20 h-20 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-graduation-cap text-gray-300 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No open scholarships listed right now</h3>
                <p class="text-gray-500 text-lg max-w-2xl mx-auto">We publish scholarships as soon as they are verified and open. In the meantime, the guidance below explains the main types of funding available to Rwandan students and how to apply for them successfully.</p>
            </div>

            <h4 class="font-bold text-gray-900 mb-3">Types of funding worth searching for</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10 text-left">
                <div class="bg-brand-gray rounded-xl p-5">
                    <h5 class="font-semibold text-gray-900 mb-2">Government scholarship</h5>
                    <p class="text-sm text-gray-500 leading-relaxed">Awards from the Ministry of Education for students meeting academic thresholds. Usually applied for through your institution rather than individually.</p>
                </div>
                <div class="bg-brand-gray rounded-xl p-5">
                    <h5 class="font-semibold text-gray-900 mb-2">Full university scholarship</h5>
                    <p class="text-sm text-gray-500 leading-relaxed">Covers tuition in full for the duration of a degree, sometimes including accommodation and a living allowance.</p>
                </div>
                <div class="bg-brand-gray rounded-xl p-5">
                    <h5 class="font-semibold text-gray-900 mb-2">Partial funding and grants</h5>
                    <p class="text-sm text-gray-500 leading-relaxed">Supports a specific cost such as fees, research equipment, or a final-year project, rather than the whole programme.</p>
                </div>
                <div class="bg-brand-gray rounded-xl p-5">
                    <h5 class="font-semibold text-gray-900 mb-2">Exchange and fellowship programmes</h5>
                    <p class="text-sm text-gray-500 leading-relaxed">Short-term study, research, or professional placements abroad, often open to recent graduates as well as current students.</p>
                </div>
            </div>

            <h4 class="font-bold text-gray-900 mb-3">How to apply successfully</h4>
            <ol class="space-y-3 text-left max-w-3xl text-gray-600 leading-relaxed mb-8">
                <li class="flex items-start gap-3"><span class="flex-shrink-0 w-6 h-6 bg-primary text-white rounded-full flex items-center justify-center text-xs font-bold">1</span><span>Start early. Many schemes have a lead time of two to three months, and the strongest applications are usually the ones that are not rushed.</span></li>
                <li class="flex items-start gap-3"><span class="flex-shrink-0 w-6 h-6 bg-primary text-white rounded-full flex items-center justify-center text-xs font-bold">2</span><span>Check the eligibility line by line before spending effort. Academic thresholds, age limits, and nationality conditions rule out most applications.</span></li>
                <li class="flex items-start gap-3"><span class="flex-shrink-0 w-6 h-6 bg-primary text-white rounded-full flex items-center justify-center text-xs font-bold">3</span><span>Request recommendation letters well ahead of the deadline. A supervisor or referee who was not warned in advance will not write you a strong letter.</span></li>
                <li class="flex items-start gap-3"><span class="flex-shrink-0 w-6 h-6 bg-primary text-white rounded-full flex items-center justify-center text-xs font-bold">4</span><span>Write a personal statement that is specific to the scheme. Generic statements that could be sent to ten different funders are usually rejected.</span></li>
                <li class="flex items-start gap-3"><span class="flex-shrink-0 w-6 h-6 bg-primary text-white rounded-full flex items-center justify-center text-xs font-bold">5</span><span>Keep every supporting document in one folder, named clearly, and submit before the closing date rather than on it.</span></li>
            </ol>

            <div class="bg-primary/5 border border-primary/15 rounded-xl p-6 text-left">
                <h5 class="font-semibold text-gray-900 mb-2">Do you run a scholarship?</h5>
                <p class="text-sm text-gray-600 leading-relaxed">If you represent a foundation, university, embassy, or NGO offering funding to Rwandan students, <a href="/pages/contact" class="text-accent font-medium hover:underline">contact our team</a> with the eligibility criteria, benefits, closing date, and application link. We will review and publish it, usually within one working day.</p>
            </div>
        </div>
        <?php endif; ?>

        <div class="bg-gradient-to-r from-primary to-primary-dark rounded-2xl p-8 md:p-12 text-center text-white mt-12">
            <h2 class="text-2xl md:text-3xl font-bold mb-4">Tips for Scholarship Applications</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8 text-left max-w-3xl mx-auto">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-check-circle text-accent mt-0.5"></i>
                    <span>Start your search early and keep track of deadlines</span>
                </div>
                <div class="flex items-start space-x-3">
                    <i class="fas fa-check-circle text-accent mt-0.5"></i>
                    <span>Prepare strong recommendation letters</span>
                </div>
                <div class="flex items-start space-x-3">
                    <i class="fas fa-check-circle text-accent mt-0.5"></i>
                    <span>Write a compelling personal statement</span>
                </div>
                <div class="flex items-start space-x-3">
                    <i class="fas fa-check-circle text-accent mt-0.5"></i>
                    <span>Highlight leadership and community involvement</span>
                </div>
                <div class="flex items-start space-x-3">
                    <i class="fas fa-check-circle text-accent mt-0.5"></i>
                    <span>Apply to multiple scholarships to increase your chances</span>
                </div>
                <div class="flex items-start space-x-3">
                    <i class="fas fa-check-circle text-accent mt-0.5"></i>
                    <span>Proofread all application materials carefully</span>
                </div>
            </div>
        </div>

    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script src="/assets/js/main.js"></script>
</body>
</html>
