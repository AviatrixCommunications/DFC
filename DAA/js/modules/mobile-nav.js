import $ from "jquery";

const $doc = $(document);
const $mobileNav = $(".js-mobile-nav");
const $hamburger = $(".js-hamburger");
const $body = $("body");
const DESKTOP_BREAKPOINT = 1024;

const FOCUSABLE = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';

function getFocusable() {
  return $mobileNav.find(FOCUSABLE).filter(":visible");
}

function closeNav() {
  $mobileNav.removeClass("is-active").attr("aria-hidden", "true");
  $hamburger.removeClass("is-active").attr("aria-expanded", "false");
  $(".js-header").removeClass("mobilenav-is-active");
  $body.css("overflow", "");

  // Reset first-level expanded submenus
  $mobileNav.find(".nav__list > .menu-item--active").removeClass("menu-item--active");
  $mobileNav.find(".nav__list > .menu-item-has-children > a").attr("aria-expanded", "false");
  $mobileNav.find(".nav__list > .menu-item-has-children > .sub-menu").attr("hidden", "");
}

function openNav() {
  $mobileNav.addClass("is-active").attr("aria-hidden", "false");
  $hamburger.addClass("is-active").attr("aria-expanded", "true");
  $(".js-header").addClass("mobilenav-is-active");
  $body.css("overflow-y", "hidden");
  // Move focus into nav for keyboard/screen reader users
  getFocusable().first().trigger("focus");
}

function showMobileNav(event) {
  event.preventDefault();
  if ($mobileNav.hasClass("is-active")) {
    closeNav();
  } else {
    openNav();
  }
}

// Trap focus within the open mobile nav
function handleKeydown(event) {
  if (!$mobileNav.hasClass("is-active")) return;

  if (event.key === "Escape") {
    closeNav();
    $hamburger.trigger("focus");
    return;
  }

  if (event.key !== "Tab") return;

  const $focusable = getFocusable();
  const $first = $focusable.first();
  const $last = $focusable.last();

  if (event.shiftKey) {
    if (document.activeElement === $first[0]) {
      event.preventDefault();
      $last.trigger("focus");
    }
  } else {
    if (document.activeElement === $last[0]) {
      event.preventDefault();
      $first.trigger("focus");
    }
  }
}

// Close if viewport crosses to desktop while nav is open
function handleResize() {
  if (window.innerWidth >= DESKTOP_BREAKPOINT && $mobileNav.hasClass("is-active")) {
    closeNav();
  }
}

/**
 * Set up mobile submenu toggles.
 * Parent items with href=# act as disclosure buttons:
 * clicking them expands/collapses their child .sub-menu.
 */
function initSubmenuToggles() {
  $mobileNav.find(".nav__list > .menu-item-has-children").each(function (index) {
    const $item = $(this);
    const $link = $item.children("a").first();
    const $submenu = $item.children(".sub-menu");

    if (!$submenu.length) return;

    // Give the submenu a unique id for aria-controls
    const submenuId = "mobile-submenu-" + index;
    $submenu.attr("id", submenuId);

    // Set up the link as a disclosure toggle
    $link
      .attr("role", "button")
      .attr("aria-expanded", "false")
      .attr("aria-controls", submenuId);

    // Hide submenu by default
    $submenu.attr("hidden", "");

    // Click handler
    $link.on("click", function (e) {
      e.preventDefault();
      toggleSubmenu($item, $link, $submenu);
    });

    // Space key for role="button" (Enter already fires click on links)
    $link.on("keydown", function (e) {
      if (e.key === " ") {
        e.preventDefault();
        toggleSubmenu($item, $link, $submenu);
      }
    });
  });
}

function toggleSubmenu($item, $link, $submenu) {
  const isExpanded = $link.attr("aria-expanded") === "true";

  if (isExpanded) {
    $link.attr("aria-expanded", "false");
    $submenu.attr("hidden", "");
    $item.removeClass("menu-item--active");
  } else {
    $link.attr("aria-expanded", "true");
    $submenu.removeAttr("hidden");
    $item.addClass("menu-item--active");
  }
}

export default function initMobileNav() {
  // Set initial ARIA state
  $mobileNav.attr("aria-hidden", "true");

  $doc.on("click", ".js-hamburger", showMobileNav);
  $doc.on("keydown", handleKeydown);
  $(window).on("resize", handleResize);

  // Set up mobile submenu disclosure toggles
  initSubmenuToggles();

  // Prevent page-jump on # parent links (covers desktop hover menus too)
  $doc.on("click", ".menu-item-has-children > a[href='#']", function (e) {
    e.preventDefault();
  });
}
