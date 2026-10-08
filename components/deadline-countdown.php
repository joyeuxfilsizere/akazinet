<?php
function render_deadline_countdown($deadline, $variant = 'card') {
    $timestamp = !empty($deadline) ? strtotime($deadline) : false;
    $isDetail = $variant === 'detail';

    if ($timestamp === false) {
?>
        <div class="job-deadline-countdown job-deadline-countdown--<?= $isDetail ? 'detail' : 'card' ?> job-deadline-countdown--open">
            <div class="job-deadline-countdown__label">
                <i class="fas fa-infinity" aria-hidden="true"></i>
                <span>Application deadline</span>
            </div>
            <strong>Open-ended</strong>
        </div>
<?php
        return;
    }

    $remaining = max(0, $timestamp - time());
    $days = (int) floor($remaining / 86400);
    $hours = (int) floor(($remaining % 86400) / 3600);
    $minutes = (int) floor(($remaining % 3600) / 60);
    $seconds = (int) ($remaining % 60);
    $initial = $remaining === 0
        ? 'Expired'
        : (($days > 0 ? $days . ($days === 1 ? ' day ' : ' days ') : '')
            . $hours . 'h ' . $minutes . 'm ' . $seconds . 's left');
    $urgent = $remaining > 0 && $remaining <= 3 * 86400;
    $expired = $remaining === 0;
    $isoDeadline = date('c', $timestamp);
?>
<div class="job-deadline-countdown job-deadline-countdown--<?= $isDetail ? 'detail' : 'card' ?><?= $urgent ? ' is-urgent' : '' ?><?= $expired ? ' is-expired' : '' ?>" data-deadline-countdown data-deadline="<?= sanitize($isoDeadline) ?>" aria-live="polite">
    <div class="job-deadline-countdown__label">
        <i class="fas fa-clock" aria-hidden="true"></i>
        <span>Application deadline</span>
    </div>
    <strong data-countdown-text><?= sanitize($initial) ?></strong>
    <span class="job-deadline-countdown__date">Closes <?= sanitize(format_deadline($deadline)) ?></span>
</div>
<?php
}
?>
