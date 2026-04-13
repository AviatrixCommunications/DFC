<?php

/**
 * Tabs Block Template.
 *
 * Container for acf/tab child blocks. Each tab block shows a styled label
 * heading (the tab button text) with content below it in the editor.
 *
 * On the frontend, tabs.js reads .tabs__panel-label from each .tabs__panel,
 * builds the ARIA tablist, and wires up WCAG 2.1 AA keyboard navigation.
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML (rendered acf/tab children).
 * @param bool   $is_preview True during AJAX preview.
 * @param int    $post_id    The post ID this block is saved to.
 */

$wrapper = [ 'class' => 'aviatrix-block aviatrix-block--tabs is-layout-constrained' ];
if ( ! empty( $block['anchor'] ) ) {
	$wrapper['id'] = $block['anchor'];
}

$attrs = get_block_wrapper_attributes( $wrapper );

$allowed = [ 'acf/tab' ];

$template = [
	[ 'acf/tab', [] ],
	[ 'acf/tab', [] ],
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
		<div class="tabs__list has-global-padding js-fadein-up" role="tablist"></div>
		<div class="tabs__panels has-global-padding js-fadein-up">
			<?php echo $content; ?>
		</div>
	</section>

<?php endif; ?>
