<?php
/**
 * Contact Card + Map Block Template
 *
 * Split layout: grey card with contact info on left, Google Map on right.
 * ACF fields: company_name, address_line_1, address_line_2, toll_free_phone,
 *             local_phone, fax_number, email, map_image (fallback), google_maps_embed_url
 */

$company   = get_field( 'company_name' ) ?: 'DuPage Flight Center';
$addr1     = get_field( 'address_line_1' ) ?: '';
$addr2     = get_field( 'address_line_2' ) ?: '';
$toll_free = get_field( 'toll_free_phone' ) ?: '';
$local     = get_field( 'local_phone' ) ?: '';
$fax       = get_field( 'fax_number' ) ?: '';
$email     = get_field( 'email' ) ?: '';
$map_embed = get_field( 'google_maps_embed_url' ) ?: '';
$map_img   = get_field( 'map_image' );

$base_class = 'aviatrix-block aviatrix-block--contact-card-map js-fadein-up';
$attrs = get_block_wrapper_attributes( [ 'class' => $base_class ] );

if ( ! function_exists( 'dfc_phone_link' ) ) {
    function dfc_phone_link( $phone ) {
        $digits = preg_replace( '/[^0-9+]/', '', $phone );
        return 'tel:' . $digits;
    }
}
?>

<section <?php echo $attrs; ?>>
    <div class="contact-card-map__card">
        <?php if ( $company ) : ?>
            <h3 class="contact-card-map__name"><?php echo esc_html( $company ); ?></h3>
        <?php endif; ?>

        <?php if ( $addr1 || $addr2 ) : ?>
            <address class="contact-card-map__address">
                <?php if ( $addr1 ) : ?><span><?php echo esc_html( $addr1 ); ?></span><br><?php endif; ?>
                <?php if ( $addr2 ) : ?><span><?php echo esc_html( $addr2 ); ?></span><?php endif; ?>
            </address>
        <?php endif; ?>

        <dl class="contact-card-map__details">
            <?php if ( $toll_free ) : ?>
                <div class="contact-card-map__detail-row">
                    <dt>Toll Free:</dt>
                    <dd><a href="<?php echo esc_url( dfc_phone_link( $toll_free ) ); ?>"><?php echo esc_html( $toll_free ); ?></a></dd>
                </div>
            <?php endif; ?>
            <?php if ( $local ) : ?>
                <div class="contact-card-map__detail-row">
                    <dt>Local:</dt>
                    <dd><a href="<?php echo esc_url( dfc_phone_link( $local ) ); ?>"><?php echo esc_html( $local ); ?></a></dd>
                </div>
            <?php endif; ?>
            <?php if ( $fax ) : ?>
                <div class="contact-card-map__detail-row">
                    <dt>Fax:</dt>
                    <dd><a href="<?php echo esc_url( dfc_phone_link( $fax ) ); ?>"><?php echo esc_html( $fax ); ?></a></dd>
                </div>
            <?php endif; ?>
            <?php if ( $email ) : ?>
                <div class="contact-card-map__detail-row">
                    <dt>Email:</dt>
                    <dd><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></dd>
                </div>
            <?php endif; ?>
        </dl>
    </div>

    <div class="contact-card-map__map">
        <?php if ( $map_embed ) : ?>
            <iframe
                src="<?php echo esc_url( $map_embed ); ?>"
                width="100%"
                height="100%"
                style="border:0;min-height:400px;"
                allowfullscreen=""
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                title="Map showing <?php echo esc_attr( $company ); ?> location">
            </iframe>
        <?php elseif ( $map_img ) :
            $alt = $map_img['alt'] ?: 'Map showing DuPage Flight Center location';
        ?>
            <img src="<?php echo esc_url( $map_img['sizes']['slider-large'] ?? $map_img['url'] ); ?>"
                 alt="<?php echo esc_attr( $alt ); ?>"
                 width="<?php echo esc_attr( $map_img['width'] ); ?>"
                 height="<?php echo esc_attr( $map_img['height'] ); ?>"
                 loading="lazy" />
        <?php elseif ( $is_preview ) : ?>
            <div style="background:#ddd;width:100%;height:100%;min-height:300px;display:flex;align-items:center;justify-content:center;color:#999;">
                Add Google Maps embed URL or select a map image &rarr;
            </div>
        <?php endif; ?>
    </div>
</section>
