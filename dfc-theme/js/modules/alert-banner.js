/**
 * Alert Banner
 * Handles notification banner display, dismissal, and animations.
 */

function slideDown(el, duration = 400) {
  const height = el.scrollHeight;
  const anim = el.animate([{ height: "0px" }, { height: height + "px" }], {
    duration,
    easing: "ease-out",
    fill: "forwards",
  });
  anim.onfinish = () => {
    el.style.height = "auto";
    el.style.overflow = "";
    anim.cancel();
  };
}

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
  const ONE_WEEK_MS = 7 * 24 * 60 * 60 * 1000;
  const now = Date.now();

  // Remove banners already dismissed via localStorage
  tray.querySelectorAll(".site-banner").forEach((banner) => {
    const id = banner.getAttribute("data-alert-id");

    if (banner.classList.contains("js-banner-once")) {
      if (localStorage.getItem(`alert_dismissed_${id}`)) {
        banner.remove();
      }
    } else if (banner.classList.contains("js-banner-weekly")) {
      const muteUntil = localStorage.getItem(`alert_mute_${id}`);
      if (muteUntil && now < parseInt(muteUntil)) {
        banner.remove();
      }
    }
  });

  // If all banners were dismissed, clean up and bail
  if (!tray.querySelectorAll(".site-banner").length) {
    tray.remove();
    document.body.classList.remove("has-notification-banner");
    return;
  }

  // Banner starts visible in the HTML (no aria-hidden).
  // JS only manages dismissal — no reveal animation needed.

  // Dismiss individual banners
  tray.addEventListener("click", (e) => {
    const closeBtn = e.target.closest(".alert-close");
    if (!closeBtn) return;

    const banner = closeBtn.closest(".site-banner");
    const alertId = banner.getAttribute("data-alert-id");

    // Persist dismissal
    if (banner.classList.contains("js-banner-once") && alertId) {
      localStorage.setItem(`alert_dismissed_${alertId}`, "true");
    } else if (banner.classList.contains("js-banner-weekly") && alertId) {
      localStorage.setItem(`alert_mute_${alertId}`, now + ONE_WEEK_MS);
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
