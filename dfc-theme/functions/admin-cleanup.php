<?php
/**
 * DFC Admin Cleanup & Dashboard UX
 *
 * Branded login page, organized admin menu, custom dashboard widgets,
 * complete comment removal, and admin color scheme.
 *
 * @package DFC
 */

defined( 'ABSPATH' ) || exit;


/* ═══════════════════════════════════════════════════════════════════════
 * COMPLETE COMMENT REMOVAL
 * ═══════════════════════════════════════════════════════════════════════ */

// Remove comment support from ALL post types
add_action( 'admin_init', function () {
    foreach ( get_post_types() as $pt ) {
        if ( post_type_supports( $pt, 'comments' ) ) {
            remove_post_type_support( $pt, 'comments' );
            remove_post_type_support( $pt, 'trackbacks' );
        }
    }
    // Remove dashboard comment widget
    remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
} );

// Close comments + pings on frontend
add_filter( 'comments_open', '__return_false', 20, 2 );
add_filter( 'pings_open', '__return_false', 20, 2 );
add_filter( 'comments_array', '__return_empty_array', 10, 2 );

// Remove from admin menu
add_action( 'admin_menu', function () {
    remove_menu_page( 'edit-comments.php' );
}, 999 );

// Remove from admin bar
add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {
    $wp_admin_bar->remove_node( 'comments' );
}, 999 );

// Remove comments column from post/page lists
add_filter( 'manage_posts_columns', function ( $c ) { unset( $c['comments'] ); return $c; } );
add_filter( 'manage_pages_columns', function ( $c ) { unset( $c['comments'] ); return $c; } );

// Redirect anyone trying to access comments page directly
add_action( 'admin_init', function () {
    global $pagenow;
    if ( $pagenow === 'edit-comments.php' ) {
        wp_redirect( admin_url() );
        exit;
    }
} );

// Disable self-pingbacks
add_action( 'pre_ping', function ( &$links ) {
    $home = get_option( 'home' );
    foreach ( $links as $l => $link ) {
        if ( 0 === strpos( $link, $home ) ) unset( $links[ $l ] );
    }
} );


/* ═══════════════════════════════════════════════════════════════════════
 * ADMIN MENU ORGANIZATION
 * ═══════════════════════════════════════════════════════════════════════ */

/**
 * Add section header dividers to the admin menu.
 */
add_action( 'admin_menu', function () {
    global $menu;

    $sections = [
        'dfc-section-content' => 'Content',
        'dfc-section-manage'  => 'Manage',
        'dfc-section-admin'   => 'Developer',
    ];

    foreach ( $sections as $slug => $label ) {
        add_menu_page( '', $label, 'read', $slug, '__return_false', 'none', 0.1 );
    }

    foreach ( $menu as $key => &$item ) {
        if ( isset( $sections[ $item[2] ] ) ) {
            $item[4] = ( $item[4] ?? '' ) . ' dfc-menu-section-header';
        }
    }
    unset( $item );
}, 5 );

/**
 * Reorganize admin menu order.
 */
add_filter( 'custom_menu_order', '__return_true' );
add_filter( 'menu_order', function ( $menu_order ) {
    if ( ! $menu_order ) return true;

    return [
        'index.php',                            // Dashboard

        // ── Content ──
        'dfc-section-content',
        'edit.php?post_type=page',              // Pages
        'upload.php',                           // Media

        // ── Manage ──
        'dfc-section-manage',
        'dfc-fuel-prices',                      // Fuel Prices
        'dfc-alert-banner',                     // Alert Banner
        'gf_edit_forms',                        // Forms
        'acf-options',                          // Theme Settings
        'users.php',                            // Users
        'themes.php',                           // Menus (Appearance)

        // ── Developer (admin only) ──
        'dfc-section-admin',
        'plugins.php',
        'options-general.php',
        'tools.php',
        'wpengine-common',                      // WP Engine
        'rank-math',                            // Rank Math SEO
        'edit.php?post_type=acf-field-group',   // ACF
        'separator1',
    ];
} );

/**
 * Clean up menu for non-admin users.
 */
add_action( 'admin_menu', function () {
    if ( current_user_can( 'administrator' ) ) return;

    // Hide developer section + items
    remove_menu_page( 'dfc-section-admin' );
    remove_menu_page( 'tools.php' );
    remove_menu_page( 'options-general.php' );
    remove_menu_page( 'plugins.php' );
    remove_menu_page( 'wpengine-common' );
    remove_menu_page( 'rank-math' );
    remove_menu_page( 'edit.php?post_type=acf-field-group' );
    remove_menu_page( 'gravitysmtp-dashboard' );
    remove_menu_page( 'wpengine-ai-toolkit' );

    // Appearance: keep Menus, hide everything else
    remove_submenu_page( 'themes.php', 'themes.php' );
    remove_submenu_page( 'themes.php', 'customize.php' );
    remove_submenu_page( 'themes.php', 'theme-editor.php' );

    // GF settings — admin only
    remove_submenu_page( 'gf_edit_forms', 'gf_settings' );
    remove_submenu_page( 'gf_edit_forms', 'gf_addons' );
    remove_submenu_page( 'gf_edit_forms', 'gf_system_status' );
    remove_submenu_page( 'gf_edit_forms', 'gf_update' );
}, 999 );

/**
 * Rename "Appearance" to "Menus" for non-admins (since that's all they see).
 */
add_action( 'admin_menu', function () {
    if ( current_user_can( 'administrator' ) ) return;
    global $menu;
    foreach ( $menu as $key => $item ) {
        if ( $item[2] === 'themes.php' ) {
            $menu[ $key ][0] = 'Menus';
        }
    }
}, 998 );


/* ═══════════════════════════════════════════════════════════════════════
 * DASHBOARD WIDGETS
 * ═══════════════════════════════════════════════════════════════════════ */

/**
 * Remove default dashboard clutter.
 */
add_action( 'wp_dashboard_setup', function () {
    remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
    remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
    remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
    remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
    remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
    remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );
    remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
    remove_meta_box( 'rank_math_dashboard_widget', 'dashboard', 'normal' );
    remove_meta_box( 'rg_forms_dashboard', 'dashboard', 'normal' );
    remove_meta_box( 'rg_forms_dashboard', 'dashboard', 'side' );
}, 999 );

/**
 * Add DFC branded dashboard widgets.
 */
add_action( 'wp_dashboard_setup', function () {
    wp_add_dashboard_widget(
        'dfc_welcome',
        'DuPage Flight Center',
        'dfc_welcome_widget',
        null, null, 'normal', 'high'
    );

    wp_add_dashboard_widget(
        'dfc_quick_links',
        'Quick Links',
        'dfc_quick_links_widget',
        null, null, 'side', 'high'
    );

    if ( current_user_can( 'edit_pages' ) ) {
        wp_add_dashboard_widget(
            'dfc_a11y_tips',
            'Accessibility Reminders',
            'dfc_a11y_tips_widget',
            null, null, 'side', 'default'
        );
    }
} );

function dfc_welcome_widget() {
    $user = wp_get_current_user();
    ?>
    <div style="padding: 8px 0;">
        <p style="font-size: 15px;">Welcome back, <strong><?php echo esc_html( $user->display_name ); ?></strong>!</p>
        <p>Use the sidebar menu to manage content for the DuPage Flight Center website.</p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 16px;">
            <a href="<?php echo admin_url( 'edit.php?post_type=page' ); ?>" class="button" style="text-align: center;">Edit Pages</a>
            <a href="<?php echo admin_url( 'upload.php' ); ?>" class="button" style="text-align: center;">Media Library</a>
            <a href="<?php echo admin_url( 'admin.php?page=dfc-fuel-prices' ); ?>" class="button" style="text-align: center;">Fuel Prices</a>
            <a href="<?php echo admin_url( 'admin.php?page=acf-options' ); ?>" class="button" style="text-align: center;">Theme Settings</a>
        </div>
        <?php if ( current_user_can( 'administrator' ) ) : ?>
            <p style="margin-top: 16px; padding-top: 12px; border-top: 1px solid #ddd; font-size: 12px; color: #666;">
                <strong>Dev:</strong>
                <a href="<?php echo admin_url( 'admin.php?page=gf_edit_forms' ); ?>">Forms</a> |
                <a href="<?php echo admin_url( 'users.php' ); ?>">Users</a> |
                <a href="<?php echo admin_url( 'admin.php?page=dfc-weather' ); ?>">Weather</a>
            </p>
        <?php endif; ?>
    </div>
    <?php
}

function dfc_quick_links_widget() {
    ?>
    <ul style="margin: 0; padding: 0; list-style: none;">
        <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank">View Live Site</a>
        </li>
        <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
            <a href="<?php echo admin_url( 'admin.php?page=dfc-fuel-prices' ); ?>">Update Fuel Prices</a>
        </li>
        <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
            <a href="<?php echo admin_url( 'admin.php?page=acf-options' ); ?>">Theme Settings</a>
        </li>
        <li style="padding: 8px 0;">
            <a href="mailto:support@aviatrixcommunications.com">Contact Aviatrix Support</a>
        </li>
    </ul>
    <?php
}

function dfc_a11y_tips_widget() {
    ?>
    <ul style="margin: 0; padding: 0 0 0 18px; font-size: 13px; line-height: 1.6;">
        <li>Every image needs descriptive alt text (or leave blank if purely decorative).</li>
        <li>Use headings in order — don't skip from H2 to H4.</li>
        <li>Link text should describe the destination, not just "click here."</li>
        <li>Ensure sufficient color contrast between text and backgrounds.</li>
        <li>PDFs should be tagged and accessible when possible.</li>
    </ul>
    <?php
}


/* ═══════════════════════════════════════════════════════════════════════
 * BRANDED LOGIN PAGE
 * ═══════════════════════════════════════════════════════════════════════ */

add_action( 'login_enqueue_scripts', function () {
    $logo_id  = get_theme_mod( 'custom_logo' );
    $logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';

    // Fall back to theme logo
    if ( ! $logo_url ) {
        $logo_url = get_template_directory_uri() . '/img/dfc-logo-horizontal-white.svg';
    }

    // Load Open Sans
    wp_enqueue_style(
        'dfc-login-fonts',
        'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap',
        [],
        null
    );

    ?>
    <style>
        /* ── Page background ── */
        body.login {
            background-color: #000;
            font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        /* ── Red accent bar at top ── */
        body.login::before {
            content: '';
            display: block;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: #EF3340;
            z-index: 9999;
        }

        /* ── Logo ── */
        #login h1 a {
            background-image: url('<?php echo esc_url( $logo_url ); ?>');
            background-size: contain;
            width: 280px;
            height: 80px;
            background-repeat: no-repeat;
            background-position: center;
            margin-bottom: 24px;
        }

        /* ── Form card ── */
        .login form {
            border-radius: 0 !important;
            border: none !important;
            box-shadow: 0 2px 16px rgba(0, 0, 0, 0.3);
            padding: 26px 24px 34px;
            background: #fff;
        }

        /* ── Labels ── */
        .login form .forgetmenot label,
        .login label {
            font-family: 'Open Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: #000;
        }

        /* ── Inputs ── */
        .login form input[type="text"],
        .login form input[type="password"] {
            font-family: 'Open Sans', sans-serif;
            font-size: 16px;
            border: 2px solid #D3D3D3;
            border-radius: 0;
            padding: 10px 14px;
            background: #fff;
            color: #000;
            transition: border-color 0.2s ease;
        }

        .login form input[type="text"]:focus,
        .login form input[type="password"]:focus {
            border-color: #EF3340;
            box-shadow: 0 0 0 2px rgba(239, 51, 64, 0.2);
            outline: none;
        }

        /* ── Password toggle ── */
        .login .wp-pwd .button.wp-hide-pw {
            color: #EF3340;
        }
        .login .wp-pwd .button.wp-hide-pw:focus {
            color: #EF3340;
            box-shadow: 0 0 0 2px rgba(239, 51, 64, 0.3);
            outline: 2px solid #EF3340;
            outline-offset: 2px;
        }

        /* ── Checkbox ── */
        .login input[type="checkbox"]:checked::before {
            content: url("data:image/svg+xml;utf8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2020%2020%27%3E%3Cpath%20d%3D%27M14.83%204.89l1.34%201.22-7.16%208-4.18-3.71%201.34-1.51%202.69%202.39z%27%20fill%3D%27%23EF3340%27%2F%3E%3C%2Fsvg%3E");
        }
        .login input[type="checkbox"]:focus {
            border-color: #EF3340;
            box-shadow: 0 0 0 2px rgba(239, 51, 64, 0.2);
        }

        /* ── Submit button ── */
        .wp-core-ui .button-primary {
            background: #EF3340 !important;
            border: none !important;
            border-radius: 0 !important;
            color: #000 !important;
            font-family: 'Open Sans', sans-serif !important;
            font-size: 15px !important;
            font-weight: 600 !important;
            padding: 8px 28px !important;
            height: auto !important;
            line-height: 1.6 !important;
            text-shadow: none !important;
            box-shadow: none !important;
            transition: background-color 0.15s ease;
            min-height: 44px;
        }
        .wp-core-ui .button-primary:hover,
        .wp-core-ui .button-primary:focus {
            background: #d42b38 !important;
            color: #000 !important;
        }
        .wp-core-ui .button-primary:focus {
            outline: 2px solid #EF3340 !important;
            outline-offset: 2px !important;
        }

        /* ── Submit row ── */
        .login .submit {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* ── Links below form ── */
        .login #backtoblog a,
        .login #nav a,
        .login .privacy-policy-page-link a {
            color: #D3D3D3 !important;
            font-family: 'Open Sans', sans-serif;
            font-size: 14px;
            text-decoration: none;
            transition: color 0.15s ease;
        }
        .login #backtoblog a:hover,
        .login #nav a:hover,
        .login .privacy-policy-page-link a:hover {
            color: #fff !important;
            text-decoration: underline;
        }
        .login #backtoblog a:focus,
        .login #nav a:focus,
        .login .privacy-policy-page-link a:focus {
            color: #fff !important;
            outline: 2px solid #EF3340;
            outline-offset: 2px;
            border-radius: 2px;
        }

        /* ── Error / message boxes ── */
        .login .message,
        .login .success {
            border-left-color: #EF3340;
            font-family: 'Open Sans', sans-serif;
        }
        .login #login_error {
            border-left-color: #D3D3D3;
            font-family: 'Open Sans', sans-serif;
        }

        /* ── Language switcher ── */
        .language-switcher {
            font-family: 'Open Sans', sans-serif;
        }
    </style>
    <?php
} );

// Login logo links to site, not wordpress.org
add_filter( 'login_headerurl', function () { return home_url( '/' ); } );
add_filter( 'login_headertext', function () { return get_bloginfo( 'name' ); } );


/* ═══════════════════════════════════════════════════════════════════════
 * ADMIN STYLES — Section headers + color scheme
 * ═══════════════════════════════════════════════════════════════════════ */

add_action( 'admin_head', function () {
    ?>
    <style>
        /* ── Section header dividers in admin menu ── */
        .dfc-menu-section-header {
            pointer-events: none;
            opacity: 1 !important;
        }
        .dfc-menu-section-header a {
            pointer-events: none !important;
            cursor: default !important;
        }
        .dfc-menu-section-header .wp-menu-name {
            font-size: 10px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.08em !important;
            color: #D3D3D3 !important;
            padding: 16px 12px 4px !important;
            line-height: 1 !important;
        }
        .dfc-menu-section-header .wp-menu-image {
            display: none !important;
        }
        .dfc-menu-section-header a:hover {
            background: transparent !important;
        }

        /* ── Admin bar accent ── */
        #wpadminbar {
            border-bottom: 2px solid #EF3340;
        }

        /* ── ACF options page polish ── */
        .acf-fields > .acf-field {
            padding: 12px 16px;
        }
        .acf-field .acf-instructions {
            font-size: 12px;
            color: #757575;
            margin-top: 2px;
            line-height: 1.4;
        }

        /* ── ACF tab styling ── */
        .acf-tab-wrap .acf-tab-button.active,
        .acf-tab-wrap .acf-tab-button:hover {
            color: #000;
            border-left: 3px solid #EF3340;
        }

        /* ── Dashboard widget styling ── */
        #dfc_welcome .inside,
        #dfc_quick_links .inside,
        #dfc_a11y_tips .inside {
            font-family: 'Open Sans', -apple-system, sans-serif;
        }
    </style>
    <?php
} );
