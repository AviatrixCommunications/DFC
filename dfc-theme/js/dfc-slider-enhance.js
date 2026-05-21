/**
 * DFC Image Slider — Enhancement Patch
 *
 * Loads after global.js. Patches grid-only sliders so:
 *   1. Arrows show whenever there's overflow to scroll (not just count > 3)
 *   2. Disabled state on prev/next when at start/end
 *   3. Resize-aware (mobile shows 1 per view, desktop 3, etc.)
 *
 * This avoids a full webpack rebuild. Once the next bundled build
 * happens, this file becomes redundant — the same logic is in
 * js/modules/image-slider.js source.
 */
(function () {
    'use strict';

    function init() {
        var sliders = document.querySelectorAll('.aviatrix-block--image-slider.image-slider--grid-only');
        sliders.forEach(setupSlider);
    }

    function setupSlider(slider) {
        var thumbContainer = slider.querySelector('.image-slider__thumbs');
        var prevBtn = slider.querySelector('.image-slider__nav--prev');
        var nextBtn = slider.querySelector('.image-slider__nav--next');
        var thumbs = Array.prototype.slice.call(slider.querySelectorAll('.image-slider__thumb'));

        if (!thumbContainer || !prevBtn || !nextBtn || thumbs.length === 0) return;

        // Replace the existing prev/next buttons to drop the bundled handlers
        // (cloneNode strips listeners). Then re-attach our own logic.
        var newPrev = prevBtn.cloneNode(true);
        var newNext = nextBtn.cloneNode(true);
        prevBtn.parentNode.replaceChild(newPrev, prevBtn);
        nextBtn.parentNode.replaceChild(newNext, nextBtn);
        prevBtn = newPrev;
        nextBtn = newNext;

        function scrollAmount() {
            // One thumb width + gap. Falls back to clientWidth if thumbs are 0px.
            return (thumbs[0] && thumbs[0].offsetWidth ? thumbs[0].offsetWidth + 12 : thumbContainer.clientWidth);
        }

        prevBtn.addEventListener('click', function () {
            thumbContainer.scrollBy({ left: -scrollAmount(), behavior: 'smooth' });
        });
        nextBtn.addEventListener('click', function () {
            thumbContainer.scrollBy({ left: scrollAmount(), behavior: 'smooth' });
        });

        function updateArrowStates() {
            var atStart = thumbContainer.scrollLeft <= 2;
            var atEnd   = thumbContainer.scrollLeft + thumbContainer.clientWidth >=
                          thumbContainer.scrollWidth - 2;
            prevBtn.classList.toggle('is-disabled', atStart);
            nextBtn.classList.toggle('is-disabled', atEnd);
            prevBtn.setAttribute('aria-disabled', atStart ? 'true' : 'false');
            nextBtn.setAttribute('aria-disabled', atEnd ? 'true' : 'false');
        }

        function updateArrowVisibility() {
            var hasOverflow = thumbContainer.scrollWidth > thumbContainer.clientWidth + 2;
            prevBtn.style.display = hasOverflow ? '' : 'none';
            nextBtn.style.display = hasOverflow ? '' : 'none';
            if (hasOverflow) updateArrowStates();
        }

        thumbContainer.addEventListener('scroll', updateArrowStates, { passive: true });
        window.addEventListener('resize', updateArrowVisibility);

        // Re-check after each thumb image loads (dimensions affect scrollWidth)
        thumbs.forEach(function (thumb) {
            var img = thumb.querySelector('img');
            if (img && !img.complete) {
                img.addEventListener('load', updateArrowVisibility);
            }
        });

        // Initial pass — wait one frame so layout settles
        requestAnimationFrame(updateArrowVisibility);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
