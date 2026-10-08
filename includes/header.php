<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../components/ad-banner.php';
require_once __DIR__ . '/../includes/schema.php';

$siteName = SITE_NAME;
$siteTagline = get_setting('site_tagline', 'Connecting Rwandans to Career Opportunities');
$whatsappNumber = get_setting('whatsapp_number', '250780092252');
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
$metaDescription = $meta_description ?? 'AkaziNet - Rwanda\'s premier job vacancy platform connecting professionals with career opportunities.';
$canonicalUrl = canonical_url();
// Pages may set $meta_robots before including this header. Filtered or empty
// listings use this to keep thin duplicate pages out of the index while still
// letting crawlers follow their links.
$metaRobots = $meta_robots ?? 'index, follow';
?>
    <meta name="description" content="<?= sanitize($metaDescription) ?>">
    <meta name="robots" content="<?= sanitize($metaRobots) ?>">
    <meta name="keywords" content="jobs Rwanda, vacancies, career, employment, AkaziNet, Kigali jobs">
    <meta name="author" content="AkaziNet">
    <meta name="rating" content="general">
    <meta name="google-adsense-account" content="ca-pub-1376469203299537">
    <meta name="google-adsense-format" content="auto">
    <meta name="theme-color" content="#0f172a">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1376469203299537"
     crossorigin="anonymous"></script>
    <link rel="canonical" href="<?= sanitize($canonicalUrl) ?>">
    <link rel="alternate" hreflang="en" href="<?= sanitize($canonicalUrl) ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="AkaziNet">
    <meta property="og:title" content="<?= sanitize(isset($pageTitle) ? $pageTitle . ' | ' . $siteName : $siteName) ?>">
    <meta property="og:description" content="<?= sanitize($metaDescription) ?>">
    <meta property="og:url" content="<?= sanitize($canonicalUrl) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" type="image/svg+xml" href="/assets/images/logo-icon.svg">
    <title><?= sanitize(isset($pageTitle) ? $pageTitle . ' | ' . $siteName : $siteName . ' - ' . $siteTagline) ?></title>

    <link rel="stylesheet" href="/assets/css/tailwind.css?v=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css?v=4">
<?php
// A page that needs a third-party stylesheet (the Leaflet map on the contact
// page) sets $extraHead to raw <link> markup before including this file. It is
// developer-written markup in a PHP source file, never request input, so it is
// echoed rather than escaped.
if (!empty($extraHead)) {
    echo $extraHead;
}
?>
<?php
// Site-level identity schema on every page. Individual pages append their own
// node types (JobPosting, Article, BreadcrumbList) via $extraSchema before
// including this file.
akazinet_ld_emit(akazinet_site_schema());

if (!empty($extraSchema)) {
    akazinet_ld_emit($extraSchema);
}

$autoBreadcrumbs = isset($breadcrumbs) && is_array($breadcrumbs) ? $breadcrumbs : [];
if (!empty($autoBreadcrumbs)) {
    akazinet_ld_emit(akazinet_breadcrumbs($autoBreadcrumbs));
}
?>
</head>
<body class="font-sans text-gray-800 bg-white antialiased">
