<?php
function render_pagination($pagination, $baseUrl = '?') {
    if ($pagination['total_pages'] <= 1) return;

    $separator = str_contains($baseUrl, '?') ? '&' : '?';
?>
<nav class="flex items-center justify-center space-x-2 mt-10" aria-label="Pagination">
    <?php if ($pagination['has_prev']): ?>
    <a href="<?= $baseUrl . $separator . 'page=' . ($pagination['current_page'] - 1) ?>" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-brand-border rounded-lg hover:bg-primary hover:text-white hover:border-primary transition-all duration-300">
        <i class="fas fa-chevron-left mr-1"></i> Prev
    </a>
    <?php endif; ?>

    <?php
    $start = max(1, $pagination['current_page'] - 2);
    $end = min($pagination['total_pages'], $pagination['current_page'] + 2);

    if ($start > 1): ?>
    <a href="<?= $baseUrl . $separator . 'page=1' ?>" class="px-3 py-2 text-sm font-medium text-gray-600 bg-white border border-brand-border rounded-lg hover:bg-primary hover:text-white transition-all">1</a>
    <?php if ($start > 2): ?><span class="text-gray-400">...</span><?php endif; ?>
    <?php endif; ?>

    <?php for ($i = $start; $i <= $end; $i++): ?>
    <a href="<?= $baseUrl . $separator . 'page=' . $i ?>" class="px-3 py-2 text-sm font-medium rounded-lg transition-all duration-300 <?= $i === $pagination['current_page'] ? 'bg-primary text-white border border-primary' : 'text-gray-600 bg-white border border-brand-border hover:bg-primary hover:text-white hover:border-primary' ?>">
        <?= $i ?>
    </a>
    <?php endfor; ?>

    <?php if ($end < $pagination['total_pages']): ?>
    <?php if ($end < $pagination['total_pages'] - 1): ?><span class="text-gray-400">...</span><?php endif; ?>
    <a href="<?= $baseUrl . $separator . 'page=' . $pagination['total_pages'] ?>" class="px-3 py-2 text-sm font-medium text-gray-600 bg-white border border-brand-border rounded-lg hover:bg-primary hover:text-white transition-all"><?= $pagination['total_pages'] ?></a>
    <?php endif; ?>

    <?php if ($pagination['has_next']): ?>
    <a href="<?= $baseUrl . $separator . 'page=' . ($pagination['current_page'] + 1) ?>" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-brand-border rounded-lg hover:bg-primary hover:text-white hover:border-primary transition-all duration-300">
        Next <i class="fas fa-chevron-right ml-1"></i>
    </a>
    <?php endif; ?>
</nav>
<?php
}
?>
