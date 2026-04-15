<?php
/**
 * FBO Showcase Block
 *
 * Full-width dark section with a centered heading and a two-image
 * collage (large image left, smaller image right). Used for the
 * "Executive Class FBO With World Class Service" area on the homepage.
 *
 * ACF fields: background_image, image_large, image_small
 * InnerBlocks: heading (H2)
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML (InnerBlocks).
 * @param bool   $is_preview True during AJAX preview in the editor.
 */

$bg_image    = get_field( 'background_image' );
$image_large = get_field( 'image_large' );
$image_small = get_field( 'image_small' );

$base_class = 'aviatrix-block aviatrix-block--fbo-showcase js-fadein-up';
$attrs      = get_block_wrapper_attributes( [ 'class' => $base_class ] );

$bg_style = '';
if ( $bg_image ) {
    $bg_style = 'background-image: url(' . esc_url( $bg_image['url'] ) . ');';
}

$template = [
    [ 'core/heading', [
        'level'       => 2,
        'placeholder' => 'Executive Class FBO With World Class Service',
        'textColor'   => 'white',
        'align'       => 'center',
    ] ],
];
?>

<?php if ( $is_preview ) : ?>
    <section <?php echo $attrs; ?> style="<?php echo esc_attr( $bg_style ); ?>">
        <div class="fbo-showcase__overlay" aria-hidden="true"></div>
        <div class="fbo-showcase__inner wrapper">
            <div class="fbo-showcase__heading">
                <InnerBlocks
                    template="<?php echo esc_attr( wp_json_encode( $template ) ); ?>"
                    templateLock="all"
                />
            </div>
            <div class="fbo-showcase__images">
                <div class="fbo-showcase__image-large">
                    <?php if ( $image_large ) : ?>
                        <img src="<?php echo esc_url( $image_large['sizes']['slider-large'] ?? $image_large['url'] ); ?>"
                             alt="<?php echo esc_attr( $image_large['alt'] ); ?>"
                             loading="lazy" />
                    <?php else : ?>
                        <div class="fbo-showcase__placeholder">Select large image &rarr;</div>
                    <?php endif; ?>
                </div>
                <div class="fbo-showcase__image-small">
                    <?php if ( $image_small ) : ?>
                        <img src="<?php echo esc_url( $image_small['sizes']['medium_large'] ?? $image_small['url'] ); ?>"
                             alt="<?php echo esc_attr( $image_small['alt'] ); ?>"
                             loading="lazy" />
                    <?php else : ?>
                        <div class="fbo-showcase__placeholder">Select small image &rarr;</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

<?php else : ?>
    <section <?php echo $attrs; ?> style="<?php echo esc_attr( $bg_style ); ?>"
        aria-label="<?php esc_attr_e( 'FBO facility showcase', 'dfc' ); ?>">
        <div class="fbo-showcase__overlay" aria-hidden="true"></div>
        <div class="fbo-showcase__inner wrapper">
            <div class="fbo-showcase__heading">
                <?php echo $content; ?>
            </div>
            <div class="fbo-showcase__images">
                <?php if ( $image_large ) :
                    $alt_large = $image_large['alt'] ?: ( $image_large['title'] ?: '' );
                ?>
                    <div class="fbo-showcase__image-large js-fadein-up">
                        <img src="<?php echo esc_url( $image_large['sizes']['slider-large'] ?? $image_large['url'] ); ?>"
                             alt="<?php echo esc_attr( $alt_large ); ?>"
                             width="784" height="416"
                             loading="lazy" />
                    </div>
                <?php endif; ?>

                <?php if ( $image_small ) :
                    $alt_small = $image_small['alt'] ?: ( $image_small['title'] ?: '' );
                ?>
                    <div class="fbo-showcase__image-small js-fadein-up">
                        <img src="<?php echo esc_url( $image_small['sizes']['medium_large'] ?? $image_small['url'] ); ?>"
                             alt="<?php echo esc_attr( $alt_small ); ?>"
                             width="385" height="414"
                             loading="lazy" />
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
