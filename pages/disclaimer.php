<?php
/**
 * Legal Disclaimer
 *
 * Kept separate from Terms because it carries the editorial-integrity signals
 * Google looks for: how listings get reviewed, how ads are labelled, and how
 * content corrections are handled.
 */

$pageTitle = 'Disclaimer';
$meta_description = 'How AkaziNet reviews job and scholarship listings, how advertising works on this site, and how to report inaccurate or misleading information.';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/schema.php';

$siteName = get_setting('site_name', 'AkaziNet');

$breadcrumbs = [
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'Disclaimer', 'url' => '/pages/disclaimer'],
];

$extraSchema = akazinet_faq([
    [
        'question' => 'Does AkaziNet verify every listing?',
        'answer' => 'We review every listing before publication for completeness, duplication, and obvious scam signals, and we remove listings that job seekers report as fraudulent. We call this review rather than verification because it is not the same thing: we do not confirm with each employer that the role exists, so we cannot guarantee the conduct of any employer, recruiter, or scholarship provider once a listing is live. Always verify payment requests and interview arrangements with the company through its own contact details.',
    ],
    [
        'question' => 'Is AkaziNet a recruitment agency?',
        'answer' => 'No. AkaziNet is a free job and scholarship information platform. We do not recruit for employers, charge candidates for applications, or act as an intermediary between applicants and employers.',
    ],
    [
        'question' => 'Does AkaziNet accept payment to post a job?',
        'answer' => 'Employers may pay for promoted placement so their vacancy appears higher in the listing. Paying for placement never guarantees an applicant, and we do not sell resumes or applicant data.',
    ],
    [
        'question' => 'How do I report a fake or fraudulent listing?',
        'answer' => 'Use the report form. Reports are reviewed by our team and listings that ask for money upfront, use free email domains for official-sounding roles, or copy another employer description are removed.',
    ],
    [
        'question' => 'Who writes the content in Career Guides?',
        'answer' => 'Career Guides are written and reviewed by the AkaziNet editorial team for Rwandan job seekers. We cite the basis for salary and hiring figures and label them as indicative where the data is not from an official source.',
    ],
]);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<section class="bg-gradient-to-br from-primary to-primary-dark py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">Disclaimer</h1>
        <p class="text-gray-300 text-lg">How <?= sanitize($siteName) ?> reviews listings, handles advertising, and corrects content.</p>
    </div>
</section>

<section class="py-12 md:py-16 bg-brand-gray">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl border border-brand-border shadow-sm p-6 sm:p-8 md:p-10">
            <div class="prose prose-gray max-w-none">
                <h2>1. Information only, not advice</h2>
                <p>
                    <?= sanitize($siteName) ?> publishes job vacancies, scholarship opportunities, and career
                    articles for informational purposes. Nothing on this site is professional, legal, financial, or
                    employment advice. Salary figures, hiring trends, and interview guidance are general and may
                    not apply to your specific situation, employer, or industry.
                </p>

                <h2>2. What our review covers, and what it does not</h2>
                <p>
                    Every listing is reviewed by our team before it is published. That review covers the things we
                    can see on the submission itself: that a named employer is given, that the role and location
                    are described, that a closing date is present, that the listing is not an obvious duplicate,
                    and that it does not already contain an obvious scam signal such as a request for payment or
                    identity documents. We also remove listings when job seekers report fraud.
                </p>
                <p>
                    Review is not verification, and we describe listings as reviewed rather than verified for
                    that reason. We are a publishing platform, not an inspector: we do not visit employer premises,
                    conduct reference checks, or confirm with a company that it has actually hired for a role. We
                    cannot independently confirm the identity, legitimacy, registration, or intentions of every
                    employer, recruiter, training provider, or scholarship administrator that advertises here.
                    A listing appearing on <?= sanitize($siteName) ?> is not an endorsement or a guarantee that
                    the employer is genuine, that the role exists, or that it is still open.
                </p>
                <p>
                    Confirm with the company before you act. Use the contact details on the company's own website
                    rather than those in the listing, and treat any request for money as disqualifying. If a
                    listing cannot be confirmed this way, treat it as unverified and do not send documents.
                </p>

                <h2>3. Never pay to get a job</h2>
                <p>
                    Legitimate employers and scholarship providers do not ask candidates to pay registration fees,
                    "CV formatting fees", interview costs, or release payments before a job is offered. If any listing
                    or contact asks you for money in exchange for a job, do not send it. Report the listing
                    <a href="/pages/report-job">here</a> and report the account to the police.
                </p>
                <p>
                    We are not responsible for losses arising from payments or documents shared with a third party in
                    response to a listing.
                </p>

                <h2>4. Accuracy of listings and links</h2>
                <p>
                    Employers close, fill, and change vacancies without notice. We aim to keep listings current, but
                    we cannot guarantee that a vacancy is still open, that its details are complete, or that an
                    application link works. Where an employer has supplied an application deadline, that deadline
                    reflects the employer's own information.
                </p>

                <h2>5. Advertising</h2>
                <p>
                    <?= sanitize($siteName) ?> is supported by advertising, including ads served by Google AdSense.
                    Advertising keeps the service free for job seekers. Advertisers do not control our editorial
                    content, and the presence of an ad near a listing does not mean we endorse the advertiser or the
                    vacancy. Promoted placements are labelled as sponsored where they appear.
                </p>

                <h2>6. Intellectual property</h2>
                <p>
                    The site design, code, and original articles written by our editorial team belong to
                    <?= sanitize($siteName) ?>. Company names, job titles, and employer logos belong to their
                    respective owners and are used to describe the vacancies they posted. If you believe content
                    infringes your rights, contact us and we will review and remove it where appropriate.
                </p>

                <h2>7. External links</h2>
                <p>
                    We link to employer websites, official application portals, and scholarship pages. We do not
                    control those sites and are not responsible for their content, availability, or privacy
                    practices.
                </p>

                <h2>8. Limitation of liability</h2>
                <p>
                    To the fullest extent permitted by law, <?= sanitize($siteName) ?> and its team are not liable
                    for any loss arising from reliance on content published here, including lost opportunities,
                    lost wages, or dealings with third parties contacted through a listing.
                </p>

                <h2>9. Corrections and reporting</h2>
                <p>
                    We correct content when we find an error. If you spot a mistake, or a listing that is
                    fraudulent, expired, or misleading, please
                    <a href="/pages/report-job">report it</a> or
                    <a href="/pages/contact">contact us</a>. Confirmed corrections are made promptly, and listings
                    that breach our
                    <a href="/pages/terms">posting rules</a> are removed.
                </p>

                <h2>10. Changes to this disclaimer</h2>
                <p>
                    We may update this disclaimer as the service grows or Rwandan regulations change. The version
                    published on this page applies to the site at the time you read it.
                </p>
            </div>

            <div class="mt-10 pt-6 border-t border-gray-200">
                <p class="text-sm text-gray-500">
                    Last reviewed <?= sanitize(get_setting('legal_last_updated', 'September 1, 2026')) ?>.
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script src="/assets/js/main.js?v=4"></script>
</body>
</html>
