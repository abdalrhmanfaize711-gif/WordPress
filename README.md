# Methaq Restaurant — WordPress Plugin + Strapi

A small Arabic RTL restaurant website delivered as an installable WordPress plugin. Restaurant content (menu, categories, description, contact details and media) is managed in Strapi.

## Repository layout
- `methaq-restaurant/` — complete WordPress plugin source.
- `docs/SETUP.md` — installation, Strapi content types, API token, permissions and troubleshooting.
- `docs/strapi-sample-data.json` — sample restaurant/menu data.
- `scripts/package-plugin.sh` — builds and validates the installable plugin ZIP.

## Build the installable ZIP
Run from a machine with `zip` and `unzip` installed:

```sh
sh scripts/package-plugin.sh
```

The output is `dist/methaq-restaurant.zip`. Upload it in WordPress under **Plugins → Add New → Upload Plugin**.

## Configure
After activation, open **Settings → Methaq Restaurant**, enter the Strapi base URL and a read-only API token, then create a WordPress page containing:

```text
[restaurant_home]
```

Set that page as the static homepage. Individual shortcodes: `[restaurant_menu]`, `[restaurant_info]`, `[restaurant_contact]`.

See [docs/SETUP.md](docs/SETUP.md) for the full setup guide and expected Strapi v4 schema.

## Validation status
Source and documentation are committed. The packaging script includes an archive integrity check. This environment did not run PHP lint or a live WordPress/Strapi integration test; configure a Strapi instance and run the checks in the setup guide before production use.
