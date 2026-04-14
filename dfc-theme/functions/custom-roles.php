<?php
/**
 * DFC Custom Roles & Capability Management
 *
 * Creates a 'Client Admin' role for DuPage Flight Center staff.
 * Client Admins can manage all content, users, forms, fuel prices,
 * theme settings, and menus — but cannot install plugins, switch themes,
 * edit code, or access WordPress core settings.
 *
 * Safety rails prevent privilege escalation to Administrator.
 *
 * @package DFC
 */

defined( 'ABSPATH' ) || exit;


/* ═══════════════════════════════════════════════════════════════════════
 * ROLE DEFINITIONS
 * ═══════════════════════════════════════════════════════════════════════ */

define( 'DFC_ROLE_VERSION', '1.0.0' );

add_action( 'admin_init', 'dfc_register_custom_roles' );
function dfc_register_custom_roles() {
    $stored = get_option( 'dfc_role_version', '0' );
    if ( $stored === DFC_ROLE_VERSION ) return;

    // ── Client Admin ──────────────────────────────────────────────
    // Full content + user management. No dev tools.

    remove_role( 'dfc_client_admin' );
    add_role( 'dfc_client_admin', __( 'Client Admin', 'dfc' ), [

        // ── Core WordPress ────────────────────────────────────────
        'read'                       => true,

        // Pages
        'edit_pages'                 => true,
        'edit_published_pages'       => true,
        'edit_others_pages'          => true,
        'publish_pages'              => true,
        'delete_pages'               => true,
        'delete_published_pages'     => true,
        'delete_others_pages'        => true,
        'delete_private_pages'       => true,
        'edit_private_pages'         => true,
        'read_private_pages'         => true,

        // Posts (future news/blog)
        'edit_posts'                 => true,
        'edit_published_posts'       => true,
        'edit_others_posts'          => true,
        'publish_posts'              => true,
        'delete_posts'               => true,
        'delete_published_posts'     => true,
        'delete_others_posts'        => true,
        'delete_private_posts'       => true,
        'edit_private_posts'         => true,
        'read_private_posts'         => true,

        // Media
        'upload_files'               => true,

        // Users — can manage users (filtered below to prevent admin escalation)
        'list_users'                 => true,
        'create_users'               => true,
        'edit_users'                 => true,
        'delete_users'               => true,
        'promote_users'              => true,

        // Theme options — needed for nav menu management + ACF options pages
        'edit_theme_options'         => true,

        // ── Gravity Forms ─────────────────────────────────────────
        'gravityforms_view_entries'     => true,
        'gravityforms_edit_entries'     => true,
        'gravityforms_delete_entries'   => true,
        'gravityforms_export_entries'   => true,
        'gravityforms_view_entry_notes' => true,
        'gravityforms_edit_entry_notes' => true,
        'gravityforms_preview_forms'    => true,
        'gravityforms_edit_forms'       => true,
        'gravityforms_create_form'      => true,
        'gravityforms_delete_forms'     => true,
        // Admin-only GF caps
        'gravityforms_edit_settings'    => false,
        'gravityforms_uninstall'        => false,
        'gravityforms_view_addons'      => false,

        // ── Explicitly denied (developer-only) ────────────────────
        'install_plugins'            => false,
        'activate_plugins'           => false,
        'update_plugins'             => false,
        'edit_plugins'               => false,
        'delete_plugins'             => false,
        'install_themes'             => false,
        'switch_themes'              => false,
        'update_themes'              => false,
        'edit_themes'                => false,
        'manage_options'             => false,
        'update_core'                => false,
        'import'                     => false,
        'export'                     => false,
        'unfiltered_html'            => false,
        'unfiltered_upload'          => false,
        'edit_files'                 => false,
    ] );

    update_option( 'dfc_role_version', DFC_ROLE_VERSION );
}

// Re-register on theme activation
add_action( 'after_switch_theme', function () {
    delete_option( 'dfc_role_version' );
    dfc_register_custom_roles();
} );


/* ═══════════════════════════════════════════════════════════════════════
 * SAFETY RAILS — PREVENT PRIVILEGE ESCALATION
 * ═══════════════════════════════════════════════════════════════════════ */

/**
 * Hide Administrator role from the role dropdown for non-admins.
 */
add_filter( 'editable_roles', function ( $roles ) {
    if ( ! current_user_can( 'administrator' ) ) {
        unset( $roles['administrator'] );
    }
    return $roles;
} );

/**
 * Prevent non-admins from editing, deleting, or promoting admin accounts.
 */
add_filter( 'map_meta_cap', function ( $caps, $cap, $user_id, $args ) {
    if ( ! in_array( $cap, [ 'edit_user', 'delete_user', 'promote_user' ], true ) ) {
        return $caps;
    }
    if ( empty( $args[0] ) ) return $caps;

    $target = get_userdata( $args[0] );
    if ( ! $target ) return $caps;

    if (
        in_array( 'administrator', $target->roles, true )
        && ! current_user_can( 'administrator' )
    ) {
        return [ 'do_not_allow' ];
    }

    return $caps;
}, 10, 4 );

/**
 * Block self-promotion to Administrator via form submission.
 */
add_action( 'edit_user_profile_update', 'dfc_prevent_self_promotion' );
add_action( 'personal_options_update', 'dfc_prevent_self_promotion' );
function dfc_prevent_self_promotion( $user_id ) {
    if ( current_user_can( 'administrator' ) ) return;

    if ( isset( $_POST['role'] ) && $_POST['role'] === 'administrator' ) {
        wp_die(
            __( 'You do not have permission to assign the Administrator role.', 'dfc' ),
            __( 'Forbidden', 'dfc' ),
            [ 'response' => 403, 'back_link' => true ]
        );
    }
}


/* ═══════════════════════════════════════════════════════════════════════
 * REDIRECT GUARDS — Belt-and-suspenders for URL access
 * ═══════════════════════════════════════════════════════════════════════ */

add_action( 'current_screen', function () {
    if ( current_user_can( 'administrator' ) ) return;

    $screen = get_current_screen();
    if ( ! $screen ) return;

    $blocked = [
        'options-general',
        'options-writing',
        'options-reading',
        'options-discussion',
        'options-media',
        'options-permalink',
        'options-privacy',
        'tools',
        'import',
        'export',
        'customize',
        'themes',
        'theme-editor',
        'plugin-editor',
        'plugin-install',
        'plugins',
        'update-core',
    ];

    if ( in_array( $screen->id, $blocked, true ) ) {
        wp_safe_redirect( admin_url( 'edit.php?post_type=page' ) );
        exit;
    }
} );


/* ═══════════════════════════════════════════════════════════════════════
 * LOGIN REDIRECT — Send Client Admins to Dashboard
 * ═══════════════════════════════════════════════════════════════════════ */

add_filter( 'login_redirect', function ( $redirect_to, $request, $user ) {
    if ( is_wp_error( $user ) || ! is_a( $user, 'WP_User' ) ) {
        return $redirect_to;
    }

    if ( in_array( 'dfc_client_admin', $user->roles, true ) ) {
        return admin_url( 'index.php' );
    }

    return $redirect_to;
}, 10, 3 );
