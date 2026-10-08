<?php
require_once __DIR__ . '/../../config/database.php';
require_admin_login();

$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminPage = basename($_SERVER['PHP_SELF'], '.php');
$flash = get_flash();

$totalJobs = $pdo->query("SELECT COUNT(*) FROM jobs WHERE is_active = 1")->fetchColumn();
$publishedJobs = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'published' AND is_active = 1")->fetchColumn();
$expiredJobs = $pdo->query("SELECT COUNT(*) FROM jobs WHERE application_deadline IS NOT NULL AND application_deadline < NOW() AND is_active = 1")->fetchColumn();
$featuredJobs = $pdo->query("SELECT COUNT(*) FROM jobs WHERE is_featured = 1 AND status = 'published' AND is_active = 1")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories WHERE is_active = 1")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Dashboard | AkaziNet</title>
    <link rel="stylesheet" href="/assets/css/tailwind.css?v=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
</head>
<body class="font-sans bg-gray-50 antialiased">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="admin-sidebar w-64 bg-primary text-white flex-shrink-0 fixed h-full z-40" id="sidebar">
            <div class="p-6 border-b border-white/10">
                <a href="/admin/" class="flex items-center space-x-2">
                    <img src="/assets/images/logo-icon.svg" alt="AkaziNet" class="w-9 h-9" />
                    <div>
                        <span class="text-lg font-bold">AkaziNet</span>
                        <span class="block text-[10px] text-gray-400 -mt-0.5">Admin Panel</span>
                    </div>
                </a>
            </div>
            <nav class="p-4 space-y-1">
                <a href="/admin/" class="admin-nav-link <?= $adminPage === 'index' ? 'active' : '' ?>">
                    <i class="fas fa-th-large w-5"></i> <span>Dashboard</span>
                </a>
                <a href="/admin/jobs" class="admin-nav-link <?= $adminPage === 'jobs' ? 'active' : '' ?>">
                    <i class="fas fa-briefcase w-5"></i> <span>Manage Jobs</span>
                </a>
                <a href="/admin/categories" class="admin-nav-link <?= $adminPage === 'categories' ? 'active' : '' ?>">
                    <i class="fas fa-tags w-5"></i> <span>Categories</span>
                </a>
                <a href="/admin/announcements" class="admin-nav-link <?= $adminPage === 'announcements' ? 'active' : '' ?>">
                    <i class="fas fa-bullhorn w-5"></i> <span>Updates &amp; News</span>
                </a>
                <a href="/admin/banners" class="admin-nav-link <?= $adminPage === 'banners' ? 'active' : '' ?>">
                    <i class="fas fa-ad w-5"></i> <span>Ad Banners</span>
                </a>
                <a href="/admin/scholarships" class="admin-nav-link <?= $adminPage === 'scholarships' ? 'active' : '' ?>">
                    <i class="fas fa-graduation-cap w-5"></i> <span>Scholarships</span>
                </a>
                <a href="/admin/scholarship-listings" class="admin-nav-link <?= $adminPage === 'scholarship-listings' ? 'active' : '' ?>">
                    <i class="fas fa-list-ul w-5"></i> <span>Scholarship Listings</span>
                </a>
                <a href="/admin/guides" class="admin-nav-link <?= $adminPage === 'guides' ? 'active' : '' ?>">
                    <i class="fas fa-book-open w-5"></i> <span>Career Guides</span>
                </a>
                <a href="/admin/job-reports" class="admin-nav-link <?= $adminPage === 'job-reports' ? 'active' : '' ?>">
                    <i class="fas fa-flag w-5"></i> <span>Job Reports</span>
                </a>
                <a href="/admin/settings" class="admin-nav-link <?= $adminPage === 'settings' ? 'active' : '' ?>">
                    <i class="fas fa-cog w-5"></i> <span>Settings</span>
                </a>
                <div class="border-t border-white/10 my-3"></div>
                <a href="/" class="admin-nav-link" target="_blank">
                    <i class="fas fa-external-link-alt w-5"></i> <span>View Website</span>
                </a>
                <a href="/admin/logout" class="admin-nav-link text-red-300 hover:text-red-200 hover:bg-red-500/20">
                    <i class="fas fa-sign-out-alt w-5"></i> <span>Logout</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 ml-64">
            <!-- Top Bar -->
            <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between sticky top-0 z-30">
                <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-bars text-gray-600"></i>
                </button>
                <div class="hidden lg:block"></div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-500"><?= date('F d, Y') ?></span>
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-primary text-sm"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700"><?= sanitize($adminName) ?></span>
                    </div>
                </div>
            </header>

            <!-- Flash Message -->
            <?php if ($flash): ?>
            <div class="mx-6 mt-4 p-4 rounded-lg flex items-center <?= $flash['type'] === 'success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700' ?>">
                <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?> mr-2"></i>
                <?= $flash['message'] ?>
            </div>
            <?php endif; ?>

            <main class="p-6">
