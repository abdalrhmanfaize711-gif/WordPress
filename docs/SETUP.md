# Methaq Restaurant — Installation & Strapi Setup

## 1. Requirements
- WordPress 6.2 or later.
- PHP 7.4 or later (WordPress runs PHP internally; you do not need to write PHP).
- Strapi v4 with its REST API enabled.

## 2. Install the plugin once
1. Download the `methaq-restaurant.zip` package.
2. In WordPress, open **Plugins → Add New → Upload Plugin**.
3. Upload the ZIP, select **Install Now**, then **Activate**.
4. The plugin automatically creates the Homepage, Menu, About and Contact pages. It sets its generated homepage as the front page only when the site has no front page selected already.
5. Open **Settings → Methaq Restaurant** to connect Strapi.

Before connecting Strapi, the homepage shows a clearly marked preview with sample content so you can see the interface immediately. After you configure Strapi, your published restaurant data replaces the demo content.

No manual PHP editing, shortcode insertion or manual page creation is required. The plugin includes these shortcodes in its generated pages:
- `[restaurant_home]`
- `[restaurant_menu]`
- `[restaurant_info]`
- `[restaurant_contact]`

## 3. Strapi content types
Create and publish the following content types. The API IDs and field names must match exactly.

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

The plugin uses these Strapi v4 REST endpoints:
- `GET /api/restaurant?populate[0]=image`
- `GET /api/categories?sort=sortOrder:asc`
- `GET /api/menu-items?populate[0]=image&populate[1]=category&sort=sortOrder:asc`

Only published menu items returned by Strapi are displayed. If `isAvailable` is false, the item is hidden.

## 4. API token and permissions
1. In Strapi Admin, create an API Token with **Read-only** permissions.
2. Grant read access to Restaurant, Category and Menu Item.
3. Copy the token into the WordPress settings page over HTTPS.
4. The token is sent only from the WordPress server to Strapi. It is not included in page HTML or frontend JavaScript.
5. The token field appears blank after saving. Leaving it blank preserves the saved token; entering a new value replaces it.

## 5. Cache and troubleshooting
- Cache lifetime is configurable from 0 to 86400 seconds. Use 0 to disable caching.
- Enter the Strapi base URL only, such as `https://cms.example.com`; do not append `/api`.
- Confirm content is published and API read permissions are enabled.
- WordPress hosting must be able to reach the Strapi server over HTTPS.
- The URL fields and relation names in your Strapi project must match the schema above.

## 6. Scope
This is a restaurant presentation website. It does not implement online ordering, payments, reservations or delivery. Content is managed in Strapi. A live API test requires a running Strapi instance and valid credentials.
