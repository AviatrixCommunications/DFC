<?php

/**
 * Blog Meta Block Template.
 *
 * Two-column layout: image (left) + sidebar info boxes (right).
 * Sidebar boxes are PHP-rendered from ACF option fields.
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML (empty).
 * @param bool   $is_preview True during AJAX preview.
 * @param int    $post_id    The post ID this block is saved to.
 */

$wrapper = [ 'class' => 'aviatrix-block aviatrix-block--blog-meta' ];
if ( ! empty( $block['anchor'] ) ) {
	$wrapper['id'] = $block['anchor'];
}

$attrs = get_block_wrapper_attributes( $wrapper );

$lock = [ 'move' => true, 'remove' => true ];

$template = [
	[ 'core/image', [
		'className' => 'blog-meta__image js-fadein',
		'sizeSlug'  => 'large',
		'lock'      => $lock,
	] ],
];

// Sidebar data from ACF options (same fields as single.php).
$media_email = get_field( 'media_email', 'option' );
$media_phone = get_field( 'media_phone', 'option' );
$events_link = get_field( 'events_calendar_link', 'option' );

$social_links = [
	'facebook_link'  => [ 'label' => 'Facebook',  'icon' => 'facebook.svg' ],
	'instagram_link' => [ 'label' => 'Instagram', 'icon' => 'instagram.svg' ],
	'linkedin_link'  => [ 'label' => 'LinkedIn',  'icon' => 'linkedin.svg' ],
	'twitter_link'   => [ 'label' => 'Twitter',   'icon' => 'twitter.svg' ],
];

ob_start(); ?>

<?php if ( $media_email || $media_phone ) : ?>
<div class="blog-meta__box blog-meta__box--media">
	<h3>
		<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M3 4V18C3 18.5304 3.21071 19.0391 3.58579 19.4142C3.96086 19.7893 4.46957 20 5 20H19C19.5304 20 20.0391 19.7893 20.4142 19.4142C20.7893 19.0391 21 18.5304 21 18V8H17" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			<path d="M3 4H17V18C17 18.5304 17.2107 19.0391 17.5858 19.4142C17.9609 19.7893 18.4696 20 19 20M13 8H7M13 12H9" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
		<span>Media and Press Inquiries</span>
	</h3>
	<?php if ( $media_email ) : ?>
		<p><strong>Email:</strong> <a href="mailto:<?php echo antispambot( $media_email ); ?>"><?php echo antispambot( $media_email ); ?></a></p>
	<?php endif; ?>
	<?php if ( $media_phone ) : ?>
		<p><strong>Phone:</strong> <a href="tel:<?php echo esc_attr( preg_replace( '/\D+/', '', $media_phone ) ); ?>"><?php echo esc_html( $media_phone ); ?></a></p>
	<?php endif; ?>
</div>
<?php endif; ?>

<?php if ( $events_link ) : ?>
<div class="blog-meta__box blog-meta__box--events">
	<h3>
		<a href="<?php echo esc_url( $events_link ); ?>" target="_blank" rel="noopener noreferrer">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path fill-rule="evenodd" clip-rule="evenodd" d="M4.8 2.40002H19.2C20.473 2.40002 21.6939 2.90574 22.5941 3.80591C23.4943 4.70609 24 5.92699 24 7.20002V19.2C24 20.4731 23.4943 21.694 22.5941 22.5941C21.6939 23.4943 20.473 24 19.2 24H4.8C3.52696 24 2.30606 23.4943 1.40589 22.5941C0.505713 21.694 0 20.4731 0 19.2V7.20002C0 5.92699 0.505713 4.70609 1.40589 3.80591C2.30606 2.90574 3.52696 2.40002 4.8 2.40002ZM4.8 4.80002C4.16348 4.80002 3.55303 5.05288 3.10294 5.50297C2.65286 5.95306 2.4 6.56351 2.4 7.20002V19.2C2.4 19.8365 2.65286 20.447 3.10294 20.8971C3.55303 21.3472 4.16348 21.6 4.8 21.6H19.2C19.8365 21.6 20.447 21.3472 20.8971 20.8971C21.3471 20.447 21.6 19.8365 21.6 19.2V7.20002C21.6 6.56351 21.3471 5.95306 20.8971 5.50297C20.447 5.05288 19.8365 4.80002 19.2 4.80002H4.8Z" fill="black"/>
				<path fill-rule="evenodd" clip-rule="evenodd" d="M1.2002 9.6C1.2002 9.28174 1.32662 8.97652 1.55167 8.75147C1.77671 8.52643 2.08194 8.4 2.4002 8.4H21.6002C21.9185 8.4 22.2237 8.52643 22.4487 8.75147C22.6738 8.97652 22.8002 9.28174 22.8002 9.6C22.8002 9.91826 22.6738 10.2235 22.4487 10.4485C22.2237 10.6736 21.9185 10.8 21.6002 10.8H2.4002C2.08194 10.8 1.77671 10.6736 1.55167 10.4485C1.32662 10.2235 1.2002 9.91826 1.2002 9.6ZM7.2002 0C7.51846 0 7.82368 0.126428 8.04872 0.351472C8.27377 0.576515 8.4002 0.88174 8.4002 1.2V6C8.4002 6.31826 8.27377 6.62348 8.04872 6.84853C7.82368 7.07357 7.51846 7.2 7.2002 7.2C6.88194 7.2 6.57671 7.07357 6.35167 6.84853C6.12662 6.62348 6.0002 6.31826 6.0002 6V1.2C6.0002 0.88174 6.12662 0.576515 6.35167 0.351472C6.57671 0.126428 6.88194 0 7.2002 0ZM16.8002 0C17.1185 0 17.4237 0.126428 17.6487 0.351472C17.8738 0.576515 18.0002 0.88174 18.0002 1.2V6C18.0002 6.31826 17.8738 6.62348 17.6487 6.84853C17.4237 7.07357 17.1185 7.2 16.8002 7.2C16.4819 7.2 16.1767 7.07357 15.9517 6.84853C15.7266 6.62348 15.6002 6.31826 15.6002 6V1.2C15.6002 0.88174 15.7266 0.576515 15.9517 0.351472C16.1767 0.126428 16.4819 0 16.8002 0Z" fill="black"/>
				<path d="M7.1998 13.2C7.1998 13.5183 7.07338 13.8235 6.84833 14.0485C6.62329 14.2736 6.31806 14.4 5.9998 14.4C5.68154 14.4 5.37632 14.2736 5.15128 14.0485C4.92623 13.8235 4.7998 13.5183 4.7998 13.2C4.7998 12.8817 4.92623 12.5765 5.15128 12.3515C5.37632 12.1264 5.68154 12 5.9998 12C6.31806 12 6.62329 12.1264 6.84833 12.3515C7.07338 12.5765 7.1998 12.8817 7.1998 13.2ZM7.1998 18C7.1998 18.3183 7.07338 18.6235 6.84833 18.8485C6.62329 19.0736 6.31806 19.2 5.9998 19.2C5.68154 19.2 5.37632 19.0736 5.15128 18.8485C4.92623 18.6235 4.7998 18.3183 4.7998 18C4.7998 17.6817 4.92623 17.3765 5.15128 17.1515C5.37632 16.9264 5.68154 16.8 5.9998 16.8C6.31806 16.8 6.62329 16.9264 6.84833 17.1515C7.07338 17.3765 7.1998 17.6817 7.1998 18ZM13.1998 13.2C13.1998 13.5183 13.0734 13.8235 12.8483 14.0485C12.6233 14.2736 12.3181 14.4 11.9998 14.4C11.6815 14.4 11.3763 14.2736 11.1513 14.0485C10.9262 13.8235 10.7998 13.5183 10.7998 13.2C10.7998 12.8817 10.9262 12.5765 11.1513 12.3515C11.3763 12.1264 11.6815 12 11.9998 12C12.3181 12 12.6233 12.1264 12.8483 12.3515C13.0734 12.5765 13.1998 12.8817 13.1998 13.2ZM13.1998 18C13.1998 18.3183 13.0734 18.6235 12.8483 18.8485C12.6233 19.0736 12.3181 19.2 11.9998 19.2C11.6815 19.2 11.3763 19.0736 11.1513 18.8485C10.9262 18.6235 10.7998 18.3183 10.7998 18C10.7998 17.6817 10.9262 17.3765 11.1513 17.1515C11.3763 16.9264 11.6815 16.8 11.9998 16.8C12.3181 16.8 12.6233 16.9264 12.8483 17.1515C13.0734 17.3765 13.1998 17.6817 13.1998 18ZM19.1998 13.2C19.1998 13.5183 19.0734 13.8235 18.8483 14.0485C18.6233 14.2736 18.3181 14.4 17.9998 14.4C17.6815 14.4 17.3763 14.2736 17.1513 14.0485C16.9262 13.8235 16.7998 13.5183 16.7998 13.2C16.7998 12.8817 16.9262 12.5765 17.1513 12.3515C17.3763 12.1264 17.6815 12 17.9998 12C18.3181 12 18.6233 12.1264 18.8483 12.3515C19.0734 12.5765 19.1998 12.8817 19.1998 13.2ZM19.1998 18C19.1998 18.3183 19.0734 18.6235 18.8483 18.8485C18.6233 19.0736 18.3181 19.2 17.9998 19.2C17.6815 19.2 17.3763 19.0736 17.1513 18.8485C16.9262 18.6235 16.7998 18.3183 16.7998 18C16.7998 17.6817 16.9262 17.3765 17.1513 17.1515C17.3763 16.9264 17.6815 16.8 17.9998 16.8C18.3181 16.8 18.6233 16.9264 18.8483 17.1515C19.0734 17.3765 19.1998 17.6817 19.1998 18Z" fill="black"/>
			</svg>
			<span>View Our Events Calendar</span>
			<svg xmlns="http://www.w3.org/2000/svg" width="15" height="12" viewBox="0 0 15 12" fill="none">
				<path d="M14.5303 6.05328C14.8232 5.76039 14.8232 5.28551 14.5303 4.99262L9.75736 0.219648C9.46447 -0.073245 8.98959 -0.073245 8.6967 0.219648C8.40381 0.512542 8.40381 0.987415 8.6967 1.28031L12.9393 5.52295L8.6967 9.76559C8.40381 10.0585 8.40381 10.5334 8.6967 10.8263C8.98959 11.1191 9.46447 11.1191 9.75736 10.8263L14.5303 6.05328ZM0 5.52295L0 6.27295L14 6.27295V5.52295V4.77295L0 4.77295L0 5.52295Z" fill="black"/>
			</svg>
		</a>
	</h3>
</div>
<?php endif; ?>

<div class="blog-meta__box blog-meta__box--social">
	<h3>Follow Us on Social Media</h3>
	<nav class="blog-meta__social nav--social" aria-label="Social media links">
		<ul>
			<?php foreach ( $social_links as $field => $meta ) :
				$url = get_field( $field, 'option' );
				if ( $url ) : ?>
					<li>
						<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
							<img src="<?php echo get_template_directory_uri(); ?>/img/<?php echo esc_attr( $meta['icon'] ); ?>" alt="" aria-hidden="true" loading="lazy" />
							<span class="u-sr-only"><?php echo esc_html( $meta['label'] ); ?></span>
						</a>
					</li>
				<?php endif;
			endforeach; ?>
		</ul>
	</nav>
</div>

<?php $sidebar_html = ob_get_clean(); ?>

<?php if ( $is_preview ) : // ---------- EDITOR ---------- ?>

	<section <?php echo $attrs; ?>>
		<div class="wrapper">
			<div class="blog-meta__columns">
				<div class="blog-meta__image-col">
					<InnerBlocks
						template="<?php echo esc_attr( wp_json_encode( $template ) ); ?>"
						templateLock="all"
					/>
				</div>
				<aside class="blog-meta__aside">
					<?php echo $sidebar_html; ?>
				</aside>
			</div>
		</div>
	</section>

<?php else : // ---------- FRONTEND ---------- ?>

	<section <?php echo $attrs; ?>>
		<div class="wrapper">
			<div class="blog-meta__columns">
				<div class="blog-meta__image-col">
					<?php echo $content; ?>
				</div>
				<aside class="blog-meta__aside js-fadein-up">
					<?php echo $sidebar_html; ?>
				</aside>
			</div>
		</div>
	</section>

<?php endif; ?>
