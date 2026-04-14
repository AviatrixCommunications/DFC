export default function initFadeIn() {
  document.addEventListener('DOMContentLoaded', () => {
    const fadeElements = document.querySelectorAll('.js-fadein, .js-fadein-up');

    if (!fadeElements.length) return;

    // Respect reduced motion preferences
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion) {
      // Make all elements visible immediately
      fadeElements.forEach(el => el.classList.add('is-visible'));
      return;
    }

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
          }
        });
      },
      {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
      }
    );

    fadeElements.forEach(el => observer.observe(el));
  });
}
