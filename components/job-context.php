<?php
/**
 * Application guidance shown under a vacancy.
 *
 * A vacancy page that only reproduces the employer's advert is republished
 * content and gets discounted. This adds an original layer written by us: a
 * short, practical checklist the reader can act on before applying.
 *
 * Note: this file previously also rendered an editorial context panel (pay
 * expectations, skills employers screen for, and a location summary). That was
 * removed at the client's request. It carried speculative salary bands for
 * roles, which is a poor thing to publish next to a real employer's advert:
 * readers reasonably take it as the employer having said so.
 */

if (!function_exists('render_job_apply_tips')) {
    function render_job_apply_tips(array $job, bool $isExpired = false)
    {
        ?>
        <div class="bg-white rounded-xl p-6 md:p-8 border border-brand-border shadow-sm">
            <h2 class="text-xl font-bold text-gray-900 mb-1 flex items-center">
                <span class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-lightbulb text-amber-500 text-sm"></i>
                </span>
                Before you apply to this role
            </h2>
            <p class="text-gray-500 text-sm mb-5">A short checklist that meaningfully improves your response rate.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3">
                <?php
                $tips = [
                    'Tailor your CV to this advert. Name the employer and the role on the first line, and mirror the exact terminology they used in the requirements.',
                    'Attach a CV saved as a PDF with your full name as the filename, and keep it to two pages unless the employer asks for more.',
                    'Write a short cover note that answers one question they will care about: why you want this specific role at this specific organisation.',
                    'Match every requirement in the listing to a line in your CV. If you meet eight of ten requirements, say so plainly rather than letting them assume.',
                    'Apply while the vacancy is fresh. Employer response times in Rwanda are typically fastest in the first week after a vacancy is posted.',
                    'Never pay a recruitment fee. No legitimate employer charges a candidate to register, process an application, or secure an interview.',
                    'Verify the recruiter independently. Search the company name and phone number before you share your national ID or travel anywhere.',
                    'Keep a record. Note the date you applied, the channel you used and who you spoke to, in case you need it later.',
                ];
                foreach ($tips as $index => $tip):
                ?>
                <div class="flex items-start">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-primary text-white text-xs font-bold flex items-center justify-center mt-0.5 mr-3"><?= $index + 1 ?></span>
                    <p class="text-gray-600 text-sm leading-relaxed"><?= sanitize($tip) ?></p>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-100">
                <h3 class="font-bold text-gray-900 mb-3">More help with this application</h3>
                <div class="flex flex-wrap gap-3">
                    <a href="/pages/guide-detail?slug=fake-job-scams-in-rwanda" class="inline-flex items-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium rounded-lg transition-colors">
                        <i class="fas fa-shield-halved mr-2 text-primary"></i> Spot a fake job offer
                    </a>
                    <a href="/pages/guide-detail?slug=how-to-write-a-cv-that-gets-shortlisted" class="inline-flex items-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium rounded-lg transition-colors">
                        <i class="fas fa-file-alt mr-2 text-primary"></i> Improve your CV
                    </a>
                    <a href="/pages/guide-detail?slug=answering-interview-questions-in-rwanda" class="inline-flex items-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium rounded-lg transition-colors">
                        <i class="fas fa-comments mr-2 text-primary"></i> Prepare for the interview
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
}
