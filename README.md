# Methaq Restaurant — WordPress Plugin + Strapi

An install-once WordPress plugin for a responsive Arabic RTL restaurant website. Restaurant details, menu categories, menu items and images are managed in Strapi.

## Install
Upload \`methaq-restaurant.zip\` through **WordPress → Plugins → Add New → Upload Plugin**, install and activate it. Then open **Settings → Methaq Restaurant** to connect Strapi and add \`[restaurant_home]\` to the homepage.

No manual PHP editing or separate frontend setup is required. PHP is included inside the plugin because WordPress uses it to load plugins.

## Repository layout
- \`methaq-restaurant/\` — complete plugin source.
- \`docs/SETUP.md\` — Strapi schema and setup guide.
- \`scripts/package-plugin.sh\` — PHP syntax check and ZIP packaging.

## Build package
Run:

\`\`\`sh
sh scripts/package-plugin.sh
\`\`\`

The package is generated at \`dist/methaq-restaurant.zip\`.

## Shortcodes
\`[restaurant_home]\`, \`[restaurant_menu]\`, \`[restaurant_info]\`, \`[restaurant_contact]\`.

## Compatibility
The response adapter targets Strapi v4 REST responses (\`data\` / \`attributes\`). A live integration test needs a running Strapi instance and valid credentials.
