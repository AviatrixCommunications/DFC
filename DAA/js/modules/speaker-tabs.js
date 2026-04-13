export default function initSpeakerTabs() {
  document.addEventListener('DOMContentLoaded', () => {
    const tabContainers = document.querySelectorAll('.speakers__tabs');

    tabContainers.forEach(container => {
      const tablist = container.querySelector('[role="tablist"]');
      const tabs = container.querySelectorAll('[role="tab"]');
      const panels = container.querySelectorAll('[role="tabpanel"]');
      const isDisabled = container.classList.contains('speakers__tabs--disabled');

      if (!tabs.length) return;

      // Handle tab click
      tabs.forEach(tab => {
        tab.addEventListener('click', () => {
          if (isDisabled || tab.getAttribute('aria-disabled') === 'true') return;
          activateTab(tab, tabs, panels);
        });
      });

      // Handle keyboard navigation
      tablist.addEventListener('keydown', (e) => {
        if (isDisabled) return;

        const currentTab = document.activeElement;
        const tabArray = Array.from(tabs);
        const currentIndex = tabArray.indexOf(currentTab);

        let newIndex;

        switch (e.key) {
          case 'ArrowRight':
            e.preventDefault();
            newIndex = currentIndex + 1 >= tabs.length ? 0 : currentIndex + 1;
            tabArray[newIndex].focus();
            activateTab(tabArray[newIndex], tabs, panels);
            break;

          case 'ArrowLeft':
            e.preventDefault();
            newIndex = currentIndex - 1 < 0 ? tabs.length - 1 : currentIndex - 1;
            tabArray[newIndex].focus();
            activateTab(tabArray[newIndex], tabs, panels);
            break;

          case 'Home':
            e.preventDefault();
            tabArray[0].focus();
            activateTab(tabArray[0], tabs, panels);
            break;

          case 'End':
            e.preventDefault();
            tabArray[tabs.length - 1].focus();
            activateTab(tabArray[tabs.length - 1], tabs, panels);
            break;
        }
      });
    });

    function activateTab(selectedTab, tabs, panels) {
      const currentPanel = Array.from(panels).find(panel => !panel.hidden);
      const panelId = selectedTab.getAttribute('aria-controls');
      const newPanel = document.getElementById(panelId);

      // If clicking the same tab, do nothing
      if (currentPanel === newPanel) return;

      // Deactivate all tabs
      tabs.forEach(tab => {
        tab.setAttribute('aria-selected', 'false');
        tab.setAttribute('tabindex', '-1');
        tab.classList.remove('speakers__tab--active');
      });

      // Activate selected tab
      selectedTab.setAttribute('aria-selected', 'true');
      selectedTab.setAttribute('tabindex', '0');
      selectedTab.classList.add('speakers__tab--active');

      // Fade out current panel, then fade in new panel
      if (currentPanel) {
        currentPanel.classList.add('speakers__panel--fading-out');
        currentPanel.classList.remove('speakers__panel--active');

        setTimeout(() => {
          currentPanel.hidden = true;
          currentPanel.classList.remove('speakers__panel--fading-out');

          if (newPanel) {
            newPanel.hidden = false;
            newPanel.classList.add('speakers__panel--fading-in');

            setTimeout(() => {
              newPanel.classList.remove('speakers__panel--fading-in');
              newPanel.classList.add('speakers__panel--active');
            }, 20);
          }
        }, 200);
      } else if (newPanel) {
        newPanel.hidden = false;
        newPanel.classList.add('speakers__panel--active');
      }
    }
  });
}
