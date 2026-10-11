<?php
$pageTitle = 'Remote Jobs';
$meta_description = 'Remote and work-from-home job opportunities on AkaziNet are coming soon. We are building a dedicated section for remote roles in Rwanda and beyond.';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<section class="bg-gradient-to-br from-primary to-primary-dark py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">Remote Jobs</h1>
        <p class="text-gray-300">Work-from-home and remote roles, all in one place.</p>
    </div>
</section>

<section class="py-12 md:py-20 bg-white">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-2xl p-8 md:p-12 text-center" style="background-color:#eff6ff;border:1px solid #bfdbfe;">
            <div class="w-16 h-16 mx-auto mb-5 rounded-full flex items-center justify-center" style="background-color:#dbeafe;">
                <i class="fas fa-laptop-house text-2xl" style="color:#1d4ed8"></i>
            </div>
            <span class="inline-block text-xs font-bold uppercase tracking-wide px-3 py-1 rounded-full mb-4" style="color:#fff;background-color:#2563eb">Coming soon</span>
            <h2 class="text-2xl font-bold text-gray-900 mb-3">We're building a Remote Jobs section</h2>
            <p class="text-gray-600 leading-relaxed mb-2">Soon you'll be able to browse remote and work-from-home opportunities alongside our regular job listings.</p>
            <p class="text-gray-500 text-sm">In the meantime, <a href="/jobs" class="text-primary font-medium hover:underline">browse all current jobs</a> or <a href="/pages/contact" class="text-primary font-medium hover:underline">get in touch</a>.</p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
