<?php
/**
 * Template Name: Fuel Prices
 *
 * Displays the full fuel pricing tables from ACF options.
 *
 * @package DFC
 */

get_header();
?>

<section class="fuel-prices-page">
    <div class="aviatrix-block aviatrix-block--page-header">
        <div class="page-header__inner">
            <h1>Fuel Price Program</h1>
        </div>
    </div>

    <div class="wrapper" style="padding-top: clamp(48px, 6vw, 80px); padding-bottom: clamp(48px, 6vw, 80px);">
        <?php echo do_shortcode( '[dfc_fuel_full]' ); ?>
    </div>
</section>

<?php get_footer(); ?>
