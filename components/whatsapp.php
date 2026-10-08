<?php
function render_whatsapp_button($jobTitle, $companyName, $whatsappNumber = '250780092252', $jobDeadline = null) {
    $encoded = urlencode(job_help_message($jobTitle, $companyName, $jobDeadline));
    $url = "https://wa.me/{$whatsappNumber}?text={$encoded}";
?>
<div class="bg-gradient-to-r from-green-50 to-green-100/50 border border-green-200 rounded-xl p-6 mt-8">
    <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="fab fa-whatsapp text-white text-2xl"></i>
            </div>
            <div>
                <h4 class="font-semibold text-gray-900">Need Assistance?</h4>
                <p class="text-sm text-gray-600">Contact us on WhatsApp for more information about this vacancy.</p>
            </div>
        </div>
        <a href="<?= $url ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-6 py-3 bg-green-500 text-white font-semibold rounded-xl hover:bg-green-600 transition-all duration-300 hover:shadow-lg hover:shadow-green-500/25 flex-shrink-0">
            <i class="fab fa-whatsapp mr-2 text-lg"></i> Contact Us on WhatsApp
        </a>
    </div>
</div>
<?php
}
?>
