import $ from "jquery";

const $doc = $(document);

export default function initLoginMenu() {
  const $toggle = $(".js-login-toggle");
  const $menu = $("#login-menu");

  if (!$toggle.length || !$menu.length) return;

  $doc.on("click", ".js-login-toggle", function (event) {
    event.preventDefault();
    event.stopPropagation();

    const isOpen = $toggle.attr("aria-expanded") === "true";

    if (isOpen) {
      closeMenu();
    } else {
      openMenu();
    }
  });

  // Close on Escape
  $doc.on("keyup", function (event) {
    if (event.key === "Escape" && $toggle.attr("aria-expanded") === "true") {
      closeMenu();
      $toggle.focus();
    }
  });

  // Close when clicking outside
  $doc.on("click", function (event) {
    if (
      $toggle.attr("aria-expanded") === "true" &&
      !$(event.target).closest(".header__login").length
    ) {
      closeMenu();
    }
  });

  // Close when focus leaves the menu
  $doc.on("focusin", function (event) {
    if (
      $toggle.attr("aria-expanded") === "true" &&
      !$(event.target).closest(".header__login").length
    ) {
      closeMenu();
    }
  });

  function openMenu() {
    $toggle.attr("aria-expanded", "true");
    $menu.removeAttr("hidden");
  }

  function closeMenu() {
    $toggle.attr("aria-expanded", "false");
    $menu.attr("hidden", "");
  }
}
