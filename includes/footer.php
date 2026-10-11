<?php
$whatsappNumber = get_setting('whatsapp_number', '250780092252');
$whatsappGroupLink = get_setting('whatsapp_group_link', '');
$siteName = SITE_NAME;
        // Footer links are limited to categories that actually have a
        // published vacancy; empty categories would only lead to thin
        // "no jobs found" pages.
        $categories = $pdo->query("
            SELECT c.name, c.slug
            FROM categories c
            WHERE c.is_active = 1
              AND EXISTS (
                  SELECT 1 FROM jobs j
                  WHERE j.category_id = c.id
                    AND j.status = 'published'
                    AND j.is_active = 1
            AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
              )
            ORDER BY c.name
            LIMIT 10
        ")->fetchAll();
?>
<?php if (file_exists(__DIR__ . '/../components/ad-banner.php')) { require_once __DIR__ . '/../components/ad-banner.php'; } ?>
<?php render_auto_ads_bootstrap(); ?>
<footer class="bg-primary text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
            <div>
                <div class="flex items-center space-x-2 mb-4">
                    <img src="/assets/images/logo-icon-white.svg" alt="AkaziNet" class="w-10 h-10" />
                    <span class="text-2xl font-bold">Akazi<span class="text-accent">Net</span></span>
                </div>
                <p class="text-gray-300 text-sm leading-relaxed mb-6">Connecting Rwandans to career opportunities across the nation. Every listing is reviewed before publication, and you should always confirm details with the employer before sending documents.</p>
                <div class="flex space-x-3">
                    <?php
                    $socialLinks = [
                        'facebook_url'  => ['fab fa-facebook-f', 'Facebook'],
                        'twitter_url'   => ['fab fa-twitter', 'X (Twitter)'],
                        'linkedin_url'  => ['fab fa-linkedin-in', 'LinkedIn'],
                        'instagram_url' => ['fab fa-instagram', 'Instagram'],
                    ];
                    foreach ($socialLinks as $key => $socialDef):
                        $socialUrl = trim((string)get_setting($key, ''));
                        if ($socialUrl === '' || !preg_match('~^https://~i', $socialUrl)) {
                            continue;
                        }
                    ?>
                    <a href="<?= sanitize($socialUrl) ?>" target="_blank" rel="noopener noreferrer nofollow" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-accent transition-all duration-300" aria-label="AkaziNet on <?= $socialDef[1] ?>"><i class="<?= $socialDef[0] ?>"></i></a>
                    <?php endforeach; ?>
                    <?php $waFooterLink = trim((string)get_setting('whatsapp_group_link', '')); ?>
                    <?php if ($waFooterLink !== '' && preg_match('~^https://~i', $waFooterLink)): ?>
                    <a href="<?= sanitize($waFooterLink) ?>" target="_blank" rel="noopener noreferrer nofollow" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-accent transition-all duration-300" aria-label="AkaziNet on WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                <ul class="space-y-2 text-sm text-gray-300">
                    <li><a href="/" class="hover:text-accent transition-colors">Home</a></li>
                    <li><a href="/jobs" class="hover:text-accent transition-colors">Browse Jobs</a></li>
                    <li><a href="/pages/guides" class="hover:text-accent transition-colors">Career Guides</a></li>
                    <li><a href="/pages/categories" class="hover:text-accent transition-colors">Job Categories</a></li>
                    <li><a href="/pages/scholarships" class="hover:text-accent transition-colors">Scholarships</a></li>
                    <li><a href="/pages/about" class="hover:text-accent transition-colors">About Us</a></li>
                    <li><a href="/pages/contact" class="hover:text-accent transition-colors">Contact</a></li>
                    <li><a href="/pages/report-job" class="hover:text-accent transition-colors">Report a Suspicious Listing</a></li>
                    <?php if (!empty($whatsappGroupLink)): ?>
                    <li><a href="<?= sanitize($whatsappGroupLink) ?>" target="_blank" rel="noopener" class="hover:text-accent transition-colors"><i class="fab fa-whatsapp mr-1"></i> Join WhatsApp Group</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-4">Categories</h3>
                <ul class="space-y-2 text-sm text-gray-300">
                    <?php foreach ($categories as $cat): ?>
                    <li><a href="/jobs?category=<?= $cat['slug'] ?>" class="hover:text-accent transition-colors"><?= $cat['name'] ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-4">Contact Info</h3>
                <ul class="space-y-3 text-sm text-gray-300">
                    <li class="flex items-start space-x-3">
                        <i class="fab fa-whatsapp mt-0.5 text-green-400"></i>
                        <span>+250 780 092 252</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <i class="fas fa-envelope mt-0.5"></i>
                        <span>contact@akazinet.com</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <i class="fas fa-map-marker-alt mt-0.5"></i>
                        <span><?= sanitize(SITE_ADDRESS) ?></span>
                    </li>
                </ul>
                <div class="mt-6 space-y-3">
                    <a href="https://wa.me/<?= $whatsappNumber ?>" target="_blank" rel="noopener" class="inline-flex items-center px-4 py-2 bg-green-600 rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">
                        <i class="fab fa-whatsapp mr-2 text-lg"></i> Chat on WhatsApp
                    </a>
                    <?php if (!empty($whatsappGroupLink)): ?>
                    <div>
                        <a href="<?= sanitize($whatsappGroupLink) ?>" target="_blank" rel="noopener" class="inline-flex items-center px-4 py-2 bg-green-500 border border-green-400 rounded-lg text-sm font-medium hover:bg-green-600 transition-colors">
                            <i class="fas fa-users mr-2"></i> Join Our WhatsApp Group
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php render_adsense_unit('footer', 'Advertisement'); ?>
    </div>

    <div class="border-t border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col md:flex-row justify-between items-center text-sm text-gray-400">
            <p>&copy; <?= date('Y') ?> <?= $siteName ?>. All rights reserved.</p>
<div class="flex flex-wrap gap-x-4 gap-y-2 mt-3 md:mt-0">
                <a href="/pages/about" class="hover:text-white transition-colors">About</a>
                <a href="/pages/contact" class="hover:text-white transition-colors">Contact</a>
                <a href="/pages/disclaimer" class="hover:text-white transition-colors">Disclaimer</a>
                <a href="/pages/privacy" class="hover:text-white transition-colors">Privacy Policy</a>
                <a href="/pages/terms" class="hover:text-white transition-colors">Terms &amp; Conditions</a>
            </div>
        </div>
    </div>
</footer>

<?php if (!empty($whatsappGroupLink)): ?>
<a href="<?= sanitize($whatsappGroupLink) ?>" target="_blank" rel="noopener noreferrer" class="fixed bottom-6 left-6 z-50 inline-flex items-center gap-2 px-5 py-3 bg-green-500 text-white font-semibold rounded-full shadow-lg hover:bg-green-600 hover:shadow-xl transition-all duration-300 animate-bounce" style="animation-duration: 3s;">
    <i class="fab fa-whatsapp text-xl"></i>
    <span class="hidden sm:inline">Join WhatsApp Group</span>
</a>
<?php endif; ?>
