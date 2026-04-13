<?php

/**
 * Icon Columns Block Template.
 *
 * Columns with icons, headings, and descriptions.
 * Uses block style variations for column count (2/3/4) and layout (vertical/horizontal).
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML.
 * @param bool   $is_preview True during AJAX preview.
 * @param int    $post_id    The post ID this block is saved to.
 */

$base_class = 'aviatrix-block aviatrix-block--icon-columns js-fadein-up is-layout-constrained';
// Default to 3-column if no style selected.
if ( empty( $block['className'] ) || strpos( $block['className'], 'is-style-' ) === false ) {
	$base_class .= ' is-style-3-column';
}

$wrapper = [ 'class' => $base_class ];
if ( ! empty( $block['anchor'] ) ) {
	$wrapper['id'] = $block['anchor'];
}

$attrs = get_block_wrapper_attributes( $wrapper );

$allowed_column_blocks = [
	'core/heading',
	'core/paragraph',
	'core/list',
	'core/list-item',
	'core/buttons',
	'core/button',
	'core/separator',
	'core/spacer',
];

$column = function ( $title = 'Column Title', $subtitle = 'Subtitle' ) use ( $allowed_column_blocks ) {
	return [ 'core/group', [
		'className'     => 'icon-columns__column',
		'templateLock'  => false,
		'allowedBlocks' => [ 'core/image', 'core/group' ],
	], [
		[ 'core/image', [
			'className' => 'icon-columns__icon',
		] ],
		[ 'core/group', [
			'className'     => 'icon-columns__content',
			'templateLock'  => false,
			'allowedBlocks' => $allowed_column_blocks,
		], [
			[ 'core/heading', [
				'level'       => 3,
				'placeholder' => $title,
			] ],
			[ 'core/heading', [
				'level'       => 4,
				'placeholder' => $subtitle,
				'className'   => 'icon-columns__subtitle',
			] ],
			[ 'core/paragraph', [
				'placeholder' => 'Add content…',
			] ],
		] ],
	] ];
};

$template = [
	[ 'core/group', [
		'className'     => 'icon-columns__grid has-global-padding',
		'templateLock'  => false,
		'allowedBlocks' => [ 'core/group' ],
	], [
		$column( 'Column Title', 'Subtitle' ),
		$column( 'Column Title', 'Subtitle' ),
		$column( 'Column Title', 'Subtitle' ),
	] ],
];
?>

<?php if ( $is_preview ) : // ---------- EDITOR ---------- ?>

	<section <?php echo $attrs; ?>>
		<InnerBlocks
			template="<?php echo esc_attr( wp_json_encode( $template ) ); ?>"
			templateLock="insert"
		/>
	</section>

<?php else : // ---------- FRONTEND ---------- ?>

	<section <?php echo $attrs; ?>>
		<?php echo $content; ?>
	</section>

<?php endif; ?>
