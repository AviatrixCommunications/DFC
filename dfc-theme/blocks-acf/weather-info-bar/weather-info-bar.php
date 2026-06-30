<?php
/**
 * Weather & Info Bar Block
 *
 * Three-column strip below the homepage hero:
 *  1. Weather (grey bg) — populated via JS from Weatherbit.io REST endpoint
 *  2. Current Fuel Prices (black bg) — from ACF options
 *  3. Request for Services (red bg) — static content with CTA
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML.
 * @param bool   $is_preview True during AJAX preview.
 */

// Pull AFTER-TAX retail (what customers actually pay at the pump). Per client
// request 6/3/2026 — info-bar previously showed pre-tax which confused visitors
// comparing the homepage to the posted pump price.
// dfc_fuel_format_price() ensures a leading $ whether the stored value is
// "7.53" (bare, from CSV import) or "$7.53" (from manual ACF entry).
$jet_raw   = function_exists('get_field') ? ( get_field( 'jet_fuel_retail_aftertax', 'option' ) ?: get_field( 'homepage_jet_a_price', 'option' ) ) : '5.60';
$avgas_raw = function_exists('get_field') ? ( get_field( 'avgas_retail_aftertax', 'option' ) ?: get_field( 'homepage_avgas_price', 'option' ) ) : '6.40';

$jet_price   = function_exists('dfc_fuel_format_price') ? dfc_fuel_format_price( $jet_raw )   : ( $jet_raw   ?: '—' );
$avgas_price = function_exists('dfc_fuel_format_price') ? dfc_fuel_format_price( $avgas_raw ) : ( $avgas_raw ?: '—' );

$fuel_page   = function_exists('get_field') ? get_field( 'fuel_prices_page_link', 'option' ) : '/current-fuel-price/';

// Editable column labels (block-level fields, with safe defaults)
$weather_label  = get_field( 'weather_label' )   ?: 'DuPage Airport Weather';
$fuel_title     = get_field( 'fuel_title' )      ?: 'Current Retail Fuel Prices';
$fuel_link_text = get_field( 'fuel_link_text' )  ?: 'View all Fuel Prices';
$services_title = get_field( 'services_title' )  ?: 'Request for Services';
$services_text  = get_field( 'services_description' ) ?: 'Utilize the link below to access our FlightBridge booking page.';
$services_link  = get_field( 'services_link' )   ?: ['url' => 'https://flightbridge.com/go/DuPage', 'title' => 'Make a Reservation', 'target' => '_blank'];

$base_class = 'aviatrix-block aviatrix-block--weather-info-bar';
$attrs = get_block_wrapper_attributes( [ 'class' => $base_class ] );
?>

<section <?php echo $attrs; ?> aria-label="Airport information">
    <div class="info-bar">

        <!-- Column 1: Weather -->
        <section class="info-bar__col info-bar__col--weather weather-widget" aria-label="Current airport weather">
            <div class="weather-widget__badge">
                <span class="weather-widget__badge-icon">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/></svg>
                </span>
                <span class="weather-widget__label"><?php echo esc_html( $weather_label ); ?></span>
            </div>
            <div class="weather-widget__data">
                <span class="weather-widget__temp"><span class="u-sr-only">Temperature: </span><span class="u"></span>--°F</span>
                <span class="weather-widget__icon-condition">
                    <span class="weather-widget__icon">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor" width="41" height="41"><circle cx="12" cy="12" r="5"/></svg>
                    </span>
                    <span class="weather-widget__condition">Loading...</span>
                </span>
            </div>
            <div class="weather-widget__meta">
                <span class="weather-widget__feels">Feels like --°</span>
                <span class="weather-widget__date"><?php echo wp_date( 'l, F j, Y' ); ?></span>
            </div>
        </section>

        <!-- Column 2: Fuel Prices -->
        <section class="info-bar__col info-bar__col--fuel" aria-label="Current retail fuel prices">
            <h3 class="info-bar__title"><?php echo esc_html( $fuel_title ); ?></h3>
            <div class="info-bar__fuel-prices">
                <p class="info-bar__fuel-price">Jet A: <strong><?php echo esc_html( $jet_price ); ?></strong></p>
                <p class="info-bar__fuel-price">AvGas: <strong><?php echo esc_html( $avgas_price ); ?></strong></p>
            </div>
            <?php if ( $fuel_page ) : ?>
                <a href="<?php echo esc_url( $fuel_page ); ?>" class="info-bar__link"><?php echo esc_html( $fuel_link_text ); ?></a>
            <?php endif; ?>
        </section>

        <!-- Column 3: Request for Services -->
        <section class="info-bar__col info-bar__col--services" aria-label="Request for services">
            <h3 class="info-bar__title"><?php echo esc_html( $services_title ); ?></h3>
            <p class="info-bar__desc"><?php echo esc_html( $services_text ); ?></p>
            <?php if ( $services_link ) : ?>
                <a href="<?php echo esc_url( $services_link['url'] ); ?>"
                   class="info-bar__link"
                   <?php if ( ! empty( $services_link['target'] ) ) : ?>target="<?php echo esc_attr( $services_link['target'] ); ?>" rel="noopener noreferrer"<?php endif; ?>>
                    <?php echo esc_html( $services_link['title'] ?: 'Make a Reservation' ); ?><?php if ( ! empty( $services_link['target'] ) && $services_link['target'] === '_blank' ) : ?><span class="u-sr-only"> (opens in new tab)</span><?php endif; ?>
                </a>
            <?php endif; ?>
        </section>

    </div>
</section>
