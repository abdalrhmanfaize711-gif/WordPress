# Methaq Restaurant — Installation and Content Guide

## 1. What the website does
This is a display-only restaurant website. It displays a restaurant introduction, menu categories, dishes, prices, photos and contact information.

It does **not** connect to Strapi or any external CMS/API. It does not include a shopping cart, online orders, booking, payment or delivery.

All editable content is stored locally in the WordPress database.

## 2. Install the ready-made base theme
1. In WordPress, open **Appearance → Themes → Add New**.
2. Search for **VW Restaurant Lite**.
3. Install it. You may keep it installed as the parent theme.
4. The free theme is available at https://wordpress.org/themes/vw-restaurant-lite/.

## 3. Install the customized child theme
1. Open **Appearance → Themes → Add New → Upload Theme**.
2. Upload `methaq-vw-restaurant.zip`.
3. Install and activate **Methaq Restaurant**.
4. This child theme inherits the parent theme and customizes the olive, cream and orange brand palette, Arabic RTL layout and restaurant homepage.

The parent theme must remain installed.

## 4. Install the display plugin
1. Open **Plugins → Add New → Upload Plugin**.
2. Upload `methaq-restaurant.zip`.
3. Install and activate it.
4. The plugin creates the Home, Menu, About and Contact pages. It selects the generated homepage only if you have not already selected a front page.
5. If the Methaq child theme is active and no primary navigation menu exists, the plugin creates the menu automatically.

## 5. Edit restaurant information
From the WordPress dashboard, open **موقع المطعم**.

Edit:
- Restaurant name.
- Tagline and homepage description.
- About text.
- Hero image URL.
- Phone number.
- Address.
- Opening hours.
- Currency label.

Click **حفظ بيانات المطعم**.

## 6. Edit menu items
In **موقع المطعم → قائمة الطعام والأسعار**, enter one item per line in this exact format:

```text
Category | Dish name | Description | Price | Image URL
```

Example:

```text
الأطباق الرئيسية | كبسة الدجاج | أرز متبّل مع الدجاج | 2500 | https://example.com/kabsa.jpg
المقبلات | سلطة طازجة | خضروات موسمية وصلصة خاصة | 900 | https://example.com/salad.jpg
المشروبات | عصير الليمون | عصير ليمون طازج | 700 |
```

The last field (image URL) is optional. Save the settings and refresh the public website.

## 7. Testing and limitations
Automated checks validate PHP syntax, local menu rendering, settings sanitization, automatic page creation, navigation generation and ZIP structure.

A final visual check still needs installation on a real WordPress site. Images linked from external image URLs require an internet connection; for fully self-hosted images, upload them to WordPress Media Library and use their image URLs.
