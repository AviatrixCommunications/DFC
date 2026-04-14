<?php
/**
 * 404 Error Page
 *
 * @package DFC
 */

get_header();
?>

<section class="error-404" aria-label="Page not found">
	<div class="error-404__header">
		<div class="wrapper">
			<h1 class="error-404__title">Page Not Found</h1>
		</div>
	</div>
	<div class="wrapper">
		<div class="error-404__content">
			<p class="text--large">We couldn't find the page you're looking for. It may have been moved or no longer exists.</p>
			<p>Try using the search below, or return to our homepage.</p>

			<form class="error-404__search" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" role="search">
				<label for="search-404" class="u-sr-only">Search this site</label>
				<div class="error-404__search-field">
					<input type="search" id="search-404" name="s" placeholder="Search DuPage Flight Center..." />
					<button type="submit" class="button" aria-label="Search">Search</button>
				</div>
			</form>

			<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="button">Return to Homepage</a></p>
		</div>
	</div>
</section>

<?php get_footer(); ?>
