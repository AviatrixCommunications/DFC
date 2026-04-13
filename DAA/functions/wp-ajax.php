<?php

// ── Blog post listing ────────────────────────────────────────────────

add_action( 'wp_ajax_filter_blog_posts', 'filter_blog_posts' );
add_action( 'wp_ajax_nopriv_filter_blog_posts', 'filter_blog_posts' );

function filter_blog_posts() {
	check_ajax_referer( 'blog_filter_nonce', 'nonce' );

	$category = sanitize_text_field( $_POST['category'] ?? '' );
	$year     = intval( $_POST['year'] ?? 0 );
	$page     = max( 1, intval( $_POST['page'] ?? 1 ) );

	$args = [
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 4,
		'paged'          => $page,
		'orderby'        => 'date',
		'order'          => 'DESC',
	];

	if ( $category ) {
		$args['category_name'] = $category;
	}
	if ( $year ) {
		$args['date_query'] = [ [ 'year' => $year ] ];
	}

	$query = new WP_Query( $args );

	ob_start();

	if ( $query->have_posts() ) :
		while ( $query->have_posts() ) : $query->the_post();
			get_component( 'post/post_item', [ 'post_id' => get_the_ID() ] );
		endwhile;
	else : ?>
		<p class="blog-listing__no-results">No posts found.</p>
	<?php endif;

	wp_reset_postdata();

	wp_send_json_success( [
		'html'      => ob_get_clean(),
		'max_pages' => $query->max_num_pages,
	] );
}

// Load more (same query, returns items only — no wrapper)
add_action( 'wp_ajax_load_more_blog_posts', 'load_more_blog_posts' );
add_action( 'wp_ajax_nopriv_load_more_blog_posts', 'load_more_blog_posts' );

function load_more_blog_posts() {
	check_ajax_referer( 'blog_filter_nonce', 'nonce' );

	$category = sanitize_text_field( $_POST['category'] ?? '' );
	$year     = intval( $_POST['year'] ?? 0 );
	$page     = max( 1, intval( $_POST['page'] ?? 1 ) );

	$args = [
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 4,
		'paged'          => $page,
		'orderby'        => 'date',
		'order'          => 'DESC',
	];

	if ( $category ) {
		$args['category_name'] = $category;
	}
	if ( $year ) {
		$args['date_query'] = [ [ 'year' => $year ] ];
	}

	$query = new WP_Query( $args );

	ob_start();

	if ( $query->have_posts() ) :
		while ( $query->have_posts() ) : $query->the_post();
			get_component( 'post/post_item', [ 'post_id' => get_the_ID() ] );
		endwhile;
	endif;

	wp_reset_postdata();

	wp_send_json_success( [
		'html'      => ob_get_clean(),
		'max_pages' => $query->max_num_pages,
	] );
}

// Localize script with nonce for blog filters
add_action( 'wp_enqueue_scripts', 'localize_blog_scripts', 99 );
function localize_blog_scripts() {
	wp_localize_script( 'theme-script', 'blogFilter', [
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'blog_filter_nonce' ),
	] );
}

// ── Project grid filters ─────────────────────────────────────────────

add_action( 'wp_ajax_filter_projects', 'filter_projects' );
add_action( 'wp_ajax_nopriv_filter_projects', 'filter_projects' );

function filter_projects() {
	check_ajax_referer( 'project_filter_nonce', 'nonce' );

	$category = isset( $_POST['category'] ) ? sanitize_text_field( $_POST['category'] ) : '';
	$location = isset( $_POST['location'] ) ? sanitize_text_field( $_POST['location'] ) : '';

	$args = [
		'post_type'      => 'projects',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	];

	$tax_query = [];

	if ( ! empty( $category ) ) {
		$tax_query[] = [
			'taxonomy' => 'project-category',
			'field'    => 'slug',
			'terms'    => $category,
		];
	}

	if ( ! empty( $location ) ) {
		$tax_query[] = [
			'taxonomy' => 'project-location',
			'field'    => 'slug',
			'terms'    => $location,
		];
	}

	if ( ! empty( $tax_query ) ) {
		$args['tax_query'] = $tax_query;
		if ( count( $tax_query ) > 1 ) {
			$args['tax_query']['relation'] = 'AND';
		}
	}

	$query = new WP_Query( $args );

	ob_start();

	if ( $query->have_posts() ) : ?>
		<div class="project-grid__list">
			<?php while ( $query->have_posts() ) : $query->the_post();
				$pid         = get_the_ID();
				$thumb_id    = get_post_thumbnail_id( $pid );
				$est         = get_field( 'estimated_completion', $pid );
				$terms       = wp_get_post_terms( $pid, 'project-category' );
				$cat_classes = array_map( fn( $t ) => 'project-grid__card-title--' . $t->slug, $terms );
				$status      = array_filter( $terms, fn( $t ) => $t->slug !== 'featured' );
				$status_name = ! empty( $status ) ? reset( $status )->name : '';
			?>
				<div class="project-grid__card">
					<div class="project-grid__card-image">
						<?php if ( $thumb_id ) :
							echo wp_get_attachment_image( $thumb_id, 'medium_large', false, [ 'loading' => 'lazy' ] );
						endif; ?>
						<?php if ( $est || $status_name ) : ?>
							<span class="project-grid__card-overlay" aria-hidden="true">
								<span class="project-grid__card-overlay-label">Estimated Completion:</span>
								<span class="project-grid__card-overlay-value"><?php
									$parts = [];
									if ( $est ) $parts[] = esc_html( $est );
									echo implode( ' &ndash; ', $parts );
								?></span>
							</span>
						<?php endif; ?>
					</div>
					<h3 class="project-grid__card-title <?php echo esc_attr( implode( ' ', $cat_classes ) ); ?>">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h3>
				</div>
			<?php endwhile; ?>
		</div>
	<?php else : ?>
		<p class="project-grid__no-results">No projects found matching your filters.</p>
	<?php endif;

	wp_reset_postdata();

	wp_send_json_success( [
		'html' => ob_get_clean(),
	] );
}

// Localize script with nonce for project filters
add_action( 'wp_enqueue_scripts', 'localize_project_filter_scripts', 99 );
function localize_project_filter_scripts() {
	wp_localize_script( 'theme-script', 'projectFilter', [
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'project_filter_nonce' ),
	] );
}
