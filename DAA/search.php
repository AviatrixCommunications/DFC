<?php
get_header();

$search_query = get_search_query();
$search_data  = function_exists( 'airport_get_search_results' )
	? airport_get_search_results( $search_query, 40 )
	: null;
?>
<header class="is-style-hero-var-1 wp-block-acf-hero">
	<div class="hero is-style-hero-var-1">
		<div class="hero__image-wrap hero__image-wrap--desktop">
			<img loading="lazy" src="<?php echo get_template_directory_uri() ?>/img/foam-pattern.svg" alt="" />
		</div>
		<div class="wrapper">
			<div class="hero__content">
				<h1 class="wp-block-heading heading--xxl">Search Results</h1>
				<?php if ( $search_query ) : ?>
					<p>Showing results for &ldquo;<?php echo esc_html( $search_query ); ?>&rdquo;</p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</header>
<section class="archive__main search-results">
	<div class="wrapper">
		<?php if ( $search_data && ! empty( $search_data['grouped'] ) ) : ?>
			<?php foreach ( $search_data['grouped'] as $type => $group ) : ?>
				<div class="search-results__group">
					<h2 class="search-results__group-title"><?php echo esc_html( $group['label'] ); ?></h2>
					<ul class="grid">
						<?php foreach ( $group['items'] as $item ) : ?>
							<li class="col-12 col-6--md col-4--lg">
								<a href="<?php echo esc_url( $item['url'] ); ?>" class="search-results__item">
									<h3 class="search-results__item-title"><?php echo esc_html( $item['title'] ); ?></h3>
									<?php if ( ! empty( $item['excerpt'] ) ) : ?>
										<p class="search-results__item-excerpt"><?php echo wp_kses( $item['excerpt'], array( 'mark' => array() ) ); ?></p>
									<?php endif; ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endforeach; ?>
		<?php elseif ( $search_data ) : ?>
			<p class="search-no-results">No results found. Please try a different search term.</p>
		<?php else : ?>
			<?php /* Fallback: plugin not active, use standard WP loop */ ?>
			<?php if ( have_posts() ) : ?>
				<ul class="grid"><?php
					while ( have_posts() ) : the_post(); ?>
						<li class="col-12 col-6--md col-4--lg"><?php
							get_component('post/post_item', array('post_id' => get_the_ID()) ); ?>
						</li><?php
					endwhile;
					the_posts_pagination(); ?>
				</ul>
			<?php else : ?>
				<p class="search-no-results">No results found. Please try a different search term.</p>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</section>
<?php get_footer(); ?>
