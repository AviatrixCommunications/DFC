
export default function initAccordion() {
  document.addEventListener('DOMContentLoaded', () => {
    const toggles = document.querySelectorAll('.beamcats__item-toggle');

    toggles.forEach(toggle => {
      toggle.addEventListener('click', () => {

        const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
        const targetId = toggle.getAttribute('aria-controls');
        const targetContent = document.getElementById(targetId);

        toggle.setAttribute('aria-expanded', !isExpanded);

        if (targetContent) {
          targetContent.hidden = isExpanded;
        }

        const parentItem = toggle.closest('.beamcats__item');
        parentItem.classList.toggle('is-active');
      });
    });
  });
}
