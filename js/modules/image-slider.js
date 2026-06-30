/**
 * DFC Image Slider
 *
 * Two modes:
 * - Default: click thumbnails or arrows to change the main preview image.
 * - Grid-only: thumbnails open in a lightbox with prev/next navigation.
 *
 * Supports keyboard navigation, WCAG aria, touch swipe, and lightbox.
 */

(function () {
    'use strict';

    // ── Lightbox (shared singleton) ─────────────────────────────
    var lightbox = null;
    var lightboxImg = null;
    var lightboxClose = null;
    var lightboxPrev = null;
    var lightboxNext = null;
    var lightboxImages = [];
    var lightboxIndex = 0;

    function createLightbox() {
        if (lightbox) return;

        lightbox = document.createElement('div');
        lightbox.className = 'dfc-lightbox';
        lightbox.setAttribute('role', 'dialog');
        lightbox.setAttribute('aria-label', 'Image lightbox');
        lightbox.setAttribute('aria-modal', 'true');
        lightbox.innerHTML =
            '<div class="dfc-lightbox__overlay"></div>' +
            '<button class="dfc-lightbox__close" aria-label="Close lightbox">' +
                '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>' +
            '</button>' +
            '<button class="dfc-lightbox__prev" aria-label="Previous image">' +
                '<svg width="20" height="15" viewBox="0 0 20 15" fill="none"><path d="M20 7.5H2M2 7.5L8.5 1M2 7.5L8.5 14" stroke="currentColor" stroke-width="2.5"/></svg>' +
            '</button>' +
            '<button class="dfc-lightbox__next" aria-label="Next image">' +
                '<svg width="20" height="15" viewBox="0 0 20 15" fill="none"><path d="M0 7.5H18M18 7.5L11.5 1M18 7.5L11.5 14" stroke="currentColor" stroke-width="2.5"/></svg>' +
            '</button>' +
            '<img class="dfc-lightbox__img" src="" alt="" />';

        document.body.appendChild(lightbox);
        lightboxImg = lightbox.querySelector('.dfc-lightbox__img');
        lightboxClose = lightbox.querySelector('.dfc-lightbox__close');
        lightboxPrev = lightbox.querySelector('.dfc-lightbox__prev');
        lightboxNext = lightbox.querySelector('.dfc-lightbox__next');

        function closeLightbox() {
            lightbox.classList.remove('is-open');
            document.body.style.overflow = '';
            if (lightbox._returnFocus) {
                lightbox._returnFocus.focus();
            }
        }

        function showImage(index) {
            if (index < 0) index = lightboxImages.length - 1;
            if (index >= lightboxImages.length) index = 0;
            lightboxIndex = index;
            lightboxImg.src = lightboxImages[index].src;
            lightboxImg.alt = lightboxImages[index].alt || '';
        }

        lightbox.querySelector('.dfc-lightbox__overlay').addEventListener('click', closeLightbox);
        lightboxClose.addEventListener('click', closeLightbox);
        lightboxPrev.addEventListener('click', function () { showImage(lightboxIndex - 1); });
        lightboxNext.addEventListener('click', function () { showImage(lightboxIndex + 1); });

        lightbox.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') showImage(lightboxIndex - 1);
            if (e.key === 'ArrowRight') showImage(lightboxIndex + 1);

            // Focus trap: Tab cycles through lightbox buttons only
            if (e.key === 'Tab') {
                var focusable = [lightboxClose, lightboxPrev, lightboxNext].filter(function (el) {
                    return el && el.style.display !== 'none';
                });
                if (focusable.length === 0) return;
                var first = focusable[0];
                var last = focusable[focusable.length - 1];
                if (e.shiftKey) {
                    if (document.activeElement === first) {
                        e.preventDefault();
                        last.focus();
                    }
                } else {
                    if (document.activeElement === last) {
                        e.preventDefault();
                        first.focus();
                    }
                }
            }
        });

        // Touch swipe in lightbox
        var touchStartX = 0;
        lightboxImg.addEventListener('touchstart', function (e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });
        lightboxImg.addEventListener('touchend', function (e) {
            var diff = touchStartX - e.changedTouches[0].screenX;
            if (Math.abs(diff) > 50) {
                if (diff > 0) showImage(lightboxIndex + 1);
                else showImage(lightboxIndex - 1);
            }
        }, { passive: true });
    }

    function openLightbox(images, startIndex, returnEl) {
        createLightbox();
        lightboxImages = images;
        lightboxIndex = startIndex;
        lightboxImg.src = images[startIndex].src;
        lightboxImg.alt = images[startIndex].alt || '';
        lightbox._returnFocus = returnEl;
        lightbox.classList.add('is-open');
        document.body.style.overflow = 'hidden';

        // Show/hide prev/next if only one image
        var showNav = images.length > 1;
        lightboxPrev.style.display = showNav ? '' : 'none';
        lightboxNext.style.display = showNav ? '' : 'none';

        lightboxClose.focus();
    }

    // ── Slider init ─────────────────────────────────────────────
    document.querySelectorAll('.js-image-slider').forEach(function (slider) {
        var mainImg   = slider.querySelector('.image-slider__active-img');
        var thumbs    = Array.from(slider.querySelectorAll('.image-slider__thumb'));
        var prevBtn   = slider.querySelector('.image-slider__nav--prev');
        var nextBtn   = slider.querySelector('.image-slider__nav--next');
        var isGridOnly = slider.classList.contains('image-slider--grid-only');
        var current   = 0;

        if (thumbs.length < 1) return;

        // ── Default mode (with main preview image) ──────────────
        if (mainImg && !isGridOnly) {
            if (thumbs.length < 2) return;

            function goTo(index) {
                if (index < 0) index = thumbs.length - 1;
                if (index >= thumbs.length) index = 0;
                current = index;
                var thumb = thumbs[index];
                mainImg.src = thumb.getAttribute('data-full-src');
                mainImg.alt = thumb.getAttribute('data-full-alt') || '';
                thumbs.forEach(function (t, i) {
                    t.classList.toggle('is-active', i === index);
                    t.setAttribute('aria-selected', i === index ? 'true' : 'false');
                });
            }

            thumbs.forEach(function (thumb, i) {
                thumb.addEventListener('click', function () { goTo(i); });
                thumb.addEventListener('keydown', function (e) {
                    if (e.key === 'ArrowRight') { e.preventDefault(); goTo(i + 1); thumbs[current].focus(); }
                    if (e.key === 'ArrowLeft')  { e.preventDefault(); goTo(i - 1); thumbs[current].focus(); }
                });
            });

            if (prevBtn) prevBtn.addEventListener('click', function () { goTo(current - 1); });
            if (nextBtn) nextBtn.addEventListener('click', function () { goTo(current + 1); });
        }

        // ── Grid-only mode (lightbox on click, scroll for 4+) ────
        if (isGridOnly) {
            // Build image array for lightbox navigation
            var galleryImages = thumbs.map(function (thumb) {
                return {
                    src: thumb.getAttribute('data-full-src'),
                    alt: thumb.getAttribute('data-full-alt') || ''
                };
            });

            thumbs.forEach(function (thumb, i) {
                thumb.addEventListener('click', function () {
                    openLightbox(galleryImages, i, thumb);
                });
            });

            // Show arrows only if more than 3 thumbnails (they scroll the strip)
            var thumbContainer = slider.querySelector('.image-slider__thumbs');
            if (thumbs.length <= 3) {
                if (prevBtn) prevBtn.style.display = 'none';
                if (nextBtn) nextBtn.style.display = 'none';
            } else if (thumbContainer && prevBtn && nextBtn) {
                // Calculate scroll distance: one thumb width + gap
                prevBtn.addEventListener('click', function () {
                    var thumbWidth = thumbs[0].offsetWidth + 12;
                    thumbContainer.scrollBy({ left: -thumbWidth, behavior: 'smooth' });
                });
                nextBtn.addEventListener('click', function () {
                    var thumbWidth = thumbs[0].offsetWidth + 12;
                    thumbContainer.scrollBy({ left: thumbWidth, behavior: 'smooth' });
                });
            }
        }

        // ── Touch swipe (default mode only) ─────────────────────
        if (!isGridOnly && mainImg) {
            var touchStartX = 0;
            slider.addEventListener('touchstart', function (e) {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });
            slider.addEventListener('touchend', function (e) {
                var diff = touchStartX - e.changedTouches[0].screenX;
                if (Math.abs(diff) > 50) {
                    if (diff > 0 && nextBtn) nextBtn.click();
                    if (diff < 0 && prevBtn) prevBtn.click();
                }
            }, { passive: true });
        }
    });
})();
