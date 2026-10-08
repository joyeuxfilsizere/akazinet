<?php
$pageTitle = 'Admin Login';
require_once __DIR__ . '/config/database.php';

if (is_admin_logged_in()) {
    redirect('/admin/');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Reject a request that does not carry the token issued with the form, so a
    // third-party site cannot force an admin's browser to submit this form.
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Your session expired. Please try again.';
    } elseif (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE (username = ? OR email = ?) AND is_active = 1");
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            // New identifier on privilege change, to close session fixation.
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?")->execute([$admin['id']]);
            redirect('/admin/');
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="min-h-screen bg-brand-gray flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center space-x-2 mb-6">
                <img src="/assets/images/logo.svg" alt="AkaziNet" class="h-12 w-auto" />
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Admin Login</h1>
            <p class="text-gray-500 mt-1">Sign in to manage job vacancies</p>
        </div>

        <div class="bg-white rounded-2xl p-8 border border-brand-border shadow-sm">
            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-600 rounded-lg p-4 mb-6 flex items-center text-sm">
                <i class="fas fa-exclamation-circle mr-2"></i> <?= $error ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Username or Email</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="username" value="<?= sanitize($username ?? '') ?>" required
                               class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all"
                               placeholder="Enter your username">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="password" name="password" required
                               class="w-full pl-11 pr-12 py-3 border border-gray-200 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all"
                               placeholder="Enter your password">
                        <button type="button" onclick="togglePassword(this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="w-full py-3 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition-all duration-300 hover:shadow-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i> Sign In
                </button>
            </form>
        </div>

        <div class="text-center mt-6">
            <a href="/" class="text-sm text-gray-500 hover:text-primary transition-colors">
                <i class="fas fa-arrow-left mr-1"></i> Back to AkaziNet
            </a>
        </div>
    </div>
</section>

<script src="/assets/js/main.js"></script>
<script>
function togglePassword(btn) {
    const input = btn.closest('.relative').querySelector('input');
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
</body>
</html>
