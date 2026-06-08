# Repository Guidelines

## Project Structure & Module Organization

This repository is a WordPress core/site checkout. Core files live at the root, `wp-admin/`, and `wp-includes/`; avoid changing them unless the task explicitly targets core behavior. Site customizations are under `wp-content/`.

The active custom theme is `wp-content/themes/twmp-ath/`. Theme PHP templates are in the theme root, shared helpers are in `inc/`, reusable markup is in `template-parts/`, block templates are in `templates/`, WooCommerce overrides are in `woocommerce/`, and ACF exports are in `acf-json/`. Source assets are in `src/` and compiled assets are emitted to `assets/`.

## Build, Test, and Development Commands

Run theme asset commands from `wp-content/themes/`.

- `npm install`: install Webpack dependencies from `package-lock.json`.
- `npm run dev`: watch and build `twmp-ath` assets in development mode with BrowserSync proxying `http://localhost/2026/ath`.
- `npm run build`: create production CSS/JS bundles in `wp-content/themes/twmp-ath/assets/`.
- `php -l path/to/file.php`: syntax-check changed PHP files before handoff.

The package declares Node `20.13.1`; use that version when possible.

## Coding Style & Naming Conventions

Follow existing WordPress theme conventions: PHP templates use lowercase filenames such as `single.php`, `archive.php`, and `template-parts/...`. Keep PHP readable with tabs or consistent local indentation, escape output (`esc_html`, `esc_url`, `wp_kses_post`), sanitize input, and guard privileged actions with capabilities/nonces.

JavaScript entry files live in `src/*.js`; shared modules belong in `src/js/lib/` or `src/js/blocks/`. SCSS partials are organized by purpose under `src/scss/components`, `global`, `mixins`, `sections`, `theme`, and `woocommerce`.

## Testing Guidelines

No PHPUnit, Jest, or Playwright suite is currently configured. For PHP changes, run `php -l` on each modified PHP file and manually verify affected pages in the local WordPress site. For asset changes, run `npm run build` and confirm files appear in `assets/css` and `assets/js`. Check responsive behavior and WooCommerce pages when touching layout, SCSS, or templates.

## Commit & Pull Request Guidelines

Recent commits are short and direct, for example `fix bug`, `add popup newsletter`, and `animation for popup`. Prefer concise, imperative messages that name the changed feature or fix, such as `fix popup language switcher`.

Pull requests should include a brief summary, changed paths or features, manual test notes, and screenshots for visual changes. Link related issues when available and mention any database, ACF JSON, or configuration changes.

## Security & Configuration Tips

Do not commit secrets from `wp-config.php`, database dumps, backups, or generated logs. Treat `backup*.sql`, `db_*.sql`, and `error_log` as local operational artifacts unless explicitly requested.
