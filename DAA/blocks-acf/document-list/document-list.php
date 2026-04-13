<?php

/**
 * Document List Block Template.
 *
 * Displays a filterable list of financial-document posts for a chosen
 * document-type taxonomy term, grouped by document-year.
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block inner HTML.
 * @param bool   $is_preview True during AJAX preview.
 * @param int    $post_id    The post ID this block is saved to.
 */

$block_id   = $block['id'];
$base_class = 'aviatrix-block aviatrix-block--document-list';

$wrapper = [ 'class' => $base_class . ' is-layout-constrained' ];
if ( ! empty( $block['anchor'] ) ) {
	$wrapper['id'] = $block['anchor'];
}

$attrs = get_block_wrapper_attributes( $wrapper );

// ── InnerBlocks config ─────────────────────────────────────────────────────
$lock     = [ 'move' => true, 'remove' => true ];
$allowed  = [ 'core/heading', 'core/paragraph', 'core/list', 'core/list-item' ];
$template = [
	[ 'core/heading',   [ 'level' => 2, 'placeholder' => 'Section title', 'lock' => $lock ] ],
	[ 'core/heading',   [ 'level' => 3, 'placeholder' => 'Subtitle (optional)' ] ],
	[ 'core/paragraph', [ 'placeholder' => 'Description text (optional)' ] ],
];

// ── Data query (shared by editor + frontend) ───────────────────────────────
$document_type_id = get_field( 'document_type' );
$grouped          = [];
$button_label     = 'View Document';
$default_year     = '';

if ( $document_type_id ) {
	$type_term    = get_term( $document_type_id, 'document-type' );
	$button_label = get_field( 'button_label', 'document-type_' . $document_type_id );
	if ( ! $button_label ) {
		$button_label = 'View Document';
	}

	$all_posts = get_posts( [
		'post_type'      => 'financial-document',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'ASC',
		'no_found_rows'  => true,
		'tax_query'      => [
			[
				'taxonomy' => 'document-type',
				'field'    => 'term_id',
				'terms'    => $document_type_id,
			],
		],
	] );

	foreach ( $all_posts as $doc ) {
		$year_terms = wp_get_post_terms( $doc->ID, 'document-year' );
		if ( empty( $year_terms ) || is_wp_error( $year_terms ) ) {
			continue;
		}
		$year_slug = $year_terms[0]->slug;
		$year_name = $year_terms[0]->name;

		if ( ! isset( $grouped[ $year_slug ] ) ) {
			$grouped[ $year_slug ] = [
				'name'  => $year_name,
				'posts' => [],
			];
		}
		$grouped[ $year_slug ]['posts'][] = $doc;
	}

	ksort( $grouped );

	// PHP coerces numeric string keys to integers; re-key as strings.
	$grouped = array_combine( array_map( 'strval', array_keys( $grouped ) ), array_values( $grouped ) );

	if ( ! empty( $grouped ) ) {
		$current_year = (string) date( 'Y' );
		$default_year = array_key_exists( $current_year, $grouped ) ? $current_year : (string) array_key_first( $grouped );
	}
}

$select_id = 'doc-year-filter-' . esc_attr( $block_id );
$status_id = 'document-list-status-' . esc_attr( $block_id );
?>

<section <?php echo $attrs; ?>>

	<?php // ── Header ───────────────────────────────────────────────────────── ?>
	<?php if ( $is_preview ) : ?>
		<div class="document-list__header has-global-padding">
			<InnerBlocks
				allowedBlocks="<?php echo esc_attr( wp_json_encode( $allowed ) ); ?>"
				template="<?php echo esc_attr( wp_json_encode( $template ) ); ?>"
			/>
		</div>
	<?php else : ?>
		<div class="document-list__header has-global-padding js-fadein-up">
			<?php echo $content; ?>
		</div>
	<?php endif; ?>

	<?php // ── Document list ────────────────────────────────────────────────── ?>
	<?php if ( ! empty( $grouped ) ) : ?>

		<div class="document-list__controls has-global-padding<?php echo $is_preview ? '' : ' js-fadein-up'; ?>">
			<select
				id="<?php echo $select_id; ?>"
				class="document-list__year-select"
				aria-label="Filter by year"
				<?php if ( $is_preview ) : ?>disabled aria-disabled="true" tabindex="-1"<?php endif; ?>
			>
				<?php foreach ( $grouped as $slug => $year_data ) : ?>
					<option
						value="<?php echo esc_attr( $slug ); ?>"
						<?php selected( $slug, $default_year ); ?>
					>
						<?php echo esc_html( $year_data['name'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="u-sr-only" aria-live="polite" aria-atomic="true" id="<?php echo $status_id; ?>"></div>

		<ul class="document-list__list has-global-padding<?php echo $is_preview ? '' : ' js-fadein-up'; ?>" role="list">
			<?php foreach ( $grouped as $slug => $year_data ) :
				if ( $is_preview && (string) $slug !== $default_year ) {
					continue;
				}
				foreach ( $year_data['posts'] as $doc ) :
					$file_url = get_field( 'document_file', $doc->ID );
					if ( ! $file_url ) {
						continue;
					}
					$title     = get_the_title( $doc->ID );
					$is_hidden = ( ! $is_preview && (string) $slug !== $default_year );
					?>
					<li
						class="document-list__item"
						data-year="<?php echo esc_attr( $slug ); ?>"
						<?php echo $is_hidden ? 'hidden' : ''; ?>
					>
						<h4 class="document-list__item-title"><?php echo esc_html( $title ); ?></h4>
						<a
							href="<?php echo esc_url( $file_url ); ?>"
							class="button document-list__btn"
							target="_blank"
							rel="noopener"
						>
							<?php echo esc_html( $button_label ); ?>
							<span class="u-sr-only"> for <?php echo esc_html( $title ); ?> <?php echo esc_html( $year_data['name'] ); ?> (opens in a new tab)</span>
						</a>
					</li>
				<?php endforeach;
			endforeach; ?>
		</ul>

	<?php elseif ( $document_type_id ) : ?>
		<p class="has-global-padding">No documents found.</p>
	<?php elseif ( $is_preview ) : ?>
		<p class="has-global-padding"><em>Select a document type in the block sidebar.</em></p>
	<?php endif; ?>

</section>
