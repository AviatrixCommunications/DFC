<?php

load_blocks_core_php_files();
add_filter( 'should_load_separate_core_block_assets', '__return_true' );
add_action( 'enqueue_block_editor_assets', 'enqueue_blocks_core_editor_js' );
add_action( 'enqueue_block_editor_assets', 'enqueue_acf_blocks_editor_js' );
add_action( 'enqueue_block_editor_assets', 'enqueue_acf_blocks_per_block_editor_js' );
add_action( 'init', 'register_blocks_and_assets' );
add_filter( 'block_categories_all', 'add_aviatrix_blocks_category', 10, 2 );

function enqueue_blocks_core_editor_js() {
	$block_path = get_template_directory() . '/blocks-core';
	$block_uri  = get_template_directory_uri() . '/blocks-core';
	$block_files = glob( "$block_path/*/editor.js" );
	foreach ( $block_files as $filepath ) :
		$blockname  = basename( dirname( $filepath ) );
		$scriptname = "blocks-core-$blockname-editor";
		wp_register_script(
			$scriptname,
			"$block_uri/$blockname/editor.js",
			[ 'wp-blocks', 'wp-dom-ready', 'wp-hooks', 'wp-element' ],
			filemtime( $filepath ),
			true
		);
		wp_enqueue_script( $scriptname );
	endforeach;
}

function enqueue_acf_blocks_editor_js() {
	$file = get_template_directory() . '/js/acf-blocks-editor.js';
	if ( file_exists( $file ) ) {
		wp_enqueue_script(
			'acf-blocks-editor',
			get_template_directory_uri() . '/js/acf-blocks-editor.js',
			[ 'wp-blocks', 'wp-dom-ready', 'wp-data' ],
			filemtime( $file ),
			true
		);
	}
}

function enqueue_acf_blocks_per_block_editor_js() {
	$block_path  = get_template_directory() . '/blocks-acf';
	$block_uri   = get_template_directory_uri() . '/blocks-acf';
	$block_files = glob( "$block_path/*/editor.js" );
	foreach ( $block_files as $filepath ) :
		$blockname  = basename( dirname( $filepath ) );
		$scriptname = "blocks-acf-$blockname-editor";
		wp_register_script(
			$scriptname,
			"$block_uri/$blockname/editor.js",
			[ 'wp-blocks', 'wp-dom-ready', 'wp-data', 'wp-core-data' ],
			filemtime( $filepath ),
			true
		);
		wp_enqueue_script( $scriptname );
	endforeach;
}

function load_blocks_core_php_files() {
	$block_path  = get_template_directory() . '/blocks-core';
	$block_files = glob( "$block_path/*/index.php" );
	foreach ( $block_files as $file ) :
		require_once ( $file );
	endforeach;
}

// Add custom Avatrix block category to the block inserter
function add_aviatrix_blocks_category( $categories, $post ) {

	array_unshift( $categories, array(
		'slug'  => 'aviatrix-blocks',
		'title' => 'Aviatrix Blocks',
	) );

	return $categories;
}

// Auto register blocks and their assets
function register_blocks_and_assets() {
	$block_folders = [ 
		'blocks-acf',
		'blocks-core',
	];
	foreach ( $block_folders as $folder ) :
		register_blocks_and_assets_in( $folder );
	endforeach;
}

function register_blocks_and_assets_in( $folder ) {
	$folder_path   = get_template_directory() . "/$folder";
	$block_folders = glob( "$folder_path/*", GLOB_ONLYDIR );
	foreach ( $block_folders as $block_path ) :
		$block_name = basename( $block_path );
		if ( file_exists( "$block_path/block.json" ) ) :
			$block_type = register_block_type( "$block_path/block.json" );

			// Set style version to file modification time for cache busting.
			$style_css = "$block_path/style.css";
			if ( $block_type && file_exists( $style_css ) ) {
				$handle = $block_type->style;
				if ( $handle && wp_styles()->registered[ $handle ] ?? false ) {
					wp_styles()->registered[ $handle ]->ver = filemtime( $style_css );
				}
			}
		endif;
	endforeach;
}

// ---------------------------------------------------------------------------
// ACF blocks: skip empty inner blocks on the frontend.
//
// pre_render_block fires BEFORE a block's children render, so we track which
// ACF block (if any) is currently rendering. render_block fires AFTER each
// block renders, letting us strip empty inner blocks and apply per-block
// decorations (e.g. hero eyebrow SVG, hero deco icons).
// ---------------------------------------------------------------------------
add_filter( 'pre_render_block', 'aviatrix_acf_pre_render', 10, 2 );
add_filter( 'render_block', 'aviatrix_acf_filter_inner_blocks', 10, 2 );

function aviatrix_acf_pre_render( $pre_render, $parsed_block ) {
	// Track block nesting depth for auto fade-in.
	$GLOBALS['aviatrix_block_depth'] = ( $GLOBALS['aviatrix_block_depth'] ?? 0 ) + 1;

	$block_name = $parsed_block['blockName'] ?? '';
	if ( str_starts_with( $block_name, 'acf/' ) ) {
		$GLOBALS['aviatrix_rendering_acf_block'] = $block_name;
		$GLOBALS['aviatrix_rendering_acf_block_class'] = $parsed_block['attrs']['className'] ?? '';
	}
	return $pre_render;
}

function aviatrix_acf_filter_inner_blocks( $block_content, $parsed_block ) {
	$block_name = $parsed_block['blockName'] ?? '';

	// Decrement block depth (incremented in pre_render).
	$GLOBALS['aviatrix_block_depth'] = ( $GLOBALS['aviatrix_block_depth'] ?? 1 ) - 1;

	// Skip all filtering during AJAX requests (ACF editor previews).
	// The empty-block stripping can interfere with ACF's InnerBlocks
	// initialization by making $content appear non-empty in the template.
	if ( wp_doing_ajax() ) {
		return $block_content;
	}

	$is_top_level = $GLOBALS['aviatrix_block_depth'] === 0;

	// --- Auto fade-in for top-level non-ACF blocks ---------------------------
	if (
		$is_top_level &&
		! empty( $block_name ) &&
		! str_starts_with( $block_name, 'acf/' ) &&
		! empty( trim( $block_content ) )
	) {
		if ( $block_name === 'gravityforms/form' ) {
			// Wrap GF in an animating div so opacity:0 doesn't break GF's JS/CSS.
			$block_content = '<div class="gravity-form-wrap js-fadein-up">' . $block_content . '</div>';
		} elseif ( preg_match( '/^\s*<\w+[^>]*\bclass="/', $block_content ) ) {
			$block_content = preg_replace( '/^(\s*<\w+[^>]*\bclass=")/', '$1js-fadein-up ', $block_content, 1 );
		} else {
			$block_content = preg_replace( '/^(\s*<\w+)(\s|>)/', '$1 class="js-fadein-up"$2', $block_content, 1 );
		}
	}

	// When the ACF block itself finishes rendering, clear the flags.
	if ( str_starts_with( $block_name, 'acf/' ) ) {
		unset( $GLOBALS['aviatrix_rendering_acf_block'] );
		unset( $GLOBALS['aviatrix_rendering_acf_block_class'] );
		return $block_content;
	}

	// Only act while inside an ACF block.
	if ( empty( $GLOBALS['aviatrix_rendering_acf_block'] ) ) {
		return $block_content;
	}

	$current_acf_block = $GLOBALS['aviatrix_rendering_acf_block'];

	// --- Empty inner-block filtering (all ACF blocks) ----------------------

	$skip_if_empty = [
		'core/heading',
		'core/paragraph',
		'core/buttons',
		'core/button',
		'core/image',
		'core/list',
		'core/quote',
		'core/separator',
		'core/spacer',
	];

	if ( $block_name === 'core/image' ) {
		$is_empty = strpos( $block_content, '<img' ) === false;
	} elseif ( $block_name === 'core/separator' || $block_name === 'core/spacer' ) {
		$is_empty = false;
	} else {
		$is_empty = trim( strip_tags( $block_content ) ) === '';
	}

	if ( in_array( $block_name, $skip_if_empty, true ) && $is_empty ) {
		return '';
	}

	// --- Accordion: inject chevron SVG into each details summary -----------

	if ( $current_acf_block === 'acf/accordion-item' && $block_name === 'core/details' ) {
		$chevron = '<svg class="accordion__chevron" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" width="17" height="10" viewBox="0 0 17 10" fill="none"><path d="M15.6106 0L17 1.48725L9.42799 9.58796C9.30666 9.71853 9.16238 9.82216 9.00345 9.89288C8.84453 9.96359 8.6741 10 8.50197 10C8.32983 10 8.1594 9.96359 8.00048 9.89288C7.84155 9.82216 7.69728 9.71853 7.57595 9.58796L0 1.48725L1.38936 0.0014019L8.5 7.60448L15.6106 0Z" fill="currentColor"/></svg>';
		$block_content = preg_replace( '|</summary>|', $chevron . '</summary>', $block_content, 1 );
	}

	return $block_content;
}

// ---------------------------------------------------------------------------
// Block visibility: restrict certain blocks to specific contexts.
// ---------------------------------------------------------------------------
add_filter( 'allowed_block_types_all', function ( $allowed_block_types, $editor_context ) {
	$all_types = WP_Block_Type_Registry::get_instance()->get_all_registered();
	$all_names = array_keys( $all_types );

	$remove = [ 'core/media-text' ];

	$post_id   = $editor_context->post->ID ?? 0;
	$post_type = $editor_context->post->post_type ?? '';
	$page_tmpl = $post_id ? get_page_template_slug( $post_id ) : '';

	// Blog-specific blocks are only available on pages using the Blog template.
	if ( $page_tmpl !== 'page-templates/blog.php' ) {
		$remove[] = 'acf/blog-meta';
		$remove[] = 'acf/blog-featured';
	}

	return array_values( array_diff( $all_names, $remove ) );
}, 10, 2 );

// Prevents user from being able to unlock blocks.
// Auto-insert a locked page-header on every Page (except the front page).
add_filter( 'block_editor_settings_all',
	function ( $settings, $context ) {
		$settings['canLockBlocks'] = false;

		$post = $context->post ?? null;
		if ( $post && $post->post_type === 'page' ) {
			$front_page_id = (int) get_option( 'page_on_front' );
			if ( ! $front_page_id || (int) $post->ID !== $front_page_id ) {
				$settings['template'] = [
					[ 'acf/page-header', [
						'lock' => [ 'move' => true, 'remove' => true ],
					] ],
				];
			}
		}

		return $settings;
	}, 10, 2
);
