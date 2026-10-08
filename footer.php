<?php
$whatsappNumber = get_setting('whatsapp_number', '250780092252');
$whatsappGroupLink = get_setting('whatsapp_group_link', '');
$siteName = get_setting('site_name', 'AkaziNet');
$categories = $pdo->query("SELECT name, slug FROM categories WHERE is_active = 1 ORDER BY name LIMIT 10")->fetchAll();
?>
<footer class="bg-primary text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
            <div>
                <div class="flex items-center space-x-2 mb-4">
                    <img src="/assets/images/logo-icon-white.svg" alt="AkaziNet" class="w-10 h-10" />
                    <span class="text-2xl font-bold">Akazi<span class="text-accent">Net</span></span>
                </div>
                <p class="text-gray-300 text-sm leading-relaxed mb-6">Connecting Rwandans to verified career opportunities across the nation. Your next career move starts here.</p>
                <div class="flex space-x-3">
                    <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-accent transition-all duration-300" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-accent transition-all duration-300" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-accent transition-all duration-300" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-accent transition-all duration-300" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                <ul class="space-y-2 text-sm text-gray-300">
                    <li><a href="/" class="hover:text-accent transition-colors">Home</a></li>
                    <li><a href="/jobs" class="hover:text-accent transition-colors">Browse Jobs</a></li>
                    <li><a href="/pages/categories" class="hover:text-accent transition-colors">Job Categories</a></li>
                    <li><a href="/pages/scholarships" class="hover:text-accent transition-colors">Scholarships</a></li>
                    <li><a href="/pages/about" class="hover:text-accent transition-colors">About Us</a></li>
                    <li><a href="/pages/contact" class="hover:text-accent transition-colors">Contact</a></li>
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
                        <span>kazinetrwa@gmail.com</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <i class="fas fa-map-marker-alt mt-0.5"></i>
                        <span>Kigali, Rwanda</span>
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
    </div>

    <div class="border-t border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col md:flex-row justify-between items-center text-sm text-gray-400">
            <p>&copy; <?= date('Y') ?> <?= $siteName ?>. All rights reserved.</p>
            <div class="flex space-x-4 mt-3 md:mt-0">
                <a href="/pages/privacy" class="hover:text-white transition-colors">Privacy Policy</a>
                <a href="/pages/terms" class="hover:text-white transition-colors">Terms & Conditions</a>
            </div>
        </div>
    </div>
</footer>
