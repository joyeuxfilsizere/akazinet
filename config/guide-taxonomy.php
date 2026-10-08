<?php
/**
 * Canonical Career Guides taxonomy.
 *
 * Why this is code and not a database table:
 *
 * 1. pages/guides.php used to build its category filter with
 *    "SELECT category, COUNT(*) FROM guides GROUP BY category". That means the
 *    menu only ever showed categories that already had a published guide. With
 *    a near-empty guides table the menu was a handful of chips with no
 *    description, and the index page fell through to "Guides are being
 *    written". A menu is an editorial statement of what a section covers, so it
 *    cannot be derived from whatever content happens to be published today.
 *
 * 2. Each entry carries a description, which the index renders under the
 *    category heading. That is the "context" the section was missing.
 *
 * 3. Ordering and naming stay stable even while guides are being drafted.
 *
 * To add a category: add an entry here with the next unused order number and a
 * slug derived from the name. Nothing else needs editing - the index, the
 * filters, the icons and the sitemap all read from this array.
 *
 * 'slug' is the URL key and must never change once published, because guides
 * link to /pages/guides?category=<slug>. Changing it breaks existing links.
 */

/**
 * @return array<int, array{slug:string,name:string,description:string,icon:string,order:int}>
 */
function akazinet_guide_categories(): array
{
    static $categories = null;

    if ($categories !== null) {
        return $categories;
    }

    $categories = [
        [
            'slug' => 'starting-out',
            'name' => 'Starting Your Career',
            'description' => 'For students, recent graduates and anyone changing direction: finding a first role with no track record, deciding between further study and work, and what employers will accept when you have nothing on paper yet.',
            'icon' => 'fa-seedling',
            'order' => 1,
        ],
        [
            'slug' => 'cvs-cover-letters',
            'name' => 'CVs & Cover Letters',
            'description' => 'The documents that decide whether you are read. Structure, length, tailoring, the photo question, language, and the cover note that gets you past the first filter.',
            'icon' => 'fa-file-alt',
            'order' => 2,
        ],
        [
            'slug' => 'applications',
            'name' => 'Applying for Jobs',
            'description' => 'What happens after the CV: where applications actually go, how recruiters track them, when to follow up, and how to handle online portals, written tests and bulk application rounds.',
            'icon' => 'fa-paper-plane',
            'order' => 3,
        ],
        [
            'slug' => 'interviews',
            'name' => 'Interviews',
            'description' => 'The most common question set in Rwanda, how to answer them with evidence, what to ask back, and how to recover when an interview goes badly or goes silent afterwards.',
            'icon' => 'fa-comments',
            'order' => 4,
        ],
        [
            'slug' => 'finding-work',
            'name' => 'Finding Work',
            'description' => 'Channels that produce results and channels that waste weeks: signboards, professional WhatsApp and LinkedIn, sector networks, job fairs, and how referrals actually happen.',
            'icon' => 'fa-magnifying-glass',
            'order' => 5,
        ],
        [
            'slug' => 'salaries',
            'name' => 'Salaries & Negotiation',
            'description' => 'What roles in Rwanda commonly pay, why the number is never the whole offer, how to research a range you can defend, and how to negotiate without making yourself easy to replace.',
            'icon' => 'fa-money-bill-wave',
            'order' => 6,
        ],
        [
            'slug' => 'skills-certs',
            'name' => 'Skills & Certifications',
            'description' => 'TVET and IPCR qualifications, computer literacy, language certificates, short courses and training of trainers - what is worth the money and what employers in Rwanda actually check.',
            'icon' => 'fa-certificate',
            'order' => 7,
        ],
        [
            'slug' => 'freelance-internships',
            'name' => 'Freelance & Internships',
            'description' => 'Getting paid for work without a permanent contract, finding internships as a student, building a first portfolio, and the unpaid-work traps that cost graduates months.',
            'icon' => 'fa-laptop-code',
            'order' => 8,
        ],
        [
            'slug' => 'career-growth',
            'name' => 'Career Growth',
            'description' => 'Staying and moving up: promotion criteria, performance reviews, the case for changing sector, and returning to work after a career break or a long absence.',
            'icon' => 'fa-arrow-trend-up',
            'order' => 9,
        ],
        [
            'slug' => 'workplace',
            'name' => 'Workplace & Employment',
            'description' => 'Contracts, probation, leave, discipline and termination under Rwandan law, plus how to resign professionally and what you are entitled to when you leave.',
            'icon' => 'fa-briefcase',
            'order' => 10,
        ],
        [
            'slug' => 'job-search-safety',
            'name' => 'Job Search Safety',
            'description' => 'Spotting fraudulent adverts before they cost you anything, the advance-fee scams that target Rwandan job seekers, and how to verify an employer is real before you send documents.',
            'icon' => 'fa-shield-halved',
            'order' => 11,
        ],
        [
            'slug' => 'sector-guides',
            'name' => 'Sector Guides',
            'description' => 'What hiring looks like close up in ICT, BPO, hospitality, agriculture, construction, healthcare, education, finance and NGOs in Rwanda, including the skills each one screens for.',
            'icon' => 'fa-chart-pie',
            'order' => 12,
        ],
        [
            'slug' => 'remote-international',
            'name' => 'Remote & International Work',
            'description' => 'Working for employers outside Rwanda, remote contracting, payment and tax realities, time zones against Rwanda working hours, and the scams that target remote job seekers.',
            'icon' => 'fa-globe',
            'order' => 13,
        ],
        [
            'slug' => 'education-courses',
            'name' => 'Education & Choosing a Course',
            'description' => 'University, TVET and professional routes compared on cost, time and hiring outcomes, plus how to check a programme is recognised and how scholarships and bursaries work.',
            'icon' => 'fa-graduation-cap',
            'order' => 14,
        ],
    ];

    usort($categories, static function ($a, $b) {
        return $a['order'] <=> $b['order'];
    });

    return $categories;
}

/**
 * Legacy category names carried by guides seeded before the taxonomy existed.
 * They are mapped onto taxonomy entries so old rows do not disappear from the
 * filters when the seeder has not been re-run yet.
 */
function akazinet_guide_category_aliases(): array
{
    return [
        'Career Advice'    => 'starting-out',
        'CVs & Cover Letters' => 'cvs-cover-letters',
        'Applications'     => 'applications',
        'Interviews'       => 'interviews',
        'Job Search'       => 'finding-work',
        'Salaries'         => 'salaries',
        'Skills & Certifications' => 'skills-certs',
        'Freelance'        => 'freelance-internships',
        'Career Growth'    => 'career-growth',
        'Workplace'        => 'workplace',
        'Job Search Safety' => 'job-search-safety',
        'Sector Guides'    => 'sector-guides',
        'Remote Work'      => 'remote-international',
        'Education'        => 'education-courses',
    ];
}

/**
 * Map a category name to a taxonomy slug. Names in the taxonomy win; anything
 * else falls through the alias table and then to a match on a normalised form,
 * so a guide carrying a legacy name still lands in the right filter.
 */
function akazinet_guide_category_slug(string $name): string
{
    $name = trim($name);
    $bySlug = [];
    foreach (akazinet_guide_categories() as $category) {
        $bySlug[$category['slug']] = $category['slug'];
        if (strcasecmp($category['name'], $name) === 0) {
            return $category['slug'];
        }
    }

    $aliases = akazinet_guide_category_aliases();
    foreach ($aliases as $legacy => $slug) {
        if (strcasecmp($legacy, $name) === 0 && isset($bySlug[$slug])) {
            return $bySlug[$slug];
        }
    }

    $normalize = static function (string $value): string {
        $value = str_replace(['&', 'and'], 'and', strtolower($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        return trim((string)$value, '-');
    };

    $target = $normalize($name);
    foreach (akazinet_guide_categories() as $category) {
        if ($normalize($category['name']) === $target) {
            return $category['slug'];
        }
    }

    return 'starting-out';
}

/**
 * One taxonomy entry by slug.
 *
 * @return array{name:string,description:string,icon:string,slug:string}|null
 */
function akazinet_guide_category(string $slug): ?array
{
    foreach (akazinet_guide_categories() as $category) {
        if ($category['slug'] === $slug) {
            return $category;
        }
    }

    return null;
}

/**
 * Human-readable name for a stored category value.
 */
function akazinet_guide_category_name(string $name): string
{
    $category = akazinet_guide_category(akazinet_guide_category_slug($name));

    return $category['name'] ?? (trim($name) !== '' ? $name : 'Career Advice');
}

/**
 * Icon for a stored category value.
 */
function akazinet_guide_category_icon(string $name): string
{
    $category = akazinet_guide_category(akazinet_guide_category_slug($name));

    return $category['icon'] ?? 'fa-lightbulb';
}

/**
 * Group a list of guide rows by taxonomy entry, preserving taxonomy order and
 * dropping empty buckets so the index never renders an empty heading.
 *
 * @param  array<int,array> $guides
 * @return array<int,array{slug:string,name:string,description:string,icon:string,guides:array}>
 */
function akazinet_guides_grouped_by_category(array $guides): array
{
    $buckets = [];
    foreach (akazinet_guide_categories() as $category) {
        $buckets[$category['slug']] = $category + ['guides' => []];
    }

    $ungrouped = [];
    foreach ($guides as $guide) {
        $slug = akazinet_guide_category_slug((string)($guide['category'] ?? ''));
        if (isset($buckets[$slug])) {
            $buckets[$slug]['guides'][] = $guide;
        } else {
            $ungrouped[] = $guide;
        }
    }

    $grouped = [];
    foreach ($buckets as $bucket) {
        if ($bucket['guides']) {
            $grouped[] = $bucket;
        }
    }

if ($ungrouped) {
          array_unshift($grouped, [
              'slug'        => 'other',
              'name'        => 'Other Guides',
              'description' => 'Additional guides that do not yet sit in one of the main categories.',
              'icon'        => 'fa-lightbulb',
              'guides'      => $ungrouped,
          ]);
      }
  
      return $grouped;
  }

/*
 * akazinet_trim_meta() also exists in config/database.php. It is repeated here
 * deliberately, behind a function_exists guard, because this file is the one
 * that always ships with the guides feature while database.php is replaced
 * independently.
 *
 * pages/guides.php builds its meta description from the taxonomy description
 * only when ?category= is present. That made the dependency invisible: the
 * index rendered fine while every filtered URL died with "Call to undefined
 * function akazinet_trim_meta()", a fatal, so the symptom was a blank 500 and
 * not an error anywhere in the page body.
 *
 * Guards on both sides mean load order cannot produce a redeclare fatal either.
 */
if (!function_exists('akazinet_trim_meta')) {
    function akazinet_trim_meta($text, $maxLength = 300)
    {
        $text = trim(preg_replace('/\s+/', ' ', (string)$text));
        $maxLength = max(20, (int)$maxLength);

        $length = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
        if ($length <= $maxLength) {
            return $text;
        }

        $cut = function_exists('mb_substr')
            ? mb_substr($text, 0, $maxLength - 1, 'UTF-8')
            : substr($text, 0, $maxLength - 1);

        $lastSpace = strrpos($cut, ' ');
        if ($lastSpace !== false && $lastSpace > 0) {
            $cut = substr($cut, 0, $lastSpace);
        }

        return rtrim($cut, " ,.;:-") . '...';
    }
}
