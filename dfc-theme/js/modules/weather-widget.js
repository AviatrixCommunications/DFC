/**
 * DFC Weather Widget
 *
 * Fetches weather data from the cached REST endpoint (Weatherbit.io)
 * and renders it in the homepage weather bar.
 *
 * Icons: Basmilius Meteocons — outlined, animated SVGs.
 * https://github.com/basmilius/weather-icons
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
                var iconName = mapWeatherbitToMeteocon(data.icon);
                var iconUrl = 'https://basmilius.github.io/weather-icons/production/line/svg/' + iconName + '.svg';
                iconEl.innerHTML = '<img src="' + iconUrl + '" alt="" width="56" height="56" style="display:block;" />';
            }

            widget.classList.add('is-loaded');
        })
        .catch(function () {
            if (widget) widget.classList.add('is-unavailable');
        });

    /**
     * Map Weatherbit icon codes to Basmilius Meteocon names.
     * https://www.weatherbit.io/api/codes
     */
    function mapWeatherbitToMeteocon(code) {
        var map = {
            // Clear
            'c01d': 'clear-day',
            'c01n': 'clear-night',
            // Few clouds
            'c02d': 'partly-cloudy-day',
            'c02n': 'partly-cloudy-night',
            // Scattered clouds
            'c03d': 'partly-cloudy-day',
            'c03n': 'partly-cloudy-night',
            // Overcast
            'c04d': 'overcast-day',
            'c04n': 'overcast-night',
            // Drizzle
            'd01d': 'partly-cloudy-day-drizzle', 'd01n': 'partly-cloudy-night-drizzle',
            'd02d': 'drizzle',                   'd02n': 'drizzle',
            'd03d': 'drizzle',                   'd03n': 'drizzle',
            // Rain
            'r01d': 'partly-cloudy-day-rain',    'r01n': 'partly-cloudy-night-rain',
            'r02d': 'rain',                      'r02n': 'rain',
            'r03d': 'extreme-rain',              'r03n': 'extreme-rain',
            // Snow
            's01d': 'partly-cloudy-day-snow',    's01n': 'partly-cloudy-night-snow',
            's02d': 'sleet',                     's02n': 'sleet',
            's03d': 'snow',                      's03n': 'snow',
            's04d': 'extreme-sleet',             's04n': 'extreme-sleet',
            's05d': 'extreme-snow',              's05n': 'extreme-snow',
            's06d': 'snow',                      's06n': 'snow',
            // Thunderstorm
            't01d': 'thunderstorms-day',         't01n': 'thunderstorms-night',
            't02d': 'thunderstorms-day-rain',    't02n': 'thunderstorms-night-rain',
            't03d': 'thunderstorms-day-extreme', 't03n': 'thunderstorms-night-extreme',
            't04d': 'thunderstorms-rain',        't04n': 'thunderstorms-rain',
            't05d': 'thunderstorms',             't05n': 'thunderstorms',
            // Atmosphere (mist, fog, haze, smoke)
            'a01d': 'mist',     'a01n': 'mist',
            'a02d': 'haze-day', 'a02n': 'haze-night',
            'a03d': 'haze-day', 'a03n': 'haze-night',
            'a04d': 'fog-day',  'a04n': 'fog-night',
            'a05d': 'fog',      'a05n': 'fog',
            'a06d': 'fog',      'a06n': 'fog',
        };
        return map[code] || 'not-available';
    }

})();
