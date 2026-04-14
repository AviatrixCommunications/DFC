<?php

/**
 * Accordion Block Template.
 *
 * Container for acf/accordion-item child blocks. Each item wraps a
 * core/details block. Authors can freely add, remove, and reorder items.
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML.
 * @param bool   $is_preview True during AJAX preview.
 * @param int    $post_id    The post ID this block is saved to.
 */

$wrapper = [ 'class' => 'aviatrix-block aviatrix-block--accordion js-fadein-up is-layout-constrained' ];
if ( ! empty( $block['anchor'] ) ) {
	$wrapper['id'] = $block['anchor'];
}

$attrs = get_block_wrapper_attributes( $wrapper );

$allowed = [ 'acf/accordion-item' ];

$template = [
	[ 'acf/accordion-item', [] ],
	[ 'acf/accordion-item', [] ],
	[ 'acf/accordion-item', [] ],
];
?>

<?php if ( $is_preview ) : // ---------- EDITOR ---------- ?>

	<section <?php echo $attrs; ?>>
		<InnerBlocks
			allowedBlocks="<?php echo esc_attr( wp_json_encode( $allowed ) ); ?>"
			template="<?php echo esc_attr( wp_json_encode( $template ) ); ?>"
		/>
	</section>

<?php else : // ---------- FRONTEND ---------- ?>

	<section <?php echo $attrs; ?>>
		<div class="accordion__items has-global-padding">
			<?php echo $content; ?>
		</div>
	</section>

<?php endif; ?>
