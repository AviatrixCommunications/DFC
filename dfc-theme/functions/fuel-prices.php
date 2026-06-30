<?php
/**
 * DFC Fuel Prices
 * ─────────────────────────────────────────────────────────────────────────
 *
 * Everything fuel-related lives here:
 *
 *   1. Capability + ACF options page registration
 *   2. Admin menu — Fuel Center (dashboard), Edit Prices (ACF), Import/Export,
 *      Schedule
 *   3. CSV template download, CSV export, CSV import (all on admin_init so
 *      headers fire cleanly before any admin HTML renders)
 *   4. Scheduled price changes — stage a complete pricing set + an
 *      activate-at datetime; WP-Cron promotes the stage to live at that time
 *   5. CSV parser + ACF writer
 *   6. Cache purge (WP Engine Varnish + Memcached)
 *   7. Shortcodes used by the frontend (homepage widget + full pricing tables)
 *
 * The capability `dfc_manage_fuel_prices` (defined in custom-roles.php as
 * DFC_CAP_FUEL) gates every fuel-related screen and action. Three roles
 * have it: administrator, dfc_client_admin, dfc_fuel_editor.
 *
 * @package DFC
 */

defined( 'ABSPATH' ) || exit;

// Fallback so this file is safe to load even if custom-roles.php hasn't
// defined the constant yet (e.g. during a partial deploy).
if ( ! defined( 'DFC_CAP_FUEL' ) ) {
    define( 'DFC_CAP_FUEL', 'dfc_manage_fuel_prices' );
}

const DFC_FUEL_SCHEDULE_OPTION = 'dfc_fuel_scheduled_change';
const DFC_FUEL_CRON_HOOK       = 'dfc_fuel_apply_scheduled_change';


/* ═══════════════════════════════════════════════════════════════════════
 * ACF OPTIONS PAGE
 * ═══════════════════════════════════════════════════════════════════════ */

add_action( 'acf/init', 'dfc_fuel_options_page' );
function dfc_fuel_options_page() {
    if ( ! function_exists( 'acf_add_options_page' ) ) return;

    // Parent — the "Fuel Center" landing screen is registered as a real
    // top-level menu below (see dfc_fuel_admin_menu), so this options page
    // is registered with autoload false and `parent_slug` pointing at our
    // top-level slug. ACF will render at admin.php?page=dfc-fuel-prices.
    acf_add_options_sub_page( [
        'page_title'  => 'Edit Fuel Prices',
        'menu_title'  => 'Edit Prices',
        'menu_slug'   => 'dfc-fuel-prices',
        'parent_slug' => 'dfc-fuel-center',
        'capability'  => DFC_CAP_FUEL,
        'position'    => 20,
        'redirect'    => false,
    ] );
}


/* ═══════════════════════════════════════════════════════════════════════
 * ADMIN MENU — Fuel Center top-level + sub-pages
 * ═══════════════════════════════════════════════════════════════════════ */

add_action( 'admin_menu', 'dfc_fuel_admin_menu', 9 );
function dfc_fuel_admin_menu() {
    add_menu_page(
        'Fuel Center',
        'Fuel Center',
        DFC_CAP_FUEL,
        'dfc-fuel-center',
        'dfc_fuel_dashboard_page',
        'dashicons-chart-line',
        25
    );

    // Subpage: dashboard view (matches the parent slug so the first item is
    // the dashboard itself).
    add_submenu_page(
        'dfc-fuel-center',
        'Fuel Center',
        'Dashboard',
        DFC_CAP_FUEL,
        'dfc-fuel-center',
        'dfc_fuel_dashboard_page'
    );

    // ACF options page is hooked separately on acf/init above with the same
    // parent_slug. WordPress will merge it into this submenu set.

    add_submenu_page(
        'dfc-fuel-center',
        'Schedule Price Change',
        'Schedule',
        DFC_CAP_FUEL,
        'dfc-fuel-schedule',
        'dfc_fuel_schedule_page'
    );

    add_submenu_page(
        'dfc-fuel-center',
        'Import / Export CSV',
        'Import / Export',
        DFC_CAP_FUEL,
        'dfc-fuel-import',
        'dfc_fuel_import_page'
    );
}

/**
 * Add a body class so we can style the fuel admin pages without leaking
 * styles to the rest of wp-admin.
 */
add_filter( 'admin_body_class', function ( $classes ) {
    if ( isset( $_GET['page'] ) && in_array( $_GET['page'], [ 'dfc-fuel-center', 'dfc-fuel-prices', 'dfc-fuel-import', 'dfc-fuel-schedule' ], true ) ) {
        $classes .= ' dfc-fuel-admin';
    }
    return $classes;
} );


/* ═══════════════════════════════════════════════════════════════════════
 * EARLY-FIRING HANDLERS — must run before any admin HTML output
 *
 * Template download, CSV export, "apply schedule now", and "cancel
 * schedule" all need to either send headers (CSV) or do a redirect.
 * Hooking these to admin_init keeps them out of the menu-page render path
 * so headers haven't been flushed yet.
 * ═══════════════════════════════════════════════════════════════════════ */

add_action( 'admin_init', 'dfc_fuel_handle_early_actions' );
function dfc_fuel_handle_early_actions() {
    if ( ! current_user_can( DFC_CAP_FUEL ) ) return;

    // ── Download CSV template ──
    if ( isset( $_GET['dfc_fuel_action'] ) && $_GET['dfc_fuel_action'] === 'download_template' ) {
        check_admin_referer( 'dfc_fuel_template' );
        dfc_fuel_download_template();
        exit;
    }

    // ── Export current prices as CSV ──
    if ( isset( $_GET['dfc_fuel_action'] ) && $_GET['dfc_fuel_action'] === 'export_current' ) {
        check_admin_referer( 'dfc_fuel_export' );
        dfc_fuel_export_current();
        exit;
    }

    // ── Cancel scheduled change ──
    if ( isset( $_POST['dfc_fuel_cancel_schedule'] ) ) {
        check_admin_referer( 'dfc_fuel_cancel_schedule' );
        dfc_fuel_clear_schedule();
        wp_safe_redirect( add_query_arg( 'dfc_msg', 'schedule_cancelled', admin_url( 'admin.php?page=dfc-fuel-center' ) ) );
        exit;
    }

    // ── Apply scheduled change immediately ──
    if ( isset( $_POST['dfc_fuel_apply_schedule_now'] ) ) {
        check_admin_referer( 'dfc_fuel_apply_now' );
        $applied = dfc_fuel_apply_scheduled_change();
        $msg = $applied ? 'schedule_applied' : 'schedule_missing';
        wp_safe_redirect( add_query_arg( 'dfc_msg', $msg, admin_url( 'admin.php?page=dfc-fuel-center' ) ) );
        exit;
    }

    // ── Save a scheduled change ──
    if ( isset( $_POST['dfc_fuel_save_schedule'] ) ) {
        check_admin_referer( 'dfc_fuel_save_schedule' );
        $result = dfc_fuel_save_schedule_from_post();
        $arg    = is_wp_error( $result )
            ? add_query_arg( [ 'dfc_msg' => 'schedule_error', 'dfc_err' => urlencode( $result->get_error_message() ) ], admin_url( 'admin.php?page=dfc-fuel-schedule' ) )
            : add_query_arg( 'dfc_msg', 'schedule_saved', admin_url( 'admin.php?page=dfc-fuel-center' ) );
        wp_safe_redirect( $arg );
        exit;
    }

    // ── Confirm a CSV preview into live data ──
    if ( isset( $_POST['dfc_fuel_confirm_import'] ) ) {
        check_admin_referer( 'dfc_fuel_confirm' );
        $payload = isset( $_POST['csv_data'] ) ? wp_unslash( $_POST['csv_data'] ) : '';
        $data    = json_decode( $payload, true );
        if ( ! $data || ! isset( $data['sections'] ) || ! is_array( $data['sections'] ) ) {
            wp_safe_redirect( add_query_arg( 'dfc_msg', 'import_invalid', admin_url( 'admin.php?page=dfc-fuel-import' ) ) );
            exit;
        }
        // If the user picked "Schedule for later" instead of "Import now",
        // route through the scheduler. Otherwise apply right away.
        if ( ! empty( $_POST['dfc_fuel_schedule_after_preview'] ) && ! empty( $_POST['dfc_fuel_activate_at'] ) ) {
            $when = dfc_fuel_parse_local_datetime( $_POST['dfc_fuel_activate_at'] );
            if ( is_wp_error( $when ) ) {
                wp_safe_redirect( add_query_arg( [ 'dfc_msg' => 'schedule_error', 'dfc_err' => urlencode( $when->get_error_message() ) ], admin_url( 'admin.php?page=dfc-fuel-import' ) ) );
                exit;
            }
            dfc_fuel_set_schedule( $data, $when, get_current_user_id() );
            wp_safe_redirect( add_query_arg( 'dfc_msg', 'schedule_saved', admin_url( 'admin.php?page=dfc-fuel-center' ) ) );
            exit;
        }

        dfc_import_fuel_data( $data );
        wp_safe_redirect( add_query_arg( 'dfc_msg', 'import_done', admin_url( 'admin.php?page=dfc-fuel-center' ) ) );
        exit;
    }
}


/* ═══════════════════════════════════════════════════════════════════════
 * ADMIN NOTICES
 * ═══════════════════════════════════════════════════════════════════════ */

add_action( 'admin_notices', function () {
    if ( empty( $_GET['dfc_msg'] ) ) return;

    $messages = [
        'import_done'        => [ 'success', 'Fuel prices imported successfully.' ],
        'import_invalid'     => [ 'error',   'Import data was invalid. Please re-upload the CSV.' ],
        'schedule_saved'     => [ 'success', 'Scheduled price change saved. It will go live automatically at the chosen time.' ],
        'schedule_cancelled' => [ 'success', 'Scheduled price change cancelled.' ],
        'schedule_applied'   => [ 'success', 'Scheduled price change applied to live prices.' ],
        'schedule_missing'   => [ 'warning', 'No scheduled change was pending.' ],
        'schedule_error'     => [ 'error',   'There was a problem scheduling the change' . ( isset( $_GET['dfc_err'] ) ? ': ' . esc_html( urldecode( $_GET['dfc_err'] ) ) : '.' ) ],
    ];

    $key = $_GET['dfc_msg'];
    if ( ! isset( $messages[ $key ] ) ) return;

    [ $type, $text ] = $messages[ $key ];
    printf(
        '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
        esc_attr( $type ),
        wp_kses( $text, [ 'br' => [] ] )
    );
} );

/**
 * Persistent banner: if a price change is scheduled, surface it on every
 * fuel admin screen until it's applied or cancelled.
 */
add_action( 'admin_notices', function () {
    if ( empty( $_GET['page'] ) ) return;
    if ( ! in_array( $_GET['page'], [ 'dfc-fuel-center', 'dfc-fuel-prices', 'dfc-fuel-import', 'dfc-fuel-schedule' ], true ) ) return;

    $sched = get_option( DFC_FUEL_SCHEDULE_OPTION );
    if ( ! $sched || empty( $sched['activate_at'] ) ) return;

    $tz      = wp_timezone();
    $when    = wp_date( 'M j, Y \a\t g:i a T', (int) $sched['activate_at'], $tz );
    $by      = ! empty( $sched['saved_by'] ) ? get_userdata( (int) $sched['saved_by'] ) : null;
    $by_name = $by ? $by->display_name : 'a user';

    printf(
        '<div class="notice notice-info"><p><strong>Price change scheduled:</strong> %s &nbsp;·&nbsp; saved by %s &nbsp;·&nbsp; <a href="%s">Review or cancel</a></p></div>',
        esc_html( $when ),
        esc_html( $by_name ),
        esc_url( admin_url( 'admin.php?page=dfc-fuel-center' ) )
    );
} );


/* ═══════════════════════════════════════════════════════════════════════
 * DASHBOARD PAGE — Fuel Center
 *
 * One-screen overview: current effective date, current retail prices,
 * status of any scheduled change, plus quick links to edit/import/schedule.
 * ═══════════════════════════════════════════════════════════════════════ */

function dfc_fuel_dashboard_page() {
    if ( ! current_user_can( DFC_CAP_FUEL ) ) {
        wp_die( __( 'You do not have permission to access fuel prices.', 'dfc' ) );
    }

    $effective = get_field( 'fuel_effective_date', 'option' );
    $jet_pre   = get_field( 'jet_fuel_retail_pretax', 'option' );
    $jet_post  = get_field( 'jet_fuel_retail_aftertax', 'option' );
    $av_pre    = get_field( 'avgas_retail_pretax', 'option' );
    $av_post   = get_field( 'avgas_retail_aftertax', 'option' );

    $sched     = get_option( DFC_FUEL_SCHEDULE_OPTION );
    $has_sched = $sched && ! empty( $sched['activate_at'] );

    ?>
    <div class="wrap dfc-fuel-wrap">
        <h1 class="wp-heading-inline">Fuel Center</h1>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=dfc-fuel-prices' ) ); ?>" class="page-title-action">Edit Prices</a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=dfc-fuel-schedule' ) ); ?>" class="page-title-action">Schedule a Change</a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=dfc-fuel-import' ) ); ?>" class="page-title-action">Import / Export CSV</a>
        <hr class="wp-header-end">

        <p class="description" style="max-width:60ch;">
            All retail and tiered pricing for Jet&nbsp;A and AvGas. Edit directly, upload a CSV, or stage a future price change that goes live automatically at the time you pick.
        </p>

        <div class="dfc-fuel-grid">

            <div class="dfc-fuel-card">
                <h2>Currently posted</h2>
                <?php if ( $effective ) : ?>
                    <p class="dfc-fuel-eff">Effective <strong><?php echo esc_html( dfc_fuel_format_date( $effective, 'F j, Y' ) ); ?></strong></p>
                <?php else : ?>
                    <p class="dfc-fuel-eff dfc-fuel-muted">No effective date set.</p>
                <?php endif; ?>

                <div class="dfc-fuel-prices">
                    <div class="dfc-fuel-price">
                        <span class="dfc-fuel-label">Jet A — Retail</span>
                        <span class="dfc-fuel-val"><?php echo esc_html( dfc_fuel_format_price( $jet_pre ) ); ?></span>
                        <span class="dfc-fuel-sub">Pre-tax</span>
                    </div>
                    <div class="dfc-fuel-price">
                        <span class="dfc-fuel-label">Jet A — Retail</span>
                        <span class="dfc-fuel-val"><?php echo esc_html( dfc_fuel_format_price( $jet_post ) ); ?></span>
                        <span class="dfc-fuel-sub">After-tax</span>
                    </div>
                    <div class="dfc-fuel-price">
                        <span class="dfc-fuel-label">AvGas — Retail</span>
                        <span class="dfc-fuel-val"><?php echo esc_html( dfc_fuel_format_price( $av_pre ) ); ?></span>
                        <span class="dfc-fuel-sub">Pre-tax</span>
                    </div>
                    <div class="dfc-fuel-price">
                        <span class="dfc-fuel-label">AvGas — Retail</span>
                        <span class="dfc-fuel-val"><?php echo esc_html( dfc_fuel_format_price( $av_post ) ); ?></span>
                        <span class="dfc-fuel-sub">After-tax</span>
                    </div>
                </div>

                <p class="dfc-fuel-actions">
                    <a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=dfc-fuel-prices' ) ); ?>">Edit Live Prices</a>
                    <a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=dfc-fuel-center&dfc_fuel_action=export_current' ), 'dfc_fuel_export' ) ); ?>">Download Current as CSV</a>
                </p>
            </div>

            <div class="dfc-fuel-card <?php echo $has_sched ? 'is-scheduled' : 'is-empty'; ?>">
                <h2>Scheduled change</h2>
                <?php if ( $has_sched ) :
                    $tz       = wp_timezone();
                    $when     = wp_date( 'F j, Y \a\t g:i a', (int) $sched['activate_at'], $tz );
                    $tz_label = wp_date( 'T', (int) $sched['activate_at'], $tz );
                    $by       = ! empty( $sched['saved_by'] ) ? get_userdata( (int) $sched['saved_by'] ) : null;
                    $by_name  = $by ? $by->display_name : 'an editor';
                    $saved_at = ! empty( $sched['saved_at'] ) ? wp_date( 'M j, Y g:i a', (int) $sched['saved_at'], $tz ) : '';

                    $jet_p = '—'; $av_p = '—';
                    if ( ! empty( $sched['data']['sections']['jet'][0]['pretax_price'] ) ) {
                        $jet_p = dfc_fuel_format_price( $sched['data']['sections']['jet'][0]['pretax_price'] );
                    }
                    if ( ! empty( $sched['data']['sections']['avgas'][0]['pretax_price'] ) ) {
                        $av_p = dfc_fuel_format_price( $sched['data']['sections']['avgas'][0]['pretax_price'] );
                    }
                ?>
                    <p class="dfc-fuel-sched-when">
                        Goes live <strong><?php echo esc_html( $when ); ?> <?php echo esc_html( $tz_label ); ?></strong>
                    </p>
                    <p class="dfc-fuel-sub">Staged by <?php echo esc_html( $by_name ); ?><?php if ( $saved_at ) : ?> on <?php echo esc_html( $saved_at ); ?><?php endif; ?>.</p>

                    <div class="dfc-fuel-prices">
                        <div class="dfc-fuel-price"><span class="dfc-fuel-label">Jet A retail</span><span class="dfc-fuel-val"><?php echo esc_html( $jet_p ); ?></span><span class="dfc-fuel-sub">Pre-tax</span></div>
                        <div class="dfc-fuel-price"><span class="dfc-fuel-label">AvGas retail</span><span class="dfc-fuel-val"><?php echo esc_html( $av_p ); ?></span><span class="dfc-fuel-sub">Pre-tax</span></div>
                    </div>

                    <p class="dfc-fuel-actions">
                        <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=dfc-fuel-schedule' ) ); ?>">View / Edit</a>

                        <form method="post" style="display:inline;">
                            <?php wp_nonce_field( 'dfc_fuel_apply_now' ); ?>
                            <button type="submit" name="dfc_fuel_apply_schedule_now" class="button" onclick="return confirm('Apply the scheduled change to live prices right now?');">Apply Now</button>
                        </form>

                        <form method="post" style="display:inline;">
                            <?php wp_nonce_field( 'dfc_fuel_cancel_schedule' ); ?>
                            <button type="submit" name="dfc_fuel_cancel_schedule" class="button button-link-delete" onclick="return confirm('Cancel the scheduled price change? The staged data will be discarded.');">Cancel</button>
                        </form>
                    </p>
                <?php else : ?>
                    <p class="dfc-fuel-muted">No price change scheduled.</p>
                    <p class="dfc-fuel-actions">
                        <a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=dfc-fuel-schedule' ) ); ?>">Schedule a Change</a>
                        <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=dfc-fuel-import' ) ); ?>">Upload CSV</a>
                    </p>
                <?php endif; ?>
            </div>

            <div class="dfc-fuel-card">
                <h2>CSV tools</h2>
                <p>Download a blank template, export current prices, or upload a file to update everything in one shot.</p>
                <p class="dfc-fuel-actions">
                    <a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=dfc-fuel-center&dfc_fuel_action=download_template' ), 'dfc_fuel_template' ) ); ?>">Download Blank Template</a>
                    <a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=dfc-fuel-center&dfc_fuel_action=export_current' ), 'dfc_fuel_export' ) ); ?>">Export Current Prices</a>
                    <a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=dfc-fuel-import' ) ); ?>">Upload CSV</a>
                </p>
            </div>

        </div>
    </div>

    <?php dfc_fuel_admin_styles(); ?>
    <?php
}


/* ═══════════════════════════════════════════════════════════════════════
 * SCHEDULE PAGE
 *
 * Edit/create a single staged price change. The form is identical to the
 * live price editor in structure, but writes into the staging option
 * instead of the live ACF fields. WP-Cron promotes staging → live at the
 * activate_at timestamp.
 * ═══════════════════════════════════════════════════════════════════════ */

function dfc_fuel_schedule_page() {
    if ( ! current_user_can( DFC_CAP_FUEL ) ) {
        wp_die( __( 'You do not have permission to access fuel prices.', 'dfc' ) );
    }

    $sched     = get_option( DFC_FUEL_SCHEDULE_OPTION );
    $has_sched = $sched && ! empty( $sched['activate_at'] );

    // Pre-fill from existing schedule, or from current live prices, or empty.
    if ( $has_sched ) {
        $data = $sched['data'];
        $when_local = wp_date( 'Y-m-d\TH:i', (int) $sched['activate_at'], wp_timezone() );
    } else {
        $data = dfc_fuel_build_data_from_live();
        $when_local = '';
    }

    ?>
    <div class="wrap dfc-fuel-wrap">
        <h1>Schedule a Price Change</h1>

        <p class="description" style="max-width:70ch;">
            Stage a new pricing set and pick exactly when it should go live. The change is held in draft form &mdash; live prices on the public site stay the same until the scheduled time arrives. You can edit or cancel a scheduled change any time before then.
        </p>

        <form method="post" class="dfc-fuel-schedule-form">
            <?php wp_nonce_field( 'dfc_fuel_save_schedule' ); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="dfc_fuel_activate_at">Activate at</label></th>
                    <td>
                        <input
                            type="datetime-local"
                            name="dfc_fuel_activate_at"
                            id="dfc_fuel_activate_at"
                            value="<?php echo esc_attr( $when_local ); ?>"
                            min="<?php echo esc_attr( wp_date( 'Y-m-d\TH:i' ) ); ?>"
                            required
                            style="font-size:1rem;padding:.4rem .6rem;"
                        />
                        <p class="description">
                            Site timezone: <code><?php echo esc_html( wp_timezone_string() ); ?></code>.
                            The change will go live at this exact moment (within ~1&nbsp;minute of the schedule check).
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="dfc_fuel_effective_date">Effective date label</label></th>
                    <td>
                        <input
                            type="date"
                            name="dfc_fuel_effective_date"
                            id="dfc_fuel_effective_date"
                            value="<?php echo esc_attr( $data['effective_date'] ?? wp_date( 'Y-m-d' ) ); ?>"
                            style="font-size:1rem;padding:.4rem .6rem;"
                        />
                        <p class="description">
                            Shown to visitors on the public Fuel Prices page (e.g. &ldquo;Effective May 21, 2026&rdquo;). Independent of the activation time above.
                        </p>
                    </td>
                </tr>
            </table>

            <?php
            foreach ( [ 'jet' => 'Jet Fuel', 'avgas' => 'AvGas' ] as $section_key => $section_label ) :
                $rows = $data['sections'][ $section_key ] ?? [];
                // Ensure at least one empty row to bootstrap an empty form.
                if ( empty( $rows ) ) {
                    $rows = [ [ 'tier_label' => '', 'gallons' => '', 'discount' => '', 'pretax_price' => '', 'aftertax_price' => '' ] ];
                }
                ?>
                <h2><?php echo esc_html( $section_label ); ?></h2>
                <table class="widefat dfc-fuel-tier-table" data-section="<?php echo esc_attr( $section_key ); ?>">
                    <thead>
                        <tr>
                            <th scope="col">Tier name</th>
                            <th scope="col">Gallons</th>
                            <th scope="col">Discount</th>
                            <th scope="col">Pre-tax price</th>
                            <th scope="col">After-tax price</th>
                            <th scope="col" class="dfc-fuel-row-actions"><span class="screen-reader-text">Remove row</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $rows as $i => $row ) : ?>
                            <?php dfc_fuel_render_tier_row( $section_key, $i, $row ); ?>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr><td colspan="6"><button type="button" class="button dfc-fuel-add-row" data-section="<?php echo esc_attr( $section_key ); ?>">+ Add row</button></td></tr>
                    </tfoot>
                </table>
            <?php endforeach; ?>

            <p class="submit">
                <button type="submit" name="dfc_fuel_save_schedule" class="button button-primary button-large">
                    <?php echo $has_sched ? 'Update Scheduled Change' : 'Save Scheduled Change'; ?>
                </button>

                <?php if ( $has_sched ) : ?>
                    <span style="margin-left:.5rem;color:#646970;">|</span>
                <?php endif; ?>

                <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=dfc-fuel-center' ) ); ?>">Cancel</a>
            </p>
        </form>

        <template id="dfc-fuel-row-tmpl">
            <?php dfc_fuel_render_tier_row( '__SECTION__', '__INDEX__', [] ); ?>
        </template>

    </div>

    <?php dfc_fuel_admin_styles(); ?>
    <?php dfc_fuel_admin_scripts(); ?>
    <?php
}

/**
 * Render one tier-row inside the schedule editor. Form keys arrive as
 * dfc_fuel_tiers[$section][$i][$col].
 */
function dfc_fuel_render_tier_row( $section, $i, $row ) {
    $cols = [ 'tier_label', 'gallons', 'discount', 'pretax_price', 'aftertax_price' ];
    echo '<tr class="dfc-fuel-row">';
    foreach ( $cols as $col ) {
        $val = $row[ $col ] ?? '';
        printf(
            '<td><input type="text" name="dfc_fuel_tiers[%1$s][%2$s][%3$s]" value="%4$s" class="dfc-fuel-input dfc-fuel-input--%3$s" /></td>',
            esc_attr( $section ),
            esc_attr( $i ),
            esc_attr( $col ),
            esc_attr( $val )
        );
    }
    echo '<td class="dfc-fuel-row-actions"><button type="button" class="button-link dfc-fuel-remove-row" aria-label="Remove row">&times;</button></td>';
    echo '</tr>';
}

/**
 * Read the live ACF data and re-shape it into the same {sections,
 * effective_date} structure the importer/scheduler use. This lets the
 * schedule form pre-fill with current prices so the editor only changes
 * what's different.
 */
function dfc_fuel_build_data_from_live() {
    $out = [
        'effective_date' => get_field( 'fuel_effective_date', 'option' ) ?: wp_date( 'Y-m-d' ),
        'sections'       => [ 'jet' => [], 'avgas' => [] ],
    ];

    foreach ( [ 'jet' => 'jet_fuel_tiers', 'avgas' => 'avgas_tiers' ] as $section_key => $field ) {
        $tiers = get_field( $field, 'option' );
        if ( ! $tiers ) continue;
        foreach ( $tiers as $tier ) {
            $rows = $tier['rows'] ?? [];
            if ( ! $rows ) {
                $out['sections'][ $section_key ][] = [
                    'tier_label'     => $tier['tier_label'] ?? '',
                    'gallons'        => '',
                    'discount'       => '',
                    'pretax_price'   => '',
                    'aftertax_price' => '',
                ];
                continue;
            }
            foreach ( $rows as $row ) {
                $out['sections'][ $section_key ][] = [
                    'tier_label'     => $tier['tier_label'] ?? '',
                    'gallons'        => $row['gallons'] ?? '',
                    'discount'       => $row['discount'] ?? '',
                    'pretax_price'   => $row['pretax_price'] ?? '',
                    'aftertax_price' => $row['aftertax_price'] ?? '',
                ];
            }
        }
    }

    return $out;
}

/**
 * Reshape the submitted schedule form into {sections, effective_date} and
 * persist as a scheduled change.
 */
function dfc_fuel_save_schedule_from_post() {
    if ( empty( $_POST['dfc_fuel_activate_at'] ) ) {
        return new WP_Error( 'no_activate_at', 'Please pick an activation date/time.' );
    }

    $when = dfc_fuel_parse_local_datetime( $_POST['dfc_fuel_activate_at'] );
    if ( is_wp_error( $when ) ) return $when;

    $data = [
        'effective_date' => isset( $_POST['dfc_fuel_effective_date'] ) ? sanitize_text_field( $_POST['dfc_fuel_effective_date'] ) : wp_date( 'Y-m-d' ),
        'sections'       => [ 'jet' => [], 'avgas' => [] ],
    ];

    $raw = $_POST['dfc_fuel_tiers'] ?? [];
    foreach ( [ 'jet', 'avgas' ] as $section_key ) {
        if ( empty( $raw[ $section_key ] ) || ! is_array( $raw[ $section_key ] ) ) continue;
        foreach ( $raw[ $section_key ] as $row ) {
            // Drop completely empty rows so editors can leave blank rows around.
            $row = array_map( 'trim', array_map( 'wp_unslash', $row ) );
            $non_empty = array_filter( $row, fn( $v ) => $v !== '' );
            if ( ! $non_empty ) continue;

            $data['sections'][ $section_key ][] = [
                'tier_label'     => sanitize_text_field( $row['tier_label']     ?? '' ),
                'gallons'        => sanitize_text_field( $row['gallons']        ?? '' ),
                'discount'       => sanitize_text_field( $row['discount']       ?? '' ),
                'pretax_price'   => sanitize_text_field( $row['pretax_price']   ?? '' ),
                'aftertax_price' => sanitize_text_field( $row['aftertax_price'] ?? '' ),
            ];
        }
    }

    if ( empty( $data['sections']['jet'] ) && empty( $data['sections']['avgas'] ) ) {
        return new WP_Error( 'empty_schedule', 'Add at least one row before saving.' );
    }

    dfc_fuel_set_schedule( $data, $when, get_current_user_id() );
    return true;
}


/* ═══════════════════════════════════════════════════════════════════════
 * SCHEDULING — option + WP-Cron
 * ═══════════════════════════════════════════════════════════════════════ */

/**
 * Persist a scheduled change and schedule the WP-Cron event that applies it.
 *
 * @param array $data  Sections + effective_date payload (same shape as the importer expects).
 * @param int   $ts    Unix timestamp at which the change should go live.
 * @param int   $uid   User who saved the schedule (for the audit banner).
 */
function dfc_fuel_set_schedule( array $data, int $ts, int $uid ) {
    // Clear any prior event so we don't end up with duplicate cron entries.
    dfc_fuel_unschedule_cron();

    $payload = [
        'activate_at' => $ts,
        'saved_at'    => time(),
        'saved_by'    => $uid,
        'data'        => $data,
    ];

    update_option( DFC_FUEL_SCHEDULE_OPTION, $payload, false );
    wp_schedule_single_event( $ts, DFC_FUEL_CRON_HOOK );
}

/**
 * Apply the staged change to live data. Returns true on success.
 *
 * Used by:
 *   - The WP-Cron event when the activate_at timestamp arrives
 *   - The "Apply Now" button in the dashboard
 */
function dfc_fuel_apply_scheduled_change() {
    $sched = get_option( DFC_FUEL_SCHEDULE_OPTION );
    if ( ! $sched || empty( $sched['data'] ) ) return false;

    dfc_import_fuel_data( $sched['data'] );

    dfc_fuel_clear_schedule();
    return true;
}

/** Hooked to the WP-Cron event. */
add_action( DFC_FUEL_CRON_HOOK, 'dfc_fuel_apply_scheduled_change' );

/**
 * Belt-and-suspenders fallback for installs where WP-Cron isn't firing
 * on time (or at all). On every admin page load, if a schedule's
 * activate_at is in the past and the cron didn't catch it, apply it now.
 *
 * Cheap — a single get_option call per admin page load.
 */
add_action( 'admin_init', function () {
    $sched = get_option( DFC_FUEL_SCHEDULE_OPTION );
    if ( ! $sched || empty( $sched['activate_at'] ) ) return;
    if ( (int) $sched['activate_at'] > time() ) return;

    dfc_fuel_apply_scheduled_change();
}, 5 );

function dfc_fuel_clear_schedule() {
    delete_option( DFC_FUEL_SCHEDULE_OPTION );
    dfc_fuel_unschedule_cron();
}

function dfc_fuel_unschedule_cron() {
    $next = wp_next_scheduled( DFC_FUEL_CRON_HOOK );
    while ( $next ) {
        wp_unschedule_event( $next, DFC_FUEL_CRON_HOOK );
        $next = wp_next_scheduled( DFC_FUEL_CRON_HOOK );
    }
}

/**
 * Parse a datetime-local input value as a timestamp in the site's
 * timezone. Returns WP_Error if the string is unparseable or in the past.
 */
function dfc_fuel_parse_local_datetime( $value ) {
    $value = trim( (string) $value );
    if ( ! $value ) return new WP_Error( 'no_when', 'Please provide an activation date and time.' );

    // datetime-local format is "YYYY-MM-DDTHH:MM" with no timezone — treat
    // as site time, not UTC.
    try {
        $dt = new DateTime( $value, wp_timezone() );
    } catch ( Exception $e ) {
        return new WP_Error( 'bad_when', 'That date/time format is invalid.' );
    }

    $ts = $dt->getTimestamp();
    if ( $ts <= time() ) {
        return new WP_Error( 'past_when', 'Activation time must be in the future.' );
    }
    return $ts;
}


/* ═══════════════════════════════════════════════════════════════════════
 * IMPORT / EXPORT PAGE
 * ═══════════════════════════════════════════════════════════════════════ */

function dfc_fuel_import_page() {
    if ( ! current_user_can( DFC_CAP_FUEL ) ) {
        wp_die( __( 'You do not have permission to access fuel prices.', 'dfc' ) );
    }

    $message = '';
    $preview_data = null;

    // The CSV preview happens here on the page load — the confirm action
    // is handled by the early admin_init handler so it can redirect.
    if ( isset( $_POST['dfc_fuel_preview'] ) && check_admin_referer( 'dfc_fuel_import' ) ) {
        if ( ! empty( $_FILES['fuel_csv']['tmp_name'] ) && is_uploaded_file( $_FILES['fuel_csv']['tmp_name'] ) ) {
            $preview_data = dfc_parse_fuel_csv( $_FILES['fuel_csv']['tmp_name'] );
            if ( is_wp_error( $preview_data ) ) {
                $message = '<div class="notice notice-error"><p>' . esc_html( $preview_data->get_error_message() ) . '</p></div>';
                $preview_data = null;
            }
        } else {
            $message = '<div class="notice notice-error"><p>Please choose a CSV file before previewing.</p></div>';
        }
    }

    ?>
    <div class="wrap dfc-fuel-wrap">
        <h1>Import / Export Fuel Prices</h1>
        <?php echo $message; // phpcs:ignore — message is already escaped above ?>

        <div class="dfc-fuel-grid">

            <div class="dfc-fuel-card">
                <h2>Upload a CSV</h2>
                <p>Update everything &mdash; effective date, both retail prices, all tiers &mdash; in one shot. The file is previewed first so you can confirm before anything goes live.</p>

                <p>
                    <a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=dfc-fuel-import&dfc_fuel_action=download_template' ), 'dfc_fuel_template' ) ); ?>">
                        Download Blank Template
                    </a>
                    <a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=dfc-fuel-import&dfc_fuel_action=export_current' ), 'dfc_fuel_export' ) ); ?>">
                        Export Current Prices
                    </a>
                </p>

                <form method="post" enctype="multipart/form-data" style="margin-top:1rem;">
                    <?php wp_nonce_field( 'dfc_fuel_import' ); ?>
                    <p>
                        <label for="fuel_csv"><strong>CSV file</strong></label><br>
                        <input type="file" name="fuel_csv" id="fuel_csv" accept=".csv,text/csv" required />
                    </p>
                    <p>
                        <button type="submit" name="dfc_fuel_preview" class="button button-primary">Preview Import</button>
                    </p>
                </form>

                <h3 style="margin-top:1.5rem;">CSV format</h3>
                <p class="description">
                    Columns: <code>section, tier_label, gallons, discount, pretax_price, aftertax_price</code>.
                    Use <code>jet</code> or <code>avgas</code> in <code>section</code>. Prices can be plain numbers (<code>7.87</code>) or include a <code>$</code> &mdash; both work. To set the effective date, add a row with <code>date</code> in the section column and the date (<code>YYYY-MM-DD</code>) in tier_label.
                </p>
            </div>

            <?php if ( $preview_data ) : ?>
                <div class="dfc-fuel-card is-preview">
                    <h2>Preview &mdash; confirm import</h2>
                    <p>Review below, then choose how to apply the change.</p>

                    <?php if ( ! empty( $preview_data['effective_date'] ) ) : ?>
                        <p><strong>Effective date:</strong> <?php echo esc_html( dfc_fuel_format_date( $preview_data['effective_date'], 'F j, Y' ) ); ?></p>
                    <?php endif; ?>

                    <?php foreach ( [ 'jet' => 'Jet Fuel', 'avgas' => 'AvGas' ] as $key => $label ) : ?>
                        <?php if ( ! empty( $preview_data['sections'][ $key ] ) ) : ?>
                            <h3 style="margin-top:1.25rem;"><?php echo esc_html( $label ); ?></h3>
                            <table class="widefat striped">
                                <thead>
                                    <tr>
                                        <th scope="col">Tier</th>
                                        <th scope="col">Gallons</th>
                                        <th scope="col">Discount</th>
                                        <th scope="col">Pre-tax</th>
                                        <th scope="col">After-tax</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ( $preview_data['sections'][ $key ] as $row ) : ?>
                                        <tr>
                                            <td><?php echo esc_html( $row['tier_label'] ); ?></td>
                                            <td><?php echo esc_html( $row['gallons'] ); ?></td>
                                            <td><?php echo esc_html( $row['discount'] ); ?></td>
                                            <td><?php echo esc_html( dfc_fuel_format_price( $row['pretax_price'] ) ); ?></td>
                                            <td><?php echo esc_html( dfc_fuel_format_price( $row['aftertax_price'] ) ); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <form method="post" style="margin-top:1.5rem;">
                        <?php wp_nonce_field( 'dfc_fuel_confirm' ); ?>
                        <input type="hidden" name="csv_data" value="<?php echo esc_attr( wp_json_encode( $preview_data ) ); ?>" />

                        <fieldset style="border:1px solid #c3c4c7;padding:1rem;margin-bottom:1rem;">
                            <legend><strong>How would you like to apply this?</strong></legend>

                            <p>
                                <label>
                                    <input type="radio" name="dfc_fuel_schedule_after_preview" value="" checked />
                                    Import now &mdash; replace live prices immediately.
                                </label>
                            </p>
                            <p>
                                <label>
                                    <input type="radio" name="dfc_fuel_schedule_after_preview" value="1" />
                                    Schedule for later &mdash; hold the change and go live at:
                                </label>
                                <br>
                                <input
                                    type="datetime-local"
                                    name="dfc_fuel_activate_at"
                                    min="<?php echo esc_attr( wp_date( 'Y-m-d\TH:i' ) ); ?>"
                                    style="margin-top:.5rem;margin-left:1.75rem;"
                                />
                                <span class="description" style="display:block;margin-left:1.75rem;margin-top:.25rem;">
                                    Site timezone: <code><?php echo esc_html( wp_timezone_string() ); ?></code>
                                </span>
                            </p>
                        </fieldset>

                        <p>
                            <button type="submit" name="dfc_fuel_confirm_import" class="button button-primary button-large">Confirm</button>
                            <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=dfc-fuel-import' ) ); ?>">Cancel</a>
                        </p>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php dfc_fuel_admin_styles(); ?>
    <?php
}


/* ═══════════════════════════════════════════════════════════════════════
 * CSV PARSER
 *
 * Tolerant of:
 *   - Dollar signs ($7.87 or 7.87)
 *   - Whitespace around values
 *   - Extra columns (only the first six are read)
 *   - The "date" pseudo-row (section=date, tier_label=YYYY-MM-DD)
 *   - Mixed-case section names
 * ═══════════════════════════════════════════════════════════════════════ */

function dfc_parse_fuel_csv( $filepath ) {
    if ( ! is_readable( $filepath ) ) {
        return new WP_Error( 'csv_read', 'Could not read the uploaded file.' );
    }

    $handle = fopen( $filepath, 'r' );
    if ( ! $handle ) {
        return new WP_Error( 'csv_read', 'Could not read the CSV file.' );
    }

    $headers = fgetcsv( $handle );
    if ( ! $headers ) {
        fclose( $handle );
        return new WP_Error( 'csv_format', 'The CSV file appears to be empty.' );
    }

    // Strip UTF-8 BOM from the first cell if present (Excel loves to add it).
    if ( isset( $headers[0] ) ) {
        $headers[0] = preg_replace( '/^\xEF\xBB\xBF/', '', $headers[0] );
    }

    // Normalize headers — lowercase, trim, spaces/hyphens → underscores.
    $headers = array_map( function ( $h ) {
        return strtolower( trim( str_replace( [ ' ', '-' ], '_', (string) $h ) ) );
    }, $headers );

    $required = [ 'section', 'tier_label', 'gallons', 'discount', 'pretax_price', 'aftertax_price' ];
    $missing  = array_diff( $required, $headers );
    if ( $missing ) {
        fclose( $handle );
        return new WP_Error(
            'csv_format',
            'CSV is missing required columns: ' . implode( ', ', $missing ) . '. Expected: ' . implode( ', ', $required ) . '.'
        );
    }

    $data = [
        'effective_date' => wp_date( 'Y-m-d' ),
        'sections'       => [ 'jet' => [], 'avgas' => [] ],
    ];

    while ( ( $row = fgetcsv( $handle ) ) !== false ) {
        if ( ! $row || count( array_filter( $row, fn( $v ) => $v !== null && $v !== '' ) ) === 0 ) continue;

        // Pad row to header length so array_combine doesn't blow up.
        $row = array_pad( $row, count( $headers ), '' );
        $row = array_slice( $row, 0, count( $headers ) );
        $row = array_combine( $headers, $row );

        $section = strtolower( trim( (string) ( $row['section'] ?? '' ) ) );

        // Effective-date pseudo-row.
        if ( $section === 'date' ) {
            $raw = trim( (string) ( $row['tier_label'] ?? '' ) );
            if ( $raw && ( $ts = strtotime( $raw ) ) ) {
                $data['effective_date'] = wp_date( 'Y-m-d', $ts );
            }
            continue;
        }

        if ( ! in_array( $section, [ 'jet', 'avgas' ], true ) ) continue;

        $data['sections'][ $section ][] = [
            'tier_label'     => trim( (string) ( $row['tier_label']     ?? '' ) ),
            'gallons'        => trim( (string) ( $row['gallons']        ?? '' ) ),
            'discount'       => dfc_fuel_normalize_price( $row['discount']       ?? '' ),
            'pretax_price'   => dfc_fuel_normalize_price( $row['pretax_price']   ?? '' ),
            'aftertax_price' => dfc_fuel_normalize_price( $row['aftertax_price'] ?? '' ),
        ];
    }

    fclose( $handle );

    if ( empty( $data['sections']['jet'] ) && empty( $data['sections']['avgas'] ) ) {
        return new WP_Error( 'csv_empty', 'No valid Jet Fuel or AvGas rows were found in the file.' );
    }

    return $data;
}

/**
 * Normalize "$7.87" / " 7.87 " / "7.87" → "7.87" for storage.
 * The display layer adds the $ back; storing it bare keeps round-trips clean.
 */
function dfc_fuel_normalize_price( $val ) {
    $val = trim( (string) $val );
    if ( $val === '' ) return '';
    // Strip currency symbols and surrounding whitespace; leave the number.
    $val = preg_replace( '/[\s\$]/', '', $val );
    return $val;
}

/**
 * Display helper: render a stored price (which may be "7.87" or "$7.87"
 * depending on how it was entered) with a leading $.
 */
function dfc_fuel_format_price( $val ) {
    $val = trim( (string) $val );
    if ( $val === '' ) return '—';
    if ( str_starts_with( $val, '$' ) ) return $val;
    return '$' . $val;
}

/**
 * Display helper: render a stored effective date (always "Y-m-d") as a
 * human-readable label in the site's timezone.
 *
 * IMPORTANT: do NOT do `wp_date( $fmt, strtotime( $date ) )` — strtotime
 * interprets a bare Y-m-d as midnight UTC, and wp_date then shifts that
 * UTC timestamp into the site's local timezone, which for any zone west
 * of UTC produces the *previous calendar day*. (e.g. "2026-06-03" parsed
 * as midnight UTC, then displayed in America/Chicago, becomes "June 02".)
 *
 * The fix is to construct the date *in the site's timezone* in the first
 * place, so the calendar date never changes during conversion.
 *
 * @param string $ymd     A date in Y-m-d format.
 * @param string $format  PHP date format (default: 'F j, Y').
 * @return string         Formatted date, or empty string if the input is unparseable.
 */
function dfc_fuel_format_date( $ymd, $format = 'F j, Y' ) {
    $ymd = trim( (string) $ymd );
    if ( $ymd === '' ) return '';

    // The leading '!' in the format string resets time components to 00:00:00
    // so we get a clean midnight (in the supplied timezone).
    $dt = DateTimeImmutable::createFromFormat( '!Y-m-d', $ymd, wp_timezone() );
    if ( ! $dt ) return '';

    return wp_date( $format, $dt->getTimestamp() );
}


/* ═══════════════════════════════════════════════════════════════════════
 * IMPORT INTO ACF
 * ═══════════════════════════════════════════════════════════════════════ */

function dfc_import_fuel_data( $data ) {
    if ( ! function_exists( 'update_field' ) ) return 'ACF not available.';

    if ( ! empty( $data['effective_date'] ) ) {
        update_field( 'fuel_effective_date', $data['effective_date'], 'option' );
    }

    $counts = [ 'jet' => 0, 'avgas' => 0 ];

    foreach ( [ 'jet' => 'jet_fuel_tiers', 'avgas' => 'avgas_tiers' ] as $section => $field ) {
        if ( empty( $data['sections'][ $section ] ) ) continue;

        // Group consecutive rows by tier_label so the ACF repeater structure
        // (Tier → Volume Levels) matches the flat CSV.
        $tiers = [];
        foreach ( $data['sections'][ $section ] as $row ) {
            $label = $row['tier_label'];
            if ( ! isset( $tiers[ $label ] ) ) {
                $tiers[ $label ] = [ 'tier_label' => $label, 'rows' => [] ];
            }
            $tiers[ $label ]['rows'][] = [
                'gallons'        => $row['gallons'],
                'discount'       => $row['discount'],
                'pretax_price'   => $row['pretax_price'],
                'aftertax_price' => $row['aftertax_price'],
            ];
            $counts[ $section ]++;
        }

        update_field( $field, array_values( $tiers ), 'option' );

        // Mirror the first row's prices into the headline retail fields.
        $first_rows = $data['sections'][ $section ];
        if ( ! empty( $first_rows[0] ) ) {
            $prefix = $section === 'jet' ? 'jet_fuel' : 'avgas';
            update_field( $prefix . '_retail_pretax',   $first_rows[0]['pretax_price'],   'option' );
            update_field( $prefix . '_retail_aftertax', $first_rows[0]['aftertax_price'], 'option' );
        }
    }

    /**
     * Listeners (e.g. cache purge) react to imports.
     */
    do_action( 'dfc_after_fuel_import', $data, $counts );

    return "Imported {$counts['jet']} Jet Fuel rows and {$counts['avgas']} AvGas rows.";
}


/* ═══════════════════════════════════════════════════════════════════════
 * CSV TEMPLATE DOWNLOAD + EXPORT
 *
 * Both run on admin_init (via dfc_fuel_handle_early_actions) so no HTML
 * has been emitted by the time we send Content-Disposition headers.
 * ═══════════════════════════════════════════════════════════════════════ */

function dfc_fuel_download_template() {
    nocache_headers();
    header( 'Content-Type: text/csv; charset=UTF-8' );
    header( 'Content-Disposition: attachment; filename="fuel-prices-template.csv"' );
    header( 'Pragma: no-cache' );
    header( 'Expires: 0' );

    $out = fopen( 'php://output', 'w' );

    // Header row.
    fputcsv( $out, [ 'section', 'tier_label', 'gallons', 'discount', 'pretax_price', 'aftertax_price' ] );

    // Effective-date pseudo-row.
    fputcsv( $out, [ 'date', wp_date( 'Y-m-d' ), '', '', '', '' ] );

    // Example data — bare numbers, no $.
    $rows = [
        [ 'jet',   'Retail Price',          'Any uplift quantity', '0.00', '7.87', '8.40' ],
        [ 'jet',   'Itinerant Customers',   '300-600 gallons',     '0.11', '7.76', '8.28' ],
        [ 'jet',   'Itinerant Customers',   '601-800 gallons',     '0.14', '7.73', '8.25' ],
        [ 'jet',   'Paragon Members',       'Any uplift quantity', '0.40', '7.47', '7.97' ],
        [ 'jet',   'Based Customers',       'Any uplift quantity', '0.50', '7.37', '7.87' ],
        [ 'avgas', 'Retail Price',          '',                    '0.00', '7.38', '7.88' ],
        [ 'avgas', 'AOPA/Phillips CC/Cash', 'Any uplift quantity', '0.05', '7.33', '7.82' ],
        [ 'avgas', 'Self Fuel',             'Any uplift quantity', '1.00', '6.38', '6.81' ],
    ];
    foreach ( $rows as $r ) fputcsv( $out, $r );

    fclose( $out );
}

/**
 * Export currently-stored prices to CSV. Matches the template format exactly,
 * so an export → edit → re-import round-trip is lossless.
 */
function dfc_fuel_export_current() {
    nocache_headers();
    header( 'Content-Type: text/csv; charset=UTF-8' );
    header( 'Content-Disposition: attachment; filename="fuel-prices-' . wp_date( 'Y-m-d' ) . '.csv"' );
    header( 'Pragma: no-cache' );
    header( 'Expires: 0' );

    $data = dfc_fuel_build_data_from_live();

    $out = fopen( 'php://output', 'w' );
    fputcsv( $out, [ 'section', 'tier_label', 'gallons', 'discount', 'pretax_price', 'aftertax_price' ] );
    fputcsv( $out, [ 'date', $data['effective_date'], '', '', '', '' ] );

    foreach ( [ 'jet', 'avgas' ] as $section_key ) {
        foreach ( $data['sections'][ $section_key ] as $row ) {
            fputcsv( $out, [
                $section_key,
                $row['tier_label'],
                $row['gallons'],
                dfc_fuel_normalize_price( $row['discount'] ),
                dfc_fuel_normalize_price( $row['pretax_price'] ),
                dfc_fuel_normalize_price( $row['aftertax_price'] ),
            ] );
        }
    }

    fclose( $out );
}


/* ═══════════════════════════════════════════════════════════════════════
 * CACHE PURGE
 * ═══════════════════════════════════════════════════════════════════════ */

/**
 * Purge caches that serve fuel-pricing content.
 *
 * WP Engine's GES stack has three relevant caches:
 *   1. Varnish / full-page cache — holds rendered HTML for anonymous
 *      visitors. This is what causes stale fuel prices after an
 *      update. `WpeCommon::purge_varnish_cache()` flushes the full
 *      page cache for the site.
 *   2. Memcached / object cache — WP Engine's persistent object
 *      cache. `WpeCommon::purge_memcached()` clears it.
 *   3. CDN (if enabled) — handled by WP Engine's own invalidation
 *      in response to the page cache purge.
 *
 * We also call `wp_cache_flush()` as a belt-and-suspenders for any
 * local object cache (useful when WPE methods are unavailable, e.g.
 * in staging or local dev).
 */
function dfc_purge_fuel_cache() {
    if ( class_exists( 'WpeCommon' ) ) {
        if ( method_exists( 'WpeCommon', 'purge_memcached' ) ) {
            \WpeCommon::purge_memcached();
        }
        if ( method_exists( 'WpeCommon', 'purge_varnish_cache' ) ) {
            // Full-site purge is the safest approach because fuel data
            // appears in multiple places (homepage widget, fuel page,
            // any page using [dfc_fuel_homepage] or [dfc_fuel_full]).
            \WpeCommon::purge_varnish_cache();
        }
    }
    wp_cache_flush();
}

/**
 * Reliable cache-purge trigger.
 *
 * The earlier approach hooked acf/save_post and only purged when
 * get_current_screen() reported the fuel options screen. That guard is
 * fragile: the screen id for an ACF options sub-page varies by ACF version
 * and menu registration, and get_current_screen() isn't available during
 * WP-Cron (scheduled changes) at all. When the guard didn't match, new
 * prices were written to the database while the full-page cache kept
 * serving the old ones — the stale-price symptom.
 *
 * Instead we watch for writes to the fuel option rows themselves. ACF stores
 * options-page fields as wp_options rows named options_<field> (for example
 * options_jet_fuel_tiers, options_avgas_retail_aftertax,
 * options_fuel_effective_date). A write to any of those, from any source,
 * queues a single purge that runs once at shutdown. That covers the ACF
 * "Edit Prices" form, the CSV importer, the scheduled-change cron, "Apply
 * Now", and direct update_field() calls, with no dependence on the current
 * screen or on being in an admin or cron context.
 */
function dfc_fuel_request_purge() {
    static $queued = false;
    if ( $queued ) return;
    $queued = true;
    // Run once, after every option write in this request has completed.
    add_action( 'shutdown', 'dfc_purge_fuel_cache', 99 );
}

function dfc_fuel_watch_option_write( $option ) {
    if ( strpos( (string) $option, 'options_' ) !== 0 ) return;

    $name = substr( $option, 8 ); // strip ACF's "options_" prefix
    if ( strpos( $name, 'fuel' )  === false
        && strpos( $name, 'jet' )   === false
        && strpos( $name, 'avgas' ) === false ) {
        return;
    }

    dfc_fuel_request_purge();
}
add_action( 'added_option',   'dfc_fuel_watch_option_write', 10, 1 );
add_action( 'updated_option', 'dfc_fuel_watch_option_write', 10, 1 );

/** CSV import / scheduled change / "Apply Now" queue the same single purge. */
add_action( 'dfc_after_fuel_import', 'dfc_fuel_request_purge' );


/* ═══════════════════════════════════════════════════════════════════════
 * ADMIN STYLES + SCRIPTS (inline, single-page concern)
 * ═══════════════════════════════════════════════════════════════════════ */

function dfc_fuel_admin_styles() {
    ?>
    <style>
        .dfc-fuel-wrap .dfc-fuel-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 1.25rem;
            margin-top: 1.25rem;
            max-width: 1400px;
        }
        .dfc-fuel-card {
            background: #fff;
            border: 1px solid #c3c4c7;
            border-radius: 6px;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        .dfc-fuel-card h2 {
            margin: 0 0 .75rem;
            font-size: 1.05rem;
            color: #1d2327;
        }
        .dfc-fuel-card.is-scheduled { border-color: #2271b1; box-shadow: 0 0 0 1px #2271b1 inset; }
        .dfc-fuel-card.is-empty p:last-of-type { margin-bottom: 0; }
        .dfc-fuel-card.is-preview { grid-column: 1 / -1; }
        .dfc-fuel-eff { margin: 0 0 1rem; color: #50575e; }
        .dfc-fuel-muted { color: #646970; }
        .dfc-fuel-prices {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem;
            margin-bottom: 1rem;
        }
        .dfc-fuel-price {
            display: flex;
            flex-direction: column;
            padding: .65rem .85rem;
            background: #f6f7f7;
            border-radius: 4px;
            border-left: 3px solid #2271b1;
        }
        .dfc-fuel-label { font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: #646970; }
        .dfc-fuel-val   { font-size: 1.4rem; font-weight: 600; color: #1d2327; line-height: 1.1; margin: .15rem 0; }
        .dfc-fuel-sub   { font-size: 11px; color: #646970; }
        .dfc-fuel-actions { display: flex; flex-wrap: wrap; gap: .5rem; margin: 0; }
        .dfc-fuel-actions form { margin: 0; }
        .dfc-fuel-sched-when { margin: 0 0 .25rem; font-size: 1.05rem; }

        .dfc-fuel-tier-table { margin-bottom: 1.5rem; }
        .dfc-fuel-tier-table .dfc-fuel-input { width: 100%; }
        .dfc-fuel-tier-table .dfc-fuel-row-actions { width: 36px; text-align: center; }
        .dfc-fuel-tier-table .dfc-fuel-remove-row { color: #b32d2e; font-size: 1.4rem; line-height: 1; }
        .dfc-fuel-tier-table .dfc-fuel-remove-row:hover { color: #d63638; }

        @media (max-width: 700px) {
            .dfc-fuel-prices { grid-template-columns: 1fr; }
        }
    </style>
    <?php
}

function dfc_fuel_admin_scripts() {
    ?>
    <script>
    (function () {
        var tmpl = document.getElementById('dfc-fuel-row-tmpl');
        if (!tmpl) return;

        document.querySelectorAll('.dfc-fuel-add-row').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var section = btn.getAttribute('data-section');
                var table   = document.querySelector('.dfc-fuel-tier-table[data-section="' + section + '"] tbody');
                if (!table) return;

                var nextIndex = table.querySelectorAll('tr').length;
                var html = tmpl.innerHTML
                    .replace(/__SECTION__/g, section)
                    .replace(/__INDEX__/g, nextIndex);

                var wrap = document.createElement('tbody');
                wrap.innerHTML = html;
                table.appendChild(wrap.firstElementChild);
            });
        });

        document.addEventListener('click', function (e) {
            if (!e.target.matches('.dfc-fuel-remove-row')) return;
            var row = e.target.closest('tr');
            if (row) row.parentNode.removeChild(row);
        });
    })();
    </script>
    <?php
}


/* ═══════════════════════════════════════════════════════════════════════
 * SHORTCODES — Frontend rendering
 *
 * Unchanged behavior from the prior version; only the admin layer was
 * rebuilt above.
 * ═══════════════════════════════════════════════════════════════════════ */

add_shortcode( 'dfc_fuel_homepage', 'dfc_fuel_homepage_shortcode' );
function dfc_fuel_homepage_shortcode() {
    // Pull AFTER-TAX retail — what customers actually pay at the pump.
    // (Matches the homepage Weather/Info Bar so both widgets agree.)
    $jet_price   = get_field( 'jet_fuel_retail_aftertax', 'option' );
    $avgas_price = get_field( 'avgas_retail_aftertax', 'option' );

    // Fall back to the separate homepage fields if main fields are empty.
    if ( ! $jet_price )   $jet_price   = get_field( 'homepage_jet_a_price', 'option' );
    if ( ! $avgas_price ) $avgas_price = get_field( 'homepage_avgas_price', 'option' );

    $jet_price   = dfc_fuel_format_price( $jet_price );
    $avgas_price = dfc_fuel_format_price( $avgas_price );
    $fuel_page   = get_field( 'fuel_prices_page_link', 'option' ) ?: '/current-fuel-price/';

    ob_start();
    ?>
    <div class="fuel-widget" aria-label="Current retail fuel prices">
        <h3 class="fuel-widget__title">Current Retail Fuel Prices</h3>
        <p class="fuel-widget__price">Jet A: <strong><?php echo esc_html( $jet_price ); ?></strong></p>
        <p class="fuel-widget__price">AvGas: <strong><?php echo esc_html( $avgas_price ); ?></strong></p>
        <a class="fuel-widget__link" href="<?php echo esc_url( $fuel_page ); ?>">View all Fuel Prices</a>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode( 'dfc_fuel_full', 'dfc_fuel_full_shortcode' );
function dfc_fuel_full_shortcode() {
    $effective_date = get_field( 'fuel_effective_date', 'option' );

    ob_start();
    ?>
    <div class="fuel-pricing" aria-label="Fuel price program">
        <?php if ( $effective_date ) : ?>
            <p class="fuel-pricing__date">
                <strong>Effective <?php echo esc_html( dfc_fuel_format_date( $effective_date, 'F d, Y' ) ); ?></strong>
            </p>
        <?php endif; ?>

        <?php
        $sections = [
            'jet_fuel_tiers' => [ 'title' => 'Jet Fuel', 'pretax' => 'jet_fuel_retail_pretax', 'aftertax' => 'jet_fuel_retail_aftertax', 'footnotes' => 'jet_fuel_footnotes' ],
            'avgas_tiers'    => [ 'title' => 'AvGas',    'pretax' => 'avgas_retail_pretax',    'aftertax' => 'avgas_retail_aftertax',    'footnotes' => 'avgas_footnotes' ],
        ];

        foreach ( $sections as $field => $meta ) :
            $tiers = get_field( $field, 'option' );
            if ( ! $tiers ) continue;
            ?>
            <div class="fuel-pricing__section">
                <table class="fuel-pricing__table">
                    <caption class="fuel-pricing__caption"><?php echo esc_html( $meta['title'] ); ?></caption>
                    <thead>
                        <tr>
                            <th scope="col"></th>
                            <th scope="col">Gallons</th>
                            <th scope="col">Discount off Posted Retail</th>
                            <th scope="col">Pre Tax Price</th>
                            <th scope="col">After Tax Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $tiers as $tier ) :
                            $rows = $tier['rows'] ?? [];
                            $first = true;
                            foreach ( $rows as $row ) : ?>
                                <tr>
                                    <?php if ( $first ) : ?>
                                        <th scope="row" rowspan="<?php echo count( $rows ); ?>">
                                            <strong><?php echo esc_html( $tier['tier_label'] ); ?></strong>
                                        </th>
                                    <?php endif; ?>
                                    <td><?php echo esc_html( $row['gallons'] ); ?></td>
                                    <td><?php echo esc_html( dfc_fuel_format_price( $row['discount'] ) ); ?></td>
                                    <td><?php echo esc_html( dfc_fuel_format_price( $row['pretax_price'] ) ); ?></td>
                                    <td><?php echo esc_html( dfc_fuel_format_price( $row['aftertax_price'] ) ); ?></td>
                                </tr>
                            <?php
                            $first = false;
                            endforeach;
                        endforeach; ?>
                    </tbody>
                </table>

                <?php
                $footnotes = get_field( $meta['footnotes'], 'option' );
                if ( $footnotes ) : ?>
                    <p class="fuel-pricing__footnotes"><?php echo wp_kses_post( $footnotes ); ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <p class="fuel-pricing__disclaimer">Prices subject to change without notice. After tax price is rounded (includes 6.75% State Tax).</p>
    </div>
    <?php
    return ob_get_clean();
}


/* ═══════════════════════════════════════════════════════════════════════
 * THEME DEACTIVATION CLEANUP
 * ═══════════════════════════════════════════════════════════════════════ */

add_action( 'switch_theme', function () {
    // Clear any pending cron event tied to our theme. The option itself
    // stays in case the theme is re-activated — restoring a previously
    // staged change.
    if ( function_exists( 'dfc_fuel_unschedule_cron' ) ) {
        dfc_fuel_unschedule_cron();
    }
} );
