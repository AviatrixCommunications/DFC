<?php

/**
 * Tab Block Template.
 *
 * A single tab panel within an acf/tabs block. The first heading with class
 * tabs__panel-label provides the tab button label. tabs.js reads this heading
 * to build the tab button and hides it from the panel to avoid SR duplication.
 *
 * Only allowed inside acf/tabs (enforced via "parent" in block.json).
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML.
 * @param bool   $is_preview True during AJAX preview.
 * @param int    $post_id    The post ID this block is saved to.
 */

$template = [
	[ 'core/heading', [
		'level'       => 3,
		'placeholder' => 'Tab label',
		'className'   => 'tabs__panel-label',
		'lock'        => [ 'move' => true, 'remove' => true ],
	] ],
	[ 'core/paragraph', [ 'placeholder' => 'Add tab content here…' ] ],
];
?>

<?php if ( $is_preview ) : // ---------- EDITOR ---------- ?>

	<div class="tabs__panel">
		<InnerBlocks
			template="<?php echo esc_attr( wp_json_encode( $template ) ); ?>"
		/>
	</div>

<?php else : // ---------- FRONTEND ---------- ?>

	<div class="tabs__panel">
		<?php echo $content; ?>
	</div>

<?php endif; ?>
