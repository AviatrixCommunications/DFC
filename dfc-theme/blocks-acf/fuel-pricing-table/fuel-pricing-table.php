<?php
/**
 * Fuel Pricing Table Block
 *
 * Two views toggled by the user:
 *  1. Table View — the existing full pricing tables (from shortcode)
 *  2. Calculator View — interactive price lookup (same ACF data, friendlier UX)
 */

$base_class = 'aviatrix-block aviatrix-block--fuel-pricing';
$attrs = get_block_wrapper_attributes(['class' => $base_class]);

// Build calculator data from ACF for the JS-powered view
$calc_data = [ 'jet' => [], 'avgas' => [] ];
foreach ( [ 'jet' => 'jet_fuel_tiers', 'avgas' => 'avgas_tiers' ] as $section => $field ) {
    $tiers = get_field( $field, 'option' );
    if ( ! $tiers ) continue;
    foreach ( $tiers as $tier ) {
        $rows = $tier['rows'] ?? [];
        foreach ( $rows as $row ) {
            $calc_data[ $section ][] = [
                'tier'     => $tier['tier_label'],
                'gallons'  => $row['gallons'],
                'discount' => $row['discount'],
                'pretax'   => $row['pretax_price'],
                'aftertax' => $row['aftertax_price'],
            ];
        }
    }
}
$effective_date = get_field( 'fuel_effective_date', 'option' );
$calc_json = wp_json_encode( $calc_data );
?>

<div <?php echo $attrs; ?>>

    <!-- View Toggle -->
    <div class="fuel-view-toggle" role="tablist" aria-label="Fuel pricing view">
        <button class="fuel-view-toggle__btn is-active" role="tab" aria-selected="true" aria-controls="fuel-view-table" id="fuel-tab-table" data-view="table">
            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/><line x1="8" y1="9" x2="10" y2="9"/></svg>
            Full Price Sheet
        </button>
        <button class="fuel-view-toggle__btn" role="tab" aria-selected="false" aria-controls="fuel-view-calc" id="fuel-tab-calc" data-view="calc">
            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="10" x2="10" y2="10"/><line x1="14" y1="10" x2="16" y2="10"/><line x1="8" y1="14" x2="10" y2="14"/><line x1="14" y1="14" x2="16" y2="14"/><line x1="8" y1="18" x2="10" y2="18"/><line x1="14" y1="18" x2="16" y2="18"/></svg>
            Price Calculator
        </button>
    </div>

    <!-- Table View (original) -->
    <div class="fuel-view" id="fuel-view-table" role="tabpanel" aria-labelledby="fuel-tab-table">
        <?php echo do_shortcode('[dfc_fuel_full]'); ?>
    </div>

    <!-- Calculator View (interactive) -->
    <div class="fuel-view" id="fuel-view-calc" role="tabpanel" aria-labelledby="fuel-tab-calc" hidden
         data-fuel='<?php echo esc_attr( $calc_json ); ?>'
         data-effective-date="<?php echo esc_attr( $effective_date ); ?>">

        <div class="fuel-calc" aria-label="Fuel price calculator">

            <?php if ( $effective_date ) : ?>
                <p class="fuel-calc__date">Prices effective <?php echo esc_html( wp_date( 'F d, Y', strtotime( $effective_date ) ) ); ?></p>
            <?php endif; ?>

            <!-- Step 1: Fuel Type -->
            <div class="fuel-calc__step">
                <label class="fuel-calc__label" id="calc-fuel-label">Select Fuel Type</label>
                <div class="fuel-calc__options" role="radiogroup" aria-labelledby="calc-fuel-label">
                    <button class="fuel-calc__option is-active" data-fuel-type="jet" role="radio" aria-checked="true">
                        <span class="fuel-calc__option-title">Jet A</span>
                    </button>
                    <button class="fuel-calc__option" data-fuel-type="avgas" role="radio" aria-checked="false">
                        <span class="fuel-calc__option-title">AvGas</span>
                    </button>
                </div>
            </div>

            <!-- Step 2: Customer Type -->
            <div class="fuel-calc__step">
                <label class="fuel-calc__label" for="calc-tier">Customer Type</label>
                <select class="fuel-calc__select" id="calc-tier">
                    <option value="">Choose your customer type...</option>
                </select>
            </div>

            <!-- Step 3: Volume (shown when tier has volume options) -->
            <div class="fuel-calc__step fuel-calc__step--volume" hidden>
                <label class="fuel-calc__label" for="calc-volume">Uplift Volume</label>
                <select class="fuel-calc__select" id="calc-volume">
                    <option value="">Choose volume...</option>
                </select>
            </div>

            <!-- Result -->
            <div class="fuel-calc__result" hidden aria-live="polite">
                <div class="fuel-calc__result-inner">
                    <div class="fuel-calc__result-row">
                        <span class="fuel-calc__result-label">Discount off Retail</span>
                        <span class="fuel-calc__result-value" id="calc-discount">—</span>
                    </div>
                    <div class="fuel-calc__result-row fuel-calc__result-row--highlight">
                        <span class="fuel-calc__result-label">Pre-Tax Price</span>
                        <span class="fuel-calc__result-value fuel-calc__result-value--large" id="calc-pretax">—</span>
                    </div>
                    <div class="fuel-calc__result-row fuel-calc__result-row--highlight">
                        <span class="fuel-calc__result-label">After-Tax Price</span>
                        <span class="fuel-calc__result-value fuel-calc__result-value--large" id="calc-aftertax">—</span>
                    </div>
                </div>
                <p class="fuel-calc__disclaimer">Prices subject to change without notice. After tax price is rounded (includes 6.75% State Tax).</p>
            </div>

        </div>
    </div>
</div>
