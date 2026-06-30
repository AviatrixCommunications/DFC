/**
 * DFC Header — sticky enhancements
 *
 * 1. Toggles `body.is-scrolled` when the page has been scrolled past
 *    the top, so CSS can apply a subtle shadow under the fixed header.
 * 2. Watches the alert banner (`#custom-notification-tray`) and
 *    publishes its current rendered height as `--dfc-alert-height`
 *    on <html>, so the fixed header + .main padding shift
 *    automatically whenever a banner opens, closes, or resizes.
 */
(function () {
    'use strict';

    // ── 1. Scroll shadow ────────────────────────────────────────
    var SCROLL_THRESHOLD = 4; // pixels
    var ticking = false;

    function onScroll() {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(function () {
            var scrolled = window.scrollY > SCROLL_THRESHOLD;
            document.body.classList.toggle('is-scrolled', scrolled);
            ticking = false;
        });
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll(); // initial state (e.g. page loaded already scrolled)

    // ── 2. Alert banner height → CSS variable ───────────────────
    function syncAlertHeight() {
        var tray = document.getElementById('custom-notification-tray');
        var height = 0;
        if (tray && getComputedStyle(tray).display !== 'none') {
            // Use getComputedStyle().display rather than offsetParent —
            // offsetParent is null on position:fixed elements, which would
            // incorrectly report the banner as hidden once it goes fixed.
            // offsetHeight returns the currently-rendered visible height
            // (which animates with the slide-down).
            height = tray.offsetHeight;
        }
        document.documentElement.style.setProperty(
            '--dfc-alert-height',
            height + 'px'
        );
    }

    function initAlertSync() {
        var tray = document.getElementById('custom-notification-tray');
        if (!tray) {
            // No banner on this page — make sure the variable is 0.
            document.documentElement.style.setProperty('--dfc-alert-height', '0px');
            return;
        }
        syncAlertHeight();
        // ResizeObserver catches animated open/close + content changes.
        if (typeof ResizeObserver !== 'undefined') {
            var ro = new ResizeObserver(syncAlertHeight);
            ro.observe(tray);
        }
        // Also resync on window resize (text reflow can change banner height).
        window.addEventListener('resize', syncAlertHeight);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAlertSync);
    } else {
        initAlertSync();
    }
})();
