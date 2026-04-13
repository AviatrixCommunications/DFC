<?php

/**
 * Hero Block Template.
 *
 * Uses InnerBlocks with a locked template of core blocks.
 * On the frontend, empty inner blocks are skipped via a render_block filter
 * scoped to this block's rendering pass.
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML (empty).
 * @param bool   $is_preview True during AJAX preview.
 * @param int    $post_id    The post ID this block is saved to.
 */

$base_class = 'aviatrix-block aviatrix-block--hero';
if ( is_front_page() ) {
	$base_class .= ' hero--home';
}
if ( empty( $block['className'] ) || strpos( $block['className'], 'is-style-' ) === false ) {
	$base_class .= ' is-style-default';
}

$attrs = get_block_wrapper_attributes( [ 'class' => $base_class ] );

$template = [
	[ 'core/columns', [ 'className' => 'hero__columns' ], [
		[ 'core/column', [ 'className' => 'hero__content', 'templateLock' => 'all', 'backgroundColor' => 'red', 'width' => '50%' ], [
			[ 'core/paragraph', [ 'className' => 'hero__eyebrow js-fadein-up', 'placeholder' => 'Eyebrow text', 'textColor' => 'dark', 'backgroundColor' => 'white' ] ],
			[ 'core/heading',   [ 'className' => 'js-fadein-up', 'level' => 1, 'placeholder' => 'Hero heading', 'textColor' => 'light' ] ],
			[ 'core/paragraph', [ 'className' => 'js-fadein-up', 'placeholder' => 'Hero description', 'textColor' => 'light' ] ],
			[ 'core/buttons',   [ 'className' => 'js-fadein-up', ], [
				[ 'core/button', [ 'placeholder' => 'Button label' ] ],
			] ],
		] ],
		[ 'core/column', [ 'className' => 'hero__media', 'templateLock' => 'all' ], [
			[ 'core/image', [ 'className' => 'js-fadein' ] ],
		] ],
	] ],
];
?>

<?php
$deco_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="145" height="141" viewBox="0 0 145 141" fill="none"><g clip-path="url(#clip0_188_1547)"><path d="M96.6666 47H48.3333V94H96.6666V47Z" fill="white" fill-opacity="0.8"/><path d="M96.6666 94H48.3333V141H96.6666V94Z" fill="white" fill-opacity="0.5"/><path d="M96.6666 0H48.3333V47H96.6666V0Z" fill="white" fill-opacity="0.5"/><path d="M48.3333 47H0V94H48.3333V47Z" fill="white" fill-opacity="0.5"/><path d="M145 47H96.6667V94H145V47Z" fill="white"/></g><defs><clipPath id="clip0_188_1547"><rect width="145" height="141" fill="white"/></clipPath></defs></svg>';
?>

<?php if ( $is_preview ) : // ---------- EDITOR ---------- ?>

	<section <?php echo $attrs; ?>>
		<InnerBlocks
			template="<?php echo esc_attr( wp_json_encode( $template ) ); ?>"
			templateLock="insert"
		/>
		<div class="hero__deco-wrap" aria-hidden="true">
			<div class="hero__deco hero__deco--1"><?php echo $deco_svg; ?></div>
			<div class="hero__deco hero__deco--2"><?php echo $deco_svg; ?></div>
			<div class="hero__deco hero__deco--3"><?php echo $deco_svg; ?></div>
		</div>
	</section>

<?php else : // ---------- FRONTEND ---------- ?>

	<section <?php echo $attrs; ?>>
		<?php echo $content; ?>
	</section>

<?php endif; ?>
