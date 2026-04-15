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

$jet_price   = function_exists('get_field') ? ( get_field( 'jet_fuel_retail_pretax', 'option' ) ?: get_field( 'homepage_jet_a_price', 'option' ) ) : '$5.60';
$avgas_price = function_exists('get_field') ? ( get_field( 'avgas_retail_pretax', 'option' ) ?: get_field( 'homepage_avgas_price', 'option' ) ) : '$6.40';
$fuel_page   = function_exists('get_field') ? get_field( 'fuel_prices_page_link', 'option' ) : '/current-fuel-price/';
$services_text = get_field( 'services_description' ) ?: 'Utilize the link below to access our FlightBridge booking page.';
$services_link = get_field( 'services_link' ) ?: ['url' => 'https://flightbridge.com/go/DuPage', 'title' => 'Make a Reservation', 'target' => '_blank'];

$base_class = 'aviatrix-block aviatrix-block--weather-info-bar';
$attrs = get_block_wrapper_attributes( [ 'class' => $base_class ] );
?>

<section <?php echo $attrs; ?> aria-label="Airport information">
    <div class="info-bar">

        <!-- Column 1: Weather -->
        <div class="info-bar__col info-bar__col--weather weather-widget" aria-label="Current airport weather">
            <div class="weather-widget__badge">
                <span class="weather-widget__badge-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/></svg>
                </span>
                <span class="weather-widget__label">DuPage Airport Weather</span>
            </div>
            <div class="weather-widget__data">
                <span class="weather-widget__temp" aria-label="Temperature">--°F</span>
                <span class="weather-widget__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="41" height="41"><circle cx="12" cy="12" r="5"/></svg>
                </span>
                <span class="weather-widget__condition">Loading...</span>
            </div>
            <div class="weather-widget__meta">
                <span class="weather-widget__feels">Feels like --°</span>
                <span class="weather-widget__date"><?php echo wp_date( 'l, F j, Y' ); ?></span>
            </div>
        </div>

        <!-- Column 2: Fuel Prices -->
        <div class="info-bar__col info-bar__col--fuel" aria-label="Current retail fuel prices">
            <h3 class="info-bar__title">Current Retail Fuel Prices</h3>
            <div class="info-bar__fuel-prices">
                <p class="info-bar__fuel-price">Jet A: <strong><?php echo esc_html( $jet_price ); ?></strong></p>
                <p class="info-bar__fuel-price">AvGas: <strong><?php echo esc_html( $avgas_price ); ?></strong></p>
            </div>
            <?php if ( $fuel_page ) : ?>
                <a href="<?php echo esc_url( $fuel_page ); ?>" class="info-bar__link">View all Fuel Prices</a>
            <?php endif; ?>
        </div>

        <!-- Column 3: Request for Services -->
        <div class="info-bar__col info-bar__col--services" aria-label="Request for services">
            <h3 class="info-bar__title">Request for Services</h3>
            <p class="info-bar__desc"><?php echo esc_html( $services_text ); ?></p>
            <?php if ( $services_link ) : ?>
                <a href="<?php echo esc_url( $services_link['url'] ); ?>"
                   class="info-bar__link"
                   <?php if ( ! empty( $services_link['target'] ) ) : ?>target="<?php echo esc_attr( $services_link['target'] ); ?>" rel="noopener noreferrer"<?php endif; ?>>
                    <?php echo esc_html( $services_link['title'] ?: 'Make a Reservation' ); ?><?php if ( ! empty( $services_link['target'] ) && $services_link['target'] === '_blank' ) : ?><span class="u-sr-only"> (opens in new tab)</span><?php endif; ?>
                </a>
            <?php endif; ?>
        </div>

    </div>
</section>
