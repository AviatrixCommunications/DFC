<?php

$required_args = array( 'post_id' );
foreach ( $required_args as $i ) :
	if ( ! isset( $args[ $i ] ) ) :
		trigger_error( "Required argument \"$i\" not provided.", E_USER_WARNING );
	endif;
endforeach;

$post_id      = $args['post_id'];
$post_title   = wp_strip_all_tags( get_the_title( $post_id ) );
$post_date    = get_the_date( 'F j, Y', $post_id );
$post_date_iso = get_the_date( 'c', $post_id );
$permalink    = get_the_permalink( $post_id );
$categories = get_the_category( $post_id );

if ( has_excerpt( $post_id ) ) {
	$post_excerpt = get_the_excerpt( $post_id );
} else {
	$content_blocks = array( 'core/paragraph', 'core/heading', 'core/list', 'core/quote', 'core/pullquote', 'core/verse', 'core/preformatted' );
	$blocks         = parse_blocks( get_post_field( 'post_content', $post_id ) );
	$text           = '';

	foreach ( $blocks as $block ) {
		if ( in_array( $block['blockName'], $content_blocks, true ) ) {
			$text .= wp_strip_all_tags( render_block( $block ) ) . ' ';
		}
	}

	$post_excerpt = wp_trim_words( $text, 20 );
}
?>

<article class="post-item">
	<div class="post-item__header">
		<time class="post-item__date" datetime="<?php echo esc_attr( $post_date_iso ); ?>"><?php echo esc_html( $post_date ); ?></time>
		<?php if ( ! empty( $categories ) ) : ?>
			<div class="post-item__categories">
				<?php foreach ( $categories as $cat ) : ?>
					<span class="post-item__category"><?php echo esc_html( $cat->name ); ?></span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
	<h3 class="post-item__title"><?php echo esc_html( $post_title ); ?></h3>
	<p class="post-item__excerpt"><?php echo esc_html( $post_excerpt ); ?></p>
	<a class="post-item__read-more" href="<?php echo esc_url( $permalink ); ?>">
		Read more
		<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="15" height="12" viewBox="0 0 15 12" fill="none"><path d="M14.5303 6.05328C14.8232 5.76039 14.8232 5.28551 14.5303 4.99262L9.75736 0.219648C9.46447 -0.073245 8.98959 -0.073245 8.6967 0.219648C8.40381 0.512542 8.40381 0.987415 8.6967 1.28031L12.9393 5.52295L8.6967 9.76559C8.40381 10.0585 8.40381 10.5334 8.6967 10.8263C8.98959 11.1191 9.46447 11.1191 9.75736 10.8263L14.5303 6.05328ZM0 5.52295L0 6.27295L14 6.27295V5.52295V4.77295L0 4.77295L0 5.52295Z" fill="currentColor"/></svg>
		<span class="u-sr-only"> of "<?php echo esc_attr( $post_title ); ?>"</span>
	</a>
</article>
