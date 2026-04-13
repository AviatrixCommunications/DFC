<?php 
get_header();

// Get the ID of the page set as the Posts page
$posts_page_id = get_option( 'page_for_posts' );

if ( $posts_page_id ) {
    $posts_page = get_post( $posts_page_id );
    if ( $posts_page && !empty( $posts_page->post_content ) ) {
        echo apply_filters( 'the_content', $posts_page->post_content ); 
    }
}
?>

<section class="posts-loop" aria-label="Project Updates Posts">
  <div class="wrapper">
    <?php if ( have_posts() ) : ?>
      <ul class="posts-loop__list">
        <?php while ( have_posts() ) : the_post(); ?>
            <li><?php
              $post_id = get_the_ID(); 
              get_component('post/post_item', array('post_id' => $post_id )); ?>
            </li>
        <?php endwhile; ?>
      </ul><?php
      if ( $wp_query->max_num_pages > 1 ) :
          $current_page = max( 1, get_query_var( 'paged' ) );
          $total_pages  = $wp_query->max_num_pages;

          $pagination_links = paginate_links( array(
              'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
              'format'    => '?paged=%#%',
              'current'   => $current_page,
              'total'     => $total_pages,
              'type'      => 'array',
              'prev_next' => false, 
          ) );
          ?>
          <nav class="pagination" role="navigation" aria-label="Pagination">
              <ul class="pagination-list">
                  <?php
 
                  if ( $current_page > 1 ) {
                      echo '<li class="pagination-prev"><a class="wp-block-button__link wp-block-button__link--prev" href="' . esc_url( get_pagenum_link( $current_page - 1 ) ) . '" aria-label="Previous Page">Prev</a></li>';
                  } else {
                      echo '<li class="pagination-prev disabled" aria-hidden="true"><span class="wp-block-button__link wp-block-button__link--prev">Prev</span></li>';
                  }

                  if ( is_array( $pagination_links ) ) { 
                    echo '<ul>';
                      foreach ( $pagination_links as $link ) {
                          echo '<li class="pagination-page">' . $link . '</li>';
                      }
                    echo '</ul>';
                  }

                  if ( $current_page < $total_pages ) {
                      echo '<li class="pagination-next"><a href="' . esc_url( get_pagenum_link( $current_page + 1 ) ) . '" aria-label="Next Page" class="wp-block-button__link">Next</a></li>';
                  } else {
                      echo '<li class="pagination-next disabled" aria-hidden="true"><span class="wp-block-button__link">Next</span></li>';
                  }
                  ?>
              </ul>
          </nav>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

<?php
get_footer();

