/**
 * DFC Airplane Parallax
 *
 * As the user scrolls past the services tabs section, the decorative
 * airplane silhouette slides further off-screen to the right — like
 * it's taxiing away. Respects prefers-reduced-motion.
 */

(function () {
    'use strict';

    var deco = document.querySelector('.services-tabs__deco');
    if (!deco) return;

    // Respect reduced motion
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var section = deco.closest('.aviatrix-block--services-tabs');
    if (!section) return;

    var ticking = false;

    function onScroll() {
        if (!ticking) {
            window.requestAnimationFrame(function () {
                var rect = section.getBoundingClientRect();
                var windowH = window.innerHeight;

                // Calculate how far through the section we've scrolled (0 to 1+)
                // 0 = section just entered viewport at bottom
                // 1 = section top has reached the top of viewport
                var progress = (windowH - rect.top) / (windowH + rect.height);
                progress = Math.max(0, Math.min(progress, 1.5));

                // Translate the airplane 0–80px to the right as user scrolls through
                var translateX = progress * 80;
                deco.style.transform = 'translateX(' + translateX + 'px)';

                ticking = false;
            });
            ticking = true;
        }
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll(); // Run once on load
})();
