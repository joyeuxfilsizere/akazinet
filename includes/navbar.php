<?php
$isAdmin = is_admin_logged_in();
require_once __DIR__ . '/../config/guide-taxonomy.php';
$navGuideCategories = akazinet_guide_categories();
?>
<style>
body { padding-top: 92px; }
.nav-dropdown-menu.guides-menu { min-width: 560px; }
.nav-dropdown-menu.guides-menu .guides-menu-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1px;
}
.guides-menu-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 7px 9px;
    border-radius: 10px;
    transition: background-color 0.15s ease;
}
.guides-menu-item:hover, .guides-menu-item:focus-visible { background-color: rgba(0, 0, 0, 0.045); }
.guides-menu-item .guides-menu-icon {
    width: 26px; height: 26px; border-radius: 7px; flex-shrink: 0;
    background-color: rgba(0, 0, 0, 0.05);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 11px;
}
.guides-menu-item .guides-menu-label { font-size: 13px; font-weight: 600; color: #111827; line-height: 1.3; }
.guides-menu-item .guides-menu-desc {
    font-size: 11px; color: #6b7280; line-height: 1.35; margin-top: 1px;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.guides-menu-all {
    display: block; text-align: center; font-size: 12px; font-weight: 600;
    padding: 8px 0 4px; border-top: 1px solid #e5e7eb; margin-top: 6px;
}
@media (max-width: 767px) { .nav-dropdown-menu.guides-menu { min-width: 320px; } }
.nav-badge {
    font-size: 9px;
    font-weight: 700;
    line-height: 1;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: #1d4ed8;
    background-color: #dbeafe;
    border: 1px solid #bfdbfe;
    padding: 3px 6px;
    border-radius: 9999px;
    white-space: nowrap;
}
</style>
<nav class="floating-navbar" id="navbar">
    <div class="floating-navbar-container" id="navbarContainer">
        <div class="floating-navbar-inner">

            <!-- Logo -->
            <a href="/" class="navbar-logo">
                <img src="/assets/images/logo.svg" alt="AkaziNet" class="h-10 w-auto" />
            </a>

            <!-- Desktop Navigation -->
            <div class="navbar-nav">

                <a href="/" class="nav-item <?= $currentPage === 'index' ? 'active' : '' ?>">
                    <span class="nav-item-text">Home</span>
                    <?php if ($currentPage === 'index'): ?><span class="nav-indicator"></span><?php endif; ?>
                </a>

                <a href="/jobs" class="nav-item <?= $currentPage === 'jobs' ? 'active' : '' ?>">
                    <span class="nav-item-text">Jobs</span>
                    <?php if ($currentPage === 'jobs'): ?><span class="nav-indicator"></span><?php endif; ?>
                </a>

                <a href="/pages/remote-jobs" class="nav-item <?= $currentPage === 'remote-jobs' ? 'active' : '' ?>">
                    <span class="nav-item-text">Remote Jobs</span>
                    <?php if ($currentPage === 'remote-jobs'): ?><span class="nav-indicator"></span><?php endif; ?>
                </a>

                <!-- Primary nav item with a topic dropdown. The section link itself
                     stays a real, visible, crawlable <a href> rather than a JS-only
                     trigger: the guides are the site's original editorial content,
                     and hiding all of it behind hover behaviour would remove the
                     strongest internal link on every page. The dropdown is the
                     taxonomy, so the full set of topics is reachable from anywhere
                     without landing on the index first. -->
                <?php $guidesActive = in_array($currentPage, ['guides', 'guide-detail'], true); ?>
                <?php
                $activeGuideCategory = akazinet_guide_category_slug((string)($_GET['category'] ?? ''));
                ?>
                <div class="nav-dropdown" id="guidesDropdown">
                    <a href="/pages/guides" class="nav-item nav-dropdown-trigger <?= $guidesActive ? 'active' : '' ?>" data-dropdown-trigger="link" aria-expanded="false" aria-haspopup="true">
                        <span class="nav-item-text">Career Guides</span>
                        <i class="fas fa-chevron-down nav-chevron"></i>
                        <?php if ($guidesActive): ?><span class="nav-indicator"></span><?php endif; ?>
                    </a>
                    <div class="nav-dropdown-menu guides-menu" role="menu">
                        <div class="nav-dropdown-arrow"></div>
                        <div class="guides-menu-grid">
                            <?php foreach ($navGuideCategories as $navCat): ?>
                            <a href="/pages/guides?category=<?= urlencode($navCat['slug']) ?>"
                               class="guides-menu-item"
                               role="menuitem"
                               <?= $activeGuideCategory === $navCat['slug'] ? 'aria-current="true"' : '' ?>>
                                <span class="guides-menu-icon"><i class="fas <?= sanitize($navCat['icon']) ?>"></i></span>
                                <span>
                                    <span class="guides-menu-label"><?= sanitize($navCat['name']) ?></span>
                                    <span class="guides-menu-desc"><?= sanitize($navCat['description']) ?></span>
                                </span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <a href="/pages/guides" class="guides-menu-all">View all Career Guides</a>
                    </div>
                </div>

                <!-- Dropdown: Explore -->
                <div class="nav-dropdown" id="exploreDropdown">
                    <button class="nav-item nav-dropdown-trigger <?= $currentPage === 'categories' || $currentPage === 'scholarships' ? 'active' : '' ?>" aria-expanded="false" aria-haspopup="true">
                        <span class="nav-item-text">Explore</span>
                        <i class="fas fa-chevron-down nav-chevron"></i>
                    </button>
                    <div class="nav-dropdown-menu" role="menu">
                        <div class="nav-dropdown-arrow"></div>
                        <a href="/pages/categories" class="dropdown-item" role="menuitem">
                            <div class="dropdown-item-icon">
                                <i class="fas fa-th-large"></i>
                            </div>
                            <div>
                                <div class="dropdown-item-label">Categories</div>
                                <div class="dropdown-item-desc">Browse by job category</div>
                            </div>
                        </a>
                        <a href="/pages/scholarships" class="dropdown-item" role="menuitem">
                            <div class="dropdown-item-icon accent">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div>
                                <div class="dropdown-item-label">Scholarships</div>
                                <div class="dropdown-item-desc">Educational opportunities</div>
                            </div>
                        </a>
                        <a href="/jobs?featured=1" class="dropdown-item" role="menuitem">
                            <div class="dropdown-item-icon green">
                                <i class="fas fa-star"></i>
                            </div>
                            <div>
                                <div class="dropdown-item-label">Featured Jobs</div>
                                <div class="dropdown-item-desc">Hand-picked openings</div>
                            </div>
                        </a>
                    </div>
                </div>

                <a href="/pages/about" class="nav-item <?= $currentPage === 'about' ? 'active' : '' ?>">
                    <span class="nav-item-text">About</span>
                    <?php if ($currentPage === 'about'): ?><span class="nav-indicator"></span><?php endif; ?>
                </a>

                <a href="/pages/contact" class="nav-item <?= $currentPage === 'contact' ? 'active' : '' ?>">
                    <span class="nav-item-text">Contact</span>
                    <?php if ($currentPage === 'contact'): ?><span class="nav-indicator"></span><?php endif; ?>
                </a>
            </div>

            <!-- Right Actions -->
            <div class="navbar-actions">
                <?php if ($isAdmin): ?>
                    <a href="/admin/" class="nav-btn nav-btn-ghost" title="Admin Dashboard">
                        <i class="fas fa-cog"></i>
                        <span class="nav-btn-label">Dashboard</span>
                    </a>
                    <a href="/admin/logout" class="nav-btn nav-btn-ghost" title="Sign Out">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                    <a href="/admin/jobs" class="nav-btn nav-btn-primary" title="Post a Job">
                        <i class="fas fa-plus"></i>
                        <span class="nav-btn-label">Post Job</span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Mobile Toggle -->
            <button class="navbar-mobile-toggle" id="mobileMenuBtn" aria-label="Toggle menu" aria-expanded="false">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu" aria-hidden="true">
        <div class="mobile-menu-inner">

            <a href="/" class="mobile-nav-item <?= $currentPage === 'index' ? 'active' : '' ?>">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="/jobs" class="mobile-nav-item <?= $currentPage === 'jobs' ? 'active' : '' ?>">
                <i class="fas fa-briefcase"></i> Jobs
            </a>
            <a href="/pages/remote-jobs" class="mobile-nav-item <?= $currentPage === 'remote-jobs' ? 'active' : '' ?>">
                <i class="fas fa-laptop-house"></i> Remote Jobs
            </a>
            <a href="/pages/guides" class="mobile-nav-item <?= $guidesActive ? 'active' : '' ?>">
                <i class="fas fa-book-open"></i> Career Guides
            </a>

            <!-- Topics are nested rather than listed flat: 14 sibling links below
                 the main items would push the rest of the mobile menu off screen. -->
            <div class="px-3 pb-2 -mt-1">
                <div class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold mb-1.5">Guide topics</div>
                <div class="grid grid-cols-2 gap-x-2">
                    <?php foreach ($navGuideCategories as $navCat): ?>
                    <a href="/pages/guides?category=<?= urlencode($navCat['slug']) ?>"
                       class="text-xs <?= $activeGuideCategory === $navCat['slug'] ? 'text-primary font-semibold' : 'text-gray-600' ?> py-1 flex items-center gap-1.5">
                        <i class="fas <?= sanitize($navCat['icon']) ?> text-[10px] opacity-60"></i>
                        <span class="truncate"><?= sanitize($navCat['name']) ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <a href="/pages/report-job" class="mobile-nav-item">
                <i class="fas fa-flag"></i> Report a Job
            </a>
            <a href="/pages/categories" class="mobile-nav-item <?= $currentPage === 'categories' ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i> Categories
            </a>
            <a href="/pages/scholarships" class="mobile-nav-item <?= $currentPage === 'scholarships' ? 'active' : '' ?>">
                <i class="fas fa-graduation-cap"></i> Scholarships
            </a>
            <a href="/pages/about" class="mobile-nav-item <?= $currentPage === 'about' ? 'active' : '' ?>">
                <i class="fas fa-info-circle"></i> About
            </a>
            <a href="/pages/contact" class="mobile-nav-item <?= $currentPage === 'contact' ? 'active' : '' ?>">
                <i class="fas fa-envelope"></i> Contact
            </a>

            <div class="mobile-menu-divider"></div>

            <?php if ($isAdmin): ?>
                <a href="/admin/" class="mobile-nav-item">
                    <i class="fas fa-cog"></i> Dashboard
                </a>
                <a href="/admin/jobs" class="mobile-cta-btn">
                    <i class="fas fa-plus"></i> Post a Job
                </a>
                <a href="/admin/logout" class="mobile-nav-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<?php if (function_exists('render_adsense_unit')) { render_adsense_unit('header', 'Advertisement'); } ?>
