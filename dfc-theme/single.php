<?php
/**
 * Single post template
 *
 * @package DFC
 */

get_header();

while ( have_posts() ) :
    the_post();
    ?>
    <div class="dfc-page-content">
        <?php the_content(); ?>
    </div>
    <?php
endwhile;

get_footer();
