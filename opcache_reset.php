<?php
header('Content-Type: text/plain');
$reset = function_exists('opcache_reset') ? @opcache_reset() : null;
echo 'opcache_reset=' . var_export($reset, true) . PHP_EOL;
$st = function_exists('opcache_get_status') ? @opcache_get_status(false) : null;
echo 'opcache_enabled=' . (is_array($st) && isset($st['opcache_enabled']) ? var_export($st['opcache_enabled'], true) : 'n/a') . PHP_EOL;
$res = @unlink(__FILE__);
echo 'self_cleaned=' . var_export($res, true) . PHP_EOL;