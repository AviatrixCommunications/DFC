/**
 * DFC Theme — Global JavaScript Entry Point
 *
 * Modules are imported and initialized here.
 * Webpack bundles this into /dist/js/global.[hash].js
 */

import initMobileNav from './modules/mobile-nav';
import initAccordion from './modules/accordion';
import initFadeIn from './modules/fade-in';
import initAlertBanner from './modules/alert-banner';

// DFC-specific modules (self-initializing IIFEs)
import './modules/search';
import './modules/image-slider';
import './modules/services-tabs';
import './modules/weather-widget';
import './modules/hero-slider';
import './modules/fuel-calculator';

// Initialize DAA-ported modules that export init functions
initMobileNav();
initAccordion();
initFadeIn();
initAlertBanner();
