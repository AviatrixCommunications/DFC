const ARROW_SVG = '<svg class="expandable-cards__toggle-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 6L15 12L9 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

export default function initExpandableCards() {
  document.addEventListener('DOMContentLoaded', () => {
    const grids = document.querySelectorAll('.expandable-cards__grid');
    if (!grids.length) return;

    grids.forEach(grid => {
      const cards = grid.querySelectorAll('.expandable-cards__card');

      cards.forEach(card => {
        const details = card.querySelector('.expandable-cards__details');
        if (!details) return;

        // Mark card as JS-initialized (hides details via CSS)
        card.classList.add('is-initialized');
        details.setAttribute('aria-hidden', 'true');

        // Create toggle button
        const toggle = document.createElement('button');
        toggle.className = 'expandable-cards__toggle';
        toggle.setAttribute('aria-expanded', 'false');
        toggle.innerHTML = `Learn More ${ARROW_SVG}`;

        card.appendChild(toggle);

        toggle.addEventListener('click', () => {
          const isExpanded = card.classList.contains('is-expanded');

          // Close other expanded cards in this grid
          grid.querySelectorAll('.expandable-cards__card.is-expanded').forEach(other => {
            if (other !== card) {
              other.classList.remove('is-expanded');
              const otherDetails = other.querySelector('.expandable-cards__details');
              if (otherDetails) otherDetails.setAttribute('aria-hidden', 'true');
              const otherToggle = other.querySelector('.expandable-cards__toggle');
              if (otherToggle) {
                otherToggle.setAttribute('aria-expanded', 'false');
                otherToggle.innerHTML = `Learn More ${ARROW_SVG}`;
              }
            }
          });

          if (isExpanded) {
            card.classList.remove('is-expanded');
            details.setAttribute('aria-hidden', 'true');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.innerHTML = `Learn More ${ARROW_SVG}`;
          } else {
            card.classList.add('is-expanded');
            details.setAttribute('aria-hidden', 'false');
            toggle.setAttribute('aria-expanded', 'true');
            toggle.innerHTML = `Show Less ${ARROW_SVG}`;

            // Scroll expanded card into view if needed
            setTimeout(() => {
              card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 50);
          }
        });
      });
    });
  });
}
