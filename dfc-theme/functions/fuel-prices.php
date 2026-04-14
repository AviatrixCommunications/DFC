<?php
/**
 * DFC Fuel Prices — ACF Options Page, CSV Import, Shortcodes
 *
 * @package DFC
 */

// ── ACF Options Pages ─────────────────────────────────────────
add_action( 'acf/init', 'dfc_fuel_options_page' );
function dfc_fuel_options_page() {
    if ( ! function_exists( 'acf_add_options_page' ) ) return;

    acf_add_options_page( [
        'page_title' => 'Fuel Prices',
        'menu_title' => 'Fuel Prices',
        'menu_slug'  => 'dfc-fuel-prices',
        'capability' => 'edit_posts',
        'icon_url'   => 'dashicons-chart-bar',
        'position'   => 25,
        'redirect'   => false,
    ] );
}

// CSV Import — registered late so ACF's parent page exists first
add_action( 'admin_menu', 'dfc_fuel_import_menu', 99 );
function dfc_fuel_import_menu() {
    add_submenu_page(
        'dfc-fuel-prices',
        'Import Fuel Prices',
        'CSV Import',
        'manage_options',
        'dfc-fuel-import',
        'dfc_fuel_import_page'
    );
}

// ── CSV Import Admin Page ─────────────────────────────────────
function dfc_fuel_import_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $message = '';
    $preview_data = null;

    // Handle template download
    if ( isset( $_GET['dfc_fuel_download_template'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'dfc_fuel_template' ) ) {
        dfc_fuel_download_template();
        exit;
    }

    // Handle CSV upload for preview
    if ( isset( $_POST['dfc_fuel_preview'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'dfc_fuel_import' ) ) {
        if ( ! empty( $_FILES['fuel_csv']['tmp_name'] ) ) {
            $preview_data = dfc_parse_fuel_csv( $_FILES['fuel_csv']['tmp_name'] );
            if ( is_wp_error( $preview_data ) ) {
                $message = '<div class="notice notice-error"><p>' . esc_html( $preview_data->get_error_message() ) . '</p></div>';
                $preview_data = null;
            }
        } else {
            $message = '<div class="notice notice-error"><p>Please select a CSV file.</p></div>';
        }
    }

    // Handle confirmed import
    if ( isset( $_POST['dfc_fuel_confirm_import'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'dfc_fuel_confirm' ) ) {
        $csv_data = json_decode( stripslashes( $_POST['csv_data'] ), true );
        // Validate structure before importing
        if ( $csv_data && isset( $csv_data['sections'] ) && is_array( $csv_data['sections'] ) ) {
            $valid = true;
            foreach ( $csv_data['sections'] as $section_key => $rows ) {
                if ( ! in_array( $section_key, [ 'jet', 'avgas' ], true ) ) {
                    $valid = false;
                    break;
                }
                if ( ! is_array( $rows ) ) {
                    $valid = false;
                    break;
                }
            }
            if ( $valid ) {
                $result = dfc_import_fuel_data( $csv_data );
                $message = '<div class="notice notice-success"><p>Fuel prices imported successfully. ' . esc_html( $result ) . '</p></div>';
            } else {
                $message = '<div class="notice notice-error"><p>Invalid import data structure. Please try again.</p></div>';
            }
        }
    }

    ?>
    <div class="wrap">
        <h1>Import Fuel Prices</h1>
        <?php echo $message; ?>

        <div style="display:flex;gap:2rem;margin-top:1rem;">
            <div style="flex:1;max-width:600px;">
                <div class="card" style="padding:1.5rem;">
                    <h2 style="margin-top:0;">Upload CSV</h2>
                    <p>Upload a CSV file to update fuel prices. Use the template below for the correct format.</p>

                    <p>
                        <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=dfc-fuel-import&dfc_fuel_download_template=1' ), 'dfc_fuel_template' ); ?>"
                           class="button">
                            Download CSV Template
                        </a>
                    </p>

                    <form method="post" enctype="multipart/form-data">
                        <?php wp_nonce_field( 'dfc_fuel_import' ); ?>
                        <p>
                            <label for="fuel_csv"><strong>CSV File:</strong></label><br>
                            <input type="file" name="fuel_csv" id="fuel_csv" accept=".csv" />
                        </p>
                        <p>
                            <input type="submit" name="dfc_fuel_preview" class="button button-primary" value="Preview Import" />
                        </p>
                    </form>
                </div>

                <div class="card" style="padding:1.5rem;margin-top:1rem;">
                    <h2 style="margin-top:0;">Manual Editing</h2>
                    <p>You can also edit fuel prices directly in the ACF fields:</p>
                    <p><a href="<?php echo admin_url( 'admin.php?page=dfc-fuel-prices' ); ?>" class="button">Edit Fuel Prices Manually</a></p>
                </div>
            </div>

            <?php if ( $preview_data ) : ?>
            <div style="flex:1;">
                <div class="card" style="padding:1.5rem;">
                    <h2 style="margin-top:0;color:#2271b1;">Preview — Confirm Import</h2>
                    <p>Review the data below before importing.</p>

                    <?php if ( ! empty( $preview_data['effective_date'] ) ) : ?>
                        <p><strong>Effective Date:</strong> <?php echo esc_html( $preview_data['effective_date'] ); ?></p>
                    <?php endif; ?>

                    <?php foreach ( ['jet' => 'Jet Fuel', 'avgas' => 'AvGas'] as $key => $label ) : ?>
                        <?php if ( ! empty( $preview_data['sections'][$key] ) ) : ?>
                            <h3><?php echo esc_html( $label ); ?></h3>
                            <table class="widefat striped" style="margin-bottom:1rem;">
                                <thead>
                                    <tr>
                                        <th>Tier</th>
                                        <th>Gallons</th>
                                        <th>Discount</th>
                                        <th>Pre-Tax</th>
                                        <th>After-Tax</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ( $preview_data['sections'][$key] as $row ) : ?>
                                    <tr>
                                        <td><?php echo esc_html( $row['tier_label'] ); ?></td>
                                        <td><?php echo esc_html( $row['gallons'] ); ?></td>
                                        <td><?php echo esc_html( $row['discount'] ); ?></td>
                                        <td><?php echo esc_html( $row['pretax_price'] ); ?></td>
                                        <td><?php echo esc_html( $row['aftertax_price'] ); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <form method="post">
                        <?php wp_nonce_field( 'dfc_fuel_confirm' ); ?>
                        <input type="hidden" name="csv_data" value="<?php echo esc_attr( wp_json_encode( $preview_data ) ); ?>" />
                        <p>
                            <input type="submit" name="dfc_fuel_confirm_import" class="button button-primary" value="Confirm & Import" />
                            <a href="<?php echo admin_url( 'admin.php?page=dfc-fuel-import' ); ?>" class="button">Cancel</a>
                        </p>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

// ── CSV Parser ────────────────────────────────────────────────
function dfc_parse_fuel_csv( $filepath ) {
    $handle = fopen( $filepath, 'r' );
    if ( ! $handle ) {
        return new WP_Error( 'csv_read', 'Could not read the CSV file.' );
    }

    $headers = fgetcsv( $handle );
    if ( ! $headers || count( $headers ) < 5 ) {
        fclose( $handle );
        return new WP_Error( 'csv_format', 'Invalid CSV format. Expected columns: section, tier_label, gallons, discount, pretax_price, aftertax_price' );
    }

    // Normalize headers
    $headers = array_map( function( $h ) {
        return strtolower( trim( str_replace( [' ', '-'], '_', $h ) ) );
    }, $headers );

    $data = [
        'effective_date' => wp_date( 'Y-m-d' ),
        'sections'       => [ 'jet' => [], 'avgas' => [] ],
    ];

    while ( ( $row = fgetcsv( $handle ) ) !== false ) {
        if ( count( $row ) < 5 ) continue;
        $row = array_combine( array_slice( $headers, 0, count( $row ) ), $row );

        // Check for effective date row
        if ( isset( $row['section'] ) && strtolower( trim( $row['section'] ) ) === 'date' ) {
            $data['effective_date'] = trim( $row['tier_label'] ?? wp_date( 'Y-m-d' ) );
            continue;
        }

        $section = strtolower( trim( $row['section'] ?? '' ) );
        if ( ! in_array( $section, [ 'jet', 'avgas' ] ) ) continue;

        $data['sections'][$section][] = [
            'tier_label'     => trim( $row['tier_label'] ?? '' ),
            'gallons'        => trim( $row['gallons'] ?? '' ),
            'discount'       => trim( $row['discount'] ?? '' ),
            'pretax_price'   => trim( $row['pretax_price'] ?? '' ),
            'aftertax_price' => trim( $row['aftertax_price'] ?? '' ),
        ];
    }

    fclose( $handle );
    return $data;
}

// ── Import into ACF ───────────────────────────────────────────
function dfc_import_fuel_data( $data ) {
    if ( ! function_exists( 'update_field' ) ) return 'ACF not available.';

    // Update effective date
    if ( ! empty( $data['effective_date'] ) ) {
        update_field( 'fuel_effective_date', $data['effective_date'], 'option' );
    }

    $counts = [ 'jet' => 0, 'avgas' => 0 ];

    foreach ( [ 'jet' => 'jet_fuel_tiers', 'avgas' => 'avgas_tiers' ] as $section => $field ) {
        if ( empty( $data['sections'][$section] ) ) continue;

        // Group rows by tier_label
        $tiers = [];
        foreach ( $data['sections'][$section] as $row ) {
            $label = $row['tier_label'];
            if ( ! isset( $tiers[$label] ) ) {
                $tiers[$label] = [
                    'tier_label' => $label,
                    'rows'       => [],
                ];
            }
            $tiers[$label]['rows'][] = [
                'gallons'        => $row['gallons'],
                'discount'       => $row['discount'],
                'pretax_price'   => $row['pretax_price'],
                'aftertax_price' => $row['aftertax_price'],
            ];
            $counts[$section]++;
        }

        update_field( $field, array_values( $tiers ), 'option' );

        // Set retail prices from first row
        $first_rows = $data['sections'][$section];
        if ( ! empty( $first_rows[0] ) ) {
            $prefix = $section === 'jet' ? 'jet_fuel' : 'avgas';
            update_field( $prefix . '_retail_pretax', $first_rows[0]['pretax_price'], 'option' );
            update_field( $prefix . '_retail_aftertax', $first_rows[0]['aftertax_price'], 'option' );
        }
    }

    return "Imported {$counts['jet']} Jet Fuel rows and {$counts['avgas']} AvGas rows.";
}

// ── CSV Template Download ─────────────────────────────────────
function dfc_fuel_download_template() {
    header( 'Content-Type: text/csv' );
    header( 'Content-Disposition: attachment; filename="fuel-prices-template.csv"' );

    $output = fopen( 'php://output', 'w' );
    fputcsv( $output, [ 'section', 'tier_label', 'gallons', 'discount', 'pretax_price', 'aftertax_price' ] );
    fputcsv( $output, [ 'date', wp_date( 'Y-m-d' ), '', '', '', '' ] );
    fputcsv( $output, [ 'jet', 'Retail Price', 'Any uplift quantity', '$0.00', '$7.87', '$8.40' ] );
    fputcsv( $output, [ 'jet', 'Itinerant Customers', '300-600 gallons', '$0.11', '$7.76', '$8.28' ] );
    fputcsv( $output, [ 'jet', 'Itinerant Customers', '601-800 gallons', '$0.14', '$7.73', '$8.25' ] );
    fputcsv( $output, [ 'jet', 'Paragon Members', 'Any uplift quantity', '$0.40', '$7.47', '$7.97' ] );
    fputcsv( $output, [ 'jet', 'Based Customers', 'Any uplift quantity', '$0.50', '$7.37', '$7.87' ] );
    fputcsv( $output, [ 'avgas', 'Retail Price', '', '$0.00', '$7.38', '$7.88' ] );
    fputcsv( $output, [ 'avgas', 'AOPA/Phillips CC/Cash', 'Any uplift quantity', '$0.05', '$7.33', '$7.82' ] );
    fputcsv( $output, [ 'avgas', 'Self Fuel', 'Any uplift quantity', '$1.00', '$6.38', '$6.81' ] );
    fclose( $output );
}

// ── Shortcode: Homepage widget ────────────────────────────────
add_shortcode( 'dfc_fuel_homepage', 'dfc_fuel_homepage_shortcode' );
function dfc_fuel_homepage_shortcode() {
    // Pull directly from the main fuel pricing fields — no need to enter twice
    $jet_price   = get_field( 'jet_fuel_retail_pretax', 'option' );
    $avgas_price = get_field( 'avgas_retail_pretax', 'option' );
    
    // Fall back to the separate homepage fields if main fields are empty
    if ( ! $jet_price ) $jet_price = get_field( 'homepage_jet_a_price', 'option' );
    if ( ! $avgas_price ) $avgas_price = get_field( 'homepage_avgas_price', 'option' );
    
    $jet_price   = $jet_price ?: '—';
    $avgas_price = $avgas_price ?: '—';
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

// ── Shortcode: Full pricing tables ────────────────────────────
add_shortcode( 'dfc_fuel_full', 'dfc_fuel_full_shortcode' );
function dfc_fuel_full_shortcode() {
    $effective_date = get_field( 'fuel_effective_date', 'option' );

    ob_start();
    ?>
    <div class="fuel-pricing" aria-label="Fuel price program">
        <?php if ( $effective_date ) : ?>
            <p class="fuel-pricing__date">
                <strong>Effective <?php echo esc_html( wp_date( 'F d, Y', strtotime( $effective_date ) ) ); ?></strong>
            </p>
        <?php endif; ?>

        <?php
        $sections = [
            'jet_fuel_tiers' => [ 'title' => 'Jet Fuel', 'pretax' => 'jet_fuel_retail_pretax', 'aftertax' => 'jet_fuel_retail_aftertax', 'footnotes' => 'jet_fuel_footnotes' ],
            'avgas_tiers'    => [ 'title' => 'AvGas', 'pretax' => 'avgas_retail_pretax', 'aftertax' => 'avgas_retail_aftertax', 'footnotes' => 'avgas_footnotes' ],
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
                                    <td><?php echo esc_html( $row['discount'] ); ?></td>
                                    <td><?php echo esc_html( $row['pretax_price'] ); ?></td>
                                    <td><?php echo esc_html( $row['aftertax_price'] ); ?></td>
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
