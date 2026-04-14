<?php
/**
 * Newsletter Signup Block
 *
 * Can be placed as a block anywhere (the footer version is hardcoded in footer.php).
 * ACF fields: gravity_form_id (number)
 */

$form_id = get_field('gravity_form_id') ?: '';
$base_class = 'aviatrix-block aviatrix-block--newsletter newsletter';
$attrs = get_block_wrapper_attributes(['class' => $base_class]);
?>

<section <?php echo $attrs; ?> aria-label="Newsletter signup">
    <div class="wrapper">
        <div class="newsletter__inner">
            <div class="newsletter__heading">
                <h2 class="newsletter__title">Subscribe to Our Weekly Fuel Price Updates</h2>
            </div>
            <div class="newsletter__form">
                <?php if ($form_id && function_exists('gravity_form')) :
                    gravity_form($form_id, false, false, false, null, true, 0);
                else : ?>
                    <p style="color:#000;">Configure Gravity Form ID in block settings.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
