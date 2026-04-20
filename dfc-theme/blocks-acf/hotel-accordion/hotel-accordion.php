<?php
/**
 * Hotel Accordion Block
 *
 * Grey background section with intro text and accordion items for hotel listings.
 * Uses InnerBlocks (jsx mode) for heading, description, and accordion blocks.
 *
 * Editor: <InnerBlocks /> renders the React component.
 * Frontend: $content is the saved HTML, wrapped in a structural container.
 */

$base_class = 'aviatrix-block aviatrix-block--hotel-accordion has-background';
$attrs = get_block_wrapper_attributes(['class' => $base_class]);

$template = [
    ['core/heading', ['level' => 2, 'placeholder' => 'Hotels', 'align' => 'center']],
    ['core/paragraph', ['placeholder' => 'Description of hotel options...', 'align' => 'center']],
    ['acf/accordion', [], [
        ['acf/accordion-item', []],
    ]],
];

$allowed = ['core/heading', 'core/paragraph', 'acf/accordion', 'acf/accordion-item'];
?>

<section <?php echo $attrs; ?>>
    <div class="hotel-accordion__inner wrapper">
        <?php if ($is_preview) : ?>
            <InnerBlocks template="<?php echo esc_attr(wp_json_encode($template)); ?>"
                         allowedBlocks="<?php echo esc_attr(wp_json_encode($allowed)); ?>" />
        <?php else : ?>
            <?php echo $content; ?>
        <?php endif; ?>
    </div>
</section>
