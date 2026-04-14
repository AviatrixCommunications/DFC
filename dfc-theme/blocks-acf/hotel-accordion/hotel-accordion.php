<?php
/**
 * Hotel Accordion Block
 *
 * Grey background section with intro text and accordion items for hotel listings.
 * Uses InnerBlocks to contain accordion blocks.
 */

$base_class = 'aviatrix-block aviatrix-block--hotel-accordion';
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

<?php if ($is_preview) : ?>
    <section <?php echo $attrs; ?>>
        <div class="hotel-accordion__intro">
            <InnerBlocks template="<?php echo esc_attr(wp_json_encode($template)); ?>"
                         allowedBlocks="<?php echo esc_attr(wp_json_encode($allowed)); ?>" />
        </div>
    </section>
<?php else : ?>
    <section <?php echo $attrs; ?>>
        <?php echo $content; ?>
    </section>
<?php endif; ?>
