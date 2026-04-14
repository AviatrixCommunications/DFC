<?php
/**
 * Icon List Block
 *
 * Vertical list with icons and descriptions. Used on Customer Service page.
 * ACF fields: icon_items (repeater) with icon (image), text (text)
 */

$items = get_field('icon_items');
if (!$items) $items = [];

$base_class = 'aviatrix-block aviatrix-block--icon-list';
$attrs = get_block_wrapper_attributes(['class' => $base_class]);
?>

<div <?php echo $attrs; ?>>
    <?php if ($items) : ?>
        <ul class="icon-list__items" role="list">
            <?php foreach ($items as $item) : ?>
                <li class="icon-list__item">
                    <?php if (!empty($item['icon'])) : ?>
                        <div class="icon-list__icon">
                            <img src="<?php echo esc_url($item['icon']['url']); ?>"
                                 alt="" aria-hidden="true" width="25" height="25" loading="lazy" />
                        </div>
                    <?php endif; ?>
                    <span class="icon-list__text"><?php echo wp_kses_post($item['text']); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php elseif ($is_preview) : ?>
        <p style="color:#999;text-align:center;">Add icon list items in the block settings &rarr;</p>
    <?php endif; ?>
</div>
