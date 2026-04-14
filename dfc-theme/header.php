<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<a class="u-sr-only u-sr-only-focusable" href="#content">Skip to main content</a>

	<div class="site-top">
	<?php get_component('alert-banner'); ?>

	<header class="header" role="banner">
		<div class="wrapper wrapper--xl">
			<div class="header__components">

				<!-- Logo -->
				<div class="header__logo">
					<a href="<?php echo home_url(); ?>" rel="home">
						<?php
						$custom_logo_id = get_theme_mod( 'custom_logo' );
						if ( $custom_logo_id ) :
							$logo_url = wp_get_attachment_image_url( $custom_logo_id, 'full' );
							$logo_alt = get_post_meta( $custom_logo_id, '_wp_attachment_image_alt', true ) ?: 'DuPage Flight Center — Home';
						?>
							<img class="header__logo-img"
								 src="<?php echo esc_url( $logo_url ); ?>"
								 alt="<?php echo esc_attr( $logo_alt ); ?>"
								 width="284" height="50" />
						<?php else : ?>
							<img class="header__logo-img"
								 src="<?php echo get_template_directory_uri(); ?>/img/dfc-logo-horizontal-white.svg"
								 alt="DuPage Flight Center — Home"
								 width="284" height="50" />
						<?php endif; ?>
					</a>
				</div>

				<!-- Desktop Nav + Search + CTA -->
				<div class="header__menu">
					<nav class="nav--main" aria-label="Main Navigation">
						<?php dfc_main_nav(); ?>
					</nav>

					<!-- Search Toggle -->
					<button type="button"
							class="button--search js-search-toggle"
							aria-expanded="false"
							aria-controls="dfc-search-panel"
							aria-label="Open search">
						<svg class="nav__search-icon nav__search-icon--search" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" width="22" height="21" viewBox="0 0 22 21" fill="none">
							<path d="M17.3615 14.9808L22 19.3694L20.4676 20.8196L15.8302 16.43C14.1047 17.739 11.9585 18.451 9.74697 18.448C4.36664 18.448 0 14.3157 0 9.22401C0 4.13236 4.36664 0 9.74697 0C15.1273 0 19.4939 4.13236 19.4939 9.22401C19.4971 11.3168 18.7448 13.3479 17.3615 14.9808ZM15.189 14.2204C16.5632 12.8826 17.3307 11.0897 17.328 9.22401C17.328 5.26076 13.9349 2.04978 9.74697 2.04978C5.55902 2.04978 2.16599 5.26076 2.16599 9.22401C2.16599 13.1873 5.55902 16.3982 9.74697 16.3982C11.7184 16.4009 13.613 15.6745 15.0266 14.3741L15.189 14.2204Z" fill="currentColor"/>
						</svg>
						<svg class="nav__search-icon nav__search-icon--close" aria-hidden="true" focusable="false" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:none">
							<path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
						</svg>
					</button>

					<?php
					// Header CTA button from ACF options
					$header_btn = function_exists('get_field') ? get_field('header_button', 'option') : null;
					if ( $header_btn ) :
						$cta_target = $header_btn['target'] ?: '_self';
						$cta_rel    = $cta_target === '_blank' ? ' rel="noopener noreferrer"' : '';
						$cta_sr     = $cta_target === '_blank' ? '<span class="u-sr-only"> (opens in new tab)</span>' : '';
					?>
						<a class="button header__cta"
						   href="<?php echo esc_url( $header_btn['url'] ); ?>"
						   target="<?php echo esc_attr( $cta_target ); ?>"<?php echo $cta_rel; ?>>
							<?php echo esc_html( $header_btn['title'] ); ?><?php echo $cta_sr; ?>
						</a>
					<?php else : ?>
						<a class="button header__cta"
						   href="https://flightbridge.com/go/DuPage"
						   target="_blank"
						   rel="noopener noreferrer">
							Schedule an Arrival<span class="u-sr-only"> (opens in new tab)</span>
						</a>
					<?php endif; ?>

					<!-- Search Panel (AJAX dropdown) -->
					<div class="dfc-search-panel" id="dfc-search-panel" hidden>
						<div class="dfc-search-panel__inner wrapper wrapper--xl">
							<p class="dfc-search-panel__heading text--large">What are you looking for?</p>
							<form class="dfc-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" role="search">
								<label for="dfc-search-input" class="u-sr-only">Search</label>
								<div class="dfc-search-form__field">
									<input type="search"
										   id="dfc-search-input"
										   class="dfc-search-form__input"
										   name="s"
										   placeholder="Search DuPage Flight Center..."
										   autocomplete="off" />
									<button type="submit" class="dfc-search-form__submit" aria-label="Submit search">
										<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" width="22" height="21" viewBox="0 0 22 21" fill="none">
											<path d="M17.3615 14.9808L22 19.3694L20.4676 20.8196L15.8302 16.43C14.1047 17.739 11.9585 18.451 9.74697 18.448C4.36664 18.448 0 14.3157 0 9.22401C0 4.13236 4.36664 0 9.74697 0C15.1273 0 19.4939 4.13236 19.4939 9.22401C19.4971 11.3168 18.7448 13.3479 17.3615 14.9808ZM15.189 14.2204C16.5632 12.8826 17.3307 11.0897 17.328 9.22401C17.328 5.26076 13.9349 2.04978 9.74697 2.04978C5.55902 2.04978 2.16599 5.26076 2.16599 9.22401C2.16599 13.1873 5.55902 16.3982 9.74697 16.3982C11.7184 16.4009 13.613 15.6745 15.0266 14.3741L15.189 14.2204Z" fill="currentColor"/>
										</svg>
									</button>
								</div>
							</form>
							<div class="dfc-search-results" id="dfc-search-results" hidden role="region" aria-live="polite" aria-label="Search results"></div>
						</div>
					</div>
				</div>

				<!-- Mobile Hamburger -->
				<button class="hamburger hamburger--squeeze js-hamburger"
						aria-haspopup="true"
						aria-expanded="false"
						aria-label="Open mobile menu"
						aria-controls="nav-mobile">
					<span class="hamburger-box" aria-hidden="true">
						<span class="hamburger-inner"></span>
					</span>
				</button>

				<!-- Mobile Nav -->
				<nav class="nav--mobile js-mobile-nav" id="nav-mobile" aria-label="Mobile navigation" aria-hidden="true">
					<div class="nav--mobile__inner">
						<?php if ( $header_btn ) : ?>
							<a class="button"
							   href="<?php echo esc_url( $header_btn['url'] ); ?>"
							   target="<?php echo esc_attr( $cta_target ); ?>"<?php echo $cta_rel; ?>>
								<?php echo esc_html( $header_btn['title'] ); ?><?php echo $cta_sr; ?>
							</a>
						<?php else : ?>
							<a class="button" href="https://flightbridge.com/go/DuPage" target="_blank" rel="noopener noreferrer">
								Schedule an Arrival<span class="u-sr-only"> (opens in new tab)</span>
							</a>
						<?php endif; ?>
						<div class="nav--mobile__main">
							<?php dfc_main_nav(); ?>
							<div class="nav--mobile__search">
								<p>What are you looking for?</p>
								<form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" role="search" aria-label="Mobile search">
									<label for="mobile-search-input" class="u-sr-only">Search</label>
									<div class="nav--mobile__search-field">
										<input type="search" id="mobile-search-input" name="s" placeholder="Search..." />
										<button type="submit" aria-label="Submit search" class="button--search">
											<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" width="22" height="21" viewBox="0 0 22 21" fill="none">
												<path d="M17.3615 14.9808L22 19.3694L20.4676 20.8196L15.8302 16.43C14.1047 17.739 11.9585 18.451 9.74697 18.448C4.36664 18.448 0 14.3157 0 9.22401C0 4.13236 4.36664 0 9.74697 0C15.1273 0 19.4939 4.13236 19.4939 9.22401C19.4971 11.3168 18.7448 13.3479 17.3615 14.9808ZM15.189 14.2204C16.5632 12.8826 17.3307 11.0897 17.328 9.22401C17.328 5.26076 13.9349 2.04978 9.74697 2.04978C5.55902 2.04978 2.16599 5.26076 2.16599 9.22401C2.16599 13.1873 5.55902 16.3982 9.74697 16.3982C11.7184 16.4009 13.613 15.6745 15.0266 14.3741L15.189 14.2204Z" fill="currentColor"/>
											</svg>
										</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</nav>

			</div>
		</div>
	</header>
	</div>

	<main class="main" id="content">
