/**
 * ACF InnerBlocks initialization fix (safety net).
 *
 * The primary fix is the wp_doing_ajax() guard in blocks.php which prevents
 * the render_block filter from interfering with ACF editor previews.
 * This script acts as a fallback: if a new ACF block still ends up with
 * empty InnerBlocks, it forces a second preview cycle.
 */
( function() {
	var select    = wp.data.select;
	var dispatch  = wp.data.dispatch;
	var subscribe = wp.data.subscribe;

	var knownIds = {};
	var ready    = false;

	// Snapshot existing block IDs once the editor has loaded.
	wp.domReady( function() {
		setTimeout( function() {
			select( 'core/block-editor' ).getBlocks().forEach( function( b ) {
				knownIds[ b.clientId ] = true;
			} );
			ready = true;
		}, 300 );
	} );

	subscribe( function() {
		if ( ! ready ) return;

		select( 'core/block-editor' ).getBlocks().forEach( function( block ) {
			if ( knownIds[ block.clientId ] ) return;
			knownIds[ block.clientId ] = true;

			if ( ! block.name || block.name.indexOf( 'acf/' ) !== 0 ) return;

			// Give ACF time to complete its initial preview render.
			setTimeout( function() {
				var current = select( 'core/block-editor' ).getBlock( block.clientId );
				if ( ! current ) return;
				if ( current.innerBlocks && current.innerBlocks.length > 0 ) return;

				// InnerBlocks are empty — nudge ACF into re-rendering.
				var data = Object.assign( {}, current.attributes.data || {} );
				data._acf_refresh = '1';
				dispatch( 'core/block-editor' ).updateBlockAttributes(
					current.clientId,
					{ data: data }
				);
			}, 500 );
		} );
	} );
} )();
