/**
 * AkaziNet - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
    // ---- Floating Navbar: Scroll Effect ----
    var navbar = document.getElementById('navbar');
    if (navbar) {
        window.addEventListener('scroll', function () {
            if (window.pageYOffset > 30) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }, { passive: true });
    }

    // ---- Mobile Menu ----
    var mobileMenuBtn = document.getElementById('mobileMenuBtn');
    var mobileMenu = document.getElementById('mobileMenu');
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function () {
            var isOpen = mobileMenu.classList.contains('open');
            if (isOpen) {
                mobileMenu.classList.remove('open');
                mobileMenuBtn.classList.remove('open');
                mobileMenuBtn.setAttribute('aria-expanded', 'false');
                mobileMenu.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            } else {
                mobileMenu.classList.add('open');
                mobileMenuBtn.classList.add('open');
                mobileMenuBtn.setAttribute('aria-expanded', 'true');
                mobileMenu.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }
        });

        // Close mobile menu on backdrop click
        mobileMenu.addEventListener('click', function (e) {
            if (e.target === mobileMenu) {
                mobileMenu.classList.remove('open');
                mobileMenuBtn.classList.remove('open');
                mobileMenuBtn.setAttribute('aria-expanded', 'false');
                mobileMenu.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }
        });
    }

    // ---- Desktop Dropdown: Explore ----
    var dropdowns = document.querySelectorAll('.nav-dropdown');
    dropdowns.forEach(function (dropdown) {
        var trigger = dropdown.querySelector('.nav-dropdown-trigger');
        var menu = dropdown.querySelector('.nav-dropdown-menu');

if (!trigger) return;

        // A trigger may be a real link (Career Guides) rather than a button. In
        // that case the click must navigate, not toggle, so we only bind the
        // click handler on button-style triggers. Hover still opens the menu.
        var isLinkTrigger = trigger.tagName === 'A' || trigger.dataset.dropdownTrigger === 'link';

        // Click to toggle
        if (!isLinkTrigger) trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = dropdown.classList.contains('open');

            // Close all other dropdowns
            dropdowns.forEach(function (d) { d.classList.remove('open'); });

            if (!isOpen) {
                dropdown.classList.add('open');
                trigger.setAttribute('aria-expanded', 'true');
} else {
                trigger.setAttribute('aria-expanded', 'false');
            }
        });

        // Keyboard: a link trigger has no click handler, so focus alone must
        // open the menu, otherwise the topic links are unreachable by keyboard.
        if (isLinkTrigger) {
            trigger.addEventListener('focus', function () {
                if (window.innerWidth > 768) {
                    dropdown.classList.add('open');
                    trigger.setAttribute('aria-expanded', 'true');
                }
            });
            trigger.addEventListener('blur', function () {
                setTimeout(function () {
                    if (!dropdown.contains(document.activeElement)) {
                        dropdown.classList.remove('open');
                        trigger.setAttribute('aria-expanded', 'false');
                    }
                }, 0);
            });
        }

        // Hover for desktop
        dropdown.addEventListener('mouseenter', function () {
            if (window.innerWidth > 768) {
                dropdowns.forEach(function (d) { d.classList.remove('open'); });
                dropdown.classList.add('open');
                trigger.setAttribute('aria-expanded', 'true');
            }
        });

        dropdown.addEventListener('mouseleave', function () {
            if (window.innerWidth > 768) {
                dropdown.classList.remove('open');
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
    });

    // Close dropdowns on outside click
    document.addEventListener('click', function () {
        dropdowns.forEach(function (d) {
            d.classList.remove('open');
            var t = d.querySelector('.nav-dropdown-trigger');
            if (t) t.setAttribute('aria-expanded', 'false');
        });
    });

    // Close dropdowns on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            dropdowns.forEach(function (d) {
                d.classList.remove('open');
                var t = d.querySelector('.nav-dropdown-trigger');
                if (t) t.setAttribute('aria-expanded', 'false');
            });
            if (mobileMenu && mobileMenu.classList.contains('open')) {
                mobileMenu.classList.remove('open');
                mobileMenuBtn.classList.remove('open');
                document.body.style.overflow = '';
            }
        }
    });

    // Animate elements on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in-up');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.job-card, .animate-on-scroll').forEach(function (el) {
        el.style.opacity = '0';
        observer.observe(el);
    });

    function updateDeadlineCountdowns() {
        document.querySelectorAll('[data-deadline-countdown]').forEach(function (element) {
            var deadline = new Date(element.getAttribute('data-deadline')).getTime();
            var textElement = element.querySelector('[data-countdown-text]');
            if (!textElement || isNaN(deadline)) return;

            var difference = deadline - Date.now();
            var totalSeconds = Math.max(0, Math.ceil(difference / 1000));
            var days = Math.floor(totalSeconds / 86400);
            var hours = Math.floor((totalSeconds % 86400) / 3600);
            var minutes = Math.floor((totalSeconds % 3600) / 60);
            var seconds = totalSeconds % 60;
            var text;

            if (totalSeconds === 0) {
                text = 'Expired';
            } else if (days > 0) {
                text = days + (days === 1 ? ' day ' : ' days ') + hours + 'h ' + minutes + 'm left';
            } else if (hours > 0) {
                text = hours + 'h ' + minutes + 'm left';
            } else if (minutes > 0) {
                text = minutes + 'm ' + seconds + 's left';
            } else {
                text = seconds + 's left';
            }

            textElement.textContent = text;
            element.classList.toggle('is-urgent', difference > 0 && difference <= 3 * 86400000);
            element.classList.toggle('is-expired', difference <= 0);
        });
    }

    function scheduleCountdownTick() {
        window.setTimeout(function () {
            updateDeadlineCountdowns();
            scheduleCountdownTick();
        }, 1000 - (Date.now() % 1000) + 10);
    }

    updateDeadlineCountdowns();
    scheduleCountdownTick();

    // Auto-dismiss flash messages
    document.querySelectorAll('[data-flash]').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity 0.3s';
            el.style.opacity = '0';
            setTimeout(function () {
                el.remove();
            }, 300);
        }, 5000);
    });

    // Confirm delete actions
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!confirm(this.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });
});

// ---- Copy Share Link ----
function fallbackCopyText(text) {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.focus();
    ta.select();
    try { document.execCommand('copy'); } catch (e) {}
    document.body.removeChild(ta);
}

document.querySelectorAll('.copy-link-btn').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var url = this.getAttribute('data-copy-url');
        var original = this.innerHTML;

        function showCopied() {
            this.innerHTML = '<i class="fas fa-check"></i>';
            this.classList.add('bg-green-600');
            var el = this;
            setTimeout(function () {
                el.innerHTML = original;
                el.classList.remove('bg-green-600');
            }, 2000);
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function () {
                showCopied.call(btn);
            }, function () {
                fallbackCopyText(url);
                showCopied.call(btn);
            });
        } else {
            fallbackCopyText(url);
            showCopied.call(btn);
        }
    });
});

// WhatsApp URL generator
function generateWhatsAppUrl(jobTitle, phoneNumber) {
    phoneNumber = phoneNumber || '250780092252';
    var message = 'Hello,\n\nI am interested in the following job vacancy:\n\n*' + jobTitle + '*\n\nCould you please provide more information about this opportunity?\n\nThank you.';
    return 'https://wa.me/' + phoneNumber + '?text=' + encodeURIComponent(message);
}

// ============================================
//  Reserved ad slots + skeleton loading states
// ============================================

(function () {
    'use strict';

    // Drop the shimmer as soon as an ad unit fills the space we reserved for
    // it. Observing the <ins> child is more reliable than a fixed timeout,
    // because AdSense has no load callback we can hook.
    function watchAdSlot(slot) {
        var ins = slot.querySelector('ins.adsbygoogle');
        if (!ins) return;

        var settled = false;

        function markLoaded() {
            if (settled) return;
            settled = true;
            slot.classList.remove('is-loading');
            slot.classList.add('is-loaded');
        }

        // AdSense injects an iframe into the <ins> when the unit fills.
        if (typeof MutationObserver !== 'undefined') {
            var mutation = new MutationObserver(function () {
                if (ins.querySelector('iframe')) {
                    markLoaded();
                    mutation.disconnect();
                }
            });
            mutation.observe(ins, { childList: true, subtree: true });
        }

        // Fallback for browsers without MutationObserver, and a safety net for
        // the case where the ad is served without ever adding an iframe.
        if (typeof ResizeObserver !== 'undefined') {
            var resize = new ResizeObserver(function () {
                if (ins.offsetHeight > 0 && ins.offsetWidth > 0) {
                    markLoaded();
                    resize.disconnect();
                }
            });
            resize.observe(ins);
        }

        window.setTimeout(function () {
            if (ins.querySelector('iframe')) {
                markLoaded();
            } else {
                // Nothing was served. Collapse the box rather than leaving a
                // grey rectangle where an ad should be.
                slot.classList.remove('is-loading');
                slot.classList.add('is-empty');
            }
        }, 8000);
    }

    function initAdSlots() {
        var slots = document.querySelectorAll('.adsense-slot--reserved');
        for (var i = 0; i < slots.length; i++) {
            watchAdSlot(slots[i]);
        }
    }

    // Reveal skeleton grids that are swapped for real markup by a fetch.
    function initSkeletonSwap() {
        var grids = document.querySelectorAll('[data-skeleton-grid]');
        for (var i = 0; i < grids.length; i++) {
            (function (grid) {
                window.setTimeout(function () {
                    grid.remove();
                }, 12000);
            })(grids[i]);
        }
    }

    function init() {
        initAdSlots();
        initSkeletonSwap();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
