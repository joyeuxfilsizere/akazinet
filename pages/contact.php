<?php
$pageTitle = 'Contact Us';
$meta_description = 'Contact the AkaziNet team for help with job listings, employer vacancies, advertising or site feedback. Reach us by email, phone or WhatsApp.';
require_once __DIR__ . '/../config/database.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $location = sanitize($_POST['location'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, location, ip_address, subject, message) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $location, $ip, $subject, $message]);
        $success = 'Thank you for your message! We will get back to you soon.';

        $adminEmail = get_setting('site_email', 'contact@akazinet.com');
        $emailSubject = "New Contact Message from $name - AkaziNet";
        $emailBody = "You have received a new message from the contact form.\n\n"
            . "Name: $name\n"
            . "Email: $email\n"
            . "Location: " . ($location ?: 'N/A') . "\n"
            . "Subject: " . ($subject ?: 'N/A') . "\n"
            . "Message:\n$message\n";
        mail($adminEmail, $emailSubject, $emailBody, "From: $email");
    }
}

$whatsappNumber = get_setting('whatsapp_number', '250780092252');

// Leaflet's own stylesheet. Pinned to an exact version because an unpinned
// range would let a CDN release push a breaking change onto a page we cannot
// redeploy against. Tile requests still go to openstreetmap.org at runtime;
// only the library itself comes from the CDN.
$extraHead = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<section class="bg-gradient-to-br from-primary to-primary-dark py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">Contact Us</h1>
        <p class="text-gray-300 text-lg">We'd love to hear from you. Reach out anytime!</p>
    </div>
</section>

<section class="py-12 md:py-20 bg-brand-gray">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <div class="bg-white rounded-xl p-6 text-center border border-brand-border shadow-sm">
                <div class="w-14 h-14 mx-auto bg-green-100 rounded-xl flex items-center justify-center mb-4">
                    <i class="fab fa-whatsapp text-green-500 text-2xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-1">WhatsApp</h3>
                <p class="text-sm text-gray-500 mb-3">Chat with us instantly</p>
                <a href="https://wa.me/<?= $whatsappNumber ?>" target="_blank" class="inline-flex items-center px-4 py-2 bg-green-500 text-white text-sm font-medium rounded-lg hover:bg-green-600 transition-colors">
                    Start Chat
                </a>
            </div>
            <div class="bg-white rounded-xl p-6 text-center border border-brand-border shadow-sm">
                <div class="w-14 h-14 mx-auto bg-primary/10 rounded-xl flex items-center justify-center mb-4">
                    <i class="fas fa-envelope text-primary text-2xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-1">Email</h3>
                <p class="text-sm text-gray-500 mb-3">Send us an email</p>
                <a href="mailto:contact@akazinet.com" class="inline-flex items-center px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors">
                    contact@akazinet.com
                </a>
            </div>
            <div class="bg-white rounded-xl p-6 text-center border border-brand-border shadow-sm">
                <div class="w-14 h-14 mx-auto bg-accent/10 rounded-xl flex items-center justify-center mb-4">
                    <i class="fas fa-map-marker-alt text-accent text-2xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-1">Location</h3>
                <p class="text-sm text-gray-500 mb-3">Visit us in person</p>
                <span class="text-sm text-gray-600"><?= sanitize(SITE_ADDRESS) ?></span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white rounded-xl p-6 md:p-8 border border-brand-border shadow-sm">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Send Us a Message</h2>

                <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-6 flex items-center">
                    <i class="fas fa-check-circle mr-2"></i> <?= $success ?>
                </div>
                <?php endif; ?>
                <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-6 flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?= $error ?>
                </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="<?= sanitize($name ?? '') ?>" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="<?= sanitize($email ?? '') ?>" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                        <input type="text" name="subject" value="<?= sanitize($subject ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input type="text" name="location" value="<?= sanitize($location ?? '') ?>" placeholder="e.g. Kigali, Rwanda" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Message <span class="text-red-500">*</span></label>
                        <textarea name="message" rows="5" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all text-sm resize-none"><?= sanitize($message ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="w-full px-6 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary-dark transition-colors">
                        <i class="fas fa-paper-plane mr-2"></i> Send Message
                    </button>
                </form>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl p-6 md:p-8 border border-brand-border shadow-sm">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Frequently Asked Questions</h2>
                    <div class="space-y-4">
                        <div class="border-b border-gray-100 pb-4">
                            <h4 class="font-medium text-gray-900 mb-1">Do I need an account to apply?</h4>
                            <p class="text-sm text-gray-500">No, AkaziNet does not require registration. Browse jobs and contact employers directly via WhatsApp.</p>
                        </div>
                        <div class="border-b border-gray-100 pb-4">
                            <h4 class="font-medium text-gray-900 mb-1">Are all job listings verified?</h4>
                            <p class="text-sm text-gray-500">Every vacancy is reviewed by our team before publication, but review is not a guarantee of authenticity. We check listings for completeness and obvious fraud and remove reported ones, yet we cannot independently confirm every employer. Confirm with the company through its own website or phone number before you send documents or pay anything. Our <a href="/pages/disclaimer">disclaimer</a> explains what our review covers.</p>
                        </div>
                        <div class="border-b border-gray-100 pb-4">
                            <h4 class="font-medium text-gray-900 mb-1">How do I report a suspicious listing?</h4>
                            <p class="text-sm text-gray-500">Contact us via WhatsApp or email and we will investigate immediately.</p>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 mb-1">Is it free for job seekers?</h4>
                            <p class="text-sm text-gray-500">Yes, AkaziNet is completely free for all job seekers. Browse and apply at no cost.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 md:p-8 border border-brand-border shadow-sm">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Our Location</h2>
                    <div id="contact-map"
                         class="bg-gray-100 rounded-lg h-72 overflow-hidden border border-gray-200"
                         data-lat="<?= sanitize((string)SITE_LAT) ?>"
                         data-lng="<?= sanitize((string)SITE_LNG) ?>"
                         data-label="<?= sanitize(SITE_ADDRESS) ?>"
                         role="region"
                         aria-label="Map of AkaziNet's office in <?= sanitize(SITE_CITY) ?>, <?= sanitize(SITE_PROVINCE) ?>, Rwanda"></div>
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-map-marker-alt text-accent mr-1"></i><?= sanitize(SITE_ADDRESS) ?>
                        </p>
                        <a href="https://www.google.com/maps/dir/?api=1&amp;destination=<?= sanitize(urlencode(SITE_CITY . ', ' . SITE_PROVINCE . ', Rwanda')) ?>"
                           target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors">
                            <i class="fas fa-directions mr-2"></i> Get directions
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-brand-border p-8 md:p-10 mt-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">How can we help?</h2>
            <p class="text-gray-500 mb-8">Choose the route that matches your question so your message reaches the right person first time.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-brand-gray rounded-xl p-6">
                    <h3 class="font-bold text-gray-900 mb-2 flex items-center gap-2"><i class="fas fa-briefcase text-primary"></i> Post a job vacancy</h3>
                    <p class="text-sm text-gray-500 leading-relaxed mb-3">Email us the employer name, role title, duties, required qualifications, location, closing date, and how candidates should apply. We review every submission and normally publish within one working day.</p>
                    <p class="text-sm text-gray-500 leading-relaxed">A clear closing date is the part we cannot work without, because it is what powers the deadline countdown on your listing.</p>
                </div>
                <div class="bg-brand-gray rounded-xl p-6">
                    <h3 class="font-bold text-gray-900 mb-2 flex items-center gap-2"><i class="fas fa-graduation-cap text-accent"></i> Post a scholarship</h3>
                    <p class="text-sm text-gray-500 leading-relaxed mb-3">Send the scholarship title, funding body, who is eligible, what the award covers, the application deadline, and the application link or contact person.</p>
                    <p class="text-sm text-gray-500 leading-relaxed">Listings that ask candidates for payment before an interview are not accepted, and existing ones are removed.</p>
                </div>
                <div class="bg-brand-gray rounded-xl p-6">
                    <h3 class="font-bold text-gray-900 mb-2 flex items-center gap-2"><i class="fas fa-exclamation-triangle text-red-600"></i> Report a suspicious listing</h3>
                    <p class="text-sm text-gray-500 leading-relaxed mb-3">Send us the link to the listing and tell us what looks wrong, such as a request for payment, bank details, or identity documents before an interview.</p>
                    <p class="text-sm text-gray-500 leading-relaxed">We treat these as priority and remove the listing while we investigate. No legitimate employer charges a recruitment fee.</p>
                </div>
                <div class="bg-brand-gray rounded-xl p-6">
                    <h3 class="font-bold text-gray-900 mb-2 flex items-center gap-2"><i class="fas fa-pen-nib text-primary"></i> Advertising and partnerships</h3>
                    <p class="text-sm text-gray-500 leading-relaxed mb-3">For display advertising, media partnerships, or organisation-wide deals, email us with your target audience and campaign goals.</p>
                    <p class="text-sm text-gray-500 leading-relaxed">We are also open to collaborations with universities, training providers, and NGOs that support Rwandan job seekers.</p>
                </div>
            </div>
            <div class="mt-8 pt-6 border-t border-gray-100 text-sm text-gray-500 leading-relaxed">
                <p><strong class="text-gray-700">Response time:</strong> we answer messages within one working day during weekdays. If your message is about a closing deadline, say so in the subject line and it will be prioritised.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="/assets/js/main.js"></script>
<script src="/assets/js/contact-map.js?v=1"></script>
</body>
</html>
