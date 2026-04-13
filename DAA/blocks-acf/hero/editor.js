( function () {
	var locked = false;

	wp.data.subscribe( function () {
		var blocks = wp.data.select( 'core/block-editor' ).getBlocks();
		var missing = false;

		blocks.forEach( function ( block ) {
			if ( block.name !== 'acf/hero' ) return;
			( block.innerBlocks || [] ).forEach( function ( col ) {
				( col.innerBlocks || [] ).forEach( function ( row ) {
					( row.innerBlocks || [] ).forEach( function ( inner ) {
						if (
							inner.name === 'core/heading' &&
							inner.attributes.level === 1 &&
							! ( inner.attributes.content || '' ).replace( /<[^>]*>/g, '' ).trim()
						) {
							missing = true;
						}
					} );
				} );
			} );
		} );

		if ( missing && ! locked ) {
			locked = true;
			wp.data.dispatch( 'core/editor' ).lockPostSaving( 'hero-h1-required' );
			wp.data.dispatch( 'core/notices' ).createNotice(
				'warning',
				'Hero block: the main heading (H1) is required.',
				{ id: 'hero-h1-required', isDismissible: false }
			);
		} else if ( ! missing && locked ) {
			locked = false;
			wp.data.dispatch( 'core/editor' ).unlockPostSaving( 'hero-h1-required' );
			wp.data.dispatch( 'core/notices' ).removeNotice( 'hero-h1-required' );
		}
	} );
} )();
