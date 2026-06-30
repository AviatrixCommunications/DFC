# DuPage Flight Center — WordPress Theme

Custom WordPress theme by Aviatrix Communications. Built on the same architecture as the DAA theme — Webpack for Sass/JS, per-block CSS, ACF blocks with InnerBlocks.

---

## Requirements

- Node.js (v18+ recommended)
- npm
- WordPress 6.4+
- PHP 8.1+
- ACF Pro
- Gravity Forms + Constant Contact Add-On
- WP Engine Smart Search
- Local WordPress site running at `https://dupage-flight-center.local`

---

## Setup

1. **Install dependencies**
   ```bash
   npm install
   ```

2. **Development mode**
   ```bash
   npm run dev
   ```

3. **Production build**
   ```bash
   npm run build
   ```

---

## Font Setup

### Open Sans (single font for headings + body)

The theme uses Open Sans for everything — headings use weight 500/600, body uses weight 400.

**Option A: Self-host (recommended for performance)**

Download Open Sans variable font `.woff2` files from [Google Fonts](https://fonts.google.com/specimen/Open+Sans) and place in `/fonts/`:
- `OpenSans-VariableFont_wdth,wght.woff2`
- `OpenSans-Italic-VariableFont_wdth,wght.woff2`

**Option B: Google Fonts CDN**

Add this to `header.php` before `wp_head()` and comment out the `@font-face` blocks in `sass/core/_fonts.scss`:
```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wdth,wght@0,75..100,300..800;1,75..100,300..800&display=swap" rel="stylesheet">
```

---

## Weatherbit.io API

The weather widget uses Weatherbit.io. Configure in **Settings → DFC Weather** in wp-admin.

**API Key:** `4d5c726c76f442919c7e68a65b784008`

The widget caches responses for 30 minutes via WordPress transients, keeping API calls well within free tier limits.

---

## Fuel Prices

Managed via **Fuel Prices** in the wp-admin sidebar.

- **Manual editing:** Direct ACF field editing
- **CSV import:** Fuel Prices → CSV Import — download template, fill in, upload, preview, confirm

**Shortcodes:**
- `[dfc_fuel_homepage]` — Compact widget for homepage (Jet A + AvGas prices)
- `[dfc_fuel_full]` — Full pricing tables for dedicated fuel page

---

## WP Engine Security Headers

Add these in WP Engine Web Rules:

```
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()
Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
```

Test CSP in report-only mode first before enforcing.

---

## File Structure

```
dfc-theme/
├── blocks-acf/           # ACF blocks (registered via block.json)
├── blocks-core/          # Core block customizations (button, columns)
├── components/           # PHP partials (alert-banner)
├── css/                  # Editor overrides
├── fonts/                # Self-hosted web fonts (.woff2)
├── functions/            # PHP includes
│   ├── blocks.php        # Block registration system
│   ├── fuel-prices.php   # Fuel pricing options + CSV import + shortcodes
│   ├── search-ajax.php   # Search REST localization
│   └── weather-api.php   # Weatherbit.io integration + admin settings
├── img/                  # Theme images and SVG icons
├── js/
│   ├── global.js         # JS entry point
│   └── modules/          # JS modules (search, weather, accordion, etc.)
├── page-templates/       # Custom page templates
├── sass/                 # SCSS source
│   ├── settings/         # Variables, mixins, functions
│   ├── core/             # Reset, fonts, base, form elements
│   ├── utilities/        # Animations, colors, responsive helpers
│   ├── components/       # Header, footer, nav, button, typography
│   └── external/         # Third-party (hamburgers menu)
├── functions.php         # Theme setup
├── header.php            # Site header with nav + search
├── footer.php            # Newsletter + footer
├── theme.json            # WordPress design tokens
├── webpack.config.js     # Build configuration
└── package.json          # npm dependencies
```
