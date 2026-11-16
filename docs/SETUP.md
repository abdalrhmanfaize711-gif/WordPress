# Methaq Restaurant — Theme, Plugin and Strapi Setup

## 1. What is included?
This solution uses two separate WordPress components:
- **Methaq Restaurant theme** — a customized child theme based on the ready-made VW Restaurant Lite theme. It controls the website presentation, parent theme header/footer, RTL styling and visual design.
- **Methaq Restaurant plugin** — connects WordPress to Strapi and supplies restaurant information, menu categories, menu items, images and contact data.

WordPress treats themes and plugins as separate installation types, so install both. You do not need to edit PHP files.

## 2. Install the base theme
1. In WordPress, open **Appearance → Themes → Add New**.
2. Search for **VW Restaurant Lite**.
3. Install and activate it once. It is a free theme from the official WordPress.org directory: https://wordpress.org/themes/vw-restaurant-lite/.
4. The theme is responsive and lists RTL language support. Its free version may offer optional paid upgrades/support.

## 3. Install the Methaq child theme
1. Upload `methaq-vw-restaurant.zip` through **Appearance → Themes → Add New → Upload Theme**.
2. Install and activate **Methaq Restaurant**.
3. The child theme inherits the ready-made VW Restaurant Lite framework and applies the Methaq colors, Arabic RTL layout and custom homepage template. The parent theme must remain installed.

## 4. Install the Strapi connector plugin
1. Go to **Plugins → Add New → Upload Plugin**.
2. Upload `methaq-restaurant.zip`, install and activate it.
3. The plugin automatically creates Home, Menu, About and Contact pages. It sets its homepage as the front page only if the site does not already have a front page.
4. Once the Methaq child theme is active, the plugin creates and assigns a restaurant navigation menu if no menu is already assigned to the theme's primary menu location.

Before Strapi is connected, the homepage shows a clearly marked preview with sample content. After configuring Strapi, the published restaurant data replaces the demo content.

## 5. Connect Strapi
Open **Settings → Methaq Restaurant** and enter the Strapi base URL, optional read-only API token, cache lifetime and currency label.

### Restaurant — Single Type, API ID `restaurant`
- `name` — Text (required)
- `description` — Long text
- `about` — Long text
- `phone` — Text
- `address` — Text
- `openingHours` — Text
- `image` — Single media

### Category — Collection Type, API ID `category`
- `name` — Text (required)
- `slug` — UID from `name`
- `sortOrder` — Integer

### Menu Item — Collection Type, API ID `menu-item`
- `name` — Text (required)
- `description` — Long text
- `price` — Decimal
- `image` — Single media
- `isAvailable` — Boolean
- `sortOrder` — Integer
- `category` — Many-to-one relation to Category

The plugin requests these Strapi v4 endpoints:
- `GET /api/restaurant?populate[0]=image`
- `GET /api/categories?sort=sortOrder:asc`
- `GET /api/menu-items?populate[0]=image&populate[1]=category&sort=sortOrder:asc`

Only published menu items are returned by Strapi. Items with `isAvailable = false` are hidden.

## 6. API token and security
1. In Strapi Admin, create an API Token with **Read-only** permissions.
2. Grant read access to Restaurant, Category and Menu Item.
3. Paste the token into the WordPress settings page over HTTPS.
4. The token is sent only from the WordPress server to Strapi. It is not included in page HTML or browser JavaScript.
5. The token field appears blank after saving. Leaving it blank preserves the saved token; entering a new value replaces it.

## 7. Cache and troubleshooting
- Cache lifetime is configurable from 0 to 86400 seconds. Set to 0 to disable caching.
- Enter only the Strapi base URL (for example, `https://cms.example.com`), without `/api`.
- Ensure content is published and API read permissions are enabled.
- WordPress hosting must be able to reach Strapi over HTTPS.
- The connector expects Strapi v4 response shape (`data` / `attributes`). Strapi v5 will require a response adapter update.

## 8. Scope and testing
This is a restaurant presentation website, not an online ordering, payment, reservation or delivery system. Automated checks validate PHP syntax, the plugin's main data/rendering behavior, and ZIP packaging. A live WordPress/Strapi integration test still requires a running WordPress site, a Strapi instance and valid credentials.
