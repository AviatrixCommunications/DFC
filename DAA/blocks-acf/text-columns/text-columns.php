<?php

/**
 * Text Columns Block Template.
 *
 * Columns of text content with vertical dividers between them.
 * Each column has an optional icon, title, and content area.
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML (empty).
 * @param bool   $is_preview True during AJAX preview.
 * @param int    $post_id    The post ID this block is saved to.
 */

$base_class = 'aviatrix-block aviatrix-block--text-columns js-fadein-up is-layout-constrained';
if ( empty( $block['className'] ) || strpos( $block['className'], 'is-style-' ) === false ) {
	$base_class .= ' is-style-3-col';
}

$wrapper = [ 'class' => $base_class ];
if ( ! empty( $block['anchor'] ) ) {
	$wrapper['id'] = $block['anchor'];
}

$attrs = get_block_wrapper_attributes( $wrapper );

$lock = [ 'move' => true, 'remove' => true ];

$template = [
	[ 'core/heading', [
		'level'       => 2,
		'placeholder' => 'Section Heading',
		'className'   => 'text-columns__heading has-global-padding',
		'lock'        => $lock,
	] ],
	[ 'core/paragraph', [
		'placeholder' => 'Add intro text…',
		'className'   => 'text-columns__intro has-global-padding',
	] ],
	[ 'core/columns', [
		'className'    => 'text-columns__columns has-global-padding',
		'lock'         => $lock,
		'templateLock' => false,
	], [
		[ 'core/column', [], [
			[ 'core/group', [
				'className'    => 'text-columns__title-row',
				'templateLock' => 'all',
			], [
				[ 'core/image', [
					'className' => 'text-columns__icon',
				] ],
				[ 'core/heading', [
					'level'       => 3,
					'placeholder' => 'Column Heading',
				] ],
			] ],
			[ 'core/paragraph', [
				'placeholder' => 'Add column content…',
			] ],
		] ],
		[ 'core/column', [], [
			[ 'core/group', [
				'className'    => 'text-columns__title-row',
				'templateLock' => 'all',
			], [
				[ 'core/image', [
					'className' => 'text-columns__icon',
				] ],
				[ 'core/heading', [
					'level'       => 3,
					'placeholder' => 'Column Heading',
				] ],
			] ],
			[ 'core/paragraph', [
				'placeholder' => 'Add column content…',
			] ],
		] ],
		[ 'core/column', [], [
			[ 'core/group', [
				'className'    => 'text-columns__title-row',
				'templateLock' => 'all',
			], [
				[ 'core/image', [
					'className' => 'text-columns__icon',
				] ],
				[ 'core/heading', [
					'level'       => 3,
					'placeholder' => 'Column Heading',
				] ],
			] ],
			[ 'core/paragraph', [
				'placeholder' => 'Add column content…',
			] ],
		] ],
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
