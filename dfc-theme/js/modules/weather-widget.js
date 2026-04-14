/**
 * DFC Weather Widget
 *
 * Fetches weather data from the cached REST endpoint (Weatherbit.io)
 * and renders it in the homepage weather bar.
 * Client-side fetch keeps WP Engine page cache intact.
 */

(function () {
    'use strict';

    var widget = document.querySelector('.weather-widget');
    if (!widget) return;

    var tempEl   = widget.querySelector('.weather-widget__temp');
    var condEl   = widget.querySelector('.weather-widget__condition');
    var feelsEl  = widget.querySelector('.weather-widget__feels');
    var dateEl   = widget.querySelector('.weather-widget__date');
    var iconEl   = widget.querySelector('.weather-widget__icon');

    var restUrl = (window.DFC && window.DFC.resturl)
        ? window.DFC.resturl + 'dfc/v1/weather'
        : '/wp-json/dfc/v1/weather';

    fetch(restUrl)
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (!data || !data.enabled) return;

            if (tempEl)  tempEl.textContent  = data.temp || '--';
            if (condEl)  condEl.textContent  = data.description || '';
            if (feelsEl) feelsEl.textContent = 'Feels like ' + (data.feels_like || '--');
            if (dateEl)  dateEl.textContent  = data.date || '';

            // Weatherbit icon mapping
            if (iconEl && data.icon) {
                iconEl.innerHTML = getWeatherIcon(data.icon);
            }

            widget.classList.add('is-loaded');
        })
        .catch(function () {
            // Fail silently — widget stays in loading/placeholder state
            if (widget) widget.classList.add('is-unavailable');
        });

    /**
     * Simple weather icon mapping.
     * Returns an SVG string for common Weatherbit icon codes.
     */
    function getWeatherIcon(code) {
        // Sun icons
        if (code === 'c01d') return '<svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>';
        // Moon icon (clear night)
        if (code === 'c01n') return '<svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';
        // Partly cloudy
        if (code.startsWith('c02') || code.startsWith('c03')) return '<svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2v2M4.93 4.93l1.41 1.41M20 12h2M17.66 17.66l1.41 1.41M2 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41M12 6a6 6 0 0 0-5.77 4.41A5 5 0 1 0 7 20h11a4 4 0 1 0-.68-7.94A6 6 0 0 0 12 6z"/></svg>';
        // Overcast / cloudy
        if (code.startsWith('c04')) return '<svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor"><path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/></svg>';
        // Rain
        if (code.startsWith('r') || code.startsWith('d')) return '<svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor"><path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/><line x1="8" y1="21" x2="8" y2="23" stroke="currentColor" stroke-width="2"/><line x1="12" y1="21" x2="12" y2="23" stroke="currentColor" stroke-width="2"/><line x1="16" y1="21" x2="16" y2="23" stroke="currentColor" stroke-width="2"/></svg>';
        // Snow
        if (code.startsWith('s')) return '<svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor"><path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/><path d="M8 22l1-2M12 22l1-2M16 22l1-2" stroke="currentColor" stroke-width="2"/></svg>';
        // Thunderstorm
        if (code.startsWith('t')) return '<svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor"><path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/><polyline points="13,16 11,20 15,20 13,24" stroke="currentColor" stroke-width="2" fill="none"/></svg>';
        // Default: cloud
        return '<svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor"><path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/></svg>';
    }

})();
