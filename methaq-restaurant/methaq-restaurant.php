<?php
/**
 * Plugin Name: Methaq Restaurant — Strapi Connector
 * Description: Responsive Arabic RTL restaurant website powered by Strapi CMS.
 * Version: 1.0.2
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Author: Methaq
 * License: GPL-2.0-or-later
 * Text Domain: methaq-restaurant
 */

if (!defined('ABSPATH')) {
    exit;
}

define('METHAQ_RESTAURANT_VERSION', '1.0.2');
define('METHAQ_RESTAURANT_URL', plugin_dir_url(__FILE__));

final class Methaq_Restaurant_Plugin
{
    const OPTION = 'methaq_restaurant_settings';

    public function __construct()
    {
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_init', [$this, 'ensure_pages_exist']);
        add_action('admin_notices', [$this, 'admin_notice']);
        add_action('wp_enqueue_scripts', [$this, 'assets']);
        register_activation_hook(__FILE__, [$this, 'ensure_pages_exist']);

        add_shortcode('restaurant_home', [$this, 'home_shortcode']);
        add_shortcode('restaurant_menu', [$this, 'menu_shortcode']);
        add_shortcode('restaurant_info', [$this, 'info_shortcode']);
        add_shortcode('restaurant_contact', [$this, 'contact_shortcode']);
    }

    /**
     * Create the restaurant pages automatically so activation produces a visible site.
     * Existing pages and an already selected homepage are never overwritten.
     */
    public function ensure_pages_exist()
    {
        if (get_option('methaq_pages_created') === 'yes') {
            return;
        }

        $pages = [
            'home' => ['title' => 'الرئيسية - مذاق', 'slug' => 'restaurant-home', 'content' => '[restaurant_home]'],
            'menu' => ['title' => 'قائمة الطعام', 'slug' => 'restaurant-menu', 'content' => '[restaurant_menu]'],
            'about' => ['title' => 'عن المطعم', 'slug' => 'restaurant-about', 'content' => '[restaurant_info]'],
            'contact' => ['title' => 'تواصل معنا', 'slug' => 'restaurant-contact', 'content' => '[restaurant_contact]'],
        ];

        $page_ids = [];
        foreach ($pages as $key => $page) {
            $existing = get_page_by_path($page['slug'], OBJECT, 'page');
            if ($existing) {
                $page_ids[$key] = (int) $existing->ID;
                continue;
            }

            $created = wp_insert_post([
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_title' => $page['title'],
                'post_name' => $page['slug'],
                'post_content' => $page['content'],
            ], true);

            if (!is_wp_error($created) && $created) {
                $page_ids[$key] = (int) $created;
            }
        }

        if (!empty($page_ids['home'])) {
            // Only assign a homepage when the site owner has not already chosen one.
            if (!(int) get_option('page_on_front')) {
                update_option('show_on_front', 'page');
                update_option('page_on_front', $page_ids['home']);
            }
            update_option('methaq_page_ids', $page_ids);
            update_option('methaq_pages_created', 'yes');
        }
    }

    public function admin_notice()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $settings = $this->settings();
        if ($settings['api_url'] !== '') {
            return;
        }

        $home_id = (int) get_option('page_on_front');
        $home_url = $home_id ? get_permalink($home_id) : '';
        echo '<div class="notice notice-info"><p><strong>Methaq Restaurant:</strong> تم تجهيز صفحات المطعم تلقائيًا. ';
        if ($home_url) {
            echo '<a href="' . esc_url($home_url) . '" target="_blank" rel="noopener">معاينة واجهة المطعم</a>. ';
        }
        echo 'أدخل بيانات Strapi من <a href="' . esc_url(admin_url('options-general.php?page=methaq-restaurant')) . '">إعدادات الإضافة</a> لاستبدال بيانات المعاينة ببيانات مطعمك.</p></div>';
    }

    private function defaults()
    {
        return [
            'api_url' => '',
            'api_token' => '',
            'cache_ttl' => 300,
            'currency' => 'ر.ي',
        ];
    }

    private function settings()
    {
        return wp_parse_args(get_option(self::OPTION, []), $this->defaults());
    }

    public function admin_menu()
    {
        add_options_page(
            'Methaq Restaurant',
            'Methaq Restaurant',
            'manage_options',
            'methaq-restaurant',
            [$this, 'settings_page']
        );
    }

    public function register_settings()
    {
        register_setting('methaq_restaurant_group', self::OPTION, [$this, 'sanitize_settings']);
    }

    public function sanitize_settings($input)
    {
        $input = is_array($input) ? $input : [];
        $current = $this->settings();
        $output = $this->defaults();

        $api_url = isset($input['api_url']) ? esc_url_raw(trim($input['api_url'])) : '';
        $api_url = untrailingslashit($api_url);
        // Be forgiving if /api was accidentally appended to the base URL.
        $api_url = preg_replace('~/api$~i', '', $api_url);
        $output['api_url'] = $api_url;

        // The token input is intentionally blank in the settings page. A blank value
        // means "keep the existing token", not "erase it".
        $new_token = isset($input['api_token']) ? trim((string) $input['api_token']) : '';
        $output['api_token'] = ($new_token !== '')
            ? sanitize_text_field($new_token)
            : $current['api_token'];

        $output['cache_ttl'] = isset($input['cache_ttl'])
            ? max(0, min(86400, absint($input['cache_ttl'])))
            : 300;
        $output['currency'] = isset($input['currency'])
            ? sanitize_text_field($input['currency'])
            : 'ر.ي';

        return $output;
    }

    public function settings_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $settings = $this->settings();
        ?>
        <div class="wrap">
            <h1>Methaq Restaurant — Strapi</h1>
            <p>Connect the plugin to your Strapi REST API. The API token is stored on the server and is never sent to visitors' browsers.</p>
            <form method="post" action="options.php">
                <?php settings_fields('methaq_restaurant_group'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="methaq-api-url">Strapi base URL</label></th>
                        <td>
                            <input class="regular-text" type="url" id="methaq-api-url"
                                name="<?php echo esc_attr(self::OPTION); ?>[api_url]"
                                value="<?php echo esc_attr($settings['api_url']); ?>"
                                placeholder="https://cms.example.com">
                            <p class="description">Enter the Strapi address only, without /api at the end.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="methaq-api-token">Strapi API token</label></th>
                        <td>
                            <input class="regular-text" type="password" autocomplete="new-password"
                                id="methaq-api-token"
                                name="<?php echo esc_attr(self::OPTION); ?>[api_token]"
                                value="" placeholder="Leave blank to keep the saved token">
                            <p class="description">
                                <?php echo $settings['api_token'] !== '' ? 'A token is currently saved. ' : 'No token is saved. '; ?>
                                Paste a read-only Strapi API token only when adding or replacing it. Requests are made server-side.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="methaq-cache-ttl">Cache lifetime (seconds)</label></th>
                        <td>
                            <input type="number" min="0" max="86400" id="methaq-cache-ttl"
                                name="<?php echo esc_attr(self::OPTION); ?>[cache_ttl]"
                                value="<?php echo esc_attr($settings['cache_ttl']); ?>">
                            <p class="description">Set to 0 to disable caching.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="methaq-currency">Currency label</label></th>
                        <td>
                            <input type="text" id="methaq-currency"
                                name="<?php echo esc_attr(self::OPTION); ?>[currency]"
                                value="<?php echo esc_attr($settings['currency']); ?>">
                        </td>
                    </tr>
                </table>
                <?php submit_button('Save settings'); ?>
            </form>
        </div>
        <?php
    }

    public function assets()
    {
        wp_enqueue_style(
            'methaq-restaurant',
            METHAQ_RESTAURANT_URL . 'assets/css/restaurant.css',
            [],
            METHAQ_RESTAURANT_VERSION
        );
        wp_enqueue_script(
            'methaq-restaurant',
            METHAQ_RESTAURANT_URL . 'assets/js/restaurant.js',
            [],
            METHAQ_RESTAURANT_VERSION,
            true
        );
    }

    private function api_get($path)
    {
        $settings = $this->settings();
        if ($settings['api_url'] === '') {
            return new WP_Error('methaq_no_api', 'Strapi URL is not configured.');
        }

        $cache_key = 'methaq_' . md5($settings['api_url'] . '|' . $path);
        if ((int) $settings['cache_ttl'] > 0) {
            $cached = get_transient($cache_key);
            if (false !== $cached) {
                return $cached;
            }
        }

        $url = trailingslashit($settings['api_url']) . 'api/' . ltrim($path, '/');
        $args = [
            'timeout' => 12,
            'redirection' => 3,
            'headers' => ['Accept' => 'application/json'],
        ];
        if ($settings['api_token'] !== '') {
            $args['headers']['Authorization'] = 'Bearer ' . $settings['api_token'];
        }

        $response = wp_safe_remote_get($url, $args);
        if (is_wp_error($response)) {
            return $response;
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        if ($status < 200 || $status >= 300) {
            return new WP_Error('methaq_api_http', 'Strapi returned HTTP ' . $status . '.');
        }

        $decoded = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($decoded) || !array_key_exists('data', $decoded)) {
            return new WP_Error('methaq_api_json', 'Strapi returned an unexpected JSON response.');
        }

        if ((int) $settings['cache_ttl'] > 0) {
            set_transient($cache_key, $decoded, (int) $settings['cache_ttl']);
        }
        return $decoded;
    }

    private function data($path)
    {
        $result = $this->api_get($path);
        if (is_wp_error($result)) {
            return [];
        }
        return is_array($result['data']) ? $result['data'] : [];
    }

    private function attrs($item)
    {
        if (isset($item['attributes']) && is_array($item['attributes'])) {
            return $item['attributes'];
        }
        return is_array($item) ? $item : [];
    }

    private function restaurant()
    {
        // Strapi v4 Single Type response: data.attributes.
        $item = $this->data('restaurant?populate%5B0%5D=image');
        if (isset($item['attributes']) || isset($item['name'])) {
            return $this->attrs($item);
        }

        // Show a complete preview immediately after installation, before Strapi is connected.
        if ($this->settings()['api_url'] === '') {
            return [
                'name' => 'مذاق',
                'description' => 'نكهات طازجة وتجربة طعام تجمع العائلة والأصدقاء.',
                'about' => 'في مذاق نهتم بالتفاصيل؛ من اختيار المكونات الطازجة إلى تقديم أطباق محضّرة بعناية.',
                'phone' => '',
                'address' => 'أضف عنوان مطعمك من Strapi',
                'openingHours' => 'أضف ساعات العمل من Strapi',
            ];
        }
        return [];
    }

    private function image_url($attributes)
    {
        $media = isset($attributes['image']) ? $attributes['image'] : null;
        if (is_array($media) && isset($media['data'])) {
            $media = $media['data'];
        }
        if (is_array($media) && isset($media['attributes']) && is_array($media['attributes'])) {
            $media = $media['attributes'];
        }
        if (!is_array($media) || empty($media['url'])) {
            return '';
        }

        $url = (string) $media['url'];
        if (preg_match('~^https?://~i', $url)) {
            return esc_url($url);
        }

        $base = $this->settings()['api_url'];
        return esc_url(untrailingslashit($base) . '/' . ltrim($url, '/'));
    }

    private function category_slug($attributes)
    {
        $category = isset($attributes['category']) ? $attributes['category'] : [];
        if (is_array($category) && isset($category['data'])) {
            $category = $category['data'];
        }
        if (is_array($category) && isset($category['attributes'])) {
            $category = $category['attributes'];
        }
        if (is_array($category) && !empty($category['slug'])) {
            return sanitize_title($category['slug']);
        }
        return '';
    }

    private function is_available($attributes)
    {
        if (!array_key_exists('isAvailable', $attributes)) {
            return true;
        }
        return filter_var($attributes['isAvailable'], FILTER_VALIDATE_BOOLEAN);
    }

    public function home_shortcode()
    {
        $restaurant = $this->restaurant();
        $name = isset($restaurant['name']) ? $restaurant['name'] : get_bloginfo('name');
        $description = isset($restaurant['description']) ? $restaurant['description'] : 'أطباق طازجة ونكهات تجمعنا.';
        $logo = $this->image_url($restaurant);

        ob_start();
        ?>
        <main class="methaq-site" dir="rtl" lang="ar">
            <header class="methaq-nav">
                <a href="#methaq-top" class="methaq-brand">
                    <?php if ($logo !== '') : ?><img src="<?php echo esc_url($logo); ?>" alt=""><?php endif; ?>
                    <span><?php echo esc_html($name); ?></span>
                </a>
                <nav aria-label="القائمة الرئيسية">
                    <a href="#methaq-menu">قائمة الطعام</a>
                    <a href="#methaq-about">عن المطعم</a>
                    <a href="#methaq-contact">تواصل معنا</a>
                </nav>
            </header>

            <section class="methaq-hero" id="methaq-top">
                <div>
                    <span class="methaq-kicker">أهلاً وسهلاً بكم</span>
                    <h1><?php echo esc_html($name); ?></h1>
                    <p><?php echo esc_html($description); ?></p>
                    <a class="methaq-button" href="#methaq-menu">اكتشف قائمتنا</a>
                </div>
            </section>

            <?php echo $this->menu_shortcode(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

            <section class="methaq-about" id="methaq-about">
                <h2>حكايتنا</h2>
                <p><?php echo esc_html(isset($restaurant['about']) ? $restaurant['about'] : $description); ?></p>
            </section>

            <?php echo $this->contact_shortcode(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

            <footer class="methaq-footer">
                <?php echo esc_html($name); ?> © <?php echo esc_html(wp_date('Y')); ?>
            </footer>
        </main>
        <?php
        return ob_get_clean();
    }

    public function menu_shortcode()
    {
        $demo_mode = ($this->settings()['api_url'] === '');
        $categories = $demo_mode ? [
            ['id' => 1, 'attributes' => ['name' => 'الأطباق الرئيسية', 'slug' => 'main-dishes']],
            ['id' => 2, 'attributes' => ['name' => 'المقبلات', 'slug' => 'appetizers']],
            ['id' => 3, 'attributes' => ['name' => 'المشروبات', 'slug' => 'drinks']],
        ] : $this->data('categories?sort=sortOrder%3Aasc');

        // Explicitly populate the image and category relation (standard Strapi v4 syntax).
        $items = $demo_mode ? [
            ['id' => 1, 'attributes' => ['name' => 'كبسة الدجاج', 'description' => 'أرز متبّل مع دجاج محضّر بعناية.', 'price' => 2500, 'isAvailable' => true, 'category' => ['data' => ['id' => 1, 'attributes' => ['slug' => 'main-dishes']]], 'image' => ['data' => ['attributes' => ['url' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=900&q=80']]]]],
            ['id' => 2, 'attributes' => ['name' => 'سلطة طازجة', 'description' => 'خضروات موسمية مع صلصة المطعم الخاصة.', 'price' => 900, 'isAvailable' => true, 'category' => ['data' => ['id' => 2, 'attributes' => ['slug' => 'appetizers']]], 'image' => ['data' => ['attributes' => ['url' => 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=900&q=80']]]]],
            ['id' => 3, 'attributes' => ['name' => 'عصير الليمون والنعناع', 'description' => 'مشروب منعش محضّر عند الطلب.', 'price' => 700, 'isAvailable' => true, 'category' => ['data' => ['id' => 3, 'attributes' => ['slug' => 'drinks']]], 'image' => ['data' => ['attributes' => ['url' => 'https://images.unsplash.com/photo-1513558161293-cw4e3f7a3d2a?auto=format&fit=crop&w=900&q=80']]]]],
        ] : $this->data('menu-items?populate%5B0%5D=image&populate%5B1%5D=category&sort=sortOrder%3Aasc');

        $visible_items = array_values(array_filter($items, function ($item) {
            return $this->is_available($this->attrs($item));
        }));
        $currency = $this->settings()['currency'];

        ob_start();
        ?>
        <section class="methaq-menu" id="methaq-menu">
            <div class="methaq-section-head">
                <span class="methaq-kicker">مختارة بعناية</span>
                <h2>قائمة الطعام</h2>
                <?php if ($demo_mode) : ?>
                    <p class="methaq-demo-label">معاينة تجريبية — ستظهر بيانات مطعمك بعد ربط Strapi</p>
                <?php endif; ?>
            </div>

            <?php if (empty($visible_items)) : ?>
                <p class="methaq-empty">لا توجد أصناف متاحة حاليًا. تحقق من نشر الأصناف وحالة التوفر في Strapi.</p>
            <?php else : ?>
                <div class="methaq-filters" aria-label="تصفية الأصناف">
                    <button type="button" class="is-active" data-filter="all">الكل</button>
                    <?php foreach ($categories as $category) : $category_attributes = $this->attrs($category); ?>
                        <?php if (!empty($category_attributes['name'])) : ?>
                            <button type="button" data-filter="<?php echo esc_attr(isset($category_attributes['slug']) ? $category_attributes['slug'] : sanitize_title($category_attributes['name'])); ?>">
                                <?php echo esc_html($category_attributes['name']); ?>
                            </button>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <div class="methaq-grid">
                    <?php foreach ($visible_items as $item) :
                        $attributes = $this->attrs($item);
                        $category_slug = $this->category_slug($attributes);
                        $image = $this->image_url($attributes);
                        $item_name = isset($attributes['name']) ? $attributes['name'] : '';
                        ?>
                        <article class="methaq-card" data-category="<?php echo esc_attr($category_slug); ?>">
                            <?php if ($image !== '') : ?>
                                <img class="methaq-card-image" src="<?php echo esc_url($image); ?>"
                                    alt="<?php echo esc_attr($item_name); ?>" loading="lazy">
                            <?php endif; ?>
                            <div class="methaq-card-body">
                                <h3><?php echo esc_html($item_name); ?></h3>
                                <p><?php echo esc_html(isset($attributes['description']) ? $attributes['description'] : ''); ?></p>
                                <strong><?php echo esc_html(number_format_i18n((float) (isset($attributes['price']) ? $attributes['price'] : 0), 2) . ' ' . $currency); ?></strong>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        <?php
        return ob_get_clean();
    }

    public function info_shortcode()
    {
        $restaurant = $this->restaurant();
        $about = isset($restaurant['about']) ? $restaurant['about'] : (isset($restaurant['description']) ? $restaurant['description'] : '');
        return '<section class="methaq-about" dir="rtl"><h2>عن المطعم</h2><p>' . esc_html($about) . '</p></section>';
    }

    public function contact_shortcode()
    {
        $restaurant = $this->restaurant();
        $phone = isset($restaurant['phone']) ? $restaurant['phone'] : '';
        $address = isset($restaurant['address']) ? $restaurant['address'] : '';
        $hours = isset($restaurant['openingHours']) ? $restaurant['openingHours'] : '';
        $phone_link = preg_replace('/[^0-9+]/', '', $phone);

        return '<section class="methaq-contact" id="methaq-contact" dir="rtl">'
            . '<span class="methaq-kicker">نسعد بتواصلكم</span><h2>زورونا أو اتصلوا بنا</h2>'
            . '<div class="methaq-contact-grid">'
            . '<p><b>الهاتف</b><br>' . ($phone !== '' ? '<a href="tel:' . esc_attr($phone_link) . '">' . esc_html($phone) . '</a>' : '—') . '</p>'
            . '<p><b>العنوان</b><br>' . esc_html($address !== '' ? $address : '—') . '</p>'
            . '<p><b>ساعات العمل</b><br>' . esc_html($hours !== '' ? $hours : '—') . '</p>'
            . '</div></section>';
    }
}

new Methaq_Restaurant_Plugin();
