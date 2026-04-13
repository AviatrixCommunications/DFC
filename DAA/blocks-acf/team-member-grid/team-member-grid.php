<?php

/**
 * Team Member Grid Block Template.
 *
 * Displays a grid of team-member posts for a selected team-member-category.
 * Supports two layouts: "photo_grid" (3-col headshot cards) and "contact_cards"
 * (2-col horizontal directory cards).
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML.
 * @param bool   $is_preview True during AJAX preview.
 * @param int    $post_id    The post ID this block is saved to.
 */

$base_class = 'aviatrix-block aviatrix-block--team-member-grid';

$wrapper = [ 'class' => $base_class . ' is-layout-constrained' ];
if ( ! empty( $block['anchor'] ) ) {
	$wrapper['id'] = $block['anchor'];
}

$attrs = get_block_wrapper_attributes( $wrapper );

// ── InnerBlocks config ─────────────────────────────────────────────────────
$lock     = [ 'move' => true, 'remove' => true ];
$allowed  = [ 'core/heading' ];
$template = [
	[ 'core/heading', [
		'level'       => 2,
		'placeholder' => 'Section title',
		'lock'        => $lock,
	] ],
];

// ── ACF field data ─────────────────────────────────────────────────────────
$category_id = get_field( 'team_member_category' );
$layout      = get_field( 'grid_layout' ) ?: 'photo_grid';

$layout_class = 'team-member-grid--' . esc_attr( $layout );

// ── Query team members ─────────────────────────────────────────────────────
$members = [];

if ( $category_id ) {
	$members = get_posts( [
		'post_type'      => 'team-member',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => [ 'menu_order' => 'ASC', 'title' => 'ASC' ],
		'no_found_rows'  => true,
		'tax_query'      => [
			[
				'taxonomy' => 'team-member-category',
				'field'    => 'term_id',
				'terms'    => $category_id,
			],
		],
	] );
}
?>

<section <?php echo $attrs; ?>>

	<?php // ── Header (InnerBlocks) ──────────────────────────────────────── ?>
	<?php if ( $is_preview ) : ?>
		<div class="team-member-grid__header has-global-padding">
			<InnerBlocks
				allowedBlocks="<?php echo esc_attr( wp_json_encode( $allowed ) ); ?>"
				template="<?php echo esc_attr( wp_json_encode( $template ) ); ?>"
			/>
		</div>
	<?php else : ?>
		<div class="team-member-grid__header has-global-padding js-fadein-up">
			<?php echo $content; ?>
		</div>
	<?php endif; ?>

	<?php // ── Grid ──────────────────────────────────────────────────────── ?>
	<?php if ( ! empty( $members ) ) : ?>

		<div class="team-member-grid__grid <?php echo $layout_class; ?> has-global-padding<?php echo $is_preview ? '' : ' js-fadein-up'; ?>">

			<?php foreach ( $members as $member ) :
				$name  = get_the_title( $member->ID );
				$role  = get_field( 'tm_role', $member->ID );
				$email = get_field( 'tm_email', $member->ID );
				$phone = get_field( 'tm_phone', $member->ID );
				$thumb = get_post_thumbnail_id( $member->ID );
			?>

				<?php if ( $layout === 'photo_grid' ) : ?>

					<div class="team-member-grid__card team-member-grid__card--photo">
						<div class="team-member-grid__photo">
							<?php if ( $thumb ) : ?>
								<?php echo wp_get_attachment_image( $thumb, 'medium_large', false, [
									'class'   => 'team-member-grid__img',
									'loading' => 'lazy',
									'alt'     => esc_attr( $name ),
								] ); ?>
							<?php endif; ?>
							<div class="team-member-grid__overlay">
								<h3 class="team-member-grid__name"><?php echo esc_html( $name ); ?></h3>
								<?php if ( $role ) : ?>
									<p class="team-member-grid__role"><?php echo esc_html( $role ); ?></p>
								<?php endif; ?>
							</div>
						</div>
					</div>

				<?php else : ?>

					<div class="team-member-grid__card team-member-grid__card--contact">
						<?php if ( $thumb ) : ?>
							<div class="team-member-grid__thumbnail">
								<?php echo wp_get_attachment_image( $thumb, 'medium', false, [
									'class'   => 'team-member-grid__img',
									'loading' => 'lazy',
									'alt'     => esc_attr( $name ),
								] ); ?>
							</div>
						<?php endif; ?>
						<div class="team-member-grid__info">
							<h3 class="team-member-grid__name">
								<?php echo esc_html( $name ); ?><?php if ( $role ) : ?>, <?php echo esc_html( $role ); ?><?php endif; ?>
							</h3>
							<?php if ( $email ) : ?>
								<p class="team-member-grid__detail">
									<strong>Email:</strong> <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
								</p>
							<?php endif; ?>
							<?php if ( $phone ) : ?>
								<p class="team-member-grid__detail">
									<strong>Phone:</strong> <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
								</p>
							<?php endif; ?>
						</div>
					</div>

				<?php endif; ?>

			<?php endforeach; ?>

		</div>

	<?php elseif ( $category_id ) : ?>
		<p class="has-global-padding">No team members found in this category.</p>
	<?php elseif ( $is_preview ) : ?>
		<p class="has-global-padding"><em>Select a team member category in the block sidebar.</em></p>
	<?php endif; ?>

</section>
