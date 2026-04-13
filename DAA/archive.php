<?php
get_header(); ?>
<header class="is-style-hero-var-1 wp-block-acf-hero">
  <div class="hero is-style-hero-var-1">
    <div class="hero__image-wrap hero__image-wrap--desktop">
      <img loading="lazy" src="<?php echo get_template_directory_uri() ?>/img/foam-pattern.svg" alt="" />
    </div>
    <div class="wrapper">
      <div class="hero__content"><?php
        if ( is_post_type_archive( ) ) {
          if ( is_post_type_archive( 'event' ) ) {
            echo '<h1 class="wp-block-heading heading--xxl">Events</h1>';
          } else {
            post_type_archive_title( '<h1 class="wp-block-heading heading--xxl">', '</h1>' );
          }
        } else {
          the_archive_title( '<h1 class="wp-block-heading heading--xxl">', '</h1>' );
        }
        the_archive_description( '<p>', '</p>' ); ?>
      </div>
    </div>
  </div>
</header>
<section class="archive__main">
  <div class="wrapper">
    <?php
    if ( have_posts() ) : ?>
      <ul class="grid"><?php
        while ( have_posts() ) : the_post(); ?>
          <li class="col-12 col-6--md col-4--lg"><?php
            get_component('post/post_item', array('post_id' => get_the_ID()) ); ?>
          </li><?php
        endwhile;
        the_posts_pagination(); ?>
      </ul><?php
    endif; ?>
  </div>
</section>
<?php get_footer(); ?>
