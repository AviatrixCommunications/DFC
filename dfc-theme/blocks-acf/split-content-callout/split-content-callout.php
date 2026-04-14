<?php
/**
 * Split Content Callout Block
 *
 * 50/50 layout: large image on one side, centered content with optional
 * icon, heading, body text, and CTA button on the other.
 * Used for Customs, Airfield Information sections on homepage.
 *
 * @param array  $block
 * @param string $content
 * @param bool   $is_preview
 */

$image    = get_field( 'callout_image' );
$icon     = get_field( 'callout_icon' );
$layout   = get_field( 'image_position' ) ?: 'left'; // left or right

$base_class = 'aviatrix-block aviatrix-block--split-callout';
$base_class .= ' split-callout--image-' . $layout;
$attrs = get_block_wrapper_attributes( [ 'class' => $base_class ] );

$template = [
    [ 'core/heading', [ 'level' => 2, 'placeholder' => 'Section heading', 'align' => 'center' ] ],
    [ 'core/paragraph', [ 'placeholder' => 'Section content...', ] ],
    [ 'core/buttons', [ 'layout' => ['type' => 'flex', 'justifyContent' => 'center'] ], [
        [ 'core/button', [ 'placeholder' => 'Button text' ] ],
    ] ],
];
?>

<?php if ( $is_preview ) : ?>
    <section <?php echo $attrs; ?>>
        <div class="split-callout__image">
            <?php if ( $image ) : ?>
                <img src="<?php echo esc_url( $image['sizes']['slider-large'] ?? $image['url'] ); ?>"
                     alt="<?php echo esc_attr( $image['alt'] ); ?>" loading="lazy" />
            <?php else : ?>
                <div style="background:#ddd;width:100%;height:100%;min-height:300px;display:flex;align-items:center;justify-content:center;color:#999;">Select image &rarr;</div>
            <?php endif; ?>
        </div>
        <div class="split-callout__content">
            <?php if ( $icon ) : ?>
                <div class="split-callout__icon">
                    <img src="<?php echo esc_url( $icon['url'] ); ?>" alt="" aria-hidden="true" width="119" height="119" />
                </div>
            <?php endif; ?>
            <InnerBlocks template="<?php echo esc_attr( wp_json_encode( $template ) ); ?>" />
        </div>
    </section>
<?php else : ?>
    <section <?php echo $attrs; ?>>
        <div class="split-callout__image js-fadein-up">
            <?php if ( $image ) :
                $alt = $image['alt'] ?: ( $image['title'] ?: '' );
            ?>
                <img src="<?php echo esc_url( $image['sizes']['slider-large'] ?? $image['url'] ); ?>"
                     alt="<?php echo esc_attr( $alt ); ?>" loading="lazy" />
            <?php endif; ?>
        </div>
        <div class="split-callout__content js-fadein-up">
            <?php if ( $icon ) : ?>
                <div class="split-callout__icon">
                    <img src="<?php echo esc_url( $icon['url'] ); ?>" alt="" aria-hidden="true" width="119" height="119" />
                </div>
            <?php endif; ?>
            <?php echo $content; ?>
        </div>
    </section>
<?php endif; ?>
