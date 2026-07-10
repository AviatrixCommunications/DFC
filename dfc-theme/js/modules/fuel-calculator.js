/**
 * DFC Fuel Price Calculator
 *
 * View toggle (table/calculator) and interactive price lookup.
 * Data comes from a JSON data attribute rendered server-side from ACF.
 */

(function () {
    'use strict';

    var block = document.querySelector('.aviatrix-block--fuel-pricing');
    if (!block) return;

    // View toggle
    var tabs = Array.from(block.querySelectorAll('.fuel-view-toggle__btn'));
    var panels = Array.from(block.querySelectorAll('.fuel-view'));

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var viewId = 'fuel-view-' + tab.getAttribute('data-view');
            tabs.forEach(function (t) {
                t.classList.remove('is-active');
                t.setAttribute('aria-selected', 'false');
            });
            tab.classList.add('is-active');
            tab.setAttribute('aria-selected', 'true');

            panels.forEach(function (p) {
                if (p.id === viewId) {
                    p.removeAttribute('hidden');
                } else {
                    p.setAttribute('hidden', '');
                }
            });
        });
    });

    // Calculator logic
    var calcPanel = document.getElementById('fuel-view-calc');
    if (!calcPanel) return;

    var fuelData;
    try {
        fuelData = JSON.parse(calcPanel.getAttribute('data-fuel'));
    } catch (e) {
        return;
    }

    var currentFuelType = 'jet';
    var fuelTypeButtons = Array.from(calcPanel.querySelectorAll('[data-fuel-type]'));
    var tierSelect = document.getElementById('calc-tier');
    var volumeStep = calcPanel.querySelector('.fuel-calc__step--volume');
    var volumeSelect = document.getElementById('calc-volume');
    var resultPanel = calcPanel.querySelector('.fuel-calc__result');
    var discountEl = document.getElementById('calc-discount');
    var pretaxEl = document.getElementById('calc-pretax');
    var aftertaxEl = document.getElementById('calc-aftertax');

    // Mirror the PHP dfc_fuel_format_price(): render a stored value with a
    // leading $ and two decimals so round amounts keep their trailing zero
    // ("0.2" -> "$0.20"). Non-numeric notes pass through with a leading $.
    function formatPrice(val) {
        if (val === null || val === undefined) return '—';
        var s = String(val).trim();
        if (s === '') return '—';
        var num = s.replace(/^[$\s]+/, '');
        if (num !== '' && !isNaN(num)) {
            return '$' + Number(num).toFixed(2);
        }
        return s.charAt(0) === '$' ? s : '$' + s;
    }

    function getUniqueValues(arr, key) {
        var seen = {};
        var result = [];
        arr.forEach(function (item) {
            if (!seen[item[key]]) {
                seen[item[key]] = true;
                result.push(item[key]);
            }
        });
        return result;
    }

    function populateTiers() {
        var data = fuelData[currentFuelType] || [];
        var tiers = getUniqueValues(data, 'tier');

        tierSelect.innerHTML = '<option value="">Choose your customer type...</option>';
        tiers.forEach(function (tier) {
            var opt = document.createElement('option');
            opt.value = tier;
            opt.textContent = tier;
            tierSelect.appendChild(opt);
        });
        tierSelect.value = '';

        // Reset downstream
        volumeStep.setAttribute('hidden', '');
        volumeSelect.innerHTML = '<option value="">Choose volume...</option>';
        resultPanel.setAttribute('hidden', '');
    }

    function populateVolumes(tier) {
        var data = fuelData[currentFuelType] || [];
        var tierRows = data.filter(function (r) { return r.tier === tier; });

        if (tierRows.length <= 1) {
            // Single row tier — show result directly
            volumeStep.setAttribute('hidden', '');
            if (tierRows.length === 1) {
                showResult(tierRows[0]);
            }
            return;
        }

        // Multiple volume options
        volumeSelect.innerHTML = '<option value="">Choose volume...</option>';
        tierRows.forEach(function (row, i) {
            var opt = document.createElement('option');
            opt.value = i;
            opt.textContent = row.gallons || 'Any quantity';
            volumeSelect.appendChild(opt);
        });
        volumeStep.removeAttribute('hidden');
        volumeSelect.value = '';
        resultPanel.setAttribute('hidden', '');
    }

    function showResult(row) {
        discountEl.textContent = formatPrice(row.discount);
        pretaxEl.textContent = formatPrice(row.pretax);
        aftertaxEl.textContent = formatPrice(row.aftertax);
        resultPanel.removeAttribute('hidden');
    }

    // Fuel type toggle
    fuelTypeButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            fuelTypeButtons.forEach(function (b) {
                b.classList.remove('is-active');
                b.setAttribute('aria-checked', 'false');
            });
            btn.classList.add('is-active');
            btn.setAttribute('aria-checked', 'true');
            currentFuelType = btn.getAttribute('data-fuel-type');
            populateTiers();
        });
    });

    // Tier select
    tierSelect.addEventListener('change', function () {
        if (!tierSelect.value) {
            volumeStep.setAttribute('hidden', '');
            resultPanel.setAttribute('hidden', '');
            return;
        }
        populateVolumes(tierSelect.value);
    });

    // Volume select
    volumeSelect.addEventListener('change', function () {
        if (volumeSelect.value === '') {
            resultPanel.setAttribute('hidden', '');
            return;
        }
        var data = fuelData[currentFuelType] || [];
        var tierRows = data.filter(function (r) { return r.tier === tierSelect.value; });
        var index = parseInt(volumeSelect.value, 10);
        if (tierRows[index]) {
            showResult(tierRows[index]);
        }
    });

    // Initialize
    populateTiers();

})();
