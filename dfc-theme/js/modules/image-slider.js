/**
 * DFC Image Slider
 *
 * Simple gallery: click thumbnails or use prev/next arrows to change
 * the main image. Full keyboard support + WCAG aria-selected.
 */

(function () {
    'use strict';

    document.querySelectorAll('.js-image-slider').forEach(function (slider) {
        var mainImg  = slider.querySelector('.image-slider__active-img');
        var thumbs   = Array.from(slider.querySelectorAll('.image-slider__thumb'));
        var prevBtn  = slider.querySelector('.image-slider__nav--prev');
        var nextBtn  = slider.querySelector('.image-slider__nav--next');
        var current  = 0;

        if (!mainImg || thumbs.length < 2) return;

        function goTo(index) {
            if (index < 0) index = thumbs.length - 1;
            if (index >= thumbs.length) index = 0;

            current = index;
            var thumb = thumbs[index];

            // Update main image
            mainImg.src = thumb.getAttribute('data-full-src');
            mainImg.alt = thumb.getAttribute('data-full-alt') || '';

            // Update active states
            thumbs.forEach(function (t, i) {
                t.classList.toggle('is-active', i === index);
                t.setAttribute('aria-selected', i === index ? 'true' : 'false');
            });
        }

        // Thumbnail clicks
        thumbs.forEach(function (thumb, i) {
            thumb.addEventListener('click', function () { goTo(i); });
            thumb.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowRight') { e.preventDefault(); goTo(i + 1); thumbs[current].focus(); }
                if (e.key === 'ArrowLeft')  { e.preventDefault(); goTo(i - 1); thumbs[current].focus(); }
            });
        });

        // Prev / Next buttons
        if (prevBtn) prevBtn.addEventListener('click', function () { goTo(current - 1); });
        if (nextBtn) nextBtn.addEventListener('click', function () { goTo(current + 1); });
    });
})();
