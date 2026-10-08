<?php
$pageTitle = 'Page Not Found';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<section class="py-20 bg-brand-gray min-h-[60vh] flex items-center">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="w-24 h-24 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
            <span class="text-5xl font-bold text-primary">404</span>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-3">Page Not Found</h1>
        <p class="text-gray-500 mb-8 max-w-md mx-auto">The page you're looking for doesn't exist or has been moved. Let's get you back on track.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/" class="px-6 py-3 bg-primary text-white font-medium rounded-xl hover:bg-primary-dark transition-colors">
                <i class="fas fa-home mr-2"></i> Go Home
            </a>
            <a href="/jobs" class="px-6 py-3 border-2 border-primary text-primary font-medium rounded-xl hover:bg-primary hover:text-white transition-all">
                <i class="fas fa-search mr-2"></i> Browse Jobs
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script src="/assets/js/main.js"></script>
</body>
</html>
