<?php

/**
 * Page Header Block Template.
 *
 * Full-width banner image followed by an H1.
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML (empty).
 * @param bool   $is_preview True during AJAX preview.
 * @param int    $post_id    The post ID this block is saved to.
 */

$wrapper = [ 'class' => 'aviatrix-block aviatrix-block--page-header' ];
if ( ! empty( $block['anchor'] ) ) {
	$wrapper['id'] = $block['anchor'];
}

$attrs = get_block_wrapper_attributes( $wrapper );

$lock = [ 'move' => true, 'remove' => true ];

$template = [
	[ 'core/image', [
		'className' => 'page-header__banner js-fadein',
		'sizeSlug'  => 'full',
		'lock'      => $lock,
	] ],
	[ 'core/group', [
		'className'     => 'page-header__content is-layout-constrained',
		'templateLock'  => 'all',
		'allowedBlocks' => [
			'core/paragraph',
			'core/heading',
			'core/list',
			'core/list-item',
			'core/buttons',
			'core/button',
		],
	], [
		[ 'core/heading', [
			'level'       => 1,
			'placeholder' => 'Page Title',
			'className'   => 'page-header__title js-fadein-up',
			'lock'        => $lock,
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

	<?php
	// Fallback: if the image inner block was empty (stripped by the filter),
	// use the post's featured image.
	if ( strpos( $content, '<img' ) === false && has_post_thumbnail() ) {
		$fallback_img = '<figure class="wp-block-image size-full page-header__banner js-fadein">'
			. get_the_post_thumbnail( get_the_ID(), 'full', [ 'class' => 'wp-image-' . get_post_thumbnail_id() ] )
			. '</figure>';
		$content = $fallback_img . $content;
	}

	// Fallback: if the H1 inner block was empty (stripped by the filter),
	// inject the page title into the content group.
	if ( strpos( $content, '<h1' ) === false ) {
		$fallback_h1 = '<h1 class="wp-block-heading page-header__title js-fadein-up">'
			. esc_html( get_the_title() )
			. '</h1>';
		if ( strpos( $content, 'page-header__content' ) !== false ) {
			$content = preg_replace(
				'/(<div[^>]*page-header__content[^>]*>)/s',
				'$1' . $fallback_h1,
				$content,
				1
			);
		} else {
			$content .= $fallback_h1;
		}
	}
	?>

	<section <?php echo $attrs; ?>>
		<?php echo $content; ?>
	</section>

<?php endif; ?>
