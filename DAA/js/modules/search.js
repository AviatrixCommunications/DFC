/**
 * Desktop search panel — open/close toggle.
 * Search is a standard WordPress form, enhanced by WPEngine Smart Search.
 */
function initDesktopSearch() {
  const toggle = document.querySelector(".js-search-toggle");
  const panel = document.getElementById("site-search");
  const input = document.getElementById("site-search-input");

  if (!toggle || !panel) return;

  function openSearch() {
    panel.removeAttribute("hidden");
    toggle.setAttribute("aria-expanded", "true");
    toggle.setAttribute("aria-label", "Close search");
    if (input) input.focus();
  }

  function closeSearch() {
    panel.setAttribute("hidden", "");
    toggle.setAttribute("aria-expanded", "false");
    toggle.setAttribute("aria-label", "Open search");
    toggle.focus();
  }

  toggle.addEventListener("click", function () {
    const isOpen = toggle.getAttribute("aria-expanded") === "true";
    if (isOpen) {
      closeSearch();
    } else {
      openSearch();
    }
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && !panel.hasAttribute("hidden")) {
      closeSearch();
    }
  });
}

export default function initSearch() {
  initDesktopSearch();
}
