<?php
/**
 * DFC Search — WP Engine Smart Search AJAX Integration
 *
 * Provides a localized REST URL for the frontend JS search module.
 * The actual AJAX search logic is in js/modules/search.js and calls
 * the standard WP REST API (which WP Engine Smart Search hooks into).
 *
 * @package DFC
 */

// ── Localize REST URL for search JS ───────────────────────────
add_action( 'wp_enqueue_scripts', 'dfc_search_localize' );
function dfc_search_localize() {
    wp_localize_script( 'dfc-script', 'dfcSearch', [
        'restUrl'    => esc_url_raw( rest_url( 'wp/v2/' ) ),
        'searchUrl'  => esc_url( home_url( '/' ) ),
        'nonce'      => wp_create_nonce( 'wp_rest' ),
    ] );
}
