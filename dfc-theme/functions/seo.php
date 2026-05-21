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
        'alternateName' => 'DFC FBO',
        'slogan'        => 'Executive Class FBO With World Class Service',
        'image'         => get_template_directory_uri() . '/img/dfc-logo-horizontal-white.svg',
        'hasMap'        => 'https://www.google.com/maps/place/DuPage+Flight+Center/',
        'priceRange'    => '$$$',
        'areaServed'    => [
            [ '@type' => 'City', 'name' => 'Chicago', 'sameAs' => 'https://en.wikipedia.org/wiki/Chicago' ],
            [ '@type' => 'City', 'name' => 'West Chicago, Illinois', 'sameAs' => 'https://en.wikipedia.org/wiki/West_Chicago,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Naperville', 'sameAs' => 'https://en.wikipedia.org/wiki/Naperville,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Oak Brook', 'sameAs' => 'https://en.wikipedia.org/wiki/Oak_Brook,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Wheaton', 'sameAs' => 'https://en.wikipedia.org/wiki/Wheaton,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Lombard', 'sameAs' => 'https://en.wikipedia.org/wiki/Lombard,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Elmhurst', 'sameAs' => 'https://en.wikipedia.org/wiki/Elmhurst,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Downers Grove', 'sameAs' => 'https://en.wikipedia.org/wiki/Downers_Grove,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Hinsdale', 'sameAs' => 'https://en.wikipedia.org/wiki/Hinsdale,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Glen Ellyn', 'sameAs' => 'https://en.wikipedia.org/wiki/Glen_Ellyn,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Burr Ridge', 'sameAs' => 'https://en.wikipedia.org/wiki/Burr_Ridge,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Willowbrook', 'sameAs' => 'https://en.wikipedia.org/wiki/Willowbrook,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Westmont', 'sameAs' => 'https://en.wikipedia.org/wiki/Westmont,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Addison', 'sameAs' => 'https://en.wikipedia.org/wiki/Addison,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Bloomingdale', 'sameAs' => 'https://en.wikipedia.org/wiki/Bloomingdale,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Carol Stream', 'sameAs' => 'https://en.wikipedia.org/wiki/Carol_Stream,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Warrenville', 'sameAs' => 'https://en.wikipedia.org/wiki/Warrenville,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Lisle', 'sameAs' => 'https://en.wikipedia.org/wiki/Lisle,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Woodridge', 'sameAs' => 'https://en.wikipedia.org/wiki/Woodridge,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Oakbrook Terrace', 'sameAs' => 'https://en.wikipedia.org/wiki/Oakbrook_Terrace,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Aurora', 'sameAs' => 'https://en.wikipedia.org/wiki/Aurora,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Schaumburg', 'sameAs' => 'https://en.wikipedia.org/wiki/Schaumburg,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Oak Park', 'sameAs' => 'https://en.wikipedia.org/wiki/Oak_Park,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Evanston', 'sameAs' => 'https://en.wikipedia.org/wiki/Evanston,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Skokie', 'sameAs' => 'https://en.wikipedia.org/wiki/Skokie,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Arlington Heights', 'sameAs' => 'https://en.wikipedia.org/wiki/Arlington_Heights,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Palatine', 'sameAs' => 'https://en.wikipedia.org/wiki/Palatine,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Des Plaines', 'sameAs' => 'https://en.wikipedia.org/wiki/Des_Plaines,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Rosemont', 'sameAs' => 'https://en.wikipedia.org/wiki/Rosemont,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Northbrook', 'sameAs' => 'https://en.wikipedia.org/wiki/Northbrook,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Wilmette', 'sameAs' => 'https://en.wikipedia.org/wiki/Wilmette,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Winnetka', 'sameAs' => 'https://en.wikipedia.org/wiki/Winnetka,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Glencoe', 'sameAs' => 'https://en.wikipedia.org/wiki/Glencoe,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Barrington', 'sameAs' => 'https://en.wikipedia.org/wiki/Barrington,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Hoffman Estates', 'sameAs' => 'https://en.wikipedia.org/wiki/Hoffman_Estates,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Orland Park', 'sameAs' => 'https://en.wikipedia.org/wiki/Orland_Park,_Illinois' ],
            [ '@type' => 'City', 'name' => 'La Grange', 'sameAs' => 'https://en.wikipedia.org/wiki/La_Grange,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Western Springs', 'sameAs' => 'https://en.wikipedia.org/wiki/Western_Springs,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Highland Park', 'sameAs' => 'https://en.wikipedia.org/wiki/Highland_Park,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Lake Forest', 'sameAs' => 'https://en.wikipedia.org/wiki/Lake_Forest,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Lake Bluff', 'sameAs' => 'https://en.wikipedia.org/wiki/Lake_Bluff,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Deerfield', 'sameAs' => 'https://en.wikipedia.org/wiki/Deerfield,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Elgin', 'sameAs' => 'https://en.wikipedia.org/wiki/Elgin,_Illinois' ],
            [ '@type' => 'City', 'name' => 'St. Charles', 'sameAs' => 'https://en.wikipedia.org/wiki/St._Charles,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Geneva', 'sameAs' => 'https://en.wikipedia.org/wiki/Geneva,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Batavia', 'sameAs' => 'https://en.wikipedia.org/wiki/Batavia,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Bolingbrook', 'sameAs' => 'https://en.wikipedia.org/wiki/Bolingbrook,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Plainfield', 'sameAs' => 'https://en.wikipedia.org/wiki/Plainfield,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Joliet', 'sameAs' => 'https://en.wikipedia.org/wiki/Joliet,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Frankfort', 'sameAs' => 'https://en.wikipedia.org/wiki/Frankfort,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Oswego', 'sameAs' => 'https://en.wikipedia.org/wiki/Oswego,_Illinois' ],
            [ '@type' => 'City', 'name' => 'Yorkville', 'sameAs' => 'https://en.wikipedia.org/wiki/Yorkville,_Illinois' ],
            [ '@type' => 'AdministrativeArea', 'name' => 'DuPage County', 'sameAs' => 'https://en.wikipedia.org/wiki/DuPage_County,_Illinois' ],
            [ '@type' => 'AdministrativeArea', 'name' => 'Cook County', 'sameAs' => 'https://en.wikipedia.org/wiki/Cook_County,_Illinois' ],
            [ '@type' => 'AdministrativeArea', 'name' => 'Kane County', 'sameAs' => 'https://en.wikipedia.org/wiki/Kane_County,_Illinois' ],
            [ '@type' => 'AdministrativeArea', 'name' => 'Lake County', 'sameAs' => 'https://en.wikipedia.org/wiki/Lake_County,_Illinois' ],
            [ '@type' => 'AdministrativeArea', 'name' => 'Will County', 'sameAs' => 'https://en.wikipedia.org/wiki/Will_County,_Illinois' ],
            [ '@type' => 'AdministrativeArea', 'name' => 'Kendall County', 'sameAs' => 'https://en.wikipedia.org/wiki/Kendall_County,_Illinois' ],
            [ '@type' => 'AdministrativeArea', 'name' => 'McHenry County', 'sameAs' => 'https://en.wikipedia.org/wiki/McHenry_County,_Illinois' ],
            [ '@type' => 'AdministrativeArea', 'name' => 'Chicago Metropolitan Area', 'sameAs' => 'https://en.wikipedia.org/wiki/Chicago_metropolitan_area' ],
            [ '@type' => 'AdministrativeArea', 'name' => 'Chicagoland', 'sameAs' => 'https://en.wikipedia.org/wiki/Chicago_metropolitan_area' ],
        ],
        'knowsAbout'    => [
            'Fixed Base Operator (FBO)',
            'Business Aviation',
            'Private Jet Services',
            'Aircraft Fueling (Jet A and AvGas)',
            'Hangar Services',
            'Ground Support',
            'Concierge Services',
            'U.S. Customs Clearance',
            'Corporate Aviation',
            'Chicago Area Executive Aviation',
        ],
    ];

    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
}

// ── Remove WP version from feeds ──────────────────────────────
add_filter( 'the_generator', '__return_empty_string' );
