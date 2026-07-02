/**
 * Alert Banner
 * Handles notification banner dismissal.
 *
 * The tray is rendered open on first paint and its height offset is set by an
 * inline pre-paint script in components/alert-banner/index.php, so there is no
 * entrance delay or slide-in on page load — the banner is simply there. This
 * module only wires up dismissal (with a slide-up animation on user action).
 */

function slideUp(el, duration = 350, onFinish) {
  el.style.overflow = "hidden";
  const cs = getComputedStyle(el);
  el.animate(
    [
      { height: cs.height, paddingTop: cs.paddingTop, paddingBottom: cs.paddingBottom },
      { height: "0px", paddingTop: "0px", paddingBottom: "0px" },
    ],
    { duration, easing: "ease-out", fill: "forwards" }
  ).onfinish = () => {
    if (onFinish) onFinish();
  };
}

function initAlertBanner() {
  const tray = document.getElementById("custom-notification-tray");
  if (!tray) return;

  const header = document.querySelector(".header--main");

  // Fallback dismissal cleanup. The inline pre-paint script in
  // components/alert-banner/index.php normally removes dismissed banners before
  // first paint; this repeats it in case that inline script was blocked (e.g.
  // by a strict Content-Security-Policy).
  tray.querySelectorAll(".site-banner").forEach((banner) => {
    const id = banner.getAttribute("data-alert-id");
    if (id && banner.classList.contains("js-banner-once")) {
      if (localStorage.getItem(`alert_dismissed_${id}`)) {
        banner.remove();
      }
    }
  });

  // If all banners were dismissed, clean up and bail.
  if (!tray.querySelectorAll(".site-banner").length) {
    tray.remove();
    document.body.classList.remove("has-notification-banner");
    return;
  }

  // Dismiss individual banners — slide-up only on user action.
  tray.addEventListener("click", (e) => {
    const closeBtn = e.target.closest(".alert-close");
    if (!closeBtn) return;

    const banner = closeBtn.closest(".site-banner");
    const alertId = banner.getAttribute("data-alert-id");

    // Persist dismissal for "once" banners.
    if (banner.classList.contains("js-banner-once") && alertId) {
      localStorage.setItem(`alert_dismissed_${alertId}`, "true");
    }

    const nextBanner = banner.nextElementSibling || banner.previousElementSibling;
    const focusTarget = nextBanner
      ? nextBanner.querySelector(".alert-close")
      : null;
    const isLast = !nextBanner;

    slideUp(banner, 350, () => {
      banner.remove();

      if (isLast) {
        tray.remove();
        document.body.classList.remove("has-notification-banner");

        const firstInteractive = header
          ? header.querySelector("a, button")
          : null;
        if (firstInteractive) firstInteractive.focus();
      } else if (focusTarget) {
        focusTarget.focus();
      }
    });
  });
}

export default initAlertBanner;
