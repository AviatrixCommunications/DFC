/**
 * DFC Mobile Navigation — Vanilla JS
 *
 * Hamburger toggle, focus trapping, Escape to close,
 * submenu disclosure toggles, resize auto-close.
 */

const DESKTOP_BREAKPOINT = 1024;
const FOCUSABLE = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';

let mobileNav;
let hamburger;

function getFocusable() {
    return Array.from(mobileNav.querySelectorAll(FOCUSABLE)).filter(
        (el) => el.offsetParent !== null
    );
}

function closeNav() {
    mobileNav.classList.remove('is-active');
    mobileNav.setAttribute('aria-hidden', 'true');
    hamburger.classList.remove('is-active');
    hamburger.setAttribute('aria-expanded', 'false');

    const header = document.querySelector('.js-header');
    if (header) header.classList.remove('mobilenav-is-active');
    document.body.style.overflow = '';

    // Reset first-level expanded submenus
    mobileNav.querySelectorAll('.nav__list > .menu-item--active').forEach((item) => {
        item.classList.remove('menu-item--active');
    });
    mobileNav.querySelectorAll('.nav__list > .menu-item-has-children > a').forEach((link) => {
        link.setAttribute('aria-expanded', 'false');
    });
    mobileNav.querySelectorAll('.nav__list > .menu-item-has-children > .sub-menu').forEach((sub) => {
        sub.setAttribute('hidden', '');
    });
}

function openNav() {
    mobileNav.classList.add('is-active');
    mobileNav.setAttribute('aria-hidden', 'false');
    hamburger.classList.add('is-active');
    hamburger.setAttribute('aria-expanded', 'true');

    const header = document.querySelector('.js-header');
    if (header) header.classList.add('mobilenav-is-active');
    document.body.style.overflowY = 'hidden';

    // Move focus into nav for keyboard/screen reader users
    const focusable = getFocusable();
    if (focusable.length) focusable[0].focus();
}

function toggleNav(event) {
    event.preventDefault();
    if (mobileNav.classList.contains('is-active')) {
        closeNav();
    } else {
        openNav();
    }
}

// Trap focus within the open mobile nav
function handleKeydown(event) {
    if (!mobileNav.classList.contains('is-active')) return;

    if (event.key === 'Escape') {
        closeNav();
        hamburger.focus();
        return;
    }

    if (event.key !== 'Tab') return;

    const focusable = getFocusable();
    if (!focusable.length) return;

    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (event.shiftKey) {
        if (document.activeElement === first) {
            event.preventDefault();
            last.focus();
        }
    } else {
        if (document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }
}

// Close if viewport crosses to desktop while nav is open
function handleResize() {
    if (window.innerWidth >= DESKTOP_BREAKPOINT && mobileNav.classList.contains('is-active')) {
        closeNav();
    }
}

function toggleSubmenu(item, link, submenu) {
    const isExpanded = link.getAttribute('aria-expanded') === 'true';

    if (isExpanded) {
        link.setAttribute('aria-expanded', 'false');
        submenu.setAttribute('hidden', '');
        item.classList.remove('menu-item--active');
    } else {
        link.setAttribute('aria-expanded', 'true');
        submenu.removeAttribute('hidden');
        item.classList.add('menu-item--active');
    }
}

/**
 * Set up mobile submenu toggles.
 * Parent items with href=# act as disclosure buttons:
 * clicking them expands/collapses their child .sub-menu.
 */
function initSubmenuToggles() {
    const parents = mobileNav.querySelectorAll('.nav__list > .menu-item-has-children');

    parents.forEach((item, index) => {
        const link = item.querySelector(':scope > a');
        const submenu = item.querySelector(':scope > .sub-menu');

        if (!link || !submenu) return;

        // Give the submenu a unique id for aria-controls
        const submenuId = 'mobile-submenu-' + index;
        submenu.id = submenuId;

        // Set up the link as a disclosure toggle
        link.setAttribute('role', 'button');
        link.setAttribute('aria-expanded', 'false');
        link.setAttribute('aria-controls', submenuId);

        // Hide submenu by default
        submenu.setAttribute('hidden', '');

        // Click handler
        link.addEventListener('click', (e) => {
            e.preventDefault();
            toggleSubmenu(item, link, submenu);
        });

        // Space key for role="button" (Enter already fires click on links)
        link.addEventListener('keydown', (e) => {
            if (e.key === ' ') {
                e.preventDefault();
                toggleSubmenu(item, link, submenu);
            }
        });
    });
}

export default function initMobileNav() {
    mobileNav = document.querySelector('.js-mobile-nav');
    hamburger = document.querySelector('.js-hamburger');

    if (!mobileNav || !hamburger) return;

    // Set initial ARIA state
    mobileNav.setAttribute('aria-hidden', 'true');

    hamburger.addEventListener('click', toggleNav);
    document.addEventListener('keydown', handleKeydown);
    window.addEventListener('resize', handleResize);

    // Set up mobile submenu disclosure toggles
    initSubmenuToggles();

    // Prevent page-jump on # parent links (covers desktop hover menus too)
    document.addEventListener('click', (e) => {
        const link = e.target.closest('.menu-item-has-children > a[href="#"]');
        if (link) e.preventDefault();
    });
}
