<?php
require_once __DIR__ . '/includes/header.php';

$recentJobs = $pdo->query("
    SELECT j.*, cat.name as category_name
    FROM jobs j
    JOIN categories cat ON j.category_id = cat.id
    ORDER BY j.created_at DESC LIMIT 5
")->fetchAll();

$recentMessages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
    <p class="text-gray-500 text-sm">Welcome back, <?= sanitize($adminName) ?>. Here's an overview of your platform.</p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Jobs</p>
                <p class="text-3xl font-bold text-gray-900 mt-1"><?= $totalJobs ?></p>
            </div>
            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                <i class="fas fa-briefcase text-primary text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Published</p>
                <p class="text-3xl font-bold text-green-600 mt-1"><?= $publishedJobs ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Expired</p>
                <p class="text-3xl font-bold text-red-500 mt-1"><?= $expiredJobs ?></p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-clock text-red-500 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Featured</p>
                <p class="text-3xl font-bold text-accent mt-1"><?= $featuredJobs ?></p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-star text-accent text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Categories</p>
                <p class="text-3xl font-bold text-gray-900 mt-1"><?= $totalCategories ?></p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-tags text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
    <a href="/admin/jobs?action=add" class="bg-primary text-white rounded-xl p-5 hover:bg-primary-dark transition-colors flex items-center space-x-3">
        <i class="fas fa-plus-circle text-2xl"></i>
        <div>
            <div class="font-semibold">Post New Job</div>
            <div class="text-sm text-green-200">Create a new vacancy</div>
        </div>
    </a>
    <a href="/admin/settings" class="bg-gray-800 text-white rounded-xl p-5 hover:bg-gray-900 transition-colors flex items-center space-x-3">
        <i class="fas fa-cog text-2xl"></i>
        <div>
            <div class="font-semibold">Settings</div>
            <div class="text-sm text-gray-300">Manage platform settings</div>
        </div>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Jobs -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-bold text-gray-900">Recent Jobs</h2>
            <a href="/admin/jobs" class="text-sm text-primary hover:text-accent transition-colors">View All</a>
        </div>
        <div class="divide-y divide-gray-50">
            <?php if (!empty($recentJobs)): ?>
            <?php foreach ($recentJobs as $rj): ?>
            <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                <div class="min-w-0 flex-1">
                    <p class="font-medium text-gray-800 text-sm truncate"><?= sanitize($rj['title']) ?></p>
                    <p class="text-xs text-gray-500 mt-0.5"><?= sanitize($rj['company_name']) ?> &middot; <?= sanitize($rj['category_name']) ?></p>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-xs text-gray-400"><i class="fas fa-eye mr-1"></i><?= (int)$rj['views'] ?></span>
                    <span class="px-2 py-0.5 text-[10px] font-medium rounded-full <?= $rj['status'] === 'published' ? 'bg-green-100 text-green-700' : ($rj['status'] === 'draft' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600') ?>">
                        <?= ucfirst($rj['status']) ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div class="p-8 text-center text-gray-400 text-sm">No jobs yet.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Messages -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-bold text-gray-900">Recent Messages</h2>
        </div>
        <div class="divide-y divide-gray-50">
            <?php if (!empty($recentMessages)): ?>
            <?php foreach ($recentMessages as $msg): ?>
            <div class="p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between mb-1.5">
                    <p class="font-medium text-gray-800 text-sm"><?= sanitize($msg['name']) ?></p>
                    <span class="text-xs text-gray-400"><?= date('M d, Y H:i', strtotime($msg['created_at'])) ?></span>
                </div>
                <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500 mb-2">
                    <a href="mailto:<?= sanitize($msg['email']) ?>" class="text-primary hover:underline"><i class="fas fa-envelope mr-1"></i><?= sanitize($msg['email']) ?></a>
                    <span><i class="fas fa-map-marker-alt mr-1"></i><?= sanitize($msg['location'] ?: 'Not provided') ?></span>
                    <?php if ($msg['subject']): ?>
                    <span class="font-medium"><i class="fas fa-tag mr-1"></i><?= sanitize($msg['subject']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="text-xs text-gray-600 bg-gray-50 rounded-lg p-3 max-h-40 overflow-y-auto whitespace-pre-wrap"><?= sanitize($msg['message']) ?></div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div class="p-8 text-center text-gray-400 text-sm">No messages yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
