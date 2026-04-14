<?php
/**
 * DFC Weather API — Weatherbit.io integration
 *
 * Admin settings page + cached REST endpoint for frontend JS.
 *
 * @package DFC
 */

// ── Admin Settings Page ───────────────────────────────────────
add_action( 'admin_menu', 'dfc_weather_admin_menu' );
function dfc_weather_admin_menu() {
    add_options_page(
        'DFC Weather Settings',
        'DFC Weather',
        'manage_options',
        'dfc-weather',
        'dfc_weather_settings_page'
    );
}

add_action( 'admin_init', 'dfc_weather_register_settings' );
function dfc_weather_register_settings() {
    register_setting( 'dfc_weather_options', 'dfc_weather_api_key', [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    register_setting( 'dfc_weather_options', 'dfc_weather_lat', [
        'type'    => 'string',
        'default' => '41.9078',
    ] );
    register_setting( 'dfc_weather_options', 'dfc_weather_lon', [
        'type'    => 'string',
        'default' => '-88.2484',
    ] );
    register_setting( 'dfc_weather_options', 'dfc_weather_units', [
        'type'    => 'string',
        'default' => 'I',
    ] );
    register_setting( 'dfc_weather_options', 'dfc_weather_cache_minutes', [
        'type'    => 'integer',
        'default' => 30,
    ] );
    register_setting( 'dfc_weather_options', 'dfc_weather_enabled', [
        'type'    => 'boolean',
        'default' => true,
    ] );
}

function dfc_weather_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    ?>
    <div class="wrap">
        <h1>DFC Weather Settings</h1>
        <p>Configure the Weatherbit.io API integration for the homepage weather widget.</p>
        <form method="post" action="options.php">
            <?php settings_fields( 'dfc_weather_options' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="dfc_weather_api_key">API Key</label></th>
                    <td>
                        <input type="password" id="dfc_weather_api_key" name="dfc_weather_api_key"
                               value="<?php echo esc_attr( get_option( 'dfc_weather_api_key' ) ); ?>"
                               class="regular-text" autocomplete="off" />
                        <p class="description">Your Weatherbit.io API key.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="dfc_weather_lat">Latitude</label></th>
                    <td>
                        <input type="text" id="dfc_weather_lat" name="dfc_weather_lat"
                               value="<?php echo esc_attr( get_option( 'dfc_weather_lat', '41.9078' ) ); ?>"
                               class="small-text" />
                        <p class="description">DuPage Airport default: 41.9078</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="dfc_weather_lon">Longitude</label></th>
                    <td>
                        <input type="text" id="dfc_weather_lon" name="dfc_weather_lon"
                               value="<?php echo esc_attr( get_option( 'dfc_weather_lon', '-88.2484' ) ); ?>"
                               class="small-text" />
                        <p class="description">DuPage Airport default: -88.2484</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="dfc_weather_units">Units</label></th>
                    <td>
                        <select id="dfc_weather_units" name="dfc_weather_units">
                            <option value="I" <?php selected( get_option( 'dfc_weather_units', 'I' ), 'I' ); ?>>Imperial (°F)</option>
                            <option value="M" <?php selected( get_option( 'dfc_weather_units', 'I' ), 'M' ); ?>>Metric (°C)</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="dfc_weather_cache_minutes">Cache (minutes)</label></th>
                    <td>
                        <input type="number" id="dfc_weather_cache_minutes" name="dfc_weather_cache_minutes"
                               value="<?php echo esc_attr( get_option( 'dfc_weather_cache_minutes', 30 ) ); ?>"
                               class="small-text" min="5" max="120" />
                        <p class="description">How long to cache weather data. Default: 30 minutes.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Enable Weather Widget</th>
                    <td>
                        <label>
                            <input type="checkbox" name="dfc_weather_enabled" value="1"
                                   <?php checked( get_option( 'dfc_weather_enabled', true ) ); ?> />
                            Show weather on homepage
                        </label>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// ── REST Endpoint ─────────────────────────────────────────────
add_action( 'rest_api_init', function () {
    register_rest_route( 'dfc/v1', '/weather', [
        'methods'             => 'GET',
        'callback'            => 'dfc_weather_endpoint',
        'permission_callback' => '__return_true',
    ] );
} );

function dfc_weather_endpoint() {
    if ( ! get_option( 'dfc_weather_enabled', true ) ) {
        return new WP_REST_Response( [ 'enabled' => false ], 200 );
    }

    $api_key = get_option( 'dfc_weather_api_key' );
    if ( ! $api_key ) {
        return new WP_REST_Response( [ 'error' => 'API key not configured' ], 500 );
    }

    // Check transient cache
    $cache_key = 'dfc_weather_data';
    $cached    = get_transient( $cache_key );
    if ( $cached !== false ) {
        return new WP_REST_Response( $cached, 200 );
    }

    // Fetch from Weatherbit
    $lat   = get_option( 'dfc_weather_lat', '41.9078' );
    $lon   = get_option( 'dfc_weather_lon', '-88.2484' );
    $units = get_option( 'dfc_weather_units', 'I' );

    $url = add_query_arg( [
        'lat'   => $lat,
        'lon'   => $lon,
        'units' => $units,
        'key'   => $api_key,
    ], 'https://api.weatherbit.io/v2.0/current' );

    $response = wp_remote_get( $url, [ 'timeout' => 10 ] );

    if ( is_wp_error( $response ) ) {
        return new WP_REST_Response( [ 'error' => 'Weather service unavailable' ], 503 );
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( empty( $body['data'][0] ) ) {
        return new WP_REST_Response( [ 'error' => 'Invalid weather response' ], 502 );
    }

    $w = $body['data'][0];
    $unit_suffix = $units === 'I' ? '°F' : '°C';

    $data = [
        'temp'        => round( $w['temp'] ) . $unit_suffix,
        'feels_like'  => round( $w['app_temp'] ) . '°',
        'description' => $w['weather']['description'] ?? 'N/A',
        'icon'        => $w['weather']['icon'] ?? '',
        'date'        => wp_date( 'l, F j, Y' ),
        'enabled'     => true,
    ];

    // Cache it
    $cache_minutes = (int) get_option( 'dfc_weather_cache_minutes', 30 );
    set_transient( $cache_key, $data, $cache_minutes * MINUTE_IN_SECONDS );

    return new WP_REST_Response( $data, 200 );
}
