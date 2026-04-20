<?php
/**
 * Proximity Table Block Template
 *
 * Repeater-driven table with alternating grey/white rows.
 * Each row shows a city name, drive time (clock icon), and distance (car icon).
 * Supports an optional heading via InnerBlocks.
 *
 * ACF fields: locations (repeater) with city (text), drive_time (text), distance (text)
 *
 * @param array  $block
 * @param string $content
 * @param bool   $is_preview
 */

$locations = get_field( 'locations' );
if ( ! $locations ) {
    $locations = [];
}

$base_class = 'aviatrix-block aviatrix-block--proximity-table js-fadein-up';
$attrs = get_block_wrapper_attributes( [ 'class' => $base_class ] );

$template = [
    [ 'core/heading', [
        'level'       => 2,
        'placeholder' => 'Location / Proximity',
        'align'       => 'center',
    ] ],
];
$allowed = [ 'core/heading', 'core/paragraph' ];
?>

<?php if ( $is_preview ) : ?>
    <section <?php echo $attrs; ?>>
        <div class="proximity-table__header wrapper">
            <InnerBlocks
                template="<?php echo esc_attr( wp_json_encode( $template ) ); ?>"
                allowedBlocks="<?php echo esc_attr( wp_json_encode( $allowed ) ); ?>"
            />
        </div>
        <?php if ( $locations ) : ?>
            <div class="proximity-table__body wrapper">
                <?php foreach ( $locations as $i => $loc ) : ?>
                    <div class="proximity-table__row <?php echo $i % 2 === 0 ? 'proximity-table__row--grey' : 'proximity-table__row--white'; ?>">
                        <span class="proximity-table__city"><?php echo esc_html( $loc['city'] ); ?></span>
                        <span class="proximity-table__time">
                            <svg class="proximity-table__icon" aria-hidden="true" width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.5 0C4.253 0 0 4.253 0 9.5S4.253 19 9.5 19 19 14.747 19 9.5 14.747 0 9.5 0zm0 17.1A7.607 7.607 0 011.9 9.5 7.607 7.607 0 019.5 1.9a7.607 7.607 0 017.6 7.6 7.607 7.607 0 01-7.6 7.6zm.475-12.35H8.55v5.225l4.57 2.74.713-1.169-3.858-2.288V4.75z" fill="currentColor"/></svg>
                            <?php echo esc_html( $loc['drive_time'] ); ?>
                        </span>
                        <span class="proximity-table__distance">
                            <svg class="proximity-table__icon" aria-hidden="true" width="21" height="19" viewBox="0 0 21 19" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18.375 6.333h-2.25L13.5 0H7.5L4.875 6.333H2.625A2.625 2.625 0 000 8.958v6.334h2.1v3.166h3.15v-3.166h10.5v3.166h3.15v-3.166H21V8.958a2.625 2.625 0 00-2.625-2.625zM8.663 1.9h3.674l1.688 4.433H6.975L8.663 1.9zM4.725 12.667a1.583 1.583 0 110-3.167 1.583 1.583 0 010 3.167zm11.55 0a1.583 1.583 0 110-3.167 1.583 1.583 0 010 3.167z" fill="currentColor"/></svg>
                            <?php echo esc_html( $loc['distance'] ); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p style="color:#999;text-align:center;padding:24px;">Add location rows in the block settings &rarr;</p>
        <?php endif; ?>
    </section>
<?php else : ?>
    <section <?php echo $attrs; ?>>
        <div class="proximity-table__header wrapper">
            <?php echo $content; ?>
        </div>
        <?php if ( $locations ) : ?>
            <div class="proximity-table__body wrapper" role="table" aria-label="Location proximity information">
                <div class="proximity-table__sr-header screen-reader-text" role="row">
                    <span role="columnheader">City</span>
                    <span role="columnheader">Drive Time</span>
                    <span role="columnheader">Distance</span>
                </div>
                <?php foreach ( $locations as $i => $loc ) : ?>
                    <div class="proximity-table__row <?php echo $i % 2 === 0 ? 'proximity-table__row--grey' : 'proximity-table__row--white'; ?>" role="row">
                        <span class="proximity-table__city" role="cell"><?php echo esc_html( $loc['city'] ); ?></span>
                        <span class="proximity-table__time" role="cell">
                            <svg class="proximity-table__icon" aria-hidden="true" focusable="false" width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.5 0C4.253 0 0 4.253 0 9.5S4.253 19 9.5 19 19 14.747 19 9.5 14.747 0 9.5 0zm0 17.1A7.607 7.607 0 011.9 9.5 7.607 7.607 0 019.5 1.9a7.607 7.607 0 017.6 7.6 7.607 7.607 0 01-7.6 7.6zm.475-12.35H8.55v5.225l4.57 2.74.713-1.169-3.858-2.288V4.75z" fill="currentColor"/></svg>
                            <span class="screen-reader-text">Drive time:</span>
                            <?php echo esc_html( $loc['drive_time'] ); ?>
                        </span>
                        <span class="proximity-table__distance" role="cell">
                            <svg class="proximity-table__icon" aria-hidden="true" focusable="false" width="21" height="19" viewBox="0 0 21 19" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18.375 6.333h-2.25L13.5 0H7.5L4.875 6.333H2.625A2.625 2.625 0 000 8.958v6.334h2.1v3.166h3.15v-3.166h10.5v3.166h3.15v-3.166H21V8.958a2.625 2.625 0 00-2.625-2.625zM8.663 1.9h3.674l1.688 4.433H6.975L8.663 1.9zM4.725 12.667a1.583 1.583 0 110-3.167 1.583 1.583 0 010 3.167zm11.55 0a1.583 1.583 0 110-3.167 1.583 1.583 0 010 3.167z" fill="currentColor"/></svg>
                            <span class="screen-reader-text">Distance:</span>
                            <?php echo esc_html( $loc['distance'] ); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>
