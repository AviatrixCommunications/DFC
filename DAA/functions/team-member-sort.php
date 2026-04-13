<?php

/**
 * Drag-and-drop sorting for the Team Members admin list.
 *
 * Enqueues jQuery UI Sortable on the edit-team-member screen and
 * provides an AJAX handler to persist the new menu_order values.
 */

add_action( 'admin_enqueue_scripts', 'tm_sort_enqueue_scripts' );

function tm_sort_enqueue_scripts( $hook ) {
	if ( 'edit.php' !== $hook ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'team-member' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_script( 'jquery-ui-sortable' );

	wp_enqueue_script(
		'tm-sort',
		get_template_directory_uri() . '/functions/team-member-sort.js',
		[ 'jquery', 'jquery-ui-sortable' ],
		filemtime( get_template_directory() . '/functions/team-member-sort.js' ),
		true
	);

	wp_localize_script( 'tm-sort', 'tmSort', [
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'tm_sort_nonce' ),
	] );

	wp_add_inline_style( 'wp-admin', '
		#the-list tr { cursor: grab; }
		#the-list tr.ui-sortable-helper { background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,.15); cursor: grabbing; }
		#the-list tr.tm-sort-placeholder { background: #f0f6fc; }
	' );
}

add_action( 'wp_ajax_sort_team_members', 'tm_sort_ajax_handler' );

function tm_sort_ajax_handler() {
	check_ajax_referer( 'tm_sort_nonce', 'nonce' );

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( 'Unauthorized', 403 );
	}

	$order = isset( $_POST['order'] ) ? array_map( 'absint', $_POST['order'] ) : [];

	if ( empty( $order ) ) {
		wp_send_json_error( 'No order data' );
	}

	foreach ( $order as $position => $post_id ) {
		wp_update_post( [
			'ID'         => $post_id,
			'menu_order' => $position,
		] );
	}

	wp_send_json_success();
}

/**
 * Default the admin list to order by menu_order so the drag-and-drop
 * result is immediately visible without needing a custom orderby param.
 */
add_action( 'pre_get_posts', 'tm_sort_default_admin_order' );

function tm_sort_default_admin_order( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( 'team-member' !== $query->get( 'post_type' ) ) {
		return;
	}

	if ( ! $query->get( 'orderby' ) ) {
		$query->set( 'orderby', 'menu_order' );
		$query->set( 'order', 'ASC' );
	}
}
