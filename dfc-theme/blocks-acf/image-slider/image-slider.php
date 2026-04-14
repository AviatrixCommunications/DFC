<?php
/**
 * Image Slider Block
 *
 * Carousel with one large image and thumbnail row. Arrow navigation.
 * ACF fields: slider_images (gallery field)
 */

$images = get_field('slider_images');
if (!$images) $images = [];

$base_class = 'aviatrix-block aviatrix-block--image-slider js-image-slider';
$attrs = get_block_wrapper_attributes(['class' => $base_class]);
?>

<div <?php echo $attrs; ?> aria-label="Image gallery" aria-roledescription="carousel">
    <?php if ($images) : ?>
        <div class="image-slider__main">
            <img src="<?php echo esc_url($images[0]['sizes']['slider-large'] ?? $images[0]['url']); ?>"
                 alt="<?php echo esc_attr($images[0]['alt'] ?: ( $images[0]['title'] ?: 'Gallery image 1' )); ?>"
                 class="image-slider__active-img"
                 loading="lazy" />
        </div>

        <?php if (count($images) > 1) : ?>
            <button class="image-slider__nav image-slider__nav--prev button--arrow" aria-label="Previous image">
                <svg aria-hidden="true" width="20" height="15" viewBox="0 0 20 15" fill="none"><path d="M20 7.5H2M2 7.5L8.5 1M2 7.5L8.5 14" stroke="currentColor" stroke-width="2"/></svg>
            </button>
            <button class="image-slider__nav image-slider__nav--next button--arrow" aria-label="Next image">
                <svg aria-hidden="true" width="20" height="15" viewBox="0 0 20 15" fill="none"><path d="M0 7.5H18M18 7.5L11.5 1M18 7.5L11.5 14" stroke="currentColor" stroke-width="2"/></svg>
            </button>

            <div class="image-slider__thumbs" role="tablist" aria-label="Image thumbnails">
                <?php foreach ($images as $i => $img) : ?>
                    <button class="image-slider__thumb<?php echo $i === 0 ? ' is-active' : ''; ?>"
                            role="tab"
                            aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
                            aria-label="Show image <?php echo $i + 1; ?>"
                            data-full-src="<?php echo esc_url($img['sizes']['slider-large'] ?? $img['url']); ?>"
                            data-full-alt="<?php echo esc_attr($img['alt'] ?: ( $img['title'] ?: 'Gallery image ' . ( $i + 1 ) )); ?>">
                        <img src="<?php echo esc_url($img['sizes']['slider-thumb'] ?? $img['url']); ?>"
                             alt="" aria-hidden="true" loading="lazy" />
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php elseif ($is_preview) : ?>
        <p style="color:#999;text-align:center;">Select images in the block settings &rarr;</p>
    <?php endif; ?>
</div>
