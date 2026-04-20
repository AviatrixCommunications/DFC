<?php
/**
 * Page Header Block Template
 *
 * Fully automatic — no editable content inside the block itself.
 * - H1 pulled from the WordPress page title
 * - Description pulled from the WordPress excerpt field
 * - Hero image pulled from the Featured Image
 *
 * Editors control everything through standard WordPress fields
 * in the Page sidebar — nothing to configure in the block.
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML (unused).
 * @param bool   $is_preview True during AJAX preview.
 */

$base_class = 'aviatrix-block aviatrix-block--page-header';

// Featured image
$featured_img_id  = get_post_thumbnail_id();
$featured_img_url = $featured_img_id ? get_the_post_thumbnail_url( null, 'hero' ) : '';
$featured_img_alt = $featured_img_id ? ( get_post_meta( $featured_img_id, '_wp_attachment_image_alt', true ) ?: get_the_title() ) : '';

if ( $featured_img_url ) {
    $base_class .= ' has-hero-image';
}

$attrs = get_block_wrapper_attributes( [ 'class' => $base_class ] );

// Title from page title
$page_title = get_the_title();

// Description from the WordPress excerpt field
$description = has_excerpt() ? get_the_excerpt() : '';
?>

<?php if ( $is_preview ) : ?>
    <div <?php echo $attrs; ?>>
        <?php if ( $featured_img_url ) : ?>
            <div class="page-header__hero">
                <img src="<?php echo esc_url( $featured_img_url ); ?>"
                     alt="<?php echo esc_attr( $featured_img_alt ); ?>"
                     style="width:100%;height:160px;object-fit:cover;" />
            </div>
        <?php else : ?>
            <div style="background:#444;padding:20px 24px;text-align:center;">
                <p style="color:#999;font-size:12px;margin:0;">Set a <strong>Featured Image</strong> in the Page sidebar to add the hero banner.</p>
            </div>
        <?php endif; ?>
        <div class="page-header__bar" style="padding:24px 32px;">
            <div class="page-header__inner" style="max-width:900px;margin:0 auto;text-align:center;">
                <p style="color:#fff;margin:0;font-size:22px;font-weight:600;line-height:1.3;">
                    <?php echo esc_html( $page_title ?: '(Page title)' ); ?>
                </p>
                <?php if ( $description ) : ?>
                    <p style="color:rgba(255,255,255,0.7);font-size:13px;margin:8px 0 0;line-height:1.5;">
                        <?php echo esc_html( $description ); ?>
                    </p>
                <?php else : ?>
                    <p style="color:rgba(255,255,255,0.35);font-size:12px;margin:6px 0 0;">
                        Add an <strong style="color:rgba(255,255,255,0.5);">Excerpt</strong> in the Page sidebar to add a description here.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php else : ?>
    <div <?php echo $attrs; ?>>
        <?php if ( $featured_img_url ) : ?>
            <div class="page-header__hero">
                <img src="<?php echo esc_url( $featured_img_url ); ?>"
                     alt="<?php echo esc_attr( $featured_img_alt ); ?>"
                     width="1920" height="400"
                     loading="eager"
                     fetchpriority="high" />
            </div>
        <?php endif; ?>
        <div class="page-header__bar">
            <div class="page-header__inner">
                <h1 class="page-header__title"><?php echo esc_html( $page_title ); ?></h1>
                <?php if ( $description ) : ?>
                    <p class="page-header__desc"><?php echo esc_html( $description ); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
