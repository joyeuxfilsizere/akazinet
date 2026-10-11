<?php
$pageTitle = 'Privacy Policy';
$meta_description = 'Read the AkaziNet privacy policy covering how we collect and use your data, cookies, Google AdSense and DoubleClick cookies, and your data rights.';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<section class="bg-gradient-to-br from-primary to-primary-dark py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">Privacy Policy</h1>
        <p class="text-gray-300">Last updated: <?= sanitize(get_setting('privacy_policy_updated', 'September 1, 2026')) ?></p>
    </div>
</section>

<section class="py-12 md:py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 prose prose-gray max-w-none">
        <h2>1. Who We Are</h2>
        <p>AkaziNet ("we", "us", "our") is a job vacancy platform operated from <?= sanitize(SITE_ADDRESS) ?>. We publish employer-submitted job openings and scholarship listings so that Rwandan job seekers and students can find opportunities with clear, verifiable application deadlines. We are the data controller for the personal information described in this policy.</p>
        <p>You can reach our data protection contact at <a href="mailto:contact@akazinet.com">contact@akazinet.com</a> or by post at <?= sanitize(SITE_ADDRESS) ?>.</p>

        <h2>2. Information We Collect</h2>
        <h3>2.1 Information you give us</h3>
        <p>When you use our contact form, newsletter, or WhatsApp channels we collect the details you choose to provide, which may include your name, email address, phone number, and the content of your message. If you apply for a job through a listing, the application is made directly with the employer, and we do not receive your application documents unless an employer forwards them to us.</p>
        <h3>2.2 Information collected automatically</h3>
        <p>Like all websites, we record standard technical data such as your IP address, browser type and version, operating system, referring page, pages visited, and approximate location derived from your IP address. This is collected to keep the service secure and to understand how the site is used.</p>
        <h3>2.3 Local storage</h3>
        <p>We store small pieces of data in your browser's local storage to remember interface preferences. This is not used to identify you across other websites.</p>

        <h2>3. How We Use Your Information</h2>
        <p>We use collected information to:</p>
        <ul>
            <li>Respond to enquiries submitted through our contact form.</li>
            <li>Operate, maintain, and secure the platform, including preventing abuse and fraud.</li>
            <li>Understand which listings and features are useful so we can improve the service.</li>
            <li>Publish and moderate job and scholarship listings submitted by employers and partners.</li>
            <li>Comply with our legal obligations and enforce our terms.</li>
        </ul>
        <p>We do not sell your personal information. We do not share it with advertisers for their own independent marketing, and we never sell, trade, or rent personal data to third parties.</p>

        <h2>4. Advertising and Google AdSense</h2>
        <p>AkaziNet is supported by advertising. We use Google AdSense to display advertisements. Some advertisements are served by Google and its advertising partners. Please read this section carefully, because it explains the cookies involved.</p>
        <h3>4.1 Third-party cookies</h3>
        <p>Third-party vendors, including Google, use cookies to serve ads based on your prior visits to this or other websites. For example, Google's use of the DART cookie enables it and its partners to serve ads to you based on your visits to this site and other sites on the internet.</p>
        <h3>4.2 Opting out of personalised advertising</h3>
        <p>You can opt out of personalised advertising from Google at <a href="https://adssettings.google.com" target="_blank" rel="noopener noreferrer">Google Ads Settings</a>. To opt out of third-party vendor cookie use for personalised advertising in general, visit <a href="https://www.aboutads.info" target="_blank" rel="noopener noreferrer">www.aboutads.info</a> and <a href="https://www.aboutads.info/choices/" target="_blank" rel="noopener noreferrer">www.aboutads.info/choices</a> for a comprehensive list of advertising networks. You can opt out of a specific vendor at <a href="https://www.aboutads.info/choices/" target="_blank" rel="noopener noreferrer">aboutads.info/choices</a>, or opt out of Yahoo's advertising at <a href="https://advertising.yahoo.com/privacy/interest/" target="_blank" rel="noopener noreferrer">advertising.yahoo.com/privacy/interest</a>.</p>
        <h3>4.3 How Google uses your data when you use our partners' sites</h3>
        <p>When you visit a site that contains Google ads, Google may use cookies to collect information about your activity on our site in order to personalise the advertising that you see. For details on how Google uses information from sites that use its ads, visit <a href="https://policies.google.com/technologies/partner-sites" target="_blank" rel="noopener noreferrer">policies.google.com/technologies/partner-sites</a>.</p>
        <h3>4.4 VPAID and video advertising</h3>
        <p>Some of the advertisements on this site may use the VPAID technology to deliver video content. VPAID allows an ad to be verified alongside the video content it plays, and the advertiser receives information such as whether the ad was viewed, played, or clicked. Your interaction with the video advertisement is subject to the advertiser's own privacy policy.</p>
        <h3>4.5 Interest-based advertising</h3>
        <p>Where permitted, Google and its partners may use information from your activity on our site to show you advertising based on your interests. You can manage your preferences through the Google Ads Settings link in section 4.2.</p>

        <h2>5. Cookies We Set</h2>
        <p>AkaziNet sets only the cookies necessary to run the site securely:</p>
        <ul>
            <li><strong>Session cookie</strong> — used to keep you signed in and to protect forms against cross-site request forgery. It is deleted when you close your browser.</li>
            <li><strong>Preferences</strong> — remembers non-essential choices such as display settings. You can clear these at any time through your browser.</li>
        </ul>
        <p>You can block or delete cookies through your browser settings. Blocking essential cookies may prevent parts of the site, such as the admin dashboard, from working correctly.</p>

        <h2>6. Advertising Safety and Content Standards</h2>
        <p>We review listings before publication and remove content that is misleading, fraudulent, discriminatory, or otherwise unlawful. Job advertisements on AkaziNet are not a guarantee of employment, salary, or working conditions. We strongly advise you to verify any employer independently before paying any fee, sending identity documents, or travelling for an interview. No legitimate employer will ever ask you to pay a recruitment or registration fee. Please report suspicious listings to <a href="mailto:contact@akazinet.com">contact@akazinet.com</a>.</p>

        <h2>7. Data Security</h2>
        <p>We take reasonable technical and organisational measures to protect personal information, including encrypted transmission over HTTPS, restricted administrator access, and input validation on all user-submitted data. No method of transmission or storage is completely secure, so we cannot guarantee absolute security.</p>

        <h2>8. Data Retention</h2>
        <p>We retain messages sent through our contact form for as long as needed to resolve the enquiry and to maintain a record of correspondence. Server log data is retained for a limited period for security and troubleshooting, and is then deleted. We do not retain job applications, because applications are submitted directly to employers.</p>

        <h2>9. Your Rights Under Data Protection Law</h2>
        <p>Depending on where you live, you may have the following rights in relation to your personal data:</p>
        <ul>
            <li><strong>Access</strong> — to ask what personal data we hold about you.</li>
            <li><strong>Rectification</strong> — to have inaccurate or incomplete data corrected.</li>
            <li><strong>Erasure</strong> — to ask us to delete your data where we have no overriding reason to keep it.</li>
            <li><strong>Restriction</strong> — to ask us to pause the use of your data in certain situations.</li>
            <li><strong>Portability</strong> — to receive your data in a machine-readable format.</li>
            <li><strong>Objection and withdrawal of consent</strong> — to object to processing based on legitimate interests, or to withdraw consent at any time where we rely on consent.</li>
        </ul>
        <p>To exercise any of these rights, email <a href="mailto:contact@akazinet.com">contact@akazinet.com</a>. We will respond within a reasonable timeframe. If you are not satisfied with our response, you may complain to the competent data protection authority in your country.</p>

        <h2>10. International Transfers</h2>
        <p>AkaziNet is operated from Rwanda. Some of our service providers, including Google, process data on servers located in other countries. Where personal data is transferred abroad, we rely on appropriate safeguards such as standard contractual clauses.</p>

        <h2>11. Children's Privacy</h2>
        <p>Our service is intended for people aged 18 or older. We do not knowingly collect personal information from children under 13, and we do not knowingly collect information from users under 18 without appropriate consent. If you believe a child has provided us with personal information, please contact us and we will delete it.</p>

        <h2>12. Third-Party Links</h2>
        <p>Our pages may link to external websites, including employer websites and messaging services such as WhatsApp. Once you follow an external link you are subject to that third party's own privacy policy, and we have no control over it. We encourage you to review the privacy policy of every site you visit.</p>

        <h2>13. Do Not Track</h2>
        <p>Because the common industry standard for honouring Do Not Track signals has not been established, our website does not currently respond to Do Not Track signals.</p>

        <h2>14. Changes to This Policy</h2>
        <p>We may update this privacy policy from time to time. The "last updated" date at the top of this page always reflects the current version. Material changes will be summarised on this page.</p>

        <h2>15. How to Contact Us</h2>
        <p>If you have any questions about this privacy policy or our data practices, contact us:</p>
        <ul>
            <li><strong>Email:</strong> <a href="mailto:contact@akazinet.com">contact@akazinet.com</a></li>
            <li><strong>Phone / WhatsApp:</strong> +250 780 092 252</li>
            <li><strong>Address:</strong> <?= sanitize(SITE_ADDRESS) ?></li>
        </ul>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script src="/assets/js/main.js"></script>
</body>
</html>
