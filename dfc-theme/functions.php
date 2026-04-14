<?php
/**
 * DuPage Flight Center Theme Functions
 *
 * @package DFC
 */

// ── Function includes ─────────────────────────────────────────
require_once get_template_directory() . '/functions/blocks.php';
require_once get_template_directory() . '/functions/weather-api.php';
require_once get_template_directory() . '/functions/fuel-prices.php';
require_once get_template_directory() . '/functions/search-ajax.php';
require_once get_template_directory() . '/functions/seo.php';
require_once get_template_directory() . '/functions/admin-cleanup.php';
require_once get_template_directory() . '/functions/custom-roles.php';

// ── SVG helper ────────────────────────────────────────────────
function get_theme_svg( string $name, string $class = '', string $title = '' ): string {
    $path = get_template_directory() . '/img/' . $name . '.svg';
    if ( ! file_exists( $path ) ) {
        return '';
    }
    $svg = file_get_contents( $path );

    $extra_attrs = ' focusable="false"';
    if ( $class ) {
        $extra_attrs .= ' class="' . esc_attr( $class ) . '"';
    }
    if ( $title ) {
        $title_id     = 'svg-' . sanitize_html_class( $name ) . '-title';
        $extra_attrs .= ' role="img" aria-labelledby="' . esc_attr( $title_id ) . '"';
        $title_tag    = '<title id="' . esc_attr( $title_id ) . '">' . esc_html( $title ) . '</title>';
        $svg = preg_replace( '/(<svg\b[^>]*>)/i', '$1' . $title_tag, $svg, 1 );
    } else {
        $extra_attrs .= ' aria-hidden="true"';
    }

    $svg = preg_replace( '/<svg\b/', '<svg' . $extra_attrs, $svg, 1 );
    return $svg;
}

// ── Component helper ──────────────────────────────────────────
function get_component( $slug, array $args = array(), $output = true ) {
    if ( ! $output ) ob_start();
    $template_file = locate_template( "components/{$slug}.php", false, false );
    $template_file = strlen( $template_file ) > 0
        ? $template_file
        : locate_template( "components/{$slug}/index.php", false, false );
    if ( file_exists( $template_file ) ) :
        require( $template_file );
    else :
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( "DFC: Could not find component '{$slug}'" );
        }
    endif;
    if ( ! $output ) return ob_get_clean();
}

// ── Remove block templates (we use classic PHP templates) ─────
remove_theme_support( 'block-templates' );

// ── Scripts and Styles ────────────────────────────────────────
function dfc_get_manifest() {
    static $manifest = null;
    if ( $manifest !== null ) return $manifest;

    $dist_path = get_template_directory() . '/dist/manifest.json';
    if ( ! file_exists( $dist_path ) ) {
        $manifest = false;
        return $manifest;
    }

    $manifest = json_decode( file_get_contents( $dist_path ), true );
    return $manifest;
}

function dfc_enqueue_assets() {
    $manifest = dfc_get_manifest();

    if ( ! $manifest ) {
        wp_enqueue_style( 'dfc-style', get_template_directory_uri() . '/dist/css/style.css', [], null );
        wp_enqueue_script( 'dfc-script', get_template_directory_uri() . '/dist/js/global.js', [], null, true );
        return;
    }

    if ( isset( $manifest['style.css'] ) ) {
        wp_enqueue_style( 'dfc-style', get_template_directory_uri() . '/dist/' . $manifest['style.css'], [], null );
    }

    if ( isset( $manifest['global.js'] ) ) {
        wp_enqueue_script( 'dfc-script', get_template_directory_uri() . '/dist/' . $manifest['global.js'], [], null, true );

        // Pass search config + fuel quick-look data to JS
        $fuel_data = [];
        $jet_tiers = get_field( 'jet_fuel_tiers', 'option' );
        $avgas_tiers = get_field( 'avgas_tiers', 'option' );
        if ( $jet_tiers ) {
            foreach ( $jet_tiers as $tier ) {
                if ( strtolower( $tier['tier_label'] ?? '' ) === 'retail price' && ! empty( $tier['rows'][0] ) ) {
                    $fuel_data['jet_retail'] = $tier['rows'][0]['aftertax_price'] ?? '';
                    break;
                }
            }
        }
        if ( $avgas_tiers ) {
            foreach ( $avgas_tiers as $tier ) {
                if ( strtolower( $tier['tier_label'] ?? '' ) === 'retail price' && ! empty( $tier['rows'][0] ) ) {
                    $fuel_data['avgas_retail'] = $tier['rows'][0]['aftertax_price'] ?? '';
                    break;
                }
            }
        }
        $fuel_data['effective_date'] = get_field( 'fuel_effective_date', 'option' ) ?: '';
        $fuel_page = get_page_by_path( 'fuel-prices' );
        $fuel_data['fuel_url'] = $fuel_page ? get_permalink( $fuel_page ) : '/fuel-prices/';

        wp_localize_script( 'dfc-script', 'dfcSearch', [
            'restUrl'  => esc_url_raw( rest_url( 'wp/v2/' ) ),
            'fuelData' => $fuel_data,
        ] );
    }
}
add_action( 'wp_enqueue_scripts', 'dfc_enqueue_assets' );

// ── Additional CSS ────────────────────────────────────────────
function dfc_enqueue_extra_css() {
    // Search CSS — styles the AJAX dropdown in the header (all pages) AND the search results page
    wp_enqueue_style(
        'dfc-search-results',
        get_template_directory_uri() . '/css/search-results.css',
        [ 'dfc-style' ],
        filemtime( get_template_directory() . '/css/search-results.css' )
    );
    // Global fixes — loaded everywhere (should be merged into SASS build long-term)
    wp_enqueue_style(
        'dfc-fixes',
        get_template_directory_uri() . '/css/dfc-fixes.css',
        [ 'dfc-style' ],
        filemtime( get_template_directory() . '/css/dfc-fixes.css' )
    );
}
add_action( 'wp_enqueue_scripts', 'dfc_enqueue_extra_css' );

// ── Localize scripts ──────────────────────────────────────────
function dfc_localize_scripts() {
    wp_localize_script( 'dfc-script', 'DFC', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'resturl' => get_rest_url(),
        'themeurl' => get_template_directory_uri(),
    ) );
}
add_action( 'wp_enqueue_scripts', 'dfc_localize_scripts' );

// ── Editor assets ─────────────────────────────────────────────
function dfc_enqueue_block_editor_assets() {
    $manifest = dfc_get_manifest();
    if ( ! $manifest ) return;

    if ( isset( $manifest['editor-style.css'] ) ) {
        wp_enqueue_style(
            'dfc-editor-style',
            get_template_directory_uri() . '/dist/' . $manifest['editor-style.css'],
            [],
            null
        );
    }

    wp_enqueue_style(
        'dfc-editor-overrides',
        get_template_directory_uri() . '/css/editor-overrides.css',
        [ 'dfc-editor-style' ],
        filemtime( get_template_directory() . '/css/editor-overrides.css' )
    );
}
add_action( 'enqueue_block_editor_assets', 'dfc_enqueue_block_editor_assets' );

// ── Comments: fully handled by functions/admin-cleanup.php ────

// ── Disable emojicons ─────────────────────────────────────────
add_action( 'init', 'dfc_disable_emojicons' );
function dfc_disable_emojicons() {
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
}

// ── Remove Customizer CSS ─────────────────────────────────────
function dfc_customizer_reg( $wp_customize ) {
    $wp_customize->remove_section( 'custom_css' );
}
add_action( 'customize_register', 'dfc_customizer_reg' );

// ── SVG upload support (sanitized) ───────────────────────────
add_filter( 'upload_mimes', 'dfc_add_svg_mime' );
add_filter( 'wp_check_filetype_and_ext', 'dfc_allow_svg_uploads', 10, 4 );
add_filter( 'wp_handle_upload_prefilter', 'dfc_sanitize_svg_upload' );

function dfc_add_svg_mime( $mimes ) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}

function dfc_allow_svg_uploads( $data, $file, $filename, $mimes ) {
    $filetype = wp_check_filetype( $filename, $mimes );
    return [
        'ext'             => $filetype['ext'],
        'type'            => $filetype['type'],
        'proper_filename' => $data['proper_filename'],
    ];
}

/**
 * Sanitize SVG files on upload — strips scripts, event handlers, and dangerous elements.
 */
function dfc_sanitize_svg_upload( $file ) {
    if ( $file['type'] !== 'image/svg+xml' ) {
        return $file;
    }

    $svg_content = file_get_contents( $file['tmp_name'] );
    if ( false === $svg_content ) {
        $file['error'] = 'Could not read SVG file.';
        return $file;
    }

    $sanitized = dfc_sanitize_svg_string( $svg_content );
    if ( false === $sanitized ) {
        $file['error'] = 'This SVG file could not be sanitized and was rejected for security reasons.';
        return $file;
    }

    file_put_contents( $file['tmp_name'], $sanitized );
    return $file;
}

/**
 * Strip dangerous content from an SVG string.
 * Removes script elements, event handlers, external references, and PHP/XML injections.
 */
function dfc_sanitize_svg_string( $svg ) {
    // Must be valid XML
    $prev = libxml_use_internal_errors( true );
    $dom  = new DOMDocument();
    $dom->formatOutput   = false;
    $dom->preserveWhiteSpace = true;

    if ( ! $dom->loadXML( $svg, LIBXML_NONET | LIBXML_NOENT ) ) {
        libxml_clear_errors();
        libxml_use_internal_errors( $prev );
        return false;
    }
    libxml_clear_errors();
    libxml_use_internal_errors( $prev );

    // Dangerous elements to strip entirely
    $dangerous_tags = [
        'script', 'use', 'foreignObject', 'set', 'animate', 'animateTransform',
        'animateMotion', 'handler', 'iframe', 'embed', 'object', 'applet',
    ];

    foreach ( $dangerous_tags as $tag ) {
        $nodes = $dom->getElementsByTagName( $tag );
        // Iterate backward to avoid index shift
        for ( $i = $nodes->length - 1; $i >= 0; $i-- ) {
            $node = $nodes->item( $i );
            $node->parentNode->removeChild( $node );
        }
    }

    // Strip all event handler attributes and dangerous attribute values
    $xpath = new DOMXPath( $dom );
    $xpath->registerNamespace( 'svg', 'http://www.w3.org/2000/svg' );
    $xpath->registerNamespace( 'xlink', 'http://www.w3.org/1999/xlink' );

    $all_elements = $xpath->query( '//*' );
    foreach ( $all_elements as $element ) {
        $attrs_to_remove = [];
        foreach ( $element->attributes as $attr ) {
            $attr_name  = strtolower( $attr->nodeName );
            $attr_value = strtolower( trim( $attr->nodeValue ) );

            // Remove event handlers (on*)
            if ( str_starts_with( $attr_name, 'on' ) ) {
                $attrs_to_remove[] = $attr->nodeName;
                continue;
            }
            // Remove javascript: and data: URIs in href/xlink:href/src
            if ( in_array( $attr_name, [ 'href', 'xlink:href', 'src', 'from', 'to', 'values' ], true ) ) {
                if ( preg_match( '/^\s*(javascript|data|vbscript)\s*:/i', $attr_value ) ) {
                    $attrs_to_remove[] = $attr->nodeName;
                    continue;
                }
            }
        }
        foreach ( $attrs_to_remove as $attr_name ) {
            $element->removeAttribute( $attr_name );
        }
    }

    $clean = $dom->saveXML( $dom->documentElement );
    if ( ! $clean ) {
        return false;
    }

    // Add XML declaration back
    return '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $clean;
}

// ── Security: generic login errors ────────────────────────────
add_filter( 'login_errors', function() {
    return 'Username, password, or email address is incorrect. Click <a href="' . esc_url( wp_lostpassword_url() ) . '">here</a> to reset your password.';
} );

// ── Security: disable XML-RPC ─────────────────────────────────
add_filter( 'xmlrpc_enabled', '__return_false' );

// ── Security: remove WP version ───────────────────────────────
remove_action( 'wp_head', 'wp_generator' );

// ── Security: disable REST API user enumeration ───────────────
add_filter( 'rest_endpoints', function( $endpoints ) {
    if ( ! is_user_logged_in() ) {
        unset( $endpoints['/wp/v2/users'] );
        unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
    }
    return $endpoints;
} );

// ── ACF Options Pages ─────────────────────────────────────────
add_action( 'acf/init', 'dfc_acf_options_pages' );
function dfc_acf_options_pages() {
    if ( ! function_exists( 'acf_add_options_page' ) ) return;

    acf_add_options_page( [
        'page_title' => 'Theme Settings',
        'menu_title' => 'Theme Settings',
        'menu_slug'  => 'acf-options',
        'capability' => 'edit_posts',
        'icon_url'   => 'dashicons-admin-generic',
        'position'   => 2,
        'redirect'   => false,
    ] );

    // Alert Banner gets its own top-level page for easy client access
    acf_add_options_page( [
        'page_title' => 'Alert Banner',
        'menu_title' => 'Alert Banner',
        'menu_slug'  => 'dfc-alert-banner',
        'capability' => 'edit_posts',
        'icon_url'   => 'dashicons-warning',
        'position'   => 3,
        'redirect'   => false,
    ] );
}

// ── Theme supports ────────────────────────────────────────────
add_action( 'after_setup_theme', 'dfc_theme_supports' );
function dfc_theme_supports() {
    add_theme_support( 'custom-logo' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'align-wide' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'editor-styles' );
    add_theme_support( 'html5', array(
        'script',
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ) );
}

// ── Menus ─────────────────────────────────────────────────────
add_action( 'after_setup_theme', 'dfc_register_menus' );
function dfc_register_menus() {
    register_nav_menus( array(
        'main_nav'       => 'Main Menu',
        'footer_nav_btm' => 'Footer Bottom Menu',
    ) );
}

function dfc_main_nav() {
    wp_nav_menu( array(
        'theme_location' => 'main_nav',
        'container'      => '',
        'items_wrap'     => '<ul class="nav__list">%3$s</ul>',
        'depth'          => 0,
        'fallback_cb'    => 'wp_page_menu',
    ) );
}

function dfc_footer_nav_btm() {
    wp_nav_menu( array(
        'theme_location' => 'footer_nav_btm',
        'container'      => false,
        'menu_id'        => 'footer-menu-btm',
    ) );
}

// ── Image sizes ───────────────────────────────────────────────
function dfc_image_sizes() {
    add_image_size( 'hero', 1920, 0, false );
    add_image_size( 'slider-large', 784, 416, true );
    add_image_size( 'slider-thumb', 382, 236, true );
}
add_action( 'after_setup_theme', 'dfc_image_sizes' );

// ── Alert Banner (cached per request) ─────────────────────────
function dfc_get_active_alerts() {
    static $alerts = null;
    if ( $alerts !== null ) return $alerts;

    $alerts = [];
    $today  = wp_date( 'Ymd' );

    if ( ! have_rows( 'bar_alerts', 'option' ) ) return $alerts;

    while ( have_rows( 'bar_alerts', 'option' ) ) : the_row();
        $display = get_sub_field( 'display_alert' );
        $show    = false;

        if ( $display === 'on' ) {
            $show = true;
        } elseif ( $display === 'schedule' ) {
            $start = get_sub_field( 'alert_start_date' );
            $end   = get_sub_field( 'alert_end_date' );
            if ( $start && $end && $today >= $start && $today <= $end ) {
                $show = true;
            }
        }

        if ( $show ) {
            $content   = get_sub_field( 'alert_content' );
            $frequency = get_sub_field( 'display_frequency' );
            $severity  = get_sub_field( 'alert_severity' );

            $alerts[] = [
                'alert_content'     => $content,
                'display_frequency' => $frequency,
                'alert_id'          => substr( md5( $content . $frequency ), 0, 8 ),
                'severity_color'    => $severity === 'urgent' ? '#EF3340' : '#D3D3D3',
                'aria'              => $severity === 'urgent' ? 'role="alert"' : 'role="status" aria-live="polite"',
                'alert_button'      => get_sub_field( 'alert_button' ),
            ];
        }
    endwhile;

    return $alerts;
}

add_filter( 'body_class', function ( $classes ) {
    if ( dfc_get_active_alerts() ) {
        $classes[] = 'has-notification-banner';
    }
    return $classes;
} );
