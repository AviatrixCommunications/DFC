<?php
/**
 * Full-Width CTA Block
 *
 * Background image with dark overlay and centered white text.
 * Used for "Executive FBO", "At Your Service 24/7", "Near Chicago", "Chicagoland ranking" sections.
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML.
 * @param bool   $is_preview True during AJAX preview.
 */

$bg_image = get_field( 'background_image' );
$overlay  = get_field( 'overlay_opacity' ) ?: 75;

$base_class = 'aviatrix-block aviatrix-block--full-width-cta js-fadein-up';
$attrs = get_block_wrapper_attributes( [ 'class' => $base_class ] );

$style = '';
if ( $bg_image ) {
    $style = 'background-image: url(' . esc_url( $bg_image['url'] ) . ');';
}

$template = [
    [ 'core/heading', [
        'level'       => 2,
        'placeholder' => 'Section heading',
        'textColor'   => 'white',
        'align'       => 'center',
    ] ],
    [ 'core/paragraph', [
        'placeholder' => 'Section description...',
        'textColor'   => 'white',
        'align'       => 'center',
    ] ],
    [ 'core/buttons', [ 'layout' => ['type' => 'flex', 'justifyContent' => 'center'] ], [
        [ 'core/button', [ 'placeholder' => 'Button text' ] ],
    ] ],
];
?>

<?php if ( $is_preview ) : ?>
    <section <?php echo $attrs; ?> style="<?php echo esc_attr( $style ); ?>">
        <div class="full-width-cta__overlay" style="opacity: <?php echo $overlay / 100; ?>" aria-hidden="true"></div>
        <div class="full-width-cta__content wrapper">
            <InnerBlocks
                template="<?php echo esc_attr( wp_json_encode( $template ) ); ?>"
            />
        </div>
    </section>
<?php else : ?>
    <section <?php echo $attrs; ?> style="<?php echo esc_attr( $style ); ?>">
        <?php if ( $bg_image ) : ?>
            <div class="full-width-cta__overlay" style="opacity: <?php echo $overlay / 100; ?>" aria-hidden="true"></div>
        <?php endif; ?>
        <div class="full-width-cta__content wrapper">
            <?php echo $content; ?>
        </div>
    </section>
<?php endif; ?>
