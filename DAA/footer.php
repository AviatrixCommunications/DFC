<?php 
$footer_address = get_field('airport_address', 'option');
$address_link = get_field('airport_address_link', 'option');
$email = get_field('footer_email', 'option');
$phone = get_field('footer_phone', 'option');
$phone_num = preg_replace('/\D+/', '', $phone);
$fax = get_field('footer_fax', 'option');
$fax_num = preg_replace('/\D+/', '', $fax); ?>    
    </main>
    <footer class="footer">
      <div class="footer__top">
        <div class="wrapper">
          <div class="footer__top-inner">
            <div class="footer__top-left">
              <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="footer__logo">
                <img class="footer__logo-img" src="<?php echo get_template_directory_uri(); ?>/img/logo-sm.png" alt="DuPage Airport Authority" loading="lazy" />
              </a>
              <?php if ( $footer_address ) : ?>
                <p>Address:</p>
                <address><?php if ( $address_link ) : ?>
                  <a href="<?php echo esc_url( $address_link ); ?>" target="_blank" rel="noopener noreferrer">
                    <?php echo nl2br( esc_html( $footer_address ) ); ?>
                  </a>
                <?php else : ?> 
                  <?php echo nl2br( esc_html( $footer_address ) );
                endif; ?>
                </address>
              <?php endif; ?>
            </div>
            <div class="footer__top-middle"><?php 
              if ( $email ) : ?>
                <p>Email: <a href="mailto:<?php echo antispambot( $email ); ?>"><?php echo antispambot( $email ); ?></a></p>
              <?php endif; ?>
              <?php if ( $phone ) : ?>
                <p>Phone: <a href="tel:<?php echo esc_attr( $phone_num ); ?>"><?php echo esc_html( $phone ); ?></a></p>
              <?php endif; ?>
              <?php if ( $fax ) : ?>
                <p>Fax: <a href="tel:<?php echo esc_attr( $fax_num ); ?>"><?php echo esc_html( $fax ); ?></a></p>
              <?php endif; ?>
              <nav class="footer__social nav--social" aria-label="Social media links">
                <ul>
                  <?php
                  $social_links = [
                    'facebook_link'  => ['label' => 'Facebook',  'icon' => 'facebook.svg'],
                    'instagram_link' => ['label' => 'Instagram', 'icon' => 'instagram.svg'],
                    'linkedin_link'  => ['label' => 'LinkedIn',  'icon' => 'linkedin.svg'],
                    'twitter_link'   => ['label' => 'Twitter',   'icon' => 'twitter.svg'],
                  ];
                  foreach ( $social_links as $field => $meta ) :
                    $url = get_field( $field, 'option' );
                    if ( $url ) : ?>
                      <li>
                        <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
                          <img src="<?php echo get_template_directory_uri(); ?>/img/<?php echo esc_attr( $meta['icon'] ); ?>" alt="" aria-hidden="true" loading="lazy" />
                          <span class="u-sr-only"><?php echo esc_html( $meta['label'] ); ?></span>
                        </a>
                      </li>
                    <?php endif;
                  endforeach;
                  ?>
                </ul>
              </nav>
            </div>
            <div class="footer__top-right">
              <a href="https://www.dupageflightcenter.com/" target="_blank" rel="noopener" class="footer__logo footer__logo--dpcc">
                <img class="footer__logo-img" src="<?php echo get_template_directory_uri(); ?>/img/dfc-logo.png" alt="Chicagoland DuPage Flight Center" loading="lazy" />
              </a>
              <a href="https://www.prairielanding.com/" target="_blank" rel="noopener" class="footer__logo footer__logo--plgc">
                <img class="footer__logo-img" src="<?php echo get_template_directory_uri(); ?>/img/plgc-logo.png" alt="Prairie Landing Golf Club" loading="lazy" />
              </a><?php
              wp_nav_menu( array(
                  'theme_location' => 'footer_nav',
                  'menu_id'        => 'footer-menu',
                  'container'      => false,
                ) );
              ?>
            </div>
          </div>
        </div>
      </div>
      <div class="footer__bottom">
        <div class="wrapper"><?php
          wp_nav_menu( array(
            'theme_location' => 'footer_nav_btm',
            'menu_id'        => 'footer-menu-btm',
            'container'      => false,
          ) );
        ?>
        </div>
      </div>
    </footer>
  <?php wp_footer(); ?>
  </body>
</html>