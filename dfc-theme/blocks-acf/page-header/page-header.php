<?php
/**
 * Page Header Block Template
 *
 * Per Figma: Featured image banner first, then black title bar below it.
 * H1 auto-pulled from WordPress page title.
 * Everything is inside the block wrapper element so nothing gets stripped.
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML (InnerBlocks).
 * @param bool   $is_preview True during AJAX preview.
 */

$base_class = 'aviatrix-block aviatrix-block--page-header';

// Add modifier when featured image is present
$featured_img_id  = get_post_thumbnail_id();
$featured_img_url = $featured_img_id ? get_the_post_thumbnail_url( null, 'hero' ) : '';
$featured_img_alt = $featured_img_id ? ( get_post_meta( $featured_img_id, '_wp_attachment_image_alt', true ) ?: get_the_title() ) : '';

if ( $featured_img_url ) {
    $base_class .= ' has-hero-image';
}

$attrs = get_block_wrapper_attributes( [ 'class' => $base_class ] );

$page_title = get_the_title();

$template = [
    [ 'core/paragraph', [
        'placeholder' => 'Optional page description...',
        'textColor'   => 'white',
        'className'   => 'page-header__desc',
    ] ],
];

$allowed_blocks = [
    'core/paragraph',
];
?>

<?php if ( $is_preview ) : ?>
    <div <?php echo $attrs; ?>>
        <?php if ( $featured_img_url ) : ?>
            <div class="page-header__hero">
                <img src="<?php echo esc_url( $featured_img_url ); ?>"
                     alt="<?php echo esc_attr( $featured_img_alt ); ?>"
                     style="width:100%;height:180px;object-fit:cover;" />
                <p style="color:#999;font-size:11px;text-align:center;margin:4px 0 0;">Featured image — set in the Page sidebar</p>
            </div>
        <?php endif; ?>
        <div class="page-header__bar">
            <div class="page-header__inner">
                <h1 class="page-header__title" style="color:#fff;margin:0;">
                    <?php echo esc_html( $page_title ?: 'Page Title' ); ?>
                </h1>
                <InnerBlocks
                    template="<?php echo esc_attr( wp_json_encode( $template ) ); ?>"
                    allowedBlocks="<?php echo esc_attr( wp_json_encode( $allowed_blocks ) ); ?>"
                />
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
                <?php
                $desc_content = preg_replace( '/<h1[^>]*>.*?<\/h1>/is', '', $content );
                $desc_content = trim( $desc_content );
                if ( $desc_content ) {
                    echo $desc_content;
                }
                ?>
            </div>
        </div>
    </div>
<?php endif; ?>
