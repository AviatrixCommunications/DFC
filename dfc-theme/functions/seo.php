<?php
/**
 * DFC SEO — Rank Math Pro Compatibility + Schema
 *
 * @package DFC
 */

// ── Rank Math breadcrumb support ──────────────────────────────
add_theme_support( 'rank-math-breadcrumbs' );

// ── LocalBusiness Schema (outputs on homepage) ────────────────
// Rank Math handles most schema, but we add FBO-specific LocalBusiness
// data that Rank Math may not cover automatically.

add_action( 'wp_head', 'dfc_local_business_schema' );
function dfc_local_business_schema() {
    if ( ! is_front_page() ) return;

    $address   = function_exists('get_field') ? get_field( 'airport_address', 'option' ) : '2700 International Drive, West Chicago, IL 60185';
    $phone     = function_exists('get_field') ? get_field( 'footer_phone', 'option' ) : '(630) 208-5600';
    $toll_free = function_exists('get_field') ? get_field( 'footer_toll_free', 'option' ) : '(800) 208-5690';
    $email     = function_exists('get_field') ? get_field( 'footer_email', 'option' ) : 'dfcfuel@dupageflightcenter.com';

    $schema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'LocalBusiness',
        'name'        => 'DuPage Flight Center',
        'description' => 'Premier FBO at DuPage Airport (DPA) offering executive aviation services, fuel, aircraft handling, and passenger amenities 24/7.',
        'url'         => home_url( '/' ),
        'telephone'   => '+1' . preg_replace( '/\D/', '', $phone ),
        'email'       => $email,
        'address'     => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => '2700 International Drive',
            'addressLocality' => 'West Chicago',
            'addressRegion'   => 'IL',
            'postalCode'      => '60185',
            'addressCountry'  => 'US',
        ],
        'geo' => [
            '@type'     => 'GeoCoordinates',
            'latitude'  => '41.9078',
            'longitude' => '-88.2484',
        ],
        'openingHoursSpecification' => [
            '@type'     => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            'opens'     => '00:00',
            'closes'    => '23:59',
        ],
        'sameAs' => array_filter( [
            function_exists('get_field') ? get_field( 'facebook_link', 'option' ) : '',
            function_exists('get_field') ? get_field( 'instagram_link', 'option' ) : '',
            function_exists('get_field') ? get_field( 'twitter_link', 'option' ) : '',
        ] ),
    ];

    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
}

// ── Remove WP version from feeds ──────────────────────────────
add_filter( 'the_generator', '__return_empty_string' );
