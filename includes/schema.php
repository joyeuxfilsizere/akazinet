<?php
/**
 * Structured data (schema.org JSON-LD) helpers.
 *
 * Google reads JSON-LD to understand the site. AkaziNet needs it for:
 *   - Organization / WebSite identity on every page (brand + trust signals)
 *   - BreadcrumbList (replaces the raw URL in the SERP)
 *   - JobPosting on vacancy pages (required by Google's job posting guidance
 *     and the main source of rich results for a jobs site)
 *   - ItemList on listing pages
 *   - Article on editorial guides
 *   - FAQPage where questions are answered on-page
 */

if (!function_exists('akazinet_ld_url')) {
    function akazinet_ld_url(string $path = '/'): string
    {
        if ($path === '') {
            $path = '/';
        }
        if (preg_match('~^https?://~i', $path)) {
            return $path;
        }
        return SITE_URL . '/' . ltrim($path, '/');
    }
}

if (!function_exists('akazinet_ld_date')) {
    function akazinet_ld_date(?string $value): ?string
    {
        if (empty($value) || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return null;
        }
        $ts = strtotime($value);
        return $ts ? date('Y-m-d', $ts) : null;
    }
}

if (!function_exists('akazinet_ld_emit')) {
    function akazinet_ld_emit(array $data): void
    {
        if (empty($data)) {
            return;
        }
        $json = json_encode(
            $data,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );
        if ($json === false) {
            return;
        }
        echo '<script type="application/ld+json">' . $json . '</script>' . "\n";
    }
}

if (!function_exists('akazinet_site_schema')) {
    function akazinet_site_schema(): array
    {
        $email = trim((string)get_setting('site_email', ''));
        $phone = trim((string)get_setting('site_phone', ''));
        // The default describes only what the site actually does. "Premier" and
        // "verified" were removed because neither can be evidenced: nothing
        // confirms an employer or a listing beyond the pre-publication review,
        // and the claim was being published as structured data to search engines.
        $description = trim((string)get_setting('site_description', 'AkaziNet is a job vacancy platform for Rwanda, listing jobs and scholarships with their closing dates so applicants can see how long they have to apply.'));

        $sameAs = [];
        foreach (['facebook_url', 'twitter_url', 'linkedin_url', 'instagram_url', 'whatsapp_group_link'] as $key) {
            $url = trim((string)get_setting($key, ''));
            if ($url !== '' && preg_match('~^https://~i', $url) && !in_array($url, $sameAs, true)) {
                $sameAs[] = $url;
            }
        }

        $organization = [
            '@type' => 'Organization',
            '@id' => SITE_URL . '/#organization',
            'name' => SITE_NAME,
            'url' => SITE_URL . '/',
            'description' => $description,
            'logo' => [
                '@type' => 'ImageObject',
                'url' => akazinet_ld_url('/assets/images/logo-icon.svg'),
            ],
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => SITE_CITY,
                'addressRegion' => SITE_PROVINCE,
                'addressCountry' => 'RW',
            ],
            // Organization.location is the only place geo is valid here; a
            // PostalAddress has no geo property, so the coordinates go on the
            // containing Place.
            'location' => [
                '@type' => 'Place',
                'name' => SITE_ADDRESS,
                'geo' => [
                    '@type' => 'GeoCoordinates',
                    'latitude' => (float)SITE_LAT,
                    'longitude' => (float)SITE_LNG,
                ],
            ],
        ];

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $organization['email'] = $email;
        }
        if ($phone !== '') {
            $organization['telephone'] = $phone;
        }
        if (!empty($sameAs)) {
            $organization['sameAs'] = $sameAs;
        }

        $website = [
            '@type' => 'WebSite',
            '@id' => SITE_URL . '/#website',
            'url' => SITE_URL . '/',
            'name' => SITE_NAME,
            'inLanguage' => 'en',
            'publisher' => ['@id' => SITE_URL . '/#organization'],
        ];

        $searchPage = [
            '@type' => 'WebPage',
            '@id' => SITE_URL . '/#webpage',
            'url' => SITE_URL . '/',
            'name' => SITE_NAME,
            'isPartOf' => ['@id' => SITE_URL . '/#website'],
            'about' => ['@id' => SITE_URL . '/#organization'],
            'inLanguage' => 'en',
        ];

        return [
            '@context' => 'https://schema.org',
            '@graph' => [$organization, $website, $searchPage],
        ];
    }
}

if (!function_exists('akazinet_breadcrumbs')) {
    /**
     * @param array $crumbs List of ['name' => string, 'url' => string]
     */
    function akazinet_breadcrumbs(array $crumbs): array
    {
        $items = [];
        $position = 1;
        foreach ($crumbs as $crumb) {
            if (empty($crumb['name'])) {
                continue;
            }
            $item = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => trim((string)$crumb['name']),
            ];
            if (!empty($crumb['url'])) {
                $item['item'] = akazinet_ld_url($crumb['url']);
            }
            $items[] = $item;
            $position++;
        }

        if (count($items) < 2) {
            return [];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }
}

if (!function_exists('akazinet_item_list')) {
    /**
     * @param array $items List of ['name' => string, 'url' => string]
     */
    function akazinet_item_list(array $items, string $name): array
    {
        $elements = [];
        $position = 1;
        foreach ($items as $item) {
            if (empty($item['url'])) {
                continue;
            }
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'url' => akazinet_ld_url($item['url']),
                'name' => trim((string)($item['name'] ?? $item['url'])),
            ];
            $position++;
        }

        if (empty($elements)) {
            return [];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $name,
            'numberOfItems' => count($elements),
            'itemListElement' => $elements,
        ];
    }
}

if (!function_exists('akazinet_job_posting')) {
    /**
     * Build a JobPosting graph node for a vacancy row.
     *
     * Google requires title, description, datePosted, hiringOrganization and
     * jobLocation. Everything else is optional but improves the rich result.
     */
    function akazinet_job_posting(array $job): array
    {
        $locationParts = array_filter([
            trim((string)($job['location'] ?? '')),
            trim((string)($job['district'] ?? '')),
            trim((string)($job['province'] ?? '')),
        ]);

        $addressRegion = trim((string)($job['province'] ?? '')) ?: 'Kigali';
        $addressLocality = trim((string)($job['district'] ?? '')) ?: trim((string)($job['location'] ?? ''));

        $jobLocation = [
            '@type' => 'Place',
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $addressLocality !== '' ? $addressLocality : 'Kigali',
                'addressRegion' => $addressRegion,
                'addressCountry' => 'RW',
            ],
        ];

        if (!empty($locationParts)) {
            $jobLocation['name'] = implode(', ', array_unique($locationParts));
        }

        $description = '';
        foreach (['description', 'responsibilities', 'requirements', 'qualifications', 'benefits', 'how_to_apply'] as $field) {
            if (!empty($job[$field])) {
                $description .= "\n\n" . str_ireplace(['<br>', '<br/>', '<br />', '</p>', '</li>', '</h2>', '</h3>'], ["\n", "\n", "\n", "\n", "\n", "\n", "\n"], (string)$job[$field]);
            }
        }
        $description = trim(html_entity_decode(strip_tags($description), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($description === '') {
            $description = trim((string)$job['title']) . ' — ' . SITE_NAME;
        }
        if (mb_strlen($description) > 5000) {
            $description = mb_substr($description, 0, 4997) . '...';
        }

        $posting = [
            '@type' => 'JobPosting',
            '@id' => SITE_URL . '/job-details?slug=' . rawurlencode($job['slug']) . '#posting',
            'url' => SITE_URL . '/job-details?slug=' . rawurlencode($job['slug']),
            'title' => trim((string)$job['title']),
            'description' => $description,
            'datePosted' => akazinet_ld_date($job['created_at'] ?? null) ?? date('Y-m-d'),
            'employmentType' => strtoupper(str_replace('-', '_', trim((string)($job['employment_type'] ?? 'FULL_TIME')))),
            'hiringOrganization' => [
                '@type' => 'Organization',
                'name' => trim((string)($job['company_name'] ?: SITE_NAME)),
                'sameAs' => SITE_URL . '/',
            ],
            'jobLocation' => $jobLocation,
        ];

        if (!empty($job['company_logo'])) {
            $posting['hiringOrganization']['logo'] = akazinet_ld_url('/assets/uploads/' . $job['company_logo']);
        }

        $validThrough = akazinet_ld_date($job['application_deadline'] ?? null);
        if ($validThrough !== null) {
            $posting['validThrough'] = $validThrough;
        }

        if (!empty($job['updated_at'])) {
            $updated = akazinet_ld_date($job['updated_at']);
            if ($updated !== null) {
                $posting['dateModified'] = $updated;
            }
        }

        if (!empty($job['education_level'])) {
            $posting['educationRequirements'] = [
                '@type' => 'EducationalOccupationalCredential',
                'credentialCategory' => trim((string)$job['education_level']),
            ];
        }

        if (!empty($job['experience_level'])) {
            $posting['experienceRequirements'] = [
                '@type' => 'OccupationalExperienceRequirements',
                'monthsOfExperience' => 0,
                'description' => trim((string)$job['experience_level']),
            ];
        }

        if (!empty($job['is_active'])) {
            $posting['directApply'] = !empty($job['application_url']) || !empty($job['application_email']);
        }

        return array_merge($posting, [
            '@context' => 'https://schema.org',
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $posting['url'],
            ],
            'publisher' => ['@id' => SITE_URL . '/#organization'],
        ]);
    }
}

if (!function_exists('akazinet_article')) {
    function akazinet_article(array $guide): array
    {
        $url = SITE_URL . '/pages/guide-detail?slug=' . rawurlencode($guide['slug']);

        $wordCount = max(1, (int)str_word_count(strip_tags((string)$guide['content'])));
        $published = akazinet_ld_date($guide['published_at'] ?? $guide['created_at'] ?? null) ?? date('Y-m-d');
        $modified = akazinet_ld_date($guide['updated_at'] ?? null) ?: $published;

        $article = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            '@id' => $url . '#article',
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url],
            'headline' => mb_substr(trim((string)$guide['title']), 0, 110),
            'description' => trim((string)($guide['excerpt'] ?? '')),
            'url' => $url,
            'datePublished' => $published,
            'dateModified' => $modified,
            'wordCount' => $wordCount,
            'inLanguage' => 'en',
            'articleSection' => trim((string)($guide['category'] ?? 'Career Advice')) ?: 'Career Advice',
            'author' => [
                '@type' => 'Organization',
                'name' => SITE_NAME,
                'url' => SITE_URL . '/',
            ],
            'publisher' => ['@id' => SITE_URL . '/#organization'],
            'isPartOf' => ['@id' => SITE_URL . '/#website'],
        ];

        // read_minutes is shown on the page as "X min read", so state it here too.
        $readMinutes = (int)($guide['read_minutes'] ?? 0);
        if ($readMinutes > 0) {
            $article['timeRequired'] = 'PT' . $readMinutes . 'M';
        }

        return $article;
    }
}

if (!function_exists('akazinet_faq')) {
    /**
     * @param array $questions List of ['question' => string, 'answer' => string]
     */
    function akazinet_faq(array $questions): array
    {
        $entities = [];
        foreach ($questions as $item) {
            if (empty($item['question']) || empty($item['answer'])) {
                continue;
            }
            $entities[] = [
                '@type' => 'Question',
                'name' => trim(strip_tags((string)$item['question'])),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => trim(strip_tags((string)$item['answer'])),
                ],
            ];
        }

        if (empty($entities)) {
            return [];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $entities,
        ];
    }
}