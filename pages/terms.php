<?php
$pageTitle = 'Terms & Conditions';
$meta_description = 'Read the AkaziNet terms and conditions covering use of this job vacancy platform, job listing accuracy, liability, and acceptable use of the site.';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<section class="bg-gradient-to-br from-primary to-primary-dark py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">Terms & Conditions</h1>
        <p class="text-gray-300">Last updated: <?= sanitize(get_setting('terms_updated', 'September 1, 2026')) ?></p>
    </div>
</section>

<section class="py-12 md:py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 prose prose-gray max-w-none">
        <h2>1. Acceptance of Terms</h2>
        <p>By accessing and using AkaziNet (the "Site", "Platform" or "Service"), you agree to be bound by these Terms and Conditions and to comply with all applicable laws and regulations of the Republic of Rwanda. If you do not agree with any part of these terms, you must immediately stop using the Platform.</p>
        <p>We may update these Terms from time to time. Material changes will be reflected by revising the "Last updated" date shown at the top of this page. Continued use of the Platform after a change has been published constitutes your acceptance of the revised Terms.</p>

        <h2>2. Platform Description</h2>
        <p>AkaziNet is a job vacancy platform that aggregates, reviews and publishes employment opportunities in Rwanda, together with a small number of scholarship listings. Our Platform is provided free of charge to job seekers and does not require the creation of an account in order to browse listings, read job details or follow application links.</p>
        <p>AkaziNet is an information service. We are not an employment agency, we do not act as an intermediary between candidates and employers, and we are not a party to any employment agreement that may result from an application you submit.</p>

        <h2>3. Job Listings and Scholarship Listings</h2>
        <p>We review every listing before publication to check for completeness, obvious duplication and apparent legitimacy. However, verification is a reasonable review only. AkaziNet does not guarantee the accuracy, completeness, currency or legitimacy of any job, scholarship or employer information published on the Platform.</p>
        <p>Job details such as salary, working hours, qualifications, closing dates and application procedures are supplied by the employer or organisation concerned. Where information has changed after publication, it may no longer be accurate. Job seekers are responsible for conducting their own due diligence, including verifying the employer directly, before applying, travelling to an interview, or paying any sum of money.</p>
        <p>We may correct, edit, or remove any listing at any time, without notice, where a listing is found to be inaccurate, expired, duplicated, misleading, or contrary to these Terms.</p>

        <h2>4. No Application Fees</h2>
        <p>AkaziNet never charges job seekers a fee to view, save, or apply for a job through the Platform. Legitimate employers do not ask candidates to pay a processing, registration, interview, or "placement" fee in order to be considered for employment. If you are asked to pay money in exchange for a job, treat the request as a likely scam and report it to us immediately.</p>

        <h2>5. Your Responsibilities as a Job Seeker</h2>
        <p>When you browse the Platform, follow an application link, or contact an employer, you agree to:</p>
        <ul>
            <li>Provide only truthful and accurate information in any application, enquiry or communication you submit.</li>
            <li>Ensure that the personal details you provide to an employer are your own, and that you are legally entitled to work in Rwanda.</li>
            <li>Review the requirements of a role carefully before applying, including location, working hours, required qualifications and closing date.</li>
            <li>Report fraudulent, misleading, discriminatory or illegal listings to us so that we can act on them.</li>
        </ul>
        <p>You are solely responsible for the content of any application or message you send to an employer, and for any decision you take as a result of information published on the Platform.</p>

        <h2>6. Employers and Listing Submissions</h2>
        <p>Employers, recruiters, government institutions and non-governmental organisations that submit a listing represent and warrant that they are authorised to publish the vacancy they submit, that the description is accurate, and that the vacancy complies with Rwandan labour law and any applicable equality or anti-discrimination requirements.</p>
        <p>We may request supporting documentation from a submitting organisation, and we reserve the right to decline or remove any submission. We may also decline to publish listings that are outside our editorial scope, that duplicate existing content, or that are likely to mislead candidates.</p>

        <h2>7. Acceptable Use</h2>
        <p>You agree not to use the Platform to:</p>
        <ul>
            <li>Submit false, misleading or fraudulent information through the contact form or any other form on the Site.</li>
            <li>Copy, scrape, republish, or redistribute the contents of the Platform in bulk without our written permission.</li>
            <li>Attempt to gain unauthorised access to any part of the Site, its server, its database, or the accounts of other users.</li>
            <li>Introduce viruses, worms, malware, or any code designed to damage, disable or overload the Platform.</li>
            <li>Use automated tools, bots, or scripts to generate artificial traffic or to interfere with the operation of the Site.</li>
            <li>Impersonate AkaziNet or any other person or organisation.</li>
        </ul>
        <p>We may suspend or block any user who breaches this section, and we may cooperate with law enforcement or internet service providers where a breach involves unlawful activity.</p>

        <h2>8. Advertising and Third-Party Content</h2>
        <p>The Platform is supported by advertising. Pages may contain advertisements served by third-party networks, including display advertisements, and these advertisements are selected and delivered by the network, not by AkaziNet.</p>
        <p>You acknowledge that advertising content may be contextual and that its appearance on a page does not constitute a recommendation, endorsement, or approval by AkaziNet of the advertiser, the advertiser's products or services, or any third-party website linked from an advertisement. We are not responsible for the content of any advertisement or for the practices of any advertiser, including its compliance with its own privacy obligations.</p>
        <p>Our advertising partners, including Google AdSense, may use cookies or similar technologies to serve and measure advertisements. Use of advertising cookies is described in our <a href="/pages/privacy" class="text-primary font-medium hover:underline">Privacy Policy</a>. You can manage your consent and cookie preferences through your browser settings or through the advertising choices presented to you on the Site.</p>

        <h2>9. Third-Party Links</h2>
        <p>The Platform contains links to external websites, including employer career pages, application portals, official institutional sites and scholarship providers. These links are provided for your convenience only.</p>
        <p>We do not control and are not responsible for the content, availability, security, accuracy, or privacy practices of any third-party website. A link does not imply that we endorse, are affiliated with, or have been sponsored by the linked party. Your use of a third-party website is governed by that website's own terms and policies, and any dispute arising from it is between you and that website.</p>

        <h2>10. Intellectual Property</h2>
        <p>All content on AkaziNet, including the Site design, layout, text, job descriptions, scholarship listings, graphics, logos, icons, code, and database structure, is the intellectual property of AkaziNet and is protected by the copyright and related rights of the Republic of Rwanda and international conventions.</p>
        <p>Individual job and scholarship descriptions remain the property of the employer or organisation that supplied them. You may quote brief extracts, with clear attribution and a link back to the original listing, for non-commercial purposes such as personal research or sharing within your own community. You may not republish listings commercially or present them as your own without written permission.</p>

        <h2>11. No Guarantee of Employment</h2>
        <p>AkaziNet does not guarantee that any listing will remain available, that any vacancy will be filled, that any employer will contact you, or that any application you submit will result in an interview or an offer. We do not guarantee any salary, benefit, working condition, or career outcome described in a listing.</p>

        <h2>12. Limitation of Liability</h2>
        <p>To the fullest extent permitted by Rwandan law, AkaziNet and its operators, editors, staff and contributors are not liable for any direct, indirect, incidental, special, consequential or exemplary damages, or for any loss of profits, income, data, employment opportunity or goodwill, arising out of or in connection with your use of, or inability to use, the Platform.</p>
        <p>This limitation applies regardless of whether the claim is based on contract, tort (including negligence), strict liability, or any other legal theory, and even if we have been advised of the possibility of such damages. Where liability cannot be excluded by law, our total aggregate liability is limited to the amount, if any, that you paid us for the Service, which is nil.</p>
        <p>We are not responsible for losses caused by fraudulent listings, fraudulent recruiters, unsolicited communications, or any decision or omission made by you or by an employer in reliance on information published on the Platform.</p>

        <h2>13. Indemnity</h2>
        <p>You agree to indemnify, defend and hold harmless AkaziNet, its operators and contributors from and against any claim, demand, loss, damage, cost or expense (including reasonable legal fees) arising out of your use of the Platform, your breach of these Terms, your violation of any law, or your infringement of the rights of any third party.</p>

        <h2>14. Data Protection</h2>
        <p>How we collect, use, store and protect personal information submitted through the Platform is described in full in our <a href="/pages/privacy" class="text-primary font-medium hover:underline">Privacy Policy</a>. By using the Platform, you confirm that you have read that policy and consent to the processing of your information as described there.</p>

        <h2>15. Age Requirement</h2>
        <p>You must be at least 18 years of age, or the age of majority in Rwanda, to use the Platform. We do not knowingly collect information from children under 13 years of age. If you believe a child has submitted personal information to us, please contact us so that we can delete it.</p>

        <h2>16. Suspension and Termination</h2>
        <p>You may stop using the Platform at any time. We may suspend, restrict or terminate access to the Platform, without notice and without liability, where we reasonably believe that you have breached these Terms, that your use is unlawful, or that your activity harms the Platform, its users, or the wider public.</p>

        <h2>17. Severability and Entire Agreement</h2>
        <p>If any provision of these Terms is found to be invalid, illegal, or unenforceable, that provision shall be modified to the minimum extent necessary to make it enforceable, or if it cannot be so modified, it shall be severed. The remaining provisions shall continue in full force and effect. Your continued use of the Platform after a change to these Terms constitutes your full acceptance of the revised Terms.</p>

        <h2>18. Governing Law</h2>
        <p>These Terms are governed by and construed in accordance with the laws of the Republic of Rwanda. Any dispute arising out of or in connection with these Terms shall be subject to the exclusive jurisdiction of the competent courts of Rwanda.</p>

        <h2>19. Contact</h2>
        <p>For questions about these Terms, to report a fraudulent listing, or to request the removal of content, please contact us at <a href="mailto:contact@akazinet.com" class="text-primary font-medium hover:underline">contact@akazinet.com</a>, via the <a href="/pages/contact" class="text-primary font-medium hover:underline">Contact page</a>, or on WhatsApp at <a href="https://wa.me/250780092252" class="text-primary font-medium hover:underline" target="_blank" rel="noopener">+250 780 092 252</a>. We aim to acknowledge reports within two working days.</p>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script src="/assets/js/main.js"></script>
</body>
</html>
