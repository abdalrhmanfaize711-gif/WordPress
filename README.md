# Methaq Restaurant — WordPress Display Website

A simple Arabic RTL restaurant presentation website. It displays the restaurant story, food menu, prices, photos and contact details. **It does not use Strapi, an external CMS, external API, online ordering or payment service.**

## How content is managed
All restaurant information is saved locally in the WordPress database through the **موقع المطعم** admin menu. From there you can edit:
- Restaurant name, tagline, description and about text.
- Hero image.
- Phone, address, opening hours and currency.
- Menu categories, dish names, descriptions, prices and optional image URLs.

Each menu item is entered on its own line with this format:

```text
Category | Dish name | Description | Price | Image URL
```

The image URL is optional. Default sample dishes are included so the site is visible immediately.

## Theme and plugin
- **Methaq Restaurant plugin** — creates the four pages and menu, stores local restaurant data and renders the restaurant sections.
- **Methaq Restaurant child theme** — customized from the ready-made VW Restaurant Lite theme. It controls the page presentation, brand colors and RTL refinements.

WordPress installs themes and plugins separately.

## Installation
1. Install the free parent theme **VW Restaurant Lite** from Appearance → Themes → Add New.
2. Upload and activate `methaq-vw-restaurant.zip` from Appearance → Themes → Add New → Upload Theme.
3. Upload and activate `methaq-restaurant.zip` from Plugins → Add New → Upload Plugin.
4. Open the public homepage. The plugin creates Home, Menu, About and Contact pages automatically.
5. Open **موقع المطعم** in the WordPress dashboard to update the sample content with the real restaurant details.

No manual PHP editing, external CMS account, server URL, API token or shortcode entry is required.

## Build
```sh
sh scripts/package-plugin.sh
sh scripts/package-theme.sh
```

The ZIP files are written to `dist/`.
