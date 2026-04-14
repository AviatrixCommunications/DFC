( function () {
	var el = wp.element.createElement;
	var registerBlockVariation = wp.blocks.registerBlockVariation;
	var unregisterBlockVariation = wp.blocks.unregisterBlockVariation;

	// Add a four columns variation to the columns block.
	registerBlockVariation( 'core/columns', {
		name: 'four-columns-equal',
		title: '25/25/25/25',
		description: 'Four columns; equal split',
		icon: el(
			'svg',
			{ width: 48, height: 48, viewBox: '0 0 48 48', xmlns: 'http://www.w3.org/2000/svg' },
			el( 'path', {
				fillRule: 'evenodd',
				d: 'M39,12H9c-1.1,0-2,0.9-2,2v20c0,1.1,0.9,2,2,2h30c1.1,0,2-0.9,2-2V14C41,12.9,40.1,12,39,12z M15,34H9V14h6V34z M23,34h-6V14h6V34z M25,14h6v20h-6V14z M39,34h-6V14h6V34z',
			} )
		),
		innerBlocks: [ [ 'core/column' ], [ 'core/column' ], [ 'core/column' ], [ 'core/column' ] ],
		scope: [ 'block' ],
	} );

	// Remove column variations.
	wp.domReady( function () {
		unregisterBlockVariation( 'core/columns', 'two-columns-one-third-two-thirds' );
		unregisterBlockVariation( 'core/columns', 'two-columns-two-thirds-one-third' );
		unregisterBlockVariation( 'core/columns', 'three-columns-wider-center' );
	} );

	// Limit allowed blocks inside columns.
	wp.hooks.addFilter( 'blocks.registerBlockType', 'limit-column-blocks', function ( settings, name ) {
		if ( name === 'core/column' ) {
			settings.attributes.identifier = {
				type: 'string',
				default: '',
			};
			var allowedBlocks = settings.allowedBlocks || [];
			return Object.assign( {}, settings, {
				allowedBlocks: allowedBlocks.concat( [
					'core/heading',
					'core/paragraph',
					'core/image',
					'core/list',
					'core/list-item',
					'core/buttons',
					'core/button',
					'core/quote',
					'core/table',
					'core/separator',
					'core/spacer',
					'core/embed',
					'core/video',
					'core/shortcode',
				] ),
			} );
		}
		return settings;
	} );
} )();
