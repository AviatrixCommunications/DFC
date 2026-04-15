<?php
/**
 * Services Tabs Block
 *
 * Two-tab toggle with image + content. Used on homepage for Aircraft/Concierge services.
 * ACF fields: decorative_image (optional), tabs (repeater) with tab_label, tab_image, tab_content
 */

$tabs = get_field('service_tabs');
if (!$tabs) $tabs = [];

$deco_image = get_field('decorative_image');

$base_class = 'aviatrix-block aviatrix-block--services-tabs';
$attrs = get_block_wrapper_attributes(['class' => $base_class]);

$template = [
    ['core/heading', ['level' => 2, 'placeholder' => 'Welcome to DuPage Flight Center', 'align' => 'center', 'className' => 'services-tabs__heading']],
    ['core/paragraph', ['placeholder' => 'Description text...', 'align' => 'center']],
];
?>

<?php if ($is_preview) : ?>
    <section <?php echo $attrs; ?>>
        <?php if ( $deco_image ) : ?>
            <div class="services-tabs__deco" aria-hidden="true">
                <img src="<?php echo esc_url( $deco_image['url'] ); ?>" alt="" role="presentation" loading="lazy" />
            </div>
        <?php endif; ?>
        <div class="services-tabs__intro">
            <InnerBlocks template="<?php echo esc_attr(wp_json_encode($template)); ?>" />
        </div>
        <p style="text-align:center;color:#999;">Configure tabs in the block settings panel &rarr;</p>
    </section>
<?php else : ?>
    <section <?php echo $attrs; ?>><?php
        // Unique prefix per block instance to avoid ID collisions
        $uid = 'st-' . ( $block['id'] ?? wp_unique_id( 'services-tabs-' ) );
        ?>

        <?php if ( $deco_image ) : ?>
            <div class="services-tabs__deco" aria-hidden="true">
                <img src="<?php echo esc_url( $deco_image['url'] ); ?>" alt="" role="presentation" loading="lazy" />
            </div>
        <?php endif; ?>

        <div class="services-tabs__intro js-fadein-up">
            <?php echo $content; ?>
        </div>

        <?php if ($tabs) : ?>
            <div class="services-tabs__body">
                <div class="services-tabs__controls" role="tablist">
                    <?php foreach ($tabs as $i => $tab) : ?>
                        <button class="services-tabs__tab"
                                role="tab"
                                id="<?php echo esc_attr( $uid . '-tab-' . $i ); ?>"
                                aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
                                aria-controls="<?php echo esc_attr( $uid . '-panel-' . $i ); ?>">
                            <?php echo esc_html($tab['tab_label']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <?php foreach ($tabs as $i => $tab) : ?>
                    <div class="services-tabs__panel js-fadein-up"
                         role="tabpanel"
                         id="<?php echo esc_attr( $uid . '-panel-' . $i ); ?>"
                         aria-labelledby="<?php echo esc_attr( $uid . '-tab-' . $i ); ?>"
                         <?php echo $i !== 0 ? 'hidden' : ''; ?>>
                        <?php if (!empty($tab['tab_image'])) :
                            $tab_img_alt = $tab['tab_image']['alt'] ?: ( $tab['tab_image']['title'] ?: '' );
                        ?>
                            <div class="services-tabs__image">
                                <img src="<?php echo esc_url($tab['tab_image']['sizes']['slider-large'] ?? $tab['tab_image']['url']); ?>"
                                     alt="<?php echo esc_attr($tab_img_alt); ?>"
                                     loading="lazy" />
                            </div>
                        <?php endif; ?>
                        <div class="services-tabs__content">
                            <div class="services-tabs__text-group">
                                <h3><?php echo esc_html($tab['tab_heading'] ?? ''); ?></h3>
                                <div><?php echo wp_kses_post($tab['tab_content'] ?? ''); ?></div>
                            </div>
                            <?php if (!empty($tab['tab_button'])) : ?>
                                <a class="button" href="<?php echo esc_url($tab['tab_button']['url']); ?>"
                                   <?php if ($tab['tab_button']['target']) : ?>target="<?php echo esc_attr($tab['tab_button']['target']); ?>" rel="noopener noreferrer"<?php endif; ?>>
                                    <?php echo esc_html($tab['tab_button']['title']); ?><?php if ( ! empty( $tab['tab_button']['target'] ) && $tab['tab_button']['target'] === '_blank' ) : ?><span class="u-sr-only"> (opens in new tab)</span><?php endif; ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>
