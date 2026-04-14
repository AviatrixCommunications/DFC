<?php

/**
 * Image + Content Block Template.
 *
 * Pure InnerBlocks approach using core/columns.
 * Image column is fully locked; content column allows
 * heading, paragraph, list, buttons.
 * Uses per-block `lock` attributes (not templateLock cascade)
 * so the content column stays insertable.
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML (empty).
 * @param bool   $is_preview True during AJAX preview.
 * @param int    $post_id    The post ID this block is saved to.
 */

$base_class = 'aviatrix-block aviatrix-block--image-content is-layout-constrained';
if ( empty( $block['className'] ) || strpos( $block['className'], 'is-style-' ) === false ) {
	$base_class .= ' is-style-half-image-left';
}

$wrapper = [ 'class' => $base_class ];
if ( ! empty( $block['anchor'] ) ) {
	$wrapper['id'] = $block['anchor'];
}

$attrs = get_block_wrapper_attributes( $wrapper );

$lock = [ 'move' => true, 'remove' => true ];

$allowed_content_blocks = [
	'core/heading',
	'core/paragraph',
	'core/list',
	'core/list-item',
	'core/buttons',
	'core/button',
];

$template = [
	[ 'core/columns', [ 'className' => 'image-content__columns has-global-padding' ], [
		[ 'core/column', [ 'className' => 'image-content__image js-fadein-up', 'lock' => $lock, 'templateLock' => 'all' ], [
			[ 'core/image', [ 'lock' => $lock ] ],
		] ],
		[ 'core/column', [ 'className' => 'image-content__content js-fadein-up', 'lock' => $lock, 'templateLock' => false, 'allowedBlocks' => $allowed_content_blocks ], [
			[ 'core/heading',   [ 'level' => 3, 'placeholder' => 'Heading (Optional)' ] ],
			[ 'core/paragraph', [ 'placeholder' => 'Add content here…' ] ],
			[ 'core/buttons',   [], [
				[ 'core/button', [ 'placeholder' => 'Button (Optional)' ] ],
			] ],
		] ],
	] ],
];
?>

<?php if ( $is_preview ) : // ---------- EDITOR ---------- ?>

	<section <?php echo $attrs; ?>>
		<?php if ( empty( $content ) ) : ?>
			<InnerBlocks
				template="<?php echo esc_attr( wp_json_encode( $template ) ); ?>"
			/>
		<?php else : ?>
			<InnerBlocks />
		<?php endif; ?>
	</section>

<?php else : // ---------- FRONTEND ---------- ?>

	<section <?php echo $attrs; ?>>
		<?php echo $content; ?>
	</section>

<?php endif; ?>
