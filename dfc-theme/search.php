<?php
/**
 * Search Results Page
 *
 * Uses WP Engine Smart Search for the query results.
 *
 * @package DFC
 */

get_header();

$search_query = get_search_query();
$total        = $wp_query->found_posts;
?>

<section class="dfc-search-page" aria-label="Search results">

	<div class="dfc-search-page__header">
		<div class="dfc-search-page__inner">
			<h1 class="dfc-search-page__title">
				<?php if ( $search_query ) : ?>
					Search Results
				<?php else : ?>
					Search
				<?php endif; ?>
			</h1>
			<?php if ( $search_query ) : ?>
				<p class="dfc-search-page__summary">
					Showing results for &ldquo;<?php echo esc_html( $search_query ); ?>&rdquo;
				</p>
			<?php endif; ?>

			<div class="dfc-search-page__form-wrap">
				<form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" role="search">
					<label for="search-page-input" class="u-sr-only">Search</label>
					<input type="search"
						   id="search-page-input"
						   class="dfc-search-page__input"
						   name="s"
						   value="<?php echo esc_attr( $search_query ); ?>"
						   placeholder="Search DuPage Flight Center..." />
					<button type="submit" class="dfc-search-page__submit" aria-label="Search">
						<svg aria-hidden="true" focusable="false" width="20" height="20" viewBox="0 0 22 21" fill="none">
							<path d="M17.3615 14.9808L22 19.3694L20.4676 20.8196L15.8302 16.43C14.1047 17.739 11.9585 18.451 9.74697 18.448C4.36664 18.448 0 14.3157 0 9.22401C0 4.13236 4.36664 0 9.74697 0C15.1273 0 19.4939 4.13236 19.4939 9.22401C19.4971 11.3168 18.7448 13.3479 17.3615 14.9808ZM15.189 14.2204C16.5632 12.8826 17.3307 11.0897 17.328 9.22401C17.328 5.26076 13.9349 2.04978 9.74697 2.04978C5.55902 2.04978 2.16599 5.26076 2.16599 9.22401C2.16599 13.1873 5.55902 16.3982 9.74697 16.3982C11.7184 16.4009 13.613 15.6745 15.0266 14.3741L15.189 14.2204Z" fill="currentColor"/>
						</svg>
					</button>
				</form>
			</div>
		</div>
	</div>

	<div class="dfc-search-page__inner">
		<?php if ( have_posts() ) : ?>
			<p class="dfc-search-page__count"><?php echo esc_html( $total ); ?> result<?php echo $total !== 1 ? 's' : ''; ?> found</p>

			<ul class="dfc-search-page__results" role="list">
				<?php while ( have_posts() ) : the_post(); ?>
					<li class="dfc-search-page__result">
						<a href="<?php the_permalink(); ?>" class="dfc-search-page__result-link">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="dfc-search-page__result-thumb">
									<?php the_post_thumbnail( 'thumbnail' ); ?>
								</div>
							<?php endif; ?>
							<div class="dfc-search-page__result-body">
								<span class="dfc-search-page__result-type"><?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ); ?></span>
								<h2 class="dfc-search-page__result-title"><?php the_title(); ?></h2>
								<?php if ( has_excerpt() || get_the_content() ) : ?>
									<p class="dfc-search-page__result-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 30 ); ?></p>
								<?php endif; ?>
							</div>
						</a>
					</li>
				<?php endwhile; ?>
			</ul>

			<nav class="dfc-search-page__pagination" aria-label="Search results pagination">
				<?php
				the_posts_pagination( array(
					'mid_size'  => 2,
					'prev_text' => '&larr; Previous',
					'next_text' => 'Next &rarr;',
				) );
				?>
			</nav>

		<?php else : ?>
			<div class="dfc-search-page__no-results">
				<h2>No results found</h2>
				<p>We couldn't find anything matching &ldquo;<?php echo esc_html( $search_query ); ?>&rdquo;. Try different keywords or browse our pages using the navigation above.</p>
			</div>
		<?php endif; ?>
	</div>

</section>

<?php get_footer(); ?>
