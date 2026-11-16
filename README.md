# Methaq Restaurant — WordPress Plugin + Strapi

An install-once WordPress plugin for a responsive Arabic RTL restaurant website. Restaurant details, menu categories, menu items and images are managed in Strapi.

## What happens after activation?
The plugin automatically creates four WordPress pages:
- Homepage ([restaurant_home]) and sets it as the front page only when no front page is already selected.
- Menu ([restaurant_menu]).
- About ([restaurant_info]).
- Contact ([restaurant_contact]).

Before Strapi is connected, the homepage displays a clearly marked demo preview with sample menu cards. Connect Strapi to replace the preview with your restaurant's content.

## Install
Upload `methaq-restaurant.zip` through **WordPress → Plugins → Add New → Upload Plugin**, install and activate it. Then open **Settings → Methaq Restaurant** to connect Strapi.

No manual PHP editing, page creation or separate frontend setup is required. PHP is included inside the plugin because WordPress uses it to load plugins.

## Repository layout
- `methaq-restaurant/` — complete plugin source.
- `docs/SETUP.md` — Strapi schema and setup guide.
- `tests/smoke-test.php` — WordPress API stubs and plugin smoke tests.
- `scripts/package-plugin.sh` — PHP syntax check and ZIP packaging.

## Build package
Run:

```sh
sh scripts/package-plugin.sh
```

The package is generated at `dist/methaq-restaurant.zip`.

## Compatibility
The response adapter targets Strapi v4 REST responses (`data` / `attributes`). A live integration test needs a running Strapi instance and valid credentials.
