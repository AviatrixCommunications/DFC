/**
 * DFC Weather Widget
 *
 * Fetches weather data from the cached REST endpoint (Weatherbit.io)
 * and renders it in the homepage weather bar.
 *
 * Icons: Inline SVGs — flat, outlined, bold stroke style matching Figma.
 * Zero CDN dependency — all icons are self-contained.
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

            if (iconEl && data.icon) {
                iconEl.innerHTML = getWeatherSVG(data.icon);
            }

            widget.classList.add('is-loaded');
        })
        .catch(function () {
            if (widget) widget.classList.add('is-unavailable');
        });

    /**
     * Return inline SVG markup for a Weatherbit icon code.
     * Flat, outlined, bold stroke — matches Figma "streamline-ultimate:weather-sun-bold".
     * All 41x41, stroke-based, currentColor.
     */
    function getWeatherSVG(code) {
        var prefix = code.substring(0, 3);
        var isDay = code.endsWith('d');
        var map = {
            'c01': clearSky(isDay),  'c02': partlyCloudy(isDay),
            'c03': mostlyCloudy(),   'c04': overcast(),
            'd01': drizzle(),        'd02': drizzle(),  'd03': drizzle(),
            'r01': rain(),           'r02': rain(),     'r03': heavyRain(),
            's01': snow(),           's02': sleet(),    's03': snow(),
            's04': sleet(),          's05': heavySnow(),'s06': snow(),
            't01': thunderstorm(),   't02': thunderstorm(), 't03': thunderstorm(),
            't04': thunderstorm(),   't05': thunderstorm(),
            'a01': mist(),           'a02': haze(),     'a03': haze(),
            'a04': fog(),            'a05': fog(),      'a06': fog(),
        };
        return map[prefix] || overcast();
    }

    function w(d) {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 41 41" width="41" height="41" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:block">' + d + '</svg>';
    }

    function clearSky(day) {
        if (!day) return w('<path d="M27 18.5a10 10 0 1 1-10-10 7 7 0 0 0 10 10z"/>');
        return w('<circle cx="20.5" cy="20.5" r="6"/><line x1="20.5" y1="7" x2="20.5" y2="10"/><line x1="20.5" y1="31" x2="20.5" y2="34"/><line x1="7" y1="20.5" x2="10" y2="20.5"/><line x1="31" y1="20.5" x2="34" y2="20.5"/><line x1="11" y1="11" x2="13.1" y2="13.1"/><line x1="27.9" y1="27.9" x2="30" y2="30"/><line x1="30" y1="11" x2="27.9" y2="13.1"/><line x1="13.1" y1="27.9" x2="11" y2="30"/>');
    }
    function partlyCloudy(day) {
        var top = day
            ? '<circle cx="26" cy="13" r="5"/><line x1="26" y1="4" x2="26" y2="6"/><line x1="35" y1="13" x2="33" y2="13"/><line x1="32.4" y1="6.6" x2="31" y2="8"/><line x1="19.6" y1="6.6" x2="21" y2="8"/><line x1="32.4" y1="19.4" x2="31" y2="18"/>'
            : '<path d="M32 12a6 6 0 1 1-6-6 4.2 4.2 0 0 0 6 6z"/>';
        return w(top + '<path d="M10 33h18a7 7 0 0 0 0-14h-1a8 8 0 1 0-15 4h-2a5 5 0 0 0 0 10z"/>');
    }
    function mostlyCloudy() { return w('<path d="M10 33h18a7 7 0 0 0 0-14h-1a8 8 0 1 0-15 4h-2a5 5 0 0 0 0 10z"/><path d="M24 11h6a4 4 0 0 1 0 8" opacity="0.4"/>'); }
    function overcast() { return w('<path d="M10 33h18a7 7 0 0 0 0-14h-1a8 8 0 1 0-15 4h-2a5 5 0 0 0 0 10z"/>'); }
    function drizzle() { return w('<path d="M10 27h18a7 7 0 0 0 0-14h-1a8 8 0 1 0-15 4h-2a5 5 0 0 0 0 10z"/><line x1="14" y1="30" x2="14" y2="32"/><line x1="20.5" y1="30" x2="20.5" y2="32"/><line x1="27" y1="30" x2="27" y2="32"/>'); }
    function rain() { return w('<path d="M10 25h18a7 7 0 0 0 0-14h-1a8 8 0 1 0-15 4h-2a5 5 0 0 0 0 10z"/><line x1="14" y1="28" x2="12" y2="34"/><line x1="20.5" y1="28" x2="18.5" y2="34"/><line x1="27" y1="28" x2="25" y2="34"/>'); }
    function heavyRain() { return w('<path d="M10 23h18a7 7 0 0 0 0-14h-1a8 8 0 1 0-15 4h-2a5 5 0 0 0 0 10z"/><line x1="12" y1="26" x2="9" y2="35"/><line x1="18" y1="26" x2="15" y2="35"/><line x1="24" y1="26" x2="21" y2="35"/><line x1="30" y1="26" x2="27" y2="35"/>'); }
    function snow() { return w('<path d="M10 25h18a7 7 0 0 0 0-14h-1a8 8 0 1 0-15 4h-2a5 5 0 0 0 0 10z"/><circle cx="14" cy="30" r="1.2" fill="currentColor" stroke="none"/><circle cx="20.5" cy="32" r="1.2" fill="currentColor" stroke="none"/><circle cx="27" cy="30" r="1.2" fill="currentColor" stroke="none"/><circle cx="17" cy="34" r="1.2" fill="currentColor" stroke="none"/><circle cx="24" cy="35" r="1.2" fill="currentColor" stroke="none"/>'); }
    function heavySnow() { return w('<path d="M10 23h18a7 7 0 0 0 0-14h-1a8 8 0 1 0-15 4h-2a5 5 0 0 0 0 10z"/><circle cx="12" cy="28" r="1.2" fill="currentColor" stroke="none"/><circle cx="18" cy="30" r="1.2" fill="currentColor" stroke="none"/><circle cx="24" cy="28" r="1.2" fill="currentColor" stroke="none"/><circle cx="30" cy="30" r="1.2" fill="currentColor" stroke="none"/><circle cx="15" cy="33" r="1.2" fill="currentColor" stroke="none"/><circle cx="21" cy="35" r="1.2" fill="currentColor" stroke="none"/><circle cx="27" cy="33" r="1.2" fill="currentColor" stroke="none"/>'); }
    function sleet() { return w('<path d="M10 25h18a7 7 0 0 0 0-14h-1a8 8 0 1 0-15 4h-2a5 5 0 0 0 0 10z"/><line x1="14" y1="28" x2="12.5" y2="33"/><circle cx="21" cy="31" r="1.2" fill="currentColor" stroke="none"/><line x1="27" y1="28" x2="25.5" y2="33"/><circle cx="17" cy="34" r="1.2" fill="currentColor" stroke="none"/>'); }
    function thunderstorm() { return w('<path d="M10 23h18a7 7 0 0 0 0-14h-1a8 8 0 1 0-15 4h-2a5 5 0 0 0 0 10z"/><polyline points="19 26 16 32 21 32 18 38"/>'); }
    function mist() { return w('<path d="M5 14h31"/><path d="M8 20h25"/><path d="M5 26h31"/><path d="M8 32h25"/>'); }
    function haze() { return w('<circle cx="20.5" cy="14" r="5"/><line x1="20.5" y1="5" x2="20.5" y2="7"/><line x1="29" y1="14" x2="31" y2="14"/><line x1="10" y1="14" x2="12" y2="14"/><path d="M8 24h25"/><path d="M5 30h31"/><path d="M8 36h25"/>'); }
    function fog() { return w('<path d="M10 20h18a7 7 0 0 0 0-14h-1a8 8 0 1 0-15 4h-2a5 5 0 0 0 0 10z"/><path d="M6 26h29"/><path d="M9 32h23"/><path d="M6 38h29"/>'); }

})();
