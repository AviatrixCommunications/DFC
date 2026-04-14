<?php
/**
 * DFC Footer
 *
 * Structure from Figma: Newsletter banner (red), Footer top (black), Footer bottom (light grey)
 */

// ACF option fields
$footer_address   = function_exists('get_field') ? get_field('airport_address', 'option') : '';
$address_link     = function_exists('get_field') ? get_field('airport_address_link', 'option') : '';
$email            = function_exists('get_field') ? get_field('footer_email', 'option') : 'dfcfuel@dupageflightcenter.com';
$phone            = function_exists('get_field') ? get_field('footer_phone', 'option') : '(630) 208-5600';
$phone_num        = preg_replace('/\D+/', '', $phone);
$toll_free        = function_exists('get_field') ? get_field('footer_toll_free', 'option') : '(800) 208-5690';
$toll_free_num    = preg_replace('/\D+/', '', $toll_free);
$fax              = function_exists('get_field') ? get_field('footer_fax', 'option') : '(630) 443-9077';
$fax_num          = preg_replace('/\D+/', '', $fax);
$newsletter_form  = function_exists('get_field') ? get_field('newsletter_gravity_form_id', 'option') : '';
?>
	</main>

	<!-- ── Newsletter Signup Banner ─────────────────────────────── -->
	<section class="newsletter" aria-label="Newsletter signup">
		<div class="wrapper">
			<div class="newsletter__inner">
				<div class="newsletter__heading">
					<h2 class="newsletter__title">Subscribe to Our Weekly Fuel Price Updates</h2>
				</div>
				<div class="newsletter__form">
					<?php if ( $newsletter_form && function_exists('gravity_form') ) :
						gravity_form( $newsletter_form, false, false, false, null, true, 0 );
					else : ?>
						<form class="newsletter__fallback-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="post">
							<div class="newsletter__fields">
								<div class="newsletter__field">
									<label for="newsletter-first" class="u-sr-only">First Name</label>
									<input type="text" id="newsletter-first" name="first_name" placeholder="First Name" autocomplete="given-name" required />
								</div>
								<div class="newsletter__field">
									<label for="newsletter-last" class="u-sr-only">Last Name</label>
									<input type="text" id="newsletter-last" name="last_name" placeholder="Last Name" autocomplete="family-name" required />
								</div>
								<div class="newsletter__field">
									<label for="newsletter-email" class="u-sr-only">Email Address</label>
									<input type="email" id="newsletter-email" name="email" placeholder="Email Address" autocomplete="email" required />
								</div>
								<button type="submit" class="button--secondary">Subscribe Now</button>
							</div>
						</form>
					<?php endif; ?>
					<p class="newsletter__disclaimer">By submitting this form, you are consenting to receive marketing emails from DuPage Airport Authority. You can revoke your consent to receive emails at any time by using the Safe Unsubscribe&reg; link, found at the bottom of every email.</p>
				</div>
			</div>
		</div>
	</section>

	<!-- ── Footer ───────────────────────────────────────────────── -->
	<footer class="footer" role="contentinfo">

		<!-- Top (black bg) -->
		<div class="footer__top">
			<div class="wrapper">
				<div class="footer__top-inner">

					<!-- Logo + Social -->
					<div class="footer__col footer__col--logo">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="footer__logo">
							<?php
							$footer_logo = function_exists('get_field') ? get_field('footer_logo', 'option') : null;
							if ( $footer_logo ) :
							?>
								<img class="footer__logo-img"
									 src="<?php echo esc_url( $footer_logo['url'] ); ?>"
									 alt="<?php echo esc_attr( $footer_logo['alt'] ?: 'DuPage Flight Center' ); ?>"
									 loading="lazy"
									 width="220" height="116" />
							<?php else :
								$custom_logo_id = get_theme_mod( 'custom_logo' );
								$logo_url = $custom_logo_id ? wp_get_attachment_image_url( $custom_logo_id, 'medium' ) : get_template_directory_uri() . '/img/dfc-logo-stacked-white.svg';
							?>
								<img class="footer__logo-img"
									 src="<?php echo esc_url( $logo_url ); ?>"
									 alt="DuPage Flight Center"
									 loading="lazy"
									 width="220" height="116" />
							<?php endif; ?>
						</a>
						<nav class="footer__social nav--social" aria-label="Social media links">
							<ul>
								<?php
								$social_links = [
									'facebook_link'  => ['label' => 'Facebook',  'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>'],
									'instagram_link' => ['label' => 'Instagram', 'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>'],
									'twitter_link'   => ['label' => 'X (Twitter)', 'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>'],
								];
								foreach ( $social_links as $field => $meta ) :
									$url = function_exists('get_field') ? get_field( $field, 'option' ) : '';
									if ( $url ) : ?>
										<li>
											<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
												<?php echo $meta['svg']; ?>
												<span class="u-sr-only"><?php echo esc_html( $meta['label'] ); ?> (opens in new tab)</span>
											</a>
										</li>
									<?php endif;
								endforeach;
								?>
							</ul>
						</nav>
					</div>

					<!-- Contact Info -->
					<div class="footer__col footer__col--contact footer__contact">
						<?php if ( $footer_address ) : ?>
							<p>
								<strong>Address:</strong>
								<?php if ( $address_link ) : ?>
									<a href="<?php echo esc_url( $address_link ); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html( $footer_address ); ?><span class="u-sr-only"> (opens in new tab)</span>
									</a>
								<?php else : ?>
									<?php echo esc_html( $footer_address ); ?>
								<?php endif; ?>
							</p>
						<?php endif; ?>

						<?php if ( $email ) : ?>
							<p><strong>Email:</strong> <a href="mailto:<?php echo antispambot( $email ); ?>"><?php echo antispambot( $email ); ?></a></p>
						<?php endif; ?>

						<?php if ( $phone ) : ?>
							<p><strong>Phone Number:</strong> <a href="tel:+1<?php echo esc_attr( $phone_num ); ?>">+1 <?php echo esc_html( $phone ); ?></a></p>
						<?php endif; ?>

						<?php if ( $toll_free ) : ?>
							<p><strong>Toll Free Phone Number:</strong> <a href="tel:+1<?php echo esc_attr( $toll_free_num ); ?>">+1 <?php echo esc_html( $toll_free ); ?></a></p>
						<?php endif; ?>

						<?php if ( $fax ) : ?>
							<p><strong>Fax Number:</strong> <a href="tel:+1<?php echo esc_attr( $fax_num ); ?>">+1 <?php echo esc_html( $fax ); ?></a></p>
						<?php endif; ?>
					</div>

					<!-- External Links + Partner Logos -->
					<div class="footer__col footer__col--links">
						<div class="footer__links">
							<?php
							$daa_link = function_exists('get_field') ? get_field('daa_link', 'option') : 'https://www.dupageairport.com/';
							$plgc_link = function_exists('get_field') ? get_field('plgc_link', 'option') : 'https://www.prairielanding.com/';
							$employment_url = function_exists('get_field') ? get_field('employment_link', 'option') : '';
							if ( $daa_link ) : ?>
								<a href="<?php echo esc_url( $daa_link ); ?>" target="_blank" rel="noopener noreferrer">Visit DuPage Airport Authority<span class="u-sr-only"> (opens in new tab)</span></a>
							<?php endif; ?>
							<?php if ( $plgc_link ) : ?>
								<a href="<?php echo esc_url( $plgc_link ); ?>" target="_blank" rel="noopener noreferrer">Visit Prairie Landing Golf Club<span class="u-sr-only"> (opens in new tab)</span></a>
							<?php endif; ?>
							<?php if ( $employment_url ) : ?>
								<a href="<?php echo esc_url( $employment_url ); ?>" target="_blank" rel="noopener noreferrer">Employment Opportunities<span class="u-sr-only"> (opens in new tab)</span></a>
							<?php endif; ?>
						</div>
						<div class="footer__partners">
							<?php
							$partner_logos = function_exists('get_field') ? get_field('partner_logos', 'option') : [];
							if ( $partner_logos ) :
								foreach ( $partner_logos as $partner ) :
									$logo = $partner['partner_logo'] ?? null;
									$url  = $partner['partner_url'] ?? '';
									$name = $partner['partner_name'] ?? '';
									if ( ! $logo ) continue;
									?>
									<?php if ( $url ) : ?>
										<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $name ?: 'Partner' ); ?> (opens in new tab)">
									<?php endif; ?>
										<img src="<?php echo esc_url( $logo['url'] ); ?>"
											 alt="<?php echo esc_attr( $name ?: $logo['alt'] ); ?>"
											 loading="lazy"
											 width="<?php echo esc_attr( $logo['width'] ); ?>"
											 height="<?php echo esc_attr( $logo['height'] ); ?>"
											 style="max-height: 40px; width: auto;" />
									<?php if ( $url ) : ?>
										</a>
									<?php endif; ?>
								<?php endforeach;
							endif; ?>
						</div>
					</div>

				</div>
			</div>
		</div>

		<!-- Bottom (light grey bg) -->
		<div class="footer__bottom">
			<div class="wrapper">
				<nav aria-label="Legal and policy links">
					<?php
					wp_nav_menu( array(
						'theme_location' => 'footer_nav_btm',
						'menu_id'        => 'footer-menu-btm',
						'container'      => false,
						'depth'          => 1,
					) );
					?>
				</nav>
			</div>
		</div>

	</footer>

	<?php wp_footer(); ?>
</body>
</html>
