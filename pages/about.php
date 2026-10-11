<?php
$pageTitle = 'About Us';
$meta_description = 'Learn about AkaziNet, the job platform helping Rwandan job seekers find vacancies that are reviewed before publication, understand deadlines and apply directly to employers across the country.';
require_once __DIR__ . '/../config/database.php';

$totalJobs = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'published' AND is_active = 1 AND (application_deadline IS NULL OR application_deadline >= CURDATE())")->fetchColumn();
$totalCompanies = $pdo->query("SELECT COUNT(DISTINCT company_name) FROM jobs WHERE status = 'published' AND is_active = 1 AND (application_deadline IS NULL OR application_deadline >= CURDATE())")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories WHERE is_active = 1")->fetchColumn();
$totalLocations = $pdo->query("SELECT COUNT(DISTINCT province) FROM jobs WHERE status = 'published' AND is_active = 1 AND (application_deadline IS NULL OR application_deadline >= CURDATE()) AND province <> ''")->fetchColumn();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<section class="bg-gradient-to-br from-primary to-primary-dark py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">About AkaziNet</h1>
        <p class="text-gray-300 text-lg">Connecting Rwandans to Career Opportunities</p>
    </div>
</section>

<section class="py-12 md:py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center mb-16">
            <div>
                <span class="text-accent font-semibold text-sm uppercase tracking-wider">Our Mission</span>
                <h2 class="text-3xl font-bold text-gray-900 mt-2 mb-4">Empowering Rwanda's Workforce</h2>
                <p class="text-gray-600 leading-relaxed">AkaziNet is dedicated to bridging the gap between job seekers and employers in Rwanda. We provide a transparent, accessible, and reliable platform where job opportunities are reviewed before publication and shared with professionals across the country.</p>
            </div>
            <div class="bg-brand-gray rounded-2xl p-8">
                <div class="grid grid-cols-2 gap-6">
                    <div class="text-center p-4 bg-white rounded-xl border border-brand-border">
                        <div class="text-3xl font-bold text-primary"><?= $totalJobs ?>+</div>
                        <div class="text-sm text-gray-500">Active Jobs</div>
                    </div>
                    <div class="text-center p-4 bg-white rounded-xl border border-brand-border">
                        <div class="text-3xl font-bold text-accent"><?= $totalCompanies ?>+</div>
                        <div class="text-sm text-gray-500">Companies</div>
                    </div>
                    <div class="text-center p-4 bg-white rounded-xl border border-brand-border">
                        <div class="text-3xl font-bold text-primary"><?= $totalCategories ?>+</div>
                        <div class="text-sm text-gray-500">Categories</div>
                    </div>
                    <div class="text-center p-4 bg-white rounded-xl border border-brand-border">
                        <div class="text-3xl font-bold text-accent"><?= $totalLocations ?></div>
                        <div class="text-sm text-gray-500">Locations</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-16">
            <span class="text-accent font-semibold text-sm uppercase tracking-wider">Our Vision</span>
            <h2 class="text-3xl font-bold text-gray-900 mt-2 mb-4">A Vision for Employment Excellence</h2>
            <p class="text-gray-600 leading-relaxed max-w-3xl">To become Rwanda's most trusted and comprehensive employment platform, facilitating meaningful connections between talent and opportunity, and contributing to the nation's economic growth by reducing unemployment and underemployment.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
            <div class="bg-brand-gray rounded-2xl p-8">
                <div class="w-14 h-14 bg-primary/10 rounded-xl flex items-center justify-center mb-4">
                    <i class="fas fa-bullseye text-primary text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Our Objective</h3>
                <p class="text-gray-500 text-sm leading-relaxed">To create a centralized, user-friendly platform that aggregates job vacancies from across Rwanda for review before publication, making job searching accessible to all citizens regardless of their location or background.</p>
            </div>
            <div class="bg-brand-gray rounded-2xl p-8">
                <div class="w-14 h-14 bg-accent/10 rounded-xl flex items-center justify-center mb-4">
                    <i class="fas fa-heart text-accent text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Our Values</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Integrity, transparency, inclusivity, and innovation drive everything we do. We review every listing before publication, protect user data, and continuously improve our platform to serve Rwandans better.</p>
            </div>
            <div class="bg-brand-gray rounded-2xl p-8">
                <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center mb-4">
                    <i class="fas fa-users text-green-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">For Job Seekers</h3>
                <p class="text-gray-500 text-sm leading-relaxed">No account needed to browse jobs. Free access to all listings, direct WhatsApp support for assistance, and detailed job information to help make informed career decisions.</p>
            </div>
        </div>

        <div class="mb-16">
            <span class="text-accent font-semibold text-sm uppercase tracking-wider">How It Works</span>
            <h2 class="text-3xl font-bold text-gray-900 mt-2 mb-4">Finding Your Next Role in Four Steps</h2>
            <p class="text-gray-600 leading-relaxed max-w-3xl mb-8">AkaziNet is designed so that a job seeker with limited data and a busy schedule can go from searching to applying in minutes, without creating an account and without paying anything.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-brand-gray rounded-2xl p-6">
                    <div class="w-10 h-10 bg-primary text-white rounded-full flex items-center justify-center font-bold mb-4">1</div>
                    <h3 class="font-bold text-gray-900 mb-2">Search or browse</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Filter vacancies by category, province, or employer using the search bar, or browse the full list ordered by deadline.</p>
                </div>
                <div class="bg-brand-gray rounded-2xl p-6">
                    <div class="w-10 h-10 bg-primary text-white rounded-full flex items-center justify-center font-bold mb-4">2</div>
                    <h3 class="font-bold text-gray-900 mb-2">Check the deadline</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Every listing shows a live countdown of the exact time remaining before applications close, so you never miss a closing date.</p>
                </div>
                <div class="bg-brand-gray rounded-2xl p-6">
                    <div class="w-10 h-10 bg-primary text-white rounded-full flex items-center justify-center font-bold mb-4">3</div>
                    <h3 class="font-bold text-gray-900 mb-2">Read the full listing</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Open the vacancy to see the employer, location, responsibilities, required qualifications, and how to submit your application.</p>
                </div>
                <div class="bg-brand-gray rounded-2xl p-6">
                    <div class="w-10 h-10 bg-primary text-white rounded-full flex items-center justify-center font-bold mb-4">4</div>
                    <h3 class="font-bold text-gray-900 mb-2">Apply directly</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Contact the employer using the details provided. AkaziNet does not handle applications and never asks for a fee.</p>
                </div>
            </div>
        </div>

        <div class="mb-16">
            <span class="text-accent font-semibold text-sm uppercase tracking-wider">Our Standards</span>
            <h2 class="text-3xl font-bold text-gray-900 mt-2 mb-4">How We Handle Every Listing</h2>
            <p class="text-gray-600 leading-relaxed max-w-3xl mb-6">Trust is the most valuable thing a job board can offer. Every vacancy and scholarship on AkaziNet passes through the same review process before it becomes public.</p>
            <ul class="space-y-4 max-w-3xl text-gray-600 leading-relaxed">
                <li class="flex items-start gap-3"><i class="fas fa-check-circle text-primary mt-1"></i><span><strong>Reviewed before publication.</strong> Submissions are checked for completeness, a named employer, a real closing date, and a working application method before they appear in search results.</span></li>
                <li class="flex items-start gap-3"><i class="fas fa-check-circle text-primary mt-1"></i><span><strong>Expired listings are removed automatically.</strong> Once a deadline passes the listing leaves the public list, so the pages you see are still open rather than a backlog of dead vacancies.</span></li>
                <li class="flex items-start gap-3"><i class="fas fa-check-circle text-primary mt-1"></i><span><strong>Fraudulent listings are removed fast.</strong> If a listing asks for payment, bank details, or identity documents before an interview, report it and we will take it down while we investigate.</span></li>
                <li class="flex items-start gap-3"><i class="fas fa-check-circle text-primary mt-1"></i><span><strong>No fee, ever, for job seekers.</strong> Browsing and applying is free. AkaziNet is funded by advertising, which is why we never charge candidates or ask for a recruitment fee.</span></li>
                <li class="flex items-start gap-3"><i class="fas fa-circle-info text-primary mt-1"></i><span><strong>Review is not verification.</strong> We check what is on the submission, but we cannot confirm with each employer that the role exists, so we describe listings as reviewed rather than verified. Confirm the company through its own website or phone number before you send documents or pay anything. The <a href="/pages/disclaimer">disclaimer</a> sets out exactly what our review covers.</span></li>
            </ul>
        </div>

        <div class="mb-16">
            <span class="text-accent font-semibold text-sm uppercase tracking-wider">For Employers</span>
            <h2 class="text-3xl font-bold text-gray-900 mt-2 mb-4">Reach Rwandan Talent Directly</h2>
            <p class="text-gray-600 leading-relaxed max-w-3xl">AkaziNet is used by candidates across all six provinces who are actively searching for work. Listing a vacancy puts your opening in front of people who have already indicated they are ready to apply, and the deadline countdown gives your listing urgency without you having to chase anyone. If you would like to post a vacancy or a scholarship, <a href="/pages/contact" class="text-accent font-medium hover:underline">contact our team</a> and we will help you get set up.</p>
        </div>

        <div class="mb-16">
            <span class="text-accent font-semibold text-sm uppercase tracking-wider">FAQ</span>
            <h2 class="text-3xl font-bold text-gray-900 mt-2 mb-6">Frequently Asked Questions</h2>
            <div class="space-y-4 max-w-3xl">
                <details class="bg-brand-gray rounded-xl p-5"><summary class="font-semibold text-gray-900 cursor-pointer">Is AkaziNet free to use?</summary><p class="text-sm text-gray-600 mt-3 leading-relaxed">Yes. Browsing, searching, and reading every listing is free for job seekers and does not require an account. The service is funded by advertising shown alongside the listings.</p></details>
                <details class="bg-brand-gray rounded-xl p-5"><summary class="font-semibold text-gray-900 cursor-pointer">Does AkaziNet handle my application?</summary><p class="text-sm text-gray-600 mt-3 leading-relaxed">No. Applications are submitted directly to the employer using the contact details on the listing. We do not collect your CV, so we never ask for personal documents.</p></details>
                <details class="bg-brand-gray rounded-xl p-5"><summary class="font-semibold text-gray-900 cursor-pointer">What happens when the countdown reaches zero?</summary><p class="text-sm text-gray-600 mt-3 leading-relaxed">The listing is treated as closed and is removed from the public job list. We recommend contacting employers early rather than waiting for the final day, as vacancies often close sooner than advertised.</p></details>
                <details class="bg-brand-gray rounded-xl p-5"><summary class="font-semibold text-gray-900 cursor-pointer">I found a suspicious listing. What should I do?</summary><p class="text-sm text-gray-600 mt-3 leading-relaxed">Report it to <a href="mailto:contact@akazinet.com" class="text-accent font-medium hover:underline">contact@akazinet.com</a> with the link. We remove listings that request payment or personal documents up front, and we act on reports as a priority.</p></details>
                <details class="bg-brand-gray rounded-xl p-5"><summary class="font-semibold text-gray-900 cursor-pointer">How do I post a job or scholarship?</summary><p class="text-sm text-gray-600 mt-3 leading-relaxed">Use the contact page to reach our team with the details of the vacancy, including the employer, role, location, closing date, and application method. We will review and publish it, usually within one working day.</p></details>
            </div>
        </div>

        <div class="bg-gradient-to-r from-primary to-primary-dark rounded-2xl p-8 md:p-12 text-center text-white">
            <h2 class="text-2xl md:text-3xl font-bold mb-4">Why Choose AkaziNet?</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8 text-left max-w-3xl mx-auto">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-check-circle text-accent mt-0.5"></i>
                    <span>Every listing is reviewed before it goes live</span>
                </div>
                <div class="flex items-start space-x-3">
                    <i class="fas fa-check-circle text-accent mt-0.5"></i>
                    <span>No registration required to browse jobs</span>
                </div>
                <div class="flex items-start space-x-3">
                    <i class="fas fa-check-circle text-accent mt-0.5"></i>
                    <span>Direct WhatsApp support for quick assistance</span>
                </div>
                <div class="flex items-start space-x-3">
                    <i class="fas fa-check-circle text-accent mt-0.5"></i>
                    <span>Covering all provinces in Rwanda</span>
                </div>
                <div class="flex items-start space-x-3">
                    <i class="fas fa-check-circle text-accent mt-0.5"></i>
                    <span>Free to use for all job seekers</span>
                </div>
                <div class="flex items-start space-x-3">
                    <i class="fas fa-check-circle text-accent mt-0.5"></i>
                    <span>Mobile-friendly and fast loading</span>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script src="/assets/js/main.js"></script>
</body>
</html>
