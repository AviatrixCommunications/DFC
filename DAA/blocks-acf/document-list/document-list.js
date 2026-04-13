(function () {
	'use strict';

	function initDocumentList(block) {
		var select = block.querySelector('.document-list__year-select');
		var items  = block.querySelectorAll('.document-list__item');
		var status = block.querySelector('[aria-live="polite"]');

		if (!select || !items.length) return;

		select.addEventListener('change', function () {
			var year         = this.value;
			var visibleCount = 0;

			items.forEach(function (item) {
				var show = item.dataset.year === year;
				item.hidden = !show;
				if (show) visibleCount++;
			});

			if (status) {
				var yearLabel = this.options[this.selectedIndex].text;
				status.textContent =
					visibleCount +
					' document' + (visibleCount !== 1 ? 's' : '') +
					' shown for ' + yearLabel + '.';
			}
		});
	}

	function init() {
		document.querySelectorAll('.aviatrix-block--document-list').forEach(initDocumentList);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
