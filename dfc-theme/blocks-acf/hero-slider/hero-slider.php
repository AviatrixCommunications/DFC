<?php
/**
 * Hero Slider Block Template
 *
 * Full-width hero that supports:
 *  - Single image (default, as shown in Figma)
 *  - Multiple slides (slider mode)
 *  - Video background (MP4 or YouTube poster)
 *
 * ACF fields: hero_slides (repeater) with slide_type, image, video_url, video_poster, overlay_opacity
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML.
 * @param bool   $is_preview True during AJAX preview.
 */

$slides = get_field( 'hero_slides' );
if ( ! $slides ) $slides = [];

$base_class = 'aviatrix-block aviatrix-block--hero-slider';
$is_slider  = count( $slides ) > 1;
if ( $is_slider ) $base_class .= ' hero-slider--multi';

$attrs = get_block_wrapper_attributes( [ 'class' => $base_class ] );
?>

<section <?php echo $attrs; ?> aria-label="Hero">
    <?php if ( $is_slider ) : ?>
        <div class="hero-slider__track js-hero-slider" role="region" aria-roledescription="carousel" aria-label="Featured content">
    <?php endif; ?>

    <?php if ( empty( $slides ) && $is_preview ) : ?>
        <!-- Editor placeholder -->
        <div class="hero-slider__slide hero-slider__slide--placeholder">
            <div class="hero-slider__media">
                <div style="background:#333;width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#999;font-size:1.25rem;">
                    Add slides in the block settings panel &rarr;
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach ( $slides as $i => $slide ) :
        $type    = $slide['slide_type'] ?? 'image';
        $image   = $slide['image'] ?? null;
        $video   = $slide['video_url'] ?? '';
        $poster  = $slide['video_poster'] ?? null;
        $overlay = (int) ( $slide['overlay_opacity'] ?? 0 );
    ?>
        <div class="hero-slider__slide<?php echo $i === 0 ? ' is-active' : ''; ?>"
             <?php if ( $is_slider ) : ?>role="group" aria-roledescription="slide" aria-label="Slide <?php echo $i + 1; ?> of <?php echo count( $slides ); ?>"<?php endif; ?>>

            <div class="hero-slider__media">
                <?php if ( $type === 'video' && $video ) : ?>
                    <video class="hero-slider__video"
                           autoplay muted loop playsinline
                           aria-label="<?php echo esc_attr( $slide['video_description'] ?? 'Background video' ); ?>"
                           <?php if ( $poster ) : ?>poster="<?php echo esc_url( $poster['url'] ); ?>"<?php endif; ?>>
                        <source src="<?php echo esc_url( $video ); ?>" type="video/mp4" />
                    </video>
                    <?php if ( ! empty( $slide['video_description'] ) ) : ?>
                        <p class="u-sr-only"><?php echo esc_html( $slide['video_description'] ); ?></p>
                    <?php endif; ?>
                <?php elseif ( $image ) :
                    $alt = $image['alt'] ?: ( $image['title'] ?: 'DuPage Flight Center' );
                ?>
                    <img class="hero-slider__img"
                         src="<?php echo esc_url( $image['sizes']['hero'] ?? $image['url'] ); ?>"
                         alt="<?php echo esc_attr( $alt ); ?>"
                         width="<?php echo esc_attr( $image['width'] ); ?>"
                         height="<?php echo esc_attr( $image['height'] ); ?>"
                         loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>"
                         fetchpriority="<?php echo $i === 0 ? 'high' : 'auto'; ?>" />
                <?php endif; ?>

                <?php if ( $overlay > 0 ) : ?>
                    <div class="hero-slider__overlay" style="opacity: <?php echo $overlay / 100; ?>" aria-hidden="true"></div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if ( $is_slider ) : ?>
        <div class="hero-slider__controls">
            <button class="hero-slider__prev button--arrow" aria-label="Previous slide">
                <svg aria-hidden="true" width="20" height="15" viewBox="0 0 20 15" fill="none"><path d="M20 7.5H2M2 7.5L8.5 1M2 7.5L8.5 14" stroke="currentColor" stroke-width="2"/></svg>
            </button>
            <button class="hero-slider__next button--arrow" aria-label="Next slide">
                <svg aria-hidden="true" width="20" height="15" viewBox="0 0 20 15" fill="none"><path d="M0 7.5H18M18 7.5L11.5 1M18 7.5L11.5 14" stroke="currentColor" stroke-width="2"/></svg>
            </button>
            <button class="hero-slider__pause" aria-label="Pause slideshow">
                <svg class="hero-slider__icon-pause" aria-hidden="true" width="16" height="16" viewBox="0 0 16 16"><rect x="3" y="2" width="4" height="12" fill="currentColor"/><rect x="9" y="2" width="4" height="12" fill="currentColor"/></svg>
                <svg class="hero-slider__icon-play" aria-hidden="true" width="16" height="16" viewBox="0 0 16 16" style="display:none"><polygon points="3,2 14,8 3,14" fill="currentColor"/></svg>
            </button>
        </div>
        </div>
    <?php endif; ?>
</section>
