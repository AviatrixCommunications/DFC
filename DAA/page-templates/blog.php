<?php
/* Template Name: Blog */
get_header();
while ( have_posts() ) : the_post(); ?>
<div class="is-layout-constrained"><?php
	the_content(); ?>
</div><?php
endwhile;

// ── Post listing with filters ────────────────────────────────────────

// Categories that have published posts.
$blog_categories = get_categories( [
	'hide_empty' => true,
	'orderby'    => 'name',
	'order'      => 'ASC',
] );

// Distinct publish years.
global $wpdb;
$blog_years = $wpdb->get_col(
	"SELECT DISTINCT YEAR(post_date) FROM {$wpdb->posts}
	 WHERE post_type = 'post' AND post_status = 'publish'
	 ORDER BY YEAR(post_date) DESC"
);

// Initial query — newest 4 posts.
$blog_query = new WP_Query( [
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 4,
	'orderby'        => 'date',
	'order'          => 'DESC',
] );
?>

<section class="blog-listing js-fadein-up">
	<div class="wrapper">
		<h2>Recent News</h2>
		<div class="blog-listing__filters">
			<div class="blog-listing__categories">
				<p class="blog-listing__filter-label">Filter by Category:</p>
				<div class="blog-listing__category-buttons" role="group" aria-label="Filter by category">
					<button type="button" class="blog-listing__cat-btn is-active" data-category="" aria-pressed="true">All News</button>
					<?php foreach ( $blog_categories as $cat ) : ?>
						<button type="button" class="blog-listing__cat-btn" data-category="<?php echo esc_attr( $cat->slug ); ?>" aria-pressed="false"><?php echo esc_html( $cat->name ); ?></button>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if ( ! empty( $blog_years ) ) : ?>
				<div class="blog-listing__year-filter">
					<select id="blog-year-filter" aria-label="Filter by year">
						<option value="">Filter by year</option>
						<?php foreach ( $blog_years as $year ) : ?>
							<option value="<?php echo esc_attr( $year ); ?>"><?php echo esc_html( $year ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>
		</div>

		<div class="u-sr-only" aria-live="polite" aria-atomic="true" id="blog-listing-status"></div>
		<div id="blog-posts-results" class="blog-listing__results">
			<div class="blog-listing__list">
				<?php if ( $blog_query->have_posts() ) :
					while ( $blog_query->have_posts() ) : $blog_query->the_post();
						get_component( 'post/post_item', [ 'post_id' => get_the_ID() ] );
					endwhile;
					wp_reset_postdata();
				else : ?>
					<p class="blog-listing__no-results">No posts found.</p>
				<?php endif; ?>
			</div>

			<?php if ( $blog_query->max_num_pages > 1 ) : ?>
				<div class="blog-listing__load-more-wrapper">
					<button type="button" class="blog-listing__load-more" data-max-pages="<?php echo esc_attr( $blog_query->max_num_pages ); ?>">View More Articles</button>
				</div>
			<?php endif; ?>
		</div>

	</div>
</section>

<?php get_footer(); ?>
