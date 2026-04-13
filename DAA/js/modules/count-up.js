export default function initCountUp() {
  document.addEventListener('DOMContentLoaded', () => {
    const elements = document.querySelectorAll('.js-count-up');

    if (!elements.length) return;

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion) return; // Numbers already display their final values

    const duration = 1500;

    function animateCount(el) {
      const text = el.textContent.trim();

      // Parse number and surrounding text (e.g. "800+" → prefix="", num=800, suffix="+")
      const match = text.match(/^([^\d]*?)([\d,]+(?:\.\d+)?)(.*)$/);
      if (!match) return;

      const prefix = match[1];
      const target = parseFloat(match[2].replace(/,/g, ''));
      const suffix = match[3];
      const hasDecimals = match[2].includes('.');
      const decimalPlaces = hasDecimals ? match[2].split('.')[1].length : 0;
      const useCommas = match[2].includes(',');

      const startTime = performance.now();

      function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);

        // Ease out cubic
        const eased = 1 - Math.pow(1 - progress, 3);
        const current = eased * target;

        let formatted = hasDecimals
          ? current.toFixed(decimalPlaces)
          : Math.floor(current).toString();

        if (useCommas) {
          formatted = Number(formatted).toLocaleString('en-US', {
            minimumFractionDigits: decimalPlaces,
            maximumFractionDigits: decimalPlaces,
          });
        }

        el.textContent = prefix + formatted + suffix;

        if (progress < 1) {
          requestAnimationFrame(update);
        }
      }

      el.textContent = prefix + (hasDecimals ? (0).toFixed(decimalPlaces) : '0') + suffix;
      requestAnimationFrame(update);
    }

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            animateCount(entry.target);
            observer.unobserve(entry.target);
          }
        });
      },
      {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
      }
    );

    elements.forEach(el => observer.observe(el));
  });
}
