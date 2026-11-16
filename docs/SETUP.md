# Methaq Restaurant — Installation & Strapi Setup

## 1. Requirements
- WordPress 6.2 or later
- PHP 7.4 or later with HTTPS support
- Strapi v4 REST API (the included response adapter expects the v4 `data/attributes` response shape)

## 2. Install the plugin
1. Zip the `methaq-restaurant` directory (the directory itself must be at the ZIP root).
2. In WordPress go to **Plugins → Add New → Upload Plugin**.
3. Upload `methaq-restaurant.zip`, install and activate it.
4. Open **Settings → Methaq Restaurant**.
5. Set the Strapi base URL, a read-only API token, cache lifetime and currency label.

## 3. Strapi content types
Create these content types in Strapi (REST API enabled):

### Restaurant — Single Type, API ID: restaurant
Fields: `name` (Text, required), `description` (Long text), `about` (Long text), `phone` (Text), `address` (Text), `openingHours` (Text), `image` (Media, single).

### Category — Collection Type, API ID: category
Fields: `name` (Text, required), `slug` (UID from name), `sortOrder` (Integer). Enable Draft & Publish if desired.

### Menu Item — Collection Type, API ID: menu-item
Fields: `name` (Text, required), `description` (Long text), `price` (Decimal), `image` (Media, single), `isAvailable` (Boolean), `sortOrder` (Integer), `category` (Many-to-one relation to Category).

Publish the Restaurant single type, categories and menu items. The plugin requests:
- `GET /api/restaurant?populate=deep`
- `GET /api/categories?sort=sortOrder:asc`
- `GET /api/menu-items?populate=*&sort=sortOrder:asc`

The plugin is designed for Strapi v4 response formatting. Strapi v5 uses a different response format; adapt the `attrs()` and media/category extraction methods before using v5.

## 4. API permissions and token
1. In Strapi Admin, create an API Token with **Read-only** type.
2. Ensure public/token permissions allow find/findOne for Restaurant, Category and Menu Item as applicable. Prefer the token approach; do not expose it in browser JavaScript.
3. Copy the token into the WordPress plugin settings over HTTPS.
4. Never commit the token to source control.

## 5. Add the website to WordPress
Create a page named Home and add this shortcode:

```text
[restaurant_home]
```

Individual sections can also be embedded:

```text
[restaurant_menu]
[restaurant_info]
[restaurant_contact]
```

Set the Home page as the site's static front page in **Settings → Reading**.

## 6. Cache and troubleshooting
- Cache lifetime is configurable in seconds. Set it to 0 to disable transient caching.
- If content changes do not appear immediately, wait for the cache lifetime or delete the WordPress transients beginning with `methaq_` using a cache-management tool.
- Confirm Strapi URL has no trailing `/api` path; enter only the base URL, e.g. `https://cms.example.com`.
- Confirm API token is valid and read-only permissions are enabled.
- Confirm all content is published, not left in Draft.
- WordPress server must be able to reach the Strapi host over HTTPS.
- If media URLs are private, configure public read access or an accessible CDN.

## 7. Security notes
- Strapi API calls are made server-side with WordPress HTTP API; the token is not printed into page HTML.
- Settings are restricted to users with `manage_options` and sanitized on save.
- Requests use `wp_safe_remote_get`, a 12-second timeout, and JSON validation.
- Use a read-only Strapi token, HTTPS, and a dedicated least-privilege account.

## 8. Current scope / limitations
This is a restaurant presentation plugin, not an ordering, payment, booking or delivery system. Content is managed in Strapi. The frontend is Arabic RTL. Verify the actual field IDs and relation names in your Strapi project; they must match this guide. Live API integration requires a configured Strapi instance and has not been verified by this repository-only setup.
