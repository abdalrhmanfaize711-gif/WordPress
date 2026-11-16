# Methaq Restaurant — WordPress Theme + Strapi Plugin

The website presentation is now based on a ready-made WordPress restaurant theme, customized for the Arabic brand **مذاق**. Strapi remains the source of restaurant, category, menu and image data.

## Theme base
The customized child theme is based on **VW Restaurant Lite**, a free theme listed in the official WordPress.org directory. The base theme supports RTL and responsive layouts. [View VW Restaurant Lite](https://wordpress.org/themes/vw-restaurant-lite/).

The `methaq-vw-restaurant/` child theme customizes colors, Arabic RTL presentation, the homepage layout and responsive restaurant sections while retaining the parent theme's header/footer framework.

## Install in this order
1. In WordPress, install and activate **VW Restaurant Lite** from Appearance → Themes → Add New.
2. Upload and activate `methaq-vw-restaurant.zip` (the Methaq child theme).
3. Upload and activate `methaq-restaurant.zip` (the Strapi connector plugin).
4. The plugin creates the Home, Menu, About and Contact pages. It adds those pages to the theme's primary navigation when no menu is already assigned.
5. Before Strapi is connected, the homepage shows a clearly marked demo preview. Configure Strapi in Settings → Methaq Restaurant to load the actual content.

WordPress requires themes and plugins to be installed separately. No manual PHP editing or shortcode insertion is required.

## Repository layout
- `methaq-vw-restaurant/` — customized child theme.
- `methaq-restaurant/` — Strapi connector plugin.
- `docs/SETUP.md` — installation and Strapi schema guide.
- `tests/smoke-test.php` — plugin behavior tests using WordPress API stubs.
- `scripts/package-plugin.sh` — build/validate plugin ZIP.
- `scripts/package-theme.sh` — build/validate child theme ZIP.

## Build packages
```sh
sh scripts/package-plugin.sh
sh scripts/package-theme.sh
```

The output files are created in `dist/`.

## Strapi compatibility
The connector currently targets Strapi v4 REST responses (`data` / `attributes`). A real API integration check requires a running Strapi instance and valid credentials.
