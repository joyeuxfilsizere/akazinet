<?php
/**
 * AkaziNet Configuration
 *
 * The de-facto kernel: every page requires this file for the PDO handle and
 * the helper functions. Credentials come from the environment (see
 * config/env.php) and are never written here, so this file is safe to read and
 * to diff.
 */

require_once __DIR__ . '/env.php';

// Database. Defaults are empty on purpose: a silent fallback password would let
// the site start up against the wrong database instead of failing visibly.
env_define('DB_HOST', 'DB_HOST', 'localhost');
env_define('DB_NAME', 'DB_NAME', '');
env_define('DB_USER', 'DB_USER', '');
env_define('DB_PASS', 'DB_PASS', '');
env_define('DB_CHARSET', 'DB_CHARSET', 'utf8mb4');

env_define('SITE_NAME', 'SITE_NAME', 'AkaziNet');
env_define('SITE_URL', 'SITE_URL', 'https://akazinet.com');
define('BASE_PATH', dirname(__DIR__));

/**
 * Where the office is, defined once.
 *
 * The address is published in six places: the contact page card, the map and its
 * directions link, the footer, the privacy policy, and schema.org JSON-LD. They
 * previously each hardcoded "Kigali, Rwanda" independently, which is how a site
 * ends up contradicting itself after a move.
 *
 * The coordinates are the centre of Kayonza town (GeoNames 202090), not a
 * building: we publish a town-level pin rather than claiming a street address we
 * cannot evidence. Read as floats at the point of use, because env_define()
 * always yields a string.
 */
env_define('SITE_CITY', 'SITE_CITY', 'Kayonza');
env_define('SITE_PROVINCE', 'SITE_PROVINCE', 'Eastern Province');
env_define('SITE_LAT', 'SITE_LAT', '-1.8847');
env_define('SITE_LNG', 'SITE_LNG', '30.5168');

/**
 * The one-line postal address, for prose. Kept next to the constants above so a
 * change of town cannot leave a stale line in a policy document.
 */
define('SITE_ADDRESS', SITE_CITY . ', ' . SITE_PROVINCE . ', Rwanda');

define('UPLOAD_PATH', BASE_PATH . '/assets/uploads/');
define('LOGO_PATH', UPLOAD_PATH . 'logos/');
define('DOC_PATH', UPLOAD_PATH . 'documents/');
/**
 * The timezone every date on this site is interpreted in.
 *
 * Deadlines are Rwandan civil dates: an employer writes "closes 8 October" and
 * means the whole of 8 October in Kigali. If that were read in the host's
 * timezone the boundary would move, because shared hosting commonly runs UTC
 * and a server in Europe/Berlin disagrees with both Kigali and the database.
 */
if (!defined('AKAZINET_SITE_TIMEZONE')) {
    define('AKAZINET_SITE_TIMEZONE', 'Africa/Kigali');
}
date_default_timezone_set(AKAZINET_SITE_TIMEZONE);

/**
 * Today's date in the site timezone, as Y-m-d.
 *
 * The single reference point for "is this deadline still open". Both the PHP
 * helper and the SQL fragment below must agree on this value or a listing will
 * be visible on the pages that use one and hidden on the pages that use the
 * other.
 */
function akazinet_site_today()
{
    return date('Y-m-d');
}

/**
 * The civil date a deadline timestamp falls on, in the site timezone.
 *
 * Kept as a function rather than an inline date() so the comparison in
 * akazinet_is_expired() names the rule it is applying. Deadlines arrive both as
 * bare "Y-m-d H:i:s" strings and as UTC ISO strings from the client-side
 * countdown, and this is what makes both land on the same civil day.
 */
function akazinet_site_date($timestamp)
{
    $zone = new DateTimeZone(AKAZINET_SITE_TIMEZONE);
    $moment = new DateTimeImmutable('@' . (int)$timestamp);
    return $moment->setTimezone($zone)->format('Y-m-d');
}

define('MAX_UPLOAD_SIZE', env_int('MAX_UPLOAD_SIZE', 10 * 1024 * 1024));
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'jfif', 'png', 'gif', 'webp', 'svg', 'avif', 'heic', 'heif', 'bmp', 'tiff', 'tif', 'ico', 'jpe', 'jp2']);
define('ALLOWED_DOCUMENT_EXTENSIONS', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'webp']);

if (session_status() !== PHP_SESSION_ACTIVE) {
    // The `secure` flag is derived rather than hardcoded so the same file works
    // on the production HTTPS host and on plain-HTTP local XAMPP. Behind a proxy
    // PHP sees the hop as HTTP, so the forwarded protocol header is consulted
    // too; otherwise the cookie would be dropped on every request in production.
    $isHttps = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');

    session_name(env('SESSION_NAME', 'AKAZINETSESSID'));
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        // httponly keeps the session id out of reach of any script that manages
        // to inject markup; samesite=Lax blocks cross-site POSTs, which is the
        // CSRF class verify_csrf() covers in depth.
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// The test suite defines AKAZINET_NO_DB so the pure helper functions below can
// be exercised without a database. Without this guard the connection is
// attempted anyway and the whole suite dies on a missing server.
if (!defined('AKAZINET_NO_DB')) {
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        // CURDATE() decides which listings are still open, so the connection's
        // timezone has to match the one PHP just set. A host running UTC would
        // otherwise roll the date over eight hours before Kigali does, briefly
        // hiding a whole day of live deadlines. A numeric offset is used rather
        // than the named zone because that is the only form settable without the
        // server's timezone tables being loaded.
        $utcOffset = date('P');
        if (preg_match('/^[+-]\d{2}:\d{2}$/', $utcOffset)) {
            $pdo->exec("SET time_zone = " . $pdo->quote($utcOffset));
        }
    } catch (PDOException $e) {
        // The driver message names the database, the user and often the host, so
        // it is shown only when APP_DEBUG is explicitly on. A public error page
        // gets the generic text plus a Retry-After, which is what a visitor
        // needs and nothing an attacker wants.
        $detail = env_bool('APP_DEBUG', false) ? ' ' . $e->getMessage() : '';

        if (!headers_sent()) {
            http_response_code(503);
            header('Retry-After: 60');
        }
        die('The site is temporarily unavailable. Please try again shortly.' . $detail);
    }
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Check a CSRF token against the one issued with the form.
 *
 * Called with no argument it reads $_POST['csrf_token'], which is the common
 * case in a POST handler. Both the token and the stored value are attacker-
 * controlled shapes, so anything that is not a string is rejected outright:
 * hash_equals() raises a TypeError on an array in PHP 8, and $_POST['csrf_token']
 * can genuinely be an array via `csrf_token[]=x`.
 */
function verify_csrf($token = null) {
    if ($token === null) {
        $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null;
    }

    if (!is_string($token) || $token === '') {
        return false;
    }

    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        return false;
    }

    // Constant-time comparison, so a wrong token leaks no information about how
    // much of it was correct.
    return hash_equals($_SESSION['csrf_token'], $token);
}

function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    $value = trim((string)$input);
    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function generate_slug($string) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

function canonical_url() {
    $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    $path = parse_url($requestUri, PHP_URL_PATH);
    if ($path === null || $path === '') {
        $path = '/';
    }
    $path = '/' . ltrim($path, '/');

    $basePath = '';
    if (defined('BASE_PATH') && isset($_SERVER['DOCUMENT_ROOT'])) {
        $docRoot = realpath($_SERVER['DOCUMENT_ROOT']);
        $base = realpath(BASE_PATH);
        if ($docRoot && $base && strpos($base, $docRoot) === 0) {
            $basePath = str_replace('\\', '/', substr($base, strlen($docRoot)));
            $basePath = rtrim($basePath, '/');
        }
    }
    if ($basePath !== '' && strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
        $path = $path === '' ? '/' : $path;
    }

    $keep = ['slug', 'id', 'guide', 'category', 'province', 'featured'];
    $query = [];
    if (isset($_GET) && is_array($_GET)) {
        foreach ($keep as $key) {
            if (isset($_GET[$key]) && $_GET[$key] !== '' && !is_array($_GET[$key])) {
                $query[$key] = (string)$_GET[$key];
            }
        }
    }

    $url = SITE_URL . ($path === '/' ? '/' : rtrim($path, '/'));
    if (!empty($query)) {
        $url .= '?' . http_build_query($query);
    }
    return $url;
}

function redirect($url, $permanent = false) {
    if ($permanent) {
        header("Location: $url", true, 301);
    } else {
        header("Location: $url");
    }
    exit;
}

function is_admin_logged_in() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function require_admin_login() {
    if (!is_admin_logged_in()) {
        redirect('/login');
    }
}

function get_setting($key, $default = '') {
    global $pdo;
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['setting_value'] : $default;
}

function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function upload_image($file, $directory = 'logos') {
    $targetDir = UPLOAD_PATH . $directory . '/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'message' => 'Invalid file type. Allowed: ' . implode(', ', ALLOWED_EXTENSIONS)];
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'message' => 'File too large. Max ' . round(MAX_UPLOAD_SIZE / 1024 / 1024) . 'MB.'];
    }

    $check = @getimagesize($file['tmp_name']);
    $isImage = $check !== false;
    if (!$isImage) {
        $finfo = @finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? @finfo_file($finfo, $file['tmp_name']) : '';
        if (is_string($mime) && strpos($mime, 'image/') === 0) {
            $isImage = true;
        }
    }
    if (!$isImage) {
        return ['success' => false, 'message' => 'File is not a valid image.'];
    }

    $filename = uniqid('img_') . '.' . $ext;
    $targetPath = $targetDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $directory . '/' . $filename];
    }

    return ['success' => false, 'message' => 'Failed to upload file.'];
}

function upload_document($file) {
    $targetDir = DOC_PATH;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload failed.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_DOCUMENT_EXTENSIONS)) {
        return ['success' => false, 'message' => 'Invalid file type. Allowed: ' . implode(', ', ALLOWED_DOCUMENT_EXTENSIONS)];
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'message' => 'File too large. Max 10MB.'];
    }

    $filename = uniqid('doc_') . '.' . $ext;
    $targetPath = $targetDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => 'documents/' . $filename];
    }

    return ['success' => false, 'message' => 'Failed to upload file.'];
}

function contains_html($str) {
    return $str !== null && preg_match('/<[a-zA-Z!\/][^>]*>/', $str);
}

/**
 * Bolds section headings inside plain-text listings.
 *
 * Scholarship and job bodies are typed into ordinary textareas rather than a
 * rich editor, so publishers naturally put a heading on its own line:
 *
 *     About This Scholarship
 *     The award covers tuition, accommodation and a monthly stipend.
 *
 *     Eligibility
 *     - Rwandan national or resident
 *
 * None of that structure reaches the browser, so readers are left to scan one
 * undifferentiated block. A line is treated as a heading only when it is short,
 * is not a list item / URL / email, does not end a sentence, and is either
 * upper case, ends with a colon, or is a terse title sitting above a longer
 * paragraph.
 */
function format_plain_text_headings($text) {
    $lines = preg_split('/\r\n|\r|\n/', $text);
    if (!is_array($lines)) {
        return $text;
    }

    $total = count($lines);
    for ($i = 0; $i < $total; $i++) {
        $line = trim($lines[$i]);
        if ($line !== '' && looks_like_heading($line, next_non_empty_line($lines, $i))) {
            $lines[$i] = '<strong>' . $line . '</strong>';
        }
    }

    return implode("\n", $lines);
}

function next_non_empty_line(array $lines, $index) {
    $total = count($lines);
    for ($i = $index + 1; $i < $total; $i++) {
        $candidate = trim($lines[$i]);
        if ($candidate !== '') {
            return $candidate;
        }
    }
    return '';
}

function looks_like_heading($line, $nextLine) {
    $length = function_exists('mb_strlen') ? mb_strlen($line, 'UTF-8') : strlen($line);
    if (!is_int($length) || $length === 0 || $length > 70) {
        return false;
    }

    // List items, bullets and numbered lines are content, not headings.
    if (preg_match('/^[\-\x{2022}\x{25CF}\x{25AA}\x{2023}\x{00B7}\x{2013}\x{2014}]\s+/u', $line)
        || preg_match('/^\d+[.)]\s+/', $line)
        || preg_match('/^[a-z][.)]\s+/', $line)) {
        return false;
    }

    // Links and email addresses belong to body copy.
    if (preg_match('~(https?://|www\.|@)~i', $line)) {
        return false;
    }

    // A full stop, comma or semicolon means the line is prose, not a heading.
    if (preg_match('/[.,;]$/', $line)) {
        return false;
    }

    // Headings open with a capital letter, a digit or a quote mark.
    if (!preg_match('/^[\p{Lu}\d"\'\x{201C}\x{201D}]/u', $line)) {
        return false;
    }

    $words = preg_split('/\s+/u', $line, -1, PREG_SPLIT_NO_EMPTY);
    $wordCount = is_array($words) ? count($words) : 0;
    if ($wordCount > 8) {
        return false;
    }

    $letters = preg_replace('/[^\p{L}]/u', '', $line);
    $upper = function_exists('mb_strtoupper') ? mb_strtoupper($letters, 'UTF-8') : strtoupper($letters);
    if ($letters !== '' && $letters === $upper) {
        return true;
    }

    if (substr($line, -1) === ':') {
        return true;
    }

    // A terse, title-cased line directly above a longer paragraph reads as a
    // heading; the same shape mid-paragraph is left untouched.
    $nextLength = function_exists('mb_strlen') ? mb_strlen($nextLine, 'UTF-8') : strlen($nextLine);
    return $wordCount <= 5 && is_int($nextLength) && $nextLength > $length;
}

function render_rich_text($content) {
    if ($content === null || trim($content) === '') {
        return '';
    }

    if (!contains_html($content)) {
        $text = sanitize($content);
        $text = format_plain_text_headings($text);
        $text = preg_replace('/(https?:\/\/[^\s<]+)/', '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-primary font-semibold hover:underline">$1</a>', $text);
        $text = preg_replace('/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/', '<strong>$1</strong>', $text);
        return nl2br($text);
    }

    // Table tags are allowed so editorial content (salary tables, comparison
    // charts) survives rendering. Without them strip_tags() silently deletes
    // the markup and leaves the bare text behind.
    $allowed = '<p><br><strong><b><em><i><u><s><a><ul><ol><li><blockquote><h2><h3><h4><h5>'
        . '<table><thead><tbody><tfoot><tr><th><td><caption><hr><code><pre><sup><sub><dl><dt><dd>';
    $html = strip_tags($content, $allowed);

    $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
    $html = preg_replace('/\s+(?:href|src|xlink:href)\s*=\s*(?:"javascript:[^"]*"|\'javascript:[^\']*\'|javascript:[^\s>]*)/i', '', $html);

    $html = preg_replace_callback('/<a([^>]*)>/i', function ($m) {
        $attrs = $m[1];
        if (preg_match('/\shref\s*=\s*("|\')(.*?)\1/i', $attrs, $h)) {
            $href = trim($h[2]);
            if (!preg_match('~^(https?://|mailto:|/|#)~i', $href)) {
                $attrs = preg_replace('/\shref\s*=\s*("[^"]*"|\'[^\']*\')/i', '', $attrs);
            }
        } elseif (preg_match('/\shref\s*=\s*([^\s>]+)/i', $attrs, $h2)) {
            $href = trim($h2[1], '"\'');
            if (!preg_match('~^(https?://|mailto:|/|#)~i', $href)) {
                $attrs = preg_replace('/\shref\s*=\s*[^\s>]+/i', '', $attrs);
            }
        }
        if (stripos($attrs, 'target=') === false) {
            $attrs .= ' target="_blank"';
        }
        if (stripos($attrs, 'rel=') === false) {
            $attrs .= ' rel="noopener noreferrer"';
        }
        return '<a' . $attrs . '>';
    }, $html);

    // Normalize Quill-style lists so bullets/numbering always render.
    // Quill 2.x outputs BOTH bullet and ordered lists as <ol> and marks the
    // <li> with data-list="bullet" / data-list="ordered". Browsers render an
    // <ol> as numbers, so bullet lists must be converted to <ul>. Inline
    // styles guarantee markers even if the stylesheet is not yet loaded.
    $html = preg_replace_callback('/<(ol|ul)([^>]*)>(.*?)<\/\1>/is', function ($list) {
        $tag = strtolower($list[1]);
        $attrs = $list[2];
        $inner = $list[3];

        $listType = null;
        if (preg_match('/data-list\s*=\s*["\'](bullet|ordered)["\']/i', $attrs, $a)) {
            $listType = $a[1];
        } elseif (preg_match('/<li[^>]*data-list\s*=\s*["\'](bullet|ordered)["\']/i', $inner, $b)) {
            $listType = $b[1];
        }

        if ($listType === 'bullet' || $listType === 'ordered') {
            $newTag = $listType === 'bullet' ? 'ul' : 'ol';
            $marker = $listType === 'bullet' ? 'list-style-type: disc;' : 'list-style-type: decimal;';
            $newInner = preg_replace_callback('/<li(\s[^>]*)?>/i', function ($li) use ($marker) {
                $liAttrs = isset($li[1]) ? $li[1] : '';
                $liAttrs = preg_replace('/\s*data-list\s*=\s*["\'][^"\']*["\']/i', '', $liAttrs);
                $liAttrs .= ' style="' . $marker . '"';
                return '<li' . $liAttrs . '>';
            }, $inner);
            if ($tag === $newTag) {
                $newAttrs = preg_replace('/\s*data-list\s*=\s*["\'][^"\']*["\']/i', '', $attrs);
            } else {
                $newAttrs = '';
            }
            return '<' . $newTag . $newAttrs . ' style="margin:.75rem 0; padding-left:1.5rem; ' . $marker . '">'
                . $newInner . '</' . $newTag . '>';
        }

        return $list[0];
    }, $html);

    return $html;
}

function paginate($total, $perPage, $currentPage) {
    $perPage = max(1, (int)$perPage);
    $total = max(0, (int)$total);
    $totalPages = (int)ceil($total / $perPage);

    // An empty result set still reports page 1 with no navigation links. Clamping
    // against $totalPages alone would collapse the page to 0 and produce a
    // negative offset for the query.
    if ($totalPages < 1) {
        $currentPage = 1;
    } else {
        $currentPage = max(1, min((int)$currentPage, $totalPages));
    }

    $offset = ($currentPage - 1) * $perPage;

    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
    ];
}

function format_deadline($deadline) {
    if (empty($deadline)) {
        return 'Open';
    }
    $formatted = date('F d, Y', strtotime($deadline));
    if (strpos($deadline, ':') !== false) {
        $formatted .= ' at ' . date('g:i A', strtotime($deadline));
    }
    return $formatted;
}

// ---------------------------------------------------------------------------
// Login throttling
//
// Without a lockout /login is an unlimited password oracle. Counters live in the
// `login_attempts` table (database/migration18.sql). That table may not exist on
// an install that has not run migration 18, so every function here falls back to
// a per-session counter rather than letting the login page fatal: a weaker
// throttle is better than no login page at all.
// ---------------------------------------------------------------------------

/**
 * Configured failure threshold before lockout.
 *
 * Guarded against 0 or a negative value: an operator setting LOGIN_MAX_ATTEMPTS=0
 * would otherwise produce either a lockout that never triggers or one that expires
 * the same instant it is created.
 */
function login_max_attempts() {
    $attempts = env_int('LOGIN_MAX_ATTEMPTS', 5);
    return $attempts >= 1 ? $attempts : 5;
}

/**
 * Lockout window in minutes, guarded the same way as the threshold.
 */
function login_lockout_minutes() {
    $minutes = env_int('LOGIN_LOCKOUT_MINUTES', 15);
    return $minutes >= 1 ? $minutes : 15;
}

/**
 * Storage key for an account's failures.
 *
 * A hash rather than the username, because login_attempts is a security log and
 * must not become a list of valid accounts. Lowercased first so 'ADMIN' and
 * 'admin' share one counter: MySQL's own string comparison is case-insensitive,
 * so a case-sensitive key would let an attacker retry the same account under
 * different casing and never reach the threshold.
 */
function login_username_key($username) {
    return md5(strtolower(trim((string)$username)));
}

/**
 * Proxies whose X-Forwarded-For header is trusted, from TRUSTED_PROXIES.
 *
 * @return string[]
 */
function login_trusted_proxies() {
    $raw = env('TRUSTED_PROXIES', '');
    if (!is_string($raw) || trim($raw) === '') {
        return [];
    }

    $proxies = [];
    foreach (explode(',', $raw) as $candidate) {
        $candidate = trim($candidate);
        if ($candidate !== '') {
            $proxies[] = $candidate;
        }
    }

    return $proxies;
}

/**
 * The client IP to count failures against.
 *
 * X-Forwarded-For is honoured ONLY when the immediate peer is a proxy we
 * configured. Otherwise any client could send the header itself and get a fresh
 * per-IP allowance on every request, which would turn the per-IP half of the
 * lockout off completely.
 *
 * Returns 'unknown' rather than '' when even REMOTE_ADDR is missing, so the
 * caller always has a non-empty string to store in the ip_address column.
 */
function login_client_ip() {
    $remote = isset($_SERVER['REMOTE_ADDR']) ? trim((string)$_SERVER['REMOTE_ADDR']) : '';

    $trusted = login_trusted_proxies();
    if ($remote !== '' && in_array($remote, $trusted, true) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // The leftmost entry is the original client; everything after it is
        // hops we added on the way in.
        $parts = explode(',', (string)$_SERVER['HTTP_X_FORWARDED_FOR']);
        $candidate = trim($parts[0]);
        if (filter_var($candidate, FILTER_VALIDATE_IP)) {
            return $candidate;
        }
    }

    if ($remote !== '') {
        return $remote;
    }

    return 'unknown';
}

/**
 * Seconds an account is still locked out for. Zero means it may try again.
 *
 * The database counters are the real ones. The session fallback exists for
 * installs missing migration 18 and is per-session, so it only slows a single
 * visitor down - it is not a security boundary, and the docblock says so.
 */
function login_lockout_remaining($username) {
    global $pdo;

    $key = login_username_key($username);
    $ip = login_client_ip();
    $window = login_max_attempts() >= 1 ? login_lockout_minutes() * 60 : 0;
    $maxAttempts = login_max_attempts();

    // instanceof, not isset(): a stub that is not a real PDO must not reach the
    // prepared-statement path, and the tests substitute exactly such a stub.
    if ($pdo instanceof PDO) {
        try {
            $cutoff = date('Y-m-d H:i:s', time() - $window);
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM login_attempts
                 WHERE attempted_at > ?
                   AND (username_key = ? OR ip_address = ?)"
            );
            $stmt->execute([$cutoff, $key, $ip]);
            $failures = (int)$stmt->fetchColumn();

            if ($failures >= $maxAttempts) {
                // A full window, not a count scaled by failures: the caller turns
                // this into "try again in N minutes", and scaling would report a
                // longer wait than the sliding window actually allows.
                return $window;
            }
        } catch (Exception $e) {
            // Table missing (migration 18 not applied) - fall through to the
            // session counter.
        }
    }

    if (!isset($_SESSION['login_failures']) || !is_array($_SESSION['login_failures'])) {
        return 0;
    }

    $now = time();

    // 'first' is when the current failure window opened, shared by every account
    // counted in it. Backdating it expires the whole window in one step.
    if (isset($_SESSION['login_failures']['first']) && $now - (int)$_SESSION['login_failures']['first'] > $window) {
        unset($_SESSION['login_failures']);
        return 0;
    }

    // md5 keys are 32 hex characters, so this can never collide with the
    // 'first' sentinel above.
    if (!isset($_SESSION['login_failures'][$key]) || !is_array($_SESSION['login_failures'][$key])) {
        return 0;
    }

    $entry = $_SESSION['login_failures'][$key];

    if (isset($entry['first']) && $now - (int)$entry['first'] > $window) {
        unset($_SESSION['login_failures'][$key]);
        return 0;
    }

    $count = isset($entry['count']) ? (int)$entry['count'] : 0;
    if ($count < $maxAttempts) {
        return 0;
    }

    return max(0, $window - ($now - (int)$entry['first']));
}

/**
 * Record one failed login attempt for an account and the client IP.
 */
function login_record_failure($username) {
    global $pdo;

    $key = login_username_key($username);
    $ip = login_client_ip();
    $now = time();
    $window = login_lockout_minutes() * 60;

    if ($pdo instanceof PDO) {
        try {
            $stmt = $pdo->prepare("INSERT INTO login_attempts (username_key, ip_address) VALUES (?, ?)");
            $stmt->execute([$key, $ip]);
        } catch (Exception $e) {
            // No table yet; the session counter below still applies.
        }
    }

    if (!isset($_SESSION['login_failures']) || !is_array($_SESSION['login_failures'])) {
        $_SESSION['login_failures'] = [];
    }

    if (!isset($_SESSION['login_failures']['first']) || $now - (int)$_SESSION['login_failures']['first'] > $window) {
        $_SESSION['login_failures']['first'] = $now;
    }

    if (!isset($_SESSION['login_failures'][$key]) || !is_array($_SESSION['login_failures'][$key])) {
        $_SESSION['login_failures'][$key] = ['count' => 0, 'first' => $now];
    }

    $_SESSION['login_failures'][$key]['count']++;
}

/**
 * Drop the recorded failures for an account after a successful login.
 *
 * Only this account's counter is cleared: an unrelated attacker's failures must
 * survive an admin signing in successfully.
 */
function login_clear_failures($username) {
    $key = login_username_key($username);
    if (isset($_SESSION['login_failures']) && is_array($_SESSION['login_failures'])) {
        unset($_SESSION['login_failures'][$key]);
    }
}

function job_help_message($jobTitle, $companyName, $jobDeadline = null) {
    $msg = "Hello,\nI'm interested in this job position\nJob name: {$jobTitle}\nAt: {$companyName}";
    if (!empty($jobDeadline)) {
        $msg .= "\nApplication Deadline: " . format_deadline($jobDeadline);
    }
    $msg .= "\nI would like you to help me to apply\nThank you 🙏";
    return $msg;
}

/**
 * Whether a listing is past its deadline and should be hidden from visitors.
 *
 * A NULL or blank deadline means "no deadline given", which is NOT the same as
 * expired. Many employers and scholarship providers post without one, and
 * treating those as expired would empty the site. So only a real, past date
 * counts as expired.
 *
 * Compares calendar days rather than clock time: a deadline of today is still
 * open today, and the listing is removed from tomorrow.
 */
function akazinet_is_expired($deadline) {
    if ($deadline === null || trim((string)$deadline) === '') {
        return false;
    }
    $timestamp = strtotime(trim((string)$deadline));
    if ($timestamp === false) {
        return false;
    }
    return akazinet_site_date($timestamp) < akazinet_site_today();
}

/**
 * SQL fragment restricting a listing query to rows visitors may still see.
 *
 * This is applied at query time rather than relying only on auto_expire_jobs().
 * auto_expire_jobs() writes to the database and swallows its own failures, so
 * if that write ever fails the only thing standing between an expired vacancy
 * and the public listing is the query. Enforcing it in the query makes the
 * hiding behaviour independent of a background write succeeding.
 *
 * CURDATE(), not NOW(): the column is a DATETIME (see migration14.sql), and a
 * deadline stored at 00:00 compares as already passed from midnight under
 * NOW(), hiding the whole day applicants were given to apply. CURDATE() keeps
 * the deadline day open and removes the listing from the next day.
 *
 * CURDATE() is safe here because config/database.php pins the connection's
 * session time_zone to the site timezone, so the database's idea of "today"
 * matches akazinet_site_today(). Prefer this helper over inlining the clause:
 * the expired-listing rule then lives in exactly one place.
 */
function akazinet_not_expired_sql($alias = '', $column = 'application_deadline') {
    $prefix = ($alias === '' || $alias === null) ? '' : $alias . '.';
    return "({$prefix}{$column} IS NULL OR {$prefix}{$column} >= CURDATE())";
}

/**
 * The WHERE clause every "listings a visitor may still apply to" query uses.
 *
 * Home page counts, category counts and listing queries each spelled this out
 * separately, so a fix to one of them silently left the others showing a
 * different number. Routing them through one clause keeps the headline counts
 * and the cards underneath them describing the same set of jobs.
 *
 * @param string $alias   Table alias for the listing column, e.g. 'j'.
 * @param string $deadline Deadline column, defaults to the jobs column.
 */
function akazinet_open_listing_sql($alias = 'j', $deadline = 'application_deadline')
{
    $prefix = ($alias === '' || $alias === null) ? '' : $alias . '.';
    return "{$prefix}status = 'published'"
        . " AND {$prefix}is_active = 1"
        . ' AND ' . akazinet_not_expired_sql($prefix === '' ? '' : $alias, $deadline);
}

function auto_expire_jobs() {
    global $pdo;
    try {
        // < CURDATE() rather than < NOW(): see akazinet_not_expired_sql(). A
        // deadline of today must stay visible until tomorrow.
        $pdo->exec("UPDATE jobs SET status = 'archived' WHERE status = 'published' AND application_deadline IS NOT NULL AND application_deadline < CURDATE()");
        // Scholarship listings are hidden by the same rule in their queries, but
        // flipping them inactive here as well keeps admin listings honest and
        // stops expired rows accumulating.
        $pdo->exec("UPDATE scholarship_listings SET is_active = 0 WHERE is_active = 1 AND deadline IS NOT NULL AND deadline < CURDATE()");
    } catch (Exception $e) {
        // silently fail
    }
}

// AKAZINET_NO_DB lets the test suite load these helpers without opening a
// connection, which is what makes the pure functions testable in isolation.
if (!defined('AKAZINET_NO_DB')) {
    auto_expire_jobs();
}
