<?php

require_once 'functions/blocks.php';
require_once 'functions/wp-ajax.php';
require_once 'functions/team-member-sort.php';

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

remove_theme_support('block-templates');
//remove_theme_support('core-block-patterns');

// Scripts and Styles

function theme_enqueue_assets() {
    $dist_path = get_template_directory() . '/dist/manifest.json';

    if (!file_exists($dist_path)) {
        // fallback if manifest doesn't exist
        wp_enqueue_style('theme-style', get_template_directory_uri() . '/dist/css/style.css', [], null);
        wp_enqueue_script('theme-script', get_template_directory_uri() . '/dist/js/global.js', [], null, true);
        return;
    }

    $manifest = json_decode(file_get_contents($dist_path), true);

    if (isset($manifest['style.css'])) {
        wp_enqueue_style('theme-style', get_template_directory_uri() . '/dist/' . $manifest['style.css'], [], null);
    }

    if (isset($manifest['global.js'])) {
        wp_enqueue_script('theme-script', get_template_directory_uri() . '/dist/' . $manifest['global.js'], [], null, true);
    }
}
add_action('wp_enqueue_scripts', 'theme_enqueue_assets');

// Gutenberg editor
function theme_enqueue_block_editor_assets() {
    $manifest = json_decode(file_get_contents(get_template_directory() . '/dist/manifest.json'), true);

    if (isset($manifest['editor-style.css'])) {
        wp_enqueue_style(
            'theme-editor-style',
            get_template_directory_uri() . '/dist/' . $manifest['editor-style.css'],
            [],
            null
        );
    }

    wp_enqueue_style(
        'theme-editor-overrides',
        get_template_directory_uri() . '/css/editor-overrides.css',
        [ 'theme-editor-style' ],
        filemtime( get_template_directory() . '/css/editor-overrides.css' )
    );
}
add_action('enqueue_block_editor_assets', 'theme_enqueue_block_editor_assets');

function localize_scripts(){
  wp_localize_script( 'theme-script', 'SITE', array(
    'ajaxurl' => admin_url( 'admin-ajax.php' ),
    'resturl' => get_rest_url()
    )
  );
}
add_action('wp_enqueue_scripts', 'localize_scripts');


// Close Comments on the Front End
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);
// Hide Existing Comments
add_filter('comments_array', '__return_empty_array', 10, 2);

// Remove Comments from Admin
add_action('admin_init', 'remove_comments');
add_action('admin_menu', 'remove_comments_page');
add_action('admin_init', 'remove_comments_links');
function remove_comments()
{
  // Redirect any user trying to access comments page
  global $pagenow;
  if ($pagenow === 'edit-comments.php') {
    wp_redirect(admin_url());
    exit;
  }
  // Remove comments metabox from dashboard
  remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
  // Disable support for comments and trackbacks in post types
  foreach (get_post_types() as $post_type) {
    if (post_type_supports($post_type, 'comments')) {
      remove_post_type_support($post_type, 'comments');
      remove_post_type_support($post_type, 'trackbacks');
    }
  }
}
// Remove comments page in menu
function remove_comments_page()
{
  remove_menu_page('edit-comments.php');
}
// Remove comments links from admin bar
function remove_comments_links()
{
  if (is_admin_bar_showing()) {
    remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
  }
}

// Content
add_action('init', 'disable_wp_emojicons');
function disable_wp_emojicons()
{
  remove_action('admin_print_styles', 'print_emoji_styles');
  remove_action('wp_head', 'print_emoji_detection_script', 7);
  remove_action('admin_print_scripts', 'print_emoji_detection_script');
  remove_action('wp_print_styles', 'print_emoji_styles');
  remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
  remove_filter('the_content_feed', 'wp_staticize_emoji');
  remove_filter('comment_text_rss', 'wp_staticize_emoji');
}

// Disable WordPress Custom CSS in Customizer
function customizer_reg($wp_customize)
{
  $wp_customize->remove_section('custom_css');
}
add_action('customize_register', 'customizer_reg');

// Images 
add_filter('upload_mimes', 'add_svg_mime_type');
add_filter('wp_check_filetype_and_ext', 'allow_svg_uploads', 10, 4);

function add_svg_mime_type($mimes)
{
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
function allow_svg_uploads($data, $file, $filename, $mimes)
{
  // Allow users to upload SVG Files to Media Library
  // http://codepen.io/chriscoyier/post/wordpress-4-7-1-svg-upload
  $filetype = wp_check_filetype($filename, $mimes);

  return [
    'ext' => $filetype['ext'],
    'type' => $filetype['type'],
    'proper_filename' => $data['proper_filename'],
  ];
}

// Make WP login error messages more generic for security
function generic_wp_errors()
{
  return 'Username, password, or email address is incorrect. Click <a href="/wp-login.php?action=lostpassword">here</a> to reset your password.';
}
add_filter('login_errors', 'generic_wp_errors');

// Thme Supports
add_action('after_setup_theme', 'theme_supports');
function theme_supports()
{
  add_theme_support('custom-logo'); 
  add_theme_support('post-thumbnails');
  add_theme_support('align-wide');
  add_theme_support('title-tag');
  add_theme_support( 'wp-block-styles' );
  add_theme_support('editor-styles');
  add_theme_support(
    'html5',
    array(
      'script',
      'search-form',
      'comment-form',
      'comment-list',
      'gallery',
      'caption',
    )
  );
}

// Menus
add_action('after_setup_theme', 'register_menus');
function register_menus()
{
  register_nav_menus(
    array(
      'main_nav' => 'Main Menu',
      'footer_nav' => 'Footer Top Menu',
      'footer_nav_btm' => 'Footer Bottom Menu',
    )
  );
}
function main_nav()
{
  wp_nav_menu(
    array(
      'theme_location' => 'main_nav',
      'menu' => '',
      'container' => '',
      'container_class' => '',
      'container_id' => '',
      'menu_class' => '',
      'menu_id' => '',
      'echo' => true,
      'fallback_cb' => 'wp_page_menu',
      'before' => '',
      'after' => '',
      'link_before' => '',
      'link_after' => '',
      'items_wrap' => '<ul class="nav__list">%3$s</ul>',
      'depth' => 0,
      'walker' => '',
    )
  );
}
function footer_nav()
{
  wp_nav_menu(
    array(
      'theme_location' => 'footer_nav',
      'menu' => '',
      'container' => '',
      'container_class' => '',
      'container_id' => '',
      'menu_class' => '',
      'menu_id' => '',
      'echo' => true,
      'fallback_cb' => 'wp_page_menu',
      'before' => '',
      'after' => '',
      'link_before' => '',
      'link_after' => '',
      'items_wrap' => '<ul class="nav__list">%3$s</ul>',
      'depth' => 0,
      'walker' => '',
    )
  );
}
function footer_nav_btm()
{
  wp_nav_menu(
    array(
      'theme_location' => 'footer_nav_btm',
      'menu' => '',
      'container' => '',
      'container_class' => '',
      'container_id' => '',
      'menu_class' => '',
      'menu_id' => '',
      'echo' => true,
      'fallback_cb' => 'wp_page_menu',
      'before' => '',
      'after' => '',
      'link_before' => '',
      'link_after' => '',
      'items_wrap' => '<ul class="nav__list">%3$s</ul>',
      'depth' => 0,
      'walker' => '',
    )
  );
}


// Helper Functions
function get_component($slug, array $args = array(), $output = true)
{
  /* $args will be available in the component file */
  if (!$output)
    ob_start();
  $template_file = locate_template("components/{$slug}.php", false, false);
  $template_file = strlen($template_file) > 0 ? $template_file : locate_template("components/{$slug}/index.php", false, false);
  if (file_exists($template_file)):
    require ($template_file);
  else:
    throw new \RuntimeException("Could not find component $slug");
  endif;
  if (!$output)
    return ob_get_clean();
}

// Alert Banner — cached per request, used by body_class filter and component
function get_active_alerts() {
  static $alerts = null;
  if ( $alerts !== null ) return $alerts;

  $alerts = [];
  $today  = date('Ymd');

  if ( ! have_rows('bar_alerts', 'option') ) return $alerts;

  while ( have_rows('bar_alerts', 'option') ) : the_row();
    $display = get_sub_field('display_alert');
    $show    = false;

    if ( $display === 'on' ) {
      $show = true;
    } elseif ( $display === 'schedule' ) {
      $start = get_sub_field('alert_start_date');
      $end   = get_sub_field('alert_end_date');
      if ( $start && $end && $today >= $start && $today <= $end ) {
        $show = true;
      }
    }

    if ( $show ) {
      $content   = get_sub_field('alert_content');
      $frequency = get_sub_field('display_frequency');
      $severity  = get_sub_field('alert_severity');

      $alerts[] = [
        'alert_content'     => $content,
        'display_frequency' => $frequency,
        'alert_id'          => substr( md5( $content . $frequency ), 0, 8 ),
        'severity_color'    => $severity === 'urgent' ? '#EF3340' : '#D3D3D3',
        'aria'              => $severity === 'urgent' ? 'role="alert"' : 'role="status" aria-live="polite"',
        'alert_button'      => get_sub_field('alert_button'),
      ];
    }
  endwhile;

  return $alerts;
}

add_filter( 'body_class', function ( $classes ) {
  if ( get_active_alerts() ) {
    $classes[] = 'has-notification-banner';
  }
  return $classes;
} );

// Add Image Sizes
function tv_image_sizes() {
    add_image_size( 'hero', 1920, 0, false );
}
add_action( 'after_setup_theme', 'tv_image_sizes' );

// Destination Coordinate Lookup Helper
add_action('admin_init', function() {
    if (!isset($_GET['populate_airport_coords']) || !current_user_can('manage_options')) {
        return;
    }
    
    // Expanded airport database
    $airport_coords = array(
        // Your existing destinations from screenshots
        'CLT' => array('lat' => 35.2140, 'lon' => -80.9431, 'city' => 'Charlotte', 'state' => 'NC'),
        'EWR' => array('lat' => 40.6895, 'lon' => -74.1745, 'city' => 'Newark', 'state' => 'NJ'),
        'IAD' => array('lat' => 38.9531, 'lon' => -77.4565, 'city' => 'Washington Dulles', 'state' => 'VA'),
        'DAL' => array('lat' => 32.8471, 'lon' => -96.8518, 'city' => 'Dallas Love', 'state' => 'TX'),
        'MDW' => array('lat' => 41.7868, 'lon' => -87.7522, 'city' => 'Chicago Midway', 'state' => 'IL'),
        'DFW' => array('lat' => 32.8998, 'lon' => -97.0403, 'city' => 'Dallas', 'state' => 'TX'),
        'ORD' => array('lat' => 41.9742, 'lon' => -87.9073, 'city' => 'Chicago Ohare', 'state' => 'IL'),
        'JFK' => array('lat' => 40.6413, 'lon' => -73.7781, 'city' => 'New York - John F Kennedy', 'state' => 'NY'),
        'DSM' => array('lat' => 41.5340, 'lon' => -93.6631, 'city' => 'Des Moines', 'state' => 'IA'),
        'SFO' => array('lat' => 37.6213, 'lon' => -122.3790, 'city' => 'San Francisco', 'state' => 'CA'),
        'BLI' => array('lat' => 48.7928, 'lon' => -122.5376, 'city' => 'Bellingham', 'state' => 'WA'),
        'ATL' => array('lat' => 33.6407, 'lon' => -84.4277, 'city' => 'Atlanta', 'state' => 'GA'),
        'YWG' => array('lat' => 49.9100, 'lon' => -97.2399, 'city' => 'Winnipeg', 'state' => 'MB'),
        'YVR' => array('lat' => 49.1947, 'lon' => -123.1838, 'city' => 'Vancouver', 'state' => 'BC'),
        'SEA' => array('lat' => 47.4502, 'lon' => -122.3088, 'city' => 'Seattle', 'state' => 'WA'),
        'STS' => array('lat' => 38.5090, 'lon' => -122.8127, 'city' => 'Santa Rosa', 'state' => 'CA'),
        'SJC' => array('lat' => 37.3639, 'lon' => -121.9289, 'city' => 'San Jose', 'state' => 'CA'),
        'SLC' => array('lat' => 40.7899, 'lon' => -111.9791, 'city' => 'Salt Lake City', 'state' => 'UT'),
        'SMF' => array('lat' => 38.6954, 'lon' => -121.5908, 'city' => 'Sacramento', 'state' => 'CA'),
        'PDX' => array('lat' => 45.5898, 'lon' => -122.5951, 'city' => 'Portland', 'state' => 'OR'),
        'YYZ' => array('lat' => 43.6777, 'lon' => -79.6248, 'city' => 'Toronto', 'state' => 'ON'),
        'OAK' => array('lat' => 37.7213, 'lon' => -122.2207, 'city' => 'Oakland', 'state' => 'CA'),
        'MSP' => array('lat' => 44.8848, 'lon' => -93.2223, 'city' => 'Minneapolis', 'state' => 'MN'),
        'LAX' => array('lat' => 33.9425, 'lon' => -118.4081, 'city' => 'Los Angeles', 'state' => 'CA'),
        'LAS' => array('lat' => 36.0840, 'lon' => -115.1537, 'city' => 'Las Vegas', 'state' => 'NV'),
        'IAH' => array('lat' => 29.9902, 'lon' => -95.3368, 'city' => 'Houston', 'state' => 'TX'),
        'HOU' => array('lat' => 29.6454, 'lon' => -95.2789, 'city' => 'Houston Hobby', 'state' => 'TX'),
        'PAE' => array('lat' => 47.9063, 'lon' => -122.2817, 'city' => 'Everett', 'state' => 'WA'),
        'EUG' => array('lat' => 44.1246, 'lon' => -123.2119, 'city' => 'Eugene', 'state' => 'OR'),
        'YEG' => array('lat' => 53.3097, 'lon' => -113.5800, 'city' => 'Edmonton', 'state' => 'AB'),
        'DEN' => array('lat' => 39.8561, 'lon' => -104.6737, 'city' => 'Denver', 'state' => 'CO'),
        'YYC' => array('lat' => 51.1225, 'lon' => -114.0133, 'city' => 'Calgary', 'state' => 'AB'),
        'BOI' => array('lat' => 43.5644, 'lon' => -116.2228, 'city' => 'Boise', 'state' => 'ID'),
        'RDM' => array('lat' => 44.2541, 'lon' => -121.1499, 'city' => 'Redmond', 'state' => 'OR'),
        'AUS' => array('lat' => 30.1975, 'lon' => -97.6664, 'city' => 'Austin', 'state' => 'TX'),
        'PHX' => array('lat' => 33.4343, 'lon' => -112.0116, 'city' => 'Phoenix', 'state' => 'AZ'),
        
        // Additional major airports
        'BOS' => array('lat' => 42.3656, 'lon' => -71.0096, 'city' => 'Boston', 'state' => 'MA'),
        'DCA' => array('lat' => 38.8512, 'lon' => -77.0402, 'city' => 'Washington Reagan', 'state' => 'DC'),
        'DTW' => array('lat' => 42.2162, 'lon' => -83.3554, 'city' => 'Detroit', 'state' => 'MI'),
        'SAN' => array('lat' => 32.7338, 'lon' => -117.1933, 'city' => 'San Diego', 'state' => 'CA'),
        'TUS' => array('lat' => 32.1161, 'lon' => -110.9410, 'city' => 'Tucson', 'state' => 'AZ'),
        'ABQ' => array('lat' => 35.0402, 'lon' => -106.6090, 'city' => 'Albuquerque', 'state' => 'NM'),
        'MIA' => array('lat' => 25.7959, 'lon' => -80.2870, 'city' => 'Miami', 'state' => 'FL'),
        'MCO' => array('lat' => 28.4312, 'lon' => -81.3081, 'city' => 'Orlando', 'state' => 'FL'),
        'TPA' => array('lat' => 27.9755, 'lon' => -82.5332, 'city' => 'Tampa', 'state' => 'FL'),
        'BNA' => array('lat' => 36.1245, 'lon' => -86.6782, 'city' => 'Nashville', 'state' => 'TN'),
        'MKE' => array('lat' => 42.9472, 'lon' => -87.8966, 'city' => 'Milwaukee', 'state' => 'WI'),
        'PHL' => array('lat' => 39.8744, 'lon' => -75.2424, 'city' => 'Philadelphia', 'state' => 'PA'),
        'BWI' => array('lat' => 39.1774, 'lon' => -76.6684, 'city' => 'Baltimore', 'state' => 'MD'),
        'RDU' => array('lat' => 35.8776, 'lon' => -78.7875, 'city' => 'Raleigh-Durham', 'state' => 'NC'),
        'STL' => array('lat' => 38.7487, 'lon' => -90.3700, 'city' => 'St. Louis', 'state' => 'MO'),
        'MCI' => array('lat' => 39.2976, 'lon' => -94.7139, 'city' => 'Kansas City', 'state' => 'MO'),
        'MSY' => array('lat' => 29.9934, 'lon' => -90.2580, 'city' => 'New Orleans', 'state' => 'LA'),
        'SAT' => array('lat' => 29.5337, 'lon' => -98.4698, 'city' => 'San Antonio', 'state' => 'TX'),
        'CMH' => array('lat' => 39.9999, 'lon' => -82.8872, 'city' => 'Columbus', 'state' => 'OH'),
        'CLE' => array('lat' => 41.4058, 'lon' => -81.8539, 'city' => 'Cleveland', 'state' => 'OH'),
        'CVG' => array('lat' => 39.0488, 'lon' => -84.6678, 'city' => 'Cincinnati', 'state' => 'KY'),
        'IND' => array('lat' => 39.7173, 'lon' => -86.2944, 'city' => 'Indianapolis', 'state' => 'IN'),
        'PIT' => array('lat' => 40.4915, 'lon' => -80.2329, 'city' => 'Pittsburgh', 'state' => 'PA'),
        'BUF' => array('lat' => 42.9405, 'lon' => -78.7322, 'city' => 'Buffalo', 'state' => 'NY'),
        'OMA' => array('lat' => 41.3032, 'lon' => -95.8941, 'city' => 'Omaha', 'state' => 'NE'),
        'RNO' => array('lat' => 39.4991, 'lon' => -119.7681, 'city' => 'Reno', 'state' => 'NV'),
        'GEG' => array('lat' => 47.6198, 'lon' => -117.5336, 'city' => 'Spokane', 'state' => 'WA'),
        'BZN' => array('lat' => 45.7769, 'lon' => -111.1529, 'city' => 'Bozeman', 'state' => 'MT'),
        'JAC' => array('lat' => 43.6073, 'lon' => -110.7378, 'city' => 'Jackson Hole', 'state' => 'WY'),
        'COS' => array('lat' => 38.8056, 'lon' => -104.7008, 'city' => 'Colorado Springs', 'state' => 'CO'),
        'FAT' => array('lat' => 36.7762, 'lon' => -119.7181, 'city' => 'Fresno', 'state' => 'CA'),
        'BUR' => array('lat' => 34.2007, 'lon' => -118.3590, 'city' => 'Burbank', 'state' => 'CA'),
        'ONT' => array('lat' => 34.0560, 'lon' => -117.6012, 'city' => 'Ontario', 'state' => 'CA'),
        'SNA' => array('lat' => 33.6762, 'lon' => -117.8683, 'city' => 'Santa Ana', 'state' => 'CA'),
        'SBA' => array('lat' => 34.4262, 'lon' => -119.8407, 'city' => 'Santa Barbara', 'state' => 'CA'),
        'MRY' => array('lat' => 36.5870, 'lon' => -121.8430, 'city' => 'Monterey', 'state' => 'CA'),
        'JAX' => array('lat' => 30.4941, 'lon' => -81.6879, 'city' => 'Jacksonville', 'state' => 'FL'),
        'FLL' => array('lat' => 26.0742, 'lon' => -80.1506, 'city' => 'Fort Lauderdale', 'state' => 'FL'),
        'RSW' => array('lat' => 26.5362, 'lon' => -81.7552, 'city' => 'Fort Myers', 'state' => 'FL'),
        'PBI' => array('lat' => 26.6832, 'lon' => -80.0956, 'city' => 'West Palm Beach', 'state' => 'FL'),
        'ELP' => array('lat' => 31.8039, 'lon' => -106.3966, 'city' => 'El Paso', 'state' => 'TX'),
        
        // Canadian airports
        'YOW' => array('lat' => 45.3225, 'lon' => -75.6692, 'city' => 'Ottawa', 'state' => 'ON'),
        'YUL' => array('lat' => 45.4706, 'lon' => -73.7408, 'city' => 'Montreal', 'state' => 'QC'),
        
        // Mexican airports
        'SJD' => array('lat' => 23.1514, 'lon' => -109.7211, 'city' => 'San Jose del Cabo', 'state' => 'BCS'),
        'PVR' => array('lat' => 20.6801, 'lon' => -105.2544, 'city' => 'Puerto Vallarta', 'state' => 'JAL'),
        'GDL' => array('lat' => 20.5218, 'lon' => -103.3111, 'city' => 'Guadalajara', 'state' => 'JAL'),
    );
    
    echo '<div style="padding: 20px; font-family: monospace;">';
    echo '<h2>Populating Airport Coordinates</h2>';
    echo '<pre>';
    
    // Get all destination posts
    $destinations = get_posts(array(
        'post_type' => 'destinations',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ));
    
    $updated_count = 0;
    $skipped_count = 0;
    $missing_codes = array();
    
    foreach ($destinations as $destination) {
        $airport_code = get_post_meta($destination->ID, 'airport_code', true);
        
        if (empty($airport_code)) {
            echo "⚠️  {$destination->post_title}: No airport code set\n";
            $skipped_count++;
            $missing_codes[] = $destination->post_title;
            continue;
        }
        
        $airport_code = strtoupper(trim($airport_code));
        
        if (isset($airport_coords[$airport_code])) {
            $coords = $airport_coords[$airport_code];
            
            // Update latitude
            update_post_meta($destination->ID, 'latitude', $coords['lat']);
            
            // Update longitude
            update_post_meta($destination->ID, 'longitude', $coords['lon']);
            
            // Update state if not set
            $current_state = get_post_meta($destination->ID, 'state-name', true);
            if (empty($current_state) && !empty($coords['state'])) {
                update_post_meta($destination->ID, 'state-name', $coords['state']);
            }
            
            echo "✅ Updated {$destination->post_title} ({$airport_code}): ";
            echo "lat={$coords['lat']}, lon={$coords['lon']}\n";
            $updated_count++;
        } else {
            echo "❌ {$destination->post_title} ({$airport_code}): Not in database - needs manual entry\n";
            $skipped_count++;
            $missing_codes[] = "{$destination->post_title} ({$airport_code})";
        }
    }
    
    echo "\n========================================\n";
    echo "Updated: {$updated_count} destinations\n";
    echo "Skipped: {$skipped_count} destinations\n";
    echo "========================================\n\n";
    
    if (!empty($missing_codes)) {
        echo "DESTINATIONS NEEDING MANUAL COORDINATES:\n";
        foreach ($missing_codes as $missing) {
            echo "- {$missing}\n";
        }
        echo "\nSearch Google for: '[AIRPORT_CODE] airport coordinates'\n";
    }
    
    echo '</pre></div>';
    exit;
});

// Show admin notice when coordinates are auto-added
add_action('admin_notices', function() {
    if (isset($_GET['coords_auto_added'])) {
        $airport_code = sanitize_text_field($_GET['coords_auto_added']);
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p>✅ Coordinates automatically added for airport code: ' . esc_html($airport_code) . '</p>';
        echo '</div>';
    }
});

/**
 * Add a meta box to manually lookup coordinates
 */
add_action('add_meta_boxes', function() {
    add_meta_box(
        'daa_coord_lookup',
        'Coordinate Lookup Helper',
        'daa_coord_lookup_metabox',
        'destinations',
        'side',
        'default'
    );
});

function daa_coord_lookup_metabox($post) {
    $airport_code = get_post_meta($post->ID, 'airport_code', true);
    $latitude = get_post_meta($post->ID, 'latitude', true);
    $longitude = get_post_meta($post->ID, 'longitude', true);
    
    echo '<div style="margin: 10px 0;">';
    
    if (!empty($latitude) && !empty($longitude)) {
        echo '<p style="color: green;">✅ Coordinates are set</p>';
        echo '<p>Lat: ' . esc_html($latitude) . '</p>';
        echo '<p>Lon: ' . esc_html($longitude) . '</p>';
    } else {
        echo '<p style="color: orange;">⚠️ Coordinates not set</p>';
    }
    
    if (!empty($airport_code)) {
        echo '<p><strong>Airport Code:</strong> ' . esc_html($airport_code) . '</p>';
        echo '<p><a href="https://www.google.com/search?q=' . urlencode($airport_code . ' airport coordinates') . '" target="_blank" class="button">Search Google for Coordinates</a></p>';
        echo '<p><a href="https://www.airnav.com/airport/' . urlencode($airport_code) . '" target="_blank" class="button">Check AirNav</a></p>';
    } else {
        echo '<p style="color: red;">Please set an airport code first</p>';
    }
    
    echo '</div>';
}
