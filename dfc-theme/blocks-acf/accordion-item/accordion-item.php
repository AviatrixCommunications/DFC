<?php

/**
 * Accordion Item Block Template.
 *
 * A single expandable item within an acf/accordion block.
 * Wraps a core/details block so authors can edit the summary and content.
 *
 * Only allowed inside acf/accordion (enforced via "parent" in block.json).
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML.
 * @param bool   $is_preview True during AJAX preview.
 * @param int    $post_id    The post ID this block is saved to.
 */

$template = [
	[ 'core/details', [
		'summary' => 'Question or topic title',
	], [
		[ 'core/paragraph', [
			'placeholder' => 'Add answer or content…',
		] ],
	] ],
];
?>

<?php if ( $is_preview ) : // ---------- EDITOR ---------- ?>

	<div class="accordion__item">
		<InnerBlocks
			template="<?php echo esc_attr( wp_json_encode( $template ) ); ?>"
		/>
	</div>

<?php else : // ---------- FRONTEND ---------- ?>

	<?php echo $content; ?>

<?php endif; ?>
