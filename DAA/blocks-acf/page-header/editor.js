( function () {
	var select   = wp.data.select;
	var dispatch = wp.data.dispatch;

	// One-time flags keyed by clientId.
	var lockEnforced   = {};
	var titlePopulated = {};
	var imagePopulated = {};

	wp.data.subscribe( function () {
		// Check post type inside subscribe — the store is not ready at load time.
		var postType = select( 'core/editor' ).getCurrentPostType();
		if ( postType !== 'page' ) return;

		var blocks = select( 'core/block-editor' ).getBlocks();

		blocks.forEach( function ( block ) {
			if ( block.name !== 'acf/page-header' ) return;

			// Enforce lock on the page-header block itself.
			if ( ! lockEnforced[ block.clientId ] ) {
				var lock = block.attributes.lock;
				if ( ! lock || ! lock.move || ! lock.remove ) {
					lockEnforced[ block.clientId ] = true;
					dispatch( 'core/block-editor' ).updateBlockAttributes( block.clientId, {
						lock: { move: true, remove: true },
					} );
				} else {
					lockEnforced[ block.clientId ] = true;
				}
			}

			// Walk inner blocks to find heading and image.
			( block.innerBlocks || [] ).forEach( function ( inner ) {
				if ( inner.name === 'core/image' ) {
					autoPopulateImage( inner );
				}
				if ( inner.name === 'core/group' ) {
					( inner.innerBlocks || [] ).forEach( function ( groupChild ) {
						if (
							groupChild.name === 'core/heading' &&
							groupChild.attributes.level === 1
						) {
							autoPopulateTitle( groupChild );
						}
					} );
				}
			} );
		} );
	} );

	function autoPopulateTitle( headingBlock ) {
		if ( titlePopulated[ headingBlock.clientId ] ) return;

		var content = ( headingBlock.attributes.content || '' )
			.replace( /<[^>]*>/g, '' )
			.trim();

		if ( content ) {
			titlePopulated[ headingBlock.clientId ] = true;
			return;
		}

		// Use the saved (persisted) title, not the live editing state,
		// to avoid capturing partial input while the user types.
		var savedTitle = ( select( 'core/editor' ).getCurrentPost() || {} ).title;
		if ( ! savedTitle ) return; // No saved title yet; will retry after save.

		titlePopulated[ headingBlock.clientId ] = true;
		dispatch( 'core/block-editor' ).updateBlockAttributes( headingBlock.clientId, {
			content: savedTitle,
		} );
	}

	function autoPopulateImage( imageBlock ) {
		if ( imagePopulated[ imageBlock.clientId ] ) return;

		if ( imageBlock.attributes.id || imageBlock.attributes.url ) {
			imagePopulated[ imageBlock.clientId ] = true;
			return;
		}

		var featuredImageId = select( 'core/editor' ).getEditedPostAttribute(
			'featured_media'
		);
		if ( ! featuredImageId ) return; // No featured image yet; will retry.

		var media = select( 'core' ).getMedia( featuredImageId, { context: 'view' } );
		if ( ! media ) return; // Still loading; will retry on next tick.

		imagePopulated[ imageBlock.clientId ] = true;

		var sizes  = ( media.media_details && media.media_details.sizes ) || {};
		var imgUrl = ( sizes.full && sizes.full.source_url ) || media.source_url;

		dispatch( 'core/block-editor' ).updateBlockAttributes( imageBlock.clientId, {
			id:       featuredImageId,
			url:      imgUrl,
			alt:      media.alt_text || '',
			sizeSlug: 'full',
		} );
	}
} )();
