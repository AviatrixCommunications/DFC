/**
 * DFC Hero Slider
 *
 * Auto-advancing slider with pause/play, prev/next.
 * Only activates when multiple slides exist (single-image heroes have no JS overhead).
 * Pauses on hover and focus for accessibility.
 */

(function () {
    'use strict';

    var slider = document.querySelector('.hero-slider--multi .js-hero-slider');
    if (!slider) return;

    var slides    = Array.from(slider.querySelectorAll('.hero-slider__slide'));
    var prevBtn   = slider.querySelector('.hero-slider__prev');
    var nextBtn   = slider.querySelector('.hero-slider__next');
    var pauseBtn  = slider.querySelector('.hero-slider__pause');
    var iconPause = pauseBtn ? pauseBtn.querySelector('.hero-slider__icon-pause') : null;
    var iconPlay  = pauseBtn ? pauseBtn.querySelector('.hero-slider__icon-play') : null;

    var current   = 0;
    var interval  = null;
    var paused    = false;
    var DELAY     = 6000; // 6 seconds per slide

    if (slides.length < 2) return;

    function goTo(index) {
        if (index < 0) index = slides.length - 1;
        if (index >= slides.length) index = 0;

        slides[current].classList.remove('is-active');
        current = index;
        slides[current].classList.add('is-active');

        // Update aria labels
        slides.forEach(function (slide, i) {
            slide.setAttribute('aria-hidden', i !== current ? 'true' : 'false');
        });
    }

    function startAutoplay() {
        if (paused) return;
        stopAutoplay();
        interval = setInterval(function () {
            goTo(current + 1);
        }, DELAY);
    }

    function stopAutoplay() {
        if (interval) {
            clearInterval(interval);
            interval = null;
        }
    }

    function togglePause() {
        paused = !paused;
        if (paused) {
            stopAutoplay();
            if (iconPause) iconPause.style.display = 'none';
            if (iconPlay)  iconPlay.style.display  = 'block';
            if (pauseBtn) pauseBtn.setAttribute('aria-label', 'Play slideshow');
        } else {
            if (iconPause) iconPause.style.display = 'block';
            if (iconPlay)  iconPlay.style.display  = 'none';
            if (pauseBtn) pauseBtn.setAttribute('aria-label', 'Pause slideshow');
            startAutoplay();
        }
    }

    // Button events
    if (prevBtn) prevBtn.addEventListener('click', function () { goTo(current - 1); startAutoplay(); });
    if (nextBtn) nextBtn.addEventListener('click', function () { goTo(current + 1); startAutoplay(); });
    if (pauseBtn) pauseBtn.addEventListener('click', togglePause);

    // Pause on hover/focus for accessibility
    slider.addEventListener('mouseenter', stopAutoplay);
    slider.addEventListener('mouseleave', function () { if (!paused) startAutoplay(); });
    slider.addEventListener('focusin', stopAutoplay);
    slider.addEventListener('focusout', function () { if (!paused) startAutoplay(); });

    // Respect prefers-reduced-motion
    var motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (motionQuery.matches) {
        paused = true;
    } else {
        startAutoplay();
    }

    motionQuery.addEventListener('change', function () {
        if (motionQuery.matches) {
            paused = true;
            stopAutoplay();
        }
    });

    // Initialize aria states
    slides.forEach(function (slide, i) {
        if (i !== 0) slide.setAttribute('aria-hidden', 'true');
    });

})();
