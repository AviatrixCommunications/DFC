/**
 * DFC Weather Widget
 *
 * Fetches weather data from the cached REST endpoint (Weatherbit.io)
 * and renders it in the homepage weather bar.
 *
 * Icons: Clean flat bold SVGs, self-hosted in /img/weather/.
 * Uses fill="currentColor" so color inherits from CSS.
 * Fetched and injected inline so color inheritance works.
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

    var themeUrl = (window.DFC && window.DFC.themeurl)
        ? window.DFC.themeurl.replace(/\/?$/, '/')
        : '/wp-content/themes/dfc-theme/';

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
                var iconName = mapWeatherbitToMeteocon(data.icon);
                var iconPath = themeUrl + 'img/weather/' + iconName + '.svg';

                // Fetch SVG and inject inline for currentColor inheritance
                fetch(iconPath)
                    .then(function (r) { return r.text(); })
                    .then(function (svgText) {
                        iconEl.innerHTML = svgText;
                        // Let CSS control size via the .weather-widget__icon container
                        var svg = iconEl.querySelector('svg');
                        if (svg) {
                            svg.removeAttribute('width');
                            svg.removeAttribute('height');
                            svg.setAttribute('aria-hidden', 'true');
                            svg.style.display = 'block';
                        }
                    })
                    .catch(function () {
                        // Fallback: Meteocons-style cloud icon
                        iconEl.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128" fill="none" aria-hidden="true" style="display:block"><path d="M96 84H36a16 16 0 01-1-32A24 24 0 0182 48a14 14 0 0114 14v2a12 12 0 010 24z" stroke="currentColor" stroke-width="5" stroke-linejoin="round"/></svg>';
                    });
            }

            widget.classList.add('is-loaded');
        })
        .catch(function () {
            if (widget) widget.classList.add('is-unavailable');
        });

    function mapWeatherbitToMeteocon(code) {
        var map = {
            'c01d': 'clear-day',           'c01n': 'clear-night',
            'c02d': 'partly-cloudy-day',   'c02n': 'partly-cloudy-night',
            'c03d': 'partly-cloudy-day',   'c03n': 'partly-cloudy-night',
            'c04d': 'overcast-day',        'c04n': 'overcast-night',
            'd01d': 'partly-cloudy-day-drizzle', 'd01n': 'partly-cloudy-night-drizzle',
            'd02d': 'drizzle',             'd02n': 'drizzle',
            'd03d': 'drizzle',             'd03n': 'drizzle',
            'r01d': 'partly-cloudy-day-rain','r01n': 'partly-cloudy-night-rain',
            'r02d': 'rain',                'r02n': 'rain',
            'r03d': 'rain',                'r03n': 'rain',
            's01d': 'partly-cloudy-day-snow','s01n': 'partly-cloudy-night-snow',
            's02d': 'sleet',               's02n': 'sleet',
            's03d': 'snow',                's03n': 'snow',
            's04d': 'sleet',               's04n': 'sleet',
            's05d': 'snow',                's05n': 'snow',
            's06d': 'snow',                's06n': 'snow',
            't01d': 'thunderstorms-day',   't01n': 'thunderstorms-night',
            't02d': 'thunderstorms-day-rain','t02n': 'thunderstorms-night-rain',
            't03d': 'thunderstorms-day-rain','t03n': 'thunderstorms-night-rain',
            't04d': 'thunderstorms-rain',  't04n': 'thunderstorms-rain',
            't05d': 'thunderstorms',       't05n': 'thunderstorms',
            'a01d': 'mist',               'a01n': 'mist',
            'a02d': 'haze-day',           'a02n': 'haze-night',
            'a03d': 'haze-day',           'a03n': 'haze-night',
            'a04d': 'fog-day',            'a04n': 'fog-night',
            'a05d': 'fog',                'a05n': 'fog',
            'a06d': 'fog',                'a06n': 'fog',
        };
        return map[code] || 'not-available';
    }

})();
