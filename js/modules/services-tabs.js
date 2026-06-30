/**
 * DFC Services Tabs
 *
 * Accessible tabbed interface following WAI-ARIA Tabs pattern.
 * Arrow keys move between tabs, Enter/Space activates.
 */

(function () {
    'use strict';

    document.querySelectorAll('.aviatrix-block--services-tabs').forEach(function (block) {
        var tabs   = Array.from(block.querySelectorAll('[role="tab"]'));
        var panels = Array.from(block.querySelectorAll('[role="tabpanel"]'));

        if (tabs.length < 2) return;

        function activate(index) {
            tabs.forEach(function (tab, i) {
                var selected = i === index;
                tab.setAttribute('aria-selected', String(selected));
                tab.setAttribute('tabindex', selected ? '0' : '-1');
            });

            panels.forEach(function (panel, i) {
                if (i === index) {
                    panel.removeAttribute('hidden');
                } else {
                    panel.setAttribute('hidden', '');
                }
            });
        }

        tabs.forEach(function (tab, i) {
            tab.addEventListener('click', function () {
                activate(i);
            });

            tab.addEventListener('keydown', function (e) {
                var next;
                switch (e.key) {
                    case 'ArrowRight':
                        e.preventDefault();
                        next = (i + 1) % tabs.length;
                        activate(next);
                        tabs[next].focus();
                        break;
                    case 'ArrowLeft':
                        e.preventDefault();
                        next = (i - 1 + tabs.length) % tabs.length;
                        activate(next);
                        tabs[next].focus();
                        break;
                    case 'Home':
                        e.preventDefault();
                        activate(0);
                        tabs[0].focus();
                        break;
                    case 'End':
                        e.preventDefault();
                        activate(tabs.length - 1);
                        tabs[tabs.length - 1].focus();
                        break;
                }
            });
        });

        // Initialize: first tab active
        activate(0);
    });
})();
