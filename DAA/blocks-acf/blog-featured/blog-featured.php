<?php

/**
 * Blog Featured Block Template.
 *
 * Two-column layout: image (left) + heading, text, button (right).
 * Users can add simple text blocks but not complex ones.
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML (empty).
 * @param bool   $is_preview True during AJAX preview.
 * @param int    $post_id    The post ID this block is saved to.
 */

$wrapper = [ 'class' => 'aviatrix-block aviatrix-block--blog-featured js-fadein-up is-layout-constrained' ];
if ( ! empty( $block['anchor'] ) ) {
	$wrapper['id'] = $block['anchor'];
}

// Default background to light-grey if none is set.
if ( empty( $block['backgroundColor'] ) && empty( $block['style']['color']['background'] ) ) {
	$wrapper['class'] .= ' has-light-grey-background-color has-background';
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
	[ 'core/columns', [ 'className' => 'blog-featured__columns has-global-padding', 'verticalAlignment' => 'center' ], [
		[ 'core/column', [ 'className' => 'blog-featured__image', 'width' => '25%', 'lock' => $lock, 'templateLock' => 'all' ], [
			[ 'core/image', [ 'lock' => $lock ] ],
		] ],
		[ 'core/column', [ 'className' => 'blog-featured__content', 'lock' => $lock, 'templateLock' => false, 'allowedBlocks' => $allowed_content_blocks ], [
			[ 'core/heading',   [ 'level' => 2, 'placeholder' => 'Heading', 'lock' => $lock ] ],
			[ 'core/paragraph', [ 'placeholder' => 'Add content here…' ] ],
			[ 'core/buttons',   [ 'lock' => $lock ], [
				[ 'core/button', [ 'placeholder' => 'Button text' ] ],
			] ],
		] ],
	] ],
];
?>

<?php if ( $is_preview ) : // ---------- EDITOR ---------- ?>

	<section <?php echo $attrs; ?>>
		<InnerBlocks
			template="<?php echo esc_attr( wp_json_encode( $template ) ); ?>"
			templateLock="all"
		/>
	</section>

<?php else : // ---------- FRONTEND ---------- ?>

	<section <?php echo $attrs; ?>>
		<?php echo $content; ?>
	</section>

<?php endif; ?>
