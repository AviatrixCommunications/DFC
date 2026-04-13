( function () {
	'use strict';

	/**
	 * Initialise a single tabs block.
	 * Builds the tablist from .tabs__panel children, wires up ARIA
	 * attributes, and handles keyboard navigation per the ARIA APG
	 * tabs pattern (https://www.w3.org/WAI/ARIA/apg/patterns/tabs/).
	 *
	 * @param {HTMLElement} block
	 */
	function initTabs( block ) {
		const tablist = block.querySelector( '.tabs__list' );
		const panels  = Array.from( block.querySelectorAll( '.tabs__panels > .tabs__panel' ) );

		if ( ! tablist || ! panels.length ) return;

		// Unique prefix so IDs are collision-free across multiple blocks.
		const uid = 'tabs-' + Math.random().toString( 36 ).slice( 2, 8 );

		const tabButtons = panels.map( function ( panel, i ) {
			const labelEl = panel.querySelector( '.tabs__panel-label' );
			const label   = labelEl ? labelEl.textContent.trim() : ( 'Tab ' + ( i + 1 ) );
			const isFirst = i === 0;
			const tabId   = uid + '-tab-' + i;
			const panelId = uid + '-panel-' + i;

			// Wire up panel ARIA.
			panel.setAttribute( 'role', 'tabpanel' );
			panel.setAttribute( 'id', panelId );
			panel.setAttribute( 'aria-labelledby', tabId );
			panel.setAttribute( 'tabindex', '0' );
			if ( ! isFirst ) panel.hidden = true;

			// Hide the heading inside the panel — it is represented by the
			// tab button and would be read twice by screen readers if left
			// visible. aria-hidden alone is not enough for some SR+browser
			// combinations, so we use the hidden attribute.
			if ( labelEl ) {
				labelEl.setAttribute( 'aria-hidden', 'true' );
				labelEl.hidden = true;
			}

			// Build tab button (inherits global button styles via element selector).
			const btn = document.createElement( 'button' );
			btn.setAttribute( 'type', 'button' );
			btn.setAttribute( 'role', 'tab' );
			btn.setAttribute( 'id', tabId );
			btn.setAttribute( 'aria-controls', panelId );
			btn.setAttribute( 'aria-selected', isFirst ? 'true' : 'false' );
			btn.setAttribute( 'tabindex', isFirst ? '0' : '-1' );
			btn.className   = 'tabs__tab';
			btn.textContent = label;

			tablist.appendChild( btn );
			return btn;
		} );

		/**
		 * Activate tab at `index`, deactivate all others.
		 * @param {number} index
		 */
		function activate( index ) {
			tabButtons.forEach( function ( btn, i ) {
				const active = i === index;
				btn.setAttribute( 'aria-selected', active ? 'true' : 'false' );
				btn.setAttribute( 'tabindex', active ? '0' : '-1' );
				panels[ i ].hidden = ! active;
			} );
		}

		// Click.
		tabButtons.forEach( function ( btn, i ) {
			btn.addEventListener( 'click', function () {
				activate( i );
				btn.focus();
			} );
		} );

		// Keyboard navigation (roving tabindex).
		tablist.addEventListener( 'keydown', function ( e ) {
			const current = tabButtons.indexOf( document.activeElement );
			if ( current === -1 ) return;

			var next = -1;
			switch ( e.key ) {
				case 'ArrowRight': next = ( current + 1 ) % tabButtons.length; break;
				case 'ArrowLeft':  next = ( current - 1 + tabButtons.length ) % tabButtons.length; break;
				case 'Home':       next = 0; break;
				case 'End':        next = tabButtons.length - 1; break;
				default: return;
			}

			e.preventDefault();
			activate( next );
			tabButtons[ next ].focus();
		} );
	}

	function init() {
		document.querySelectorAll( '.aviatrix-block--tabs' ).forEach( initTabs );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
