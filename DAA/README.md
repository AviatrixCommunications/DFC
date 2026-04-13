# The Aviatrix Base Theme

WordPress base theme by Aviatrix Communications. Uses **Webpack** for Sass/JS compilation, a separate **Node build script** for per-block CSS, and **BrowserSync** for live reloading.

---

## Requirements

- Node.js (v18+ recommended)
- npm
- Local WordPress site running at `http://dupage-airport-authority-daa.local`
- This theme located in your site's `/wp-content/themes/` folder

---

## Setup

1. **Install dependencies**

   ```bash
   npm install
   ```

2. **Development mode (watch + live reload)**

   ```bash
   npm run dev
   ```

   Runs two processes in parallel via `concurrently`:
   - **Webpack** — watches and compiles Sass/JS to `/dist/`
   - **Block CSS builder** — watches and compiles `blocks-acf/*/style.scss` to `blocks-acf/*/style.css`

   BrowserSync starts at [http://localhost:3000](http://localhost:3000) and reloads on changes to PHP files, dist assets, and block CSS.

3. **Production build**

   ```bash
   npm run build
   ```

   Minifies and hashes all output. Also compiles block CSS.

4. **Build block CSS only**

   ```bash
   npm run build:blocks
   ```

---

## File Structure

```
theme/
├── blocks-acf/                    # ACF blocks (registered via block.json)
│   ├── hero/
│   │   ├── block.json             # Block registration + "style": "file:./style.css"
│   │   ├── hero.php               # Block template (uses InnerBlocks)
│   │   ├── editor.js              # Editor-only JS (optional)
│   │   ├── style.scss             # Block Sass source
│   │   └── style.css              # Compiled (git-ignored)
│   ├── cta/
│   ├── image-cta/
│   ├── images-content/
│   ├── speakers/
│   └── title-content/
│
├── blocks-core/                   # Core block customizations
│   ├── button/
│   │   └── editor.js              # Replaces fill/outline with Primary/Secondary/Text styles
│   ├── columns/
│   │   └── editor.js              # Adds 4-column variation, removes unused variations
│   └── list/
│       └── editor.js              # Adds alignment support
│
├── sass/
│   ├── style.scss                 # Frontend Sass entry
│   ├── editor-style.scss          # Editor Sass entry (prefixed with .editor-styles-wrapper)
│   ├── settings/
│   │   ├── _variables.scss        # Design tokens, breakpoints, colors
│   │   ├── _mixins.scss           # Media query and utility mixins
│   │   └── _functions.scss        # Breakpoint helper function
│   ├── components/                # Shared component styles
│   └── external/
│       └── hamburgers/            # Hamburger menu animation library
│
├── css/
│   └── editor-overrides.css       # Editor UI tweaks (hides Width control on buttons)
│
├── js/
│   └── global.js                  # Main JS entry
│
├── dist/                          # Webpack output (git-ignored)
│   ├── css/
│   │   ├── style.[hash].css       # Frontend stylesheet
│   │   └── editor-style.[hash].css
│   ├── js/
│   │   └── global.[hash].js
│   └── manifest.json              # Hash map for cache-busted enqueuing
│
├── functions/
│   └── blocks.php                 # Block registration, editor script enqueuing, hero filters
│
├── functions.php                  # Theme setup, menus, asset enqueuing
├── webpack.config.js              # Webpack config (Sass, JS, BrowserSync, manifest)
├── build-block-css.js             # Block SCSS compiler (sass + autoprefixer)
└── package.json
```

---

## How It Works

### Asset pipeline (Webpack)
- Webpack compiles `sass/style.scss` and `sass/editor-style.scss` to hashed CSS in `/dist/`.
- Editor styles are automatically prefixed with `.editor-styles-wrapper` via PostCSS.
- `dist/manifest.json` maps filenames to their hashed versions.
- `functions.php` reads the manifest and enqueues the correct assets.

### Per-block CSS
- Each ACF block has its own `style.scss` in its block folder.
- `build-block-css.js` compiles these to `style.css` using Dart Sass + Autoprefixer.
- Each block's `block.json` declares `"style": "file:./style.css"`, so WordPress only loads the CSS on pages where the block is used.
- Cache busting is handled via `filemtime()` in `functions/blocks.php`.

### Core block editor scripts
- `blocks-core/*/editor.js` files customize the block editor (button styles, column variations, etc.).
- These are registered and enqueued via `enqueue_blocks_core_editor_js()` in `functions/blocks.php` with `wp-blocks`, `wp-dom-ready`, `wp-hooks`, and `wp-element` as dependencies.

### BrowserSync
- Proxies `http://dupage-airport-authority-daa.local` at `http://localhost:3000`.
- CSS injection for dist files (no full reload needed).
- Full page reload for block CSS changes and PHP file edits.

---

## Common Tips

- Always develop at `http://localhost:3000/` for live reload to work.
- If BrowserSync doesn't start, check that `dupage-airport-authority-daa.local` is running in Local WP.
- You can stop the watcher anytime with `Ctrl + C`.
- Production builds automatically clean the `/dist` folder before rebuilding.
- Block CSS files (`blocks-acf/*/style.css`) are git-ignored since they're compiled from source.

---
