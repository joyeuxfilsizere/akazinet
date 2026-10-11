<?php
/**
 * Report a suspicious listing.
 *
 * Public trust feature: job seekers can flag fake vacancies and scammers. The
 * footer, the disclaimer, and every job page link here.
 */

$pageTitle = 'Report a Job or Scam';
$meta_description = 'Report a fake vacancy, fraudulent recruiter, or misleading scholarship listing on AkaziNet. Reviewed by our team, and confirmed scams are removed.';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/schema.php';

$success = '';
$error = '';

$reportTypes = [
    'fake_job' => 'Fake job or does not exist',
    'scam_payment' => 'Asked me to pay money',
    'misleading' => 'Misleading job description',
    'expired' => 'Vacancy is closed or filled',
    'duplicate' => 'Duplicate listing',
    'inappropriate' => 'Discriminatory or inappropriate content',
    'wrong_info' => 'Incorrect information',
    'other' => 'Something else',
];

$prefillSlug = trim((string)($_GET['slug'] ?? ''));
$prefillTitle = trim((string)($_GET['title'] ?? ''));

$selectedJob = null;
if ($prefillSlug !== '') {
    try {
        $stmt = $pdo->prepare("SELECT id, title, slug, company_name FROM jobs WHERE slug = ? LIMIT 1");
        $stmt->execute([$prefillSlug]);
        $selectedJob = $stmt->fetch() ?: null;
    } catch (PDOException $e) {
        $selectedJob = null;
    }
    if ($selectedJob) {
        $prefillTitle = $selectedJob['title'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    $details = trim((string)($_POST['details'] ?? ''));
    $email = trim((string)($_POST['reporter_email'] ?? ''));
    $phone = trim((string)($_POST['reporter_phone'] ?? ''));
    $reportType = (string)($_POST['report_type'] ?? 'other');
    $jobSlug = trim((string)($_POST['job_slug'] ?? ''));
    $jobTitle = trim((string)($_POST['job_title'] ?? ''));
    $jobId = null;

    if (!is_string($csrfToken) || !verify_csrf($csrfToken)) {
        $error = 'Your session expired. Please try again.';
    } elseif (!array_key_exists($reportType, $reportTypes)) {
        $error = 'Please choose a reason.';
    } elseif (strlen($details) < 20) {
        $error = 'Please describe the problem in a little more detail (at least 20 characters).';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address, or leave it blank.';
    } else {
        if ($jobSlug !== '') {
            try {
                $jobStmt = $pdo->prepare("SELECT id FROM jobs WHERE slug = ? LIMIT 1");
                $jobStmt->execute([$jobSlug]);
                $found = $jobStmt->fetch();
                $jobId = $found ? (int)$found['id'] : null;
            } catch (PDOException $e) {
                $jobId = null;
            }
        }

        try {
            $insert = $pdo->prepare("
                INSERT INTO job_reports
                    (job_id, job_slug, job_title, report_type, details, reporter_email, reporter_phone, ip_address)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $insert->execute([
                $jobId,
                $jobSlug !== '' ? $jobSlug : null,
                $jobTitle !== '' ? mb_substr($jobTitle, 0, 200) : null,
                $reportType,
                $details,
                $email !== '' ? $email : null,
                $phone !== '' ? $phone : null,
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]);

            $adminEmail = get_setting('site_email', 'contact@akazinet.com');
            $mailSubject = 'New listing report on AkaziNet';
            $mailBody = "A job seeker reported a listing.\n\n"
                . "Reason: " . $reportTypes[$reportType] . "\n"
                . "Listing: " . ($jobTitle !== '' ? $jobTitle : '(not specified)') . "\n"
                . "URL: " . ($jobSlug !== '' ? SITE_URL . '/job-details?slug=' . $jobSlug : '(not specified)') . "\n"
                . "Reporter email: " . ($email ?: 'not provided') . "\n"
                . "Reporter phone: " . ($phone ?: 'not provided') . "\n\n"
                . "Details:\n$details\n";
            @mail($adminEmail, $mailSubject, $mailBody, 'From: ' . get_setting('site_email', 'contact@akazinet.com'));

            $success = 'Thank you. We received your report and our team reviews every submission. '
                . 'If the listing asks for money, do not pay and report it to the police as well.';
            $details = '';
            $email = '';
            $phone = '';
            $jobSlug = '';
            $jobTitle = '';
        } catch (PDOException $e) {
            $error = 'We could not save your report right now. Please email us instead using the contact page.';
        }
    }
}

$breadcrumbs = [
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'Report a Listing', 'url' => '/pages/report-job'],
];

$adminEmail = get_setting('site_email', 'contact@akazinet.com');

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<section class="bg-gradient-to-br from-primary to-primary-dark py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">Report a Suspicious Listing</h1>
        <p class="text-gray-300 text-lg">Help us keep AkaziNet safe. Confirmed scams are removed.</p>
    </div>
</section>

<section class="py-12 md:py-16 bg-brand-gray">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="bg-red-50 border border-red-200 rounded-xl p-5 mb-8">
            <h2 class="font-bold text-red-800 mb-2 flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i> If a listing asks for money, stop there
            </h2>
            <p class="text-sm text-red-700 mb-3">
                No legitimate employer or scholarship provider asks candidates for registration fees, CV fees,
                interview costs, or an upfront payment before an offer. If that happens, do not send anything.
            </p>
            <p class="text-sm text-red-700">
                Report it here <em>and</em> report the account to the police. Sending money makes you part of the
                fraud, not a refundable fee.
            </p>
        </div>

        <div class="bg-white rounded-xl border border-brand-border shadow-sm p-6 sm:p-8">
            <?php if ($success !== ''): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6" role="status">
                <p class="text-green-800 font-medium flex items-start">
                    <i class="fas fa-check-circle mr-2 mt-0.5"></i>
                    <span><?= sanitize($success) ?></span>
                </p>
            </div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6" role="alert">
                <p class="text-red-800 font-medium flex items-start">
                    <i class="fas fa-exclamation-circle mr-2 mt-0.5"></i>
                    <span><?= sanitize($error) ?></span>
                </p>
            </div>
            <?php endif; ?>

            <form method="post" action="/pages/report-job" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?= sanitize(csrf_token()) ?>">

                <?php if ($selectedJob): ?>
                <div class="bg-primary/5 border border-primary/15 rounded-lg p-4">
                    <p class="text-xs font-semibold text-primary uppercase tracking-wider mb-1">Reporting this listing</p>
                    <p class="font-semibold text-gray-900"><?= sanitize($selectedJob['title']) ?></p>
                    <p class="text-sm text-gray-500">
                        <?= sanitize((string)($selectedJob['company_name'] ?? '')) ?>
                        <?php if (!empty($selectedJob['company_name'])): ?> &middot; <?php endif; ?>
                        <a href="/job-details?slug=<?= urlencode($selectedJob['slug']) ?>" class="text-primary hover:underline">view listing</a>
                    </p>
                </div>
                <?php endif; ?>

                <div>
                    <label for="job_title" class="block text-sm font-medium text-gray-700 mb-2">Which listing? <span class="text-gray-400">(optional)</span></label>
                    <input type="text" id="job_title" name="job_title" value="<?= sanitize($prefillTitle) ?>"
                        placeholder="Paste the job title"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                    <input type="hidden" name="job_slug" value="<?= sanitize($prefillSlug) ?>">
                    <p class="text-xs text-gray-500 mt-1">
                        Reporting from the listing page fills this in automatically.
                    </p>
                </div>

                <div>
                    <label for="report_type" class="block text-sm font-medium text-gray-700 mb-2">What is wrong with it? <span class="text-red-500">*</span></label>
                    <select id="report_type" name="report_type" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                        <?php foreach ($reportTypes as $value => $label): ?>
                        <option value="<?= sanitize($value) ?>" <?= (($_POST['report_type'] ?? '') === $value) ? 'selected' : '' ?>>
                            <?= sanitize($label) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="details" class="block text-sm font-medium text-gray-700 mb-2">Tell us what happened <span class="text-red-500">*</span></label>
                    <textarea id="details" name="details" rows="6" required
                        placeholder="For example: they asked me to pay 50,000 RWF for a 'registration fee' before an interview, and the recruiter's number is no longer active."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none resize-y"><?= sanitize($details ?? '') ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        Include any phone number, email address, or bank account you were given. Do not include your
                        national ID number or full bank details.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="reporter_email" class="block text-sm font-medium text-gray-700 mb-2">Your email <span class="text-gray-400">(optional)</span></label>
                        <input type="email" id="reporter_email" name="reporter_email" value="<?= sanitize($email ?? '') ?>"
                            placeholder="you@example.com"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                        <p class="text-xs text-gray-500 mt-1">Only if you want to be told what we did.</p>
                    </div>
                    <div>
                        <label for="reporter_phone" class="block text-sm font-medium text-gray-700 mb-2">Your phone <span class="text-gray-400">(optional)</span></label>
                        <input type="tel" id="reporter_phone" name="reporter_phone" value="<?= sanitize($phone ?? '') ?>"
                            placeholder="+250 7XX XXX XXX"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-4 pt-2">
                    <button type="submit"
                        class="inline-flex items-center px-6 py-3 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition-colors">
                        <i class="fas fa-flag mr-2"></i> Submit report
                    </button>
                    <p class="text-xs text-gray-500">
                        Prefer email? Write to
                        <a href="mailto:<?= sanitize($adminEmail) ?>" class="text-primary hover:underline"><?= sanitize($adminEmail) ?></a>.
                    </p>
                </div>
            </form>
        </div>

        <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl border border-brand-border p-5">
                <i class="fas fa-magnifying-glass text-accent text-xl mb-2"></i>
                <h3 class="font-bold text-gray-900 mb-1 text-sm">We review reports</h3>
                <p class="text-sm text-gray-500">Every submission is read by our team, usually within a few days.</p>
            </div>
            <div class="bg-white rounded-xl border border-brand-border p-5">
                <i class="fas fa-trash-can text-accent text-xl mb-2"></i>
                <h3 class="font-bold text-gray-900 mb-1 text-sm">Confirmed scams are removed</h3>
                <p class="text-sm text-gray-500">Listings that ask for payment or impersonate employers are taken down.</p>
            </div>
            <div class="bg-white rounded-xl border border-brand-border p-5">
                <i class="fas fa-user-secret text-accent text-xl mb-2"></i>
                <h3 class="font-bold text-gray-900 mb-1 text-sm">No fee, ever</h3>
                <p class="text-sm text-gray-500">We never ask for payment to remove a listing or to "unlock" a report.</p>
            </div>
        </div>

    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script src="/assets/js/main.js?v=4"></script>
</body>
</html>
