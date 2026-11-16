<?php
/**
 * Plugin Name: Methaq Restaurant — Display Website
 * Description: A simple Arabic RTL restaurant presentation website with locally managed menu and contact details.
 * Version: 2.0.0
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Author: Methaq
 * License: GPL-2.0-or-later
 * Text Domain: methaq-restaurant
 */

if (!defined('ABSPATH')) {
    exit;
}

define('METHAQ_RESTAURANT_VERSION', '2.0.0');
define('METHAQ_RESTAURANT_URL', plugin_dir_url(__FILE__));

final class Methaq_Restaurant_Plugin
{
    const OPTION = 'methaq_restaurant_display_settings';

    public function __construct()
    {
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_init', [$this, 'ensure_pages_exist']);
        add_action('admin_notices', [$this, 'admin_notice']);
        add_action('wp_enqueue_scripts', [$this, 'assets']);
        add_action('after_switch_theme', [$this, 'ensure_pages_exist']);

        register_activation_hook(__FILE__, [$this, 'activate']);

        add_shortcode('restaurant_home', [$this, 'home_shortcode']);
        add_shortcode('restaurant_menu', [$this, 'menu_shortcode']);
        add_shortcode('restaurant_info', [$this, 'info_shortcode']);
        add_shortcode('restaurant_contact', [$this, 'contact_shortcode']);
    }

    public function activate()
    {
        // This release no longer uses the old external CMS connector.
        // Remove the old URL/token settings from previous versions.
        delete_option('methaq_restaurant_settings');
        $this->ensure_pages_exist();
    }

    private function defaults()
    {
        return [
            'restaurant_name' => 'مذاق',
            'tagline' => 'نكهات طازجة وتجربة طعام تجمع العائلة والأصدقاء.',
            'description' => 'في مطعم مذاق نجمع بين الوصفات المحبوبة والمكونات الطازجة لنقدّم لكم تجربة تستحق العودة.',
            'about' => 'نؤمن أن الوجبة الطيبة تجمعنا. نهتم بالتفاصيل الصغيرة؛ من اختيار المكونات إلى طريقة التقديم، لنقدم أطباقًا شهية في أجواء مريحة تناسب العائلة والأصدقاء.',
            'phone' => '',
            'address' => 'أضف عنوان المطعم هنا',
            'opening_hours' => 'يوميًا — أضف ساعات العمل',
            'currency' => 'ر.ي',
            'hero_image' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1800&q=85',
            'menu_items' => implode("\n", [
                'الأطباق الرئيسية|كبسة الدجاج|أرز متبّل مع دجاج محضّر بعناية.|2500|https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=900&q=80',
                'المقبلات|سلطة طازجة|خضروات موسمية مع صلصة المطعم الخاصة.|900|https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=900&q=80',
                'المشروبات|عصير الليمون والنعناع|مشروب منعش محضّر عند الطلب.|700|https://images.unsplash.com/photo-1513558161293-cfaf765edfd4?auto=format&fit=crop&w=900&q=80',
            ]),
        ];
    }

    private function settings()
    {
        return wp_parse_args(get_option(self::OPTION, []), $this->defaults());
    }

    public function admin_menu()
    {
        add_menu_page(
            'إعدادات موقع المطعم',
            'موقع المطعم',
            'manage_options',
            'methaq-restaurant',
            [$this, 'settings_page'],
            'dashicons-store',
            58
        );
    }

    public function register_settings()
    {
        register_setting(
            'methaq_restaurant_display_group',
            self::OPTION,
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_settings'],
                'default' => $this->defaults(),
            ]
        );
    }

    public function sanitize_settings($input)
    {
        $input = is_array($input) ? $input : [];
        $defaults = $this->defaults();
        $output = [];

        $text_fields = [
            'restaurant_name',
            'tagline',
            'phone',
            'address',
            'opening_hours',
            'currency',
        ];

        foreach ($text_fields as $field) {
            $output[$field] = isset($input[$field])
                ? sanitize_text_field($input[$field])
                : $defaults[$field];
        }

        $output['description'] = isset($input['description'])
            ? sanitize_textarea_field($input['description'])
            : $defaults['description'];

        $output['about'] = isset($input['about'])
            ? sanitize_textarea_field($input['about'])
            : $defaults['about'];

        $output['hero_image'] = isset($input['hero_image'])
            ? esc_url_raw(trim($input['hero_image']))
            : $defaults['hero_image'];

        $output['menu_items'] = isset($input['menu_items'])
            ? sanitize_textarea_field($input['menu_items'])
            : $defaults['menu_items'];

        return $output;
    }

    private function field($name, $label, $type = 'text', $description = '')
    {
        $settings = $this->settings();
        $value = isset($settings[$name]) ? $settings[$name] : '';
        ?>
        <tr>
            <th scope="row"><label for="methaq-<?php echo esc_attr($name); ?>"><?php echo esc_html($label); ?></label></th>
            <td>
                <?php if ($type === 'textarea') : ?>
                    <textarea class="large-text" rows="4" id="methaq-<?php echo esc_attr($name); ?>"
                        name="<?php echo esc_attr(self::OPTION); ?>[<?php echo esc_attr($name); ?>]"><?php echo esc_textarea($value); ?></textarea>
                <?php else : ?>
                    <input class="regular-text" type="<?php echo esc_attr($type); ?>"
                        id="methaq-<?php echo esc_attr($name); ?>"
                        name="<?php echo esc_attr(self::OPTION); ?>[<?php echo esc_attr($name); ?>]"
                        value="<?php echo esc_attr($value); ?>">
                <?php endif; ?>
                <?php if ($description !== '') : ?>
                    <p class="description"><?php echo esc_html($description); ?></p>
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }

    public function settings_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1>إعدادات موقع المطعم — مذاق</h1>
            <p>هذه صفحة عرض فقط. عدّل معلومات المطعم والأصناف من هنا؛ لا يحتاج الموقع إلى Strapi أو أي خدمة خارجية.</p>
            <form method="post" action="options.php">
                <?php settings_fields('methaq_restaurant_display_group'); ?>
                <h2>معلومات المطعم</h2>
                <table class="form-table" role="presentation">
                    <?php
                    $this->field('restaurant_name', 'اسم المطعم');
                    $this->field('tagline', 'الجملة التعريفية');
                    $this->field('description', 'وصف الصفحة الرئيسية', 'textarea');
                    $this->field('about', 'عن المطعم', 'textarea');
                    $this->field('hero_image', 'رابط صورة الواجهة', 'url', 'ضع رابط صورة عامة للمطعم أو اترك الصورة الافتراضية.');
                    ?>
                </table>

                <h2>معلومات التواصل</h2>
                <table class="form-table" role="presentation">
                    <?php
                    $this->field('phone', 'رقم الهاتف');
                    $this->field('address', 'العنوان');
                    $this->field('opening_hours', 'ساعات العمل');
                    $this->field('currency', 'رمز العملة');
                    ?>
                </table>

                <h2>قائمة الطعام والأسعار</h2>
                <p>اكتب كل صنف في سطر مستقل، وافصل البيانات بعلامة <code>|</code> بهذا الترتيب:</p>
                <p><code>التصنيف | اسم الطبق | الوصف | السعر | رابط الصورة</code></p>
                <table class="form-table" role="presentation">
                    <?php
                    $this->field(
                        'menu_items',
                        'أصناف الطعام',
                        'textarea',
                        'مثال: الأطباق الرئيسية | كبسة الدجاج | أرز متبّل مع الدجاج | 2500 | https://example.com/dish.jpg — رابط الصورة اختياري.'
                    );
                    ?>
                </table>
                <?php submit_button('حفظ بيانات المطعم'); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Create the four public pages on activation. Existing pages are preserved.
     */
    public function ensure_pages_exist()
    {
        $pages = [
            'home' => ['title' => 'الرئيسية - مذاق', 'slug' => 'restaurant-home', 'content' => '[restaurant_home]'],
            'menu' => ['title' => 'قائمة الطعام', 'slug' => 'restaurant-menu', 'content' => '[restaurant_menu]'],
            'about' => ['title' => 'عن المطعم', 'slug' => 'restaurant-about', 'content' => '[restaurant_info]'],
            'contact' => ['title' => 'تواصل معنا', 'slug' => 'restaurant-contact', 'content' => '[restaurant_contact]'],
        ];

        $page_ids = (array) get_option('methaq_page_ids', []);
        foreach ($pages as $key => $page) {
            if (!empty($page_ids[$key]) && get_post((int) $page_ids[$key])) {
                continue;
            }

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
            // Never overwrite a homepage already selected by the site owner.
            if (!(int) get_option('page_on_front')) {
                update_option('show_on_front', 'page');
                update_option('page_on_front', (int) $page_ids['home']);
            }
            update_option('methaq_page_ids', $page_ids);
            $this->ensure_navigation($page_ids);
        }
    }

    private function ensure_navigation($page_ids)
    {
        if (!function_exists('get_stylesheet') || get_stylesheet() !== 'methaq-vw-restaurant') {
            return;
        }
        if (!function_exists('get_registered_nav_menus')) {
            return;
        }

        $registered = get_registered_nav_menus();
        $assigned = get_nav_menu_locations();

        foreach ($registered as $location => $description) {
            if (!empty($assigned[$location])) {
                return;
            }
        }

        if (empty($registered) || !function_exists('wp_create_nav_menu')) {
            return;
        }

        $menu = wp_get_nav_menu_object('مذاق - القائمة الرئيسية');
        $menu_id = $menu ? (int) $menu->term_id : wp_create_nav_menu('مذاق - القائمة الرئيسية');
        if (is_wp_error($menu_id) || !$menu_id) {
            return;
        }

        $labels = [
            'home' => 'الرئيسية',
            'menu' => 'قائمة الطعام',
            'about' => 'عن المطعم',
            'contact' => 'تواصل معنا',
        ];

        foreach ($labels as $key => $label) {
            if (empty($page_ids[$key])) {
                continue;
            }
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => $label,
                'menu-item-object' => 'page',
                'menu-item-object-id' => (int) $page_ids[$key],
                'menu-item-type' => 'post_type',
                'menu-item-status' => 'publish',
            ]);
        }

        $location = isset($registered['primary']) ? 'primary' : key($registered);
        $assigned[$location] = $menu_id;
        set_theme_mod('nav_menu_locations', $assigned);
    }

    public function admin_notice()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $theme_active = function_exists('get_stylesheet') && get_stylesheet() === 'methaq-vw-restaurant';
        if (!$theme_active) {
            $theme_url = admin_url('theme-install.php?search=VW%20Restaurant%20Lite');
            echo '<div class="notice notice-warning"><p><strong>موقع مذاق:</strong> للحصول على التصميم المعدّل، ثبّت قالب <a href="' . esc_url($theme_url) . '">VW Restaurant Lite</a> ثم فعّل قالب Methaq Restaurant الفرعي.</p></div>';
        }

        $home_id = (int) get_option('page_on_front');
        $home_url = $home_id ? get_permalink($home_id) : '';
        echo '<div class="notice notice-info"><p><strong>موقع مذاق:</strong> ';
        if ($home_url) {
            echo '<a href="' . esc_url($home_url) . '" target="_blank" rel="noopener">معاينة الموقع</a>. ';
        }
        echo 'يمكنك تعديل اسم المطعم والأصناف والأسعار من قائمة <a href="' . esc_url(admin_url('admin.php?page=methaq-restaurant')) . '">موقع المطعم</a>.</p></div>';
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

    private function menu_items()
    {
        $settings = $this->settings();
        $lines = preg_split('/\r\n|\r|\n/', (string) $settings['menu_items']);
        $items = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = array_map('trim', explode('|', $line));
            if (count($parts) < 4) {
                continue;
            }

            $items[] = [
                'category' => sanitize_text_field($parts[0]),
                'name' => sanitize_text_field($parts[1]),
                'description' => sanitize_text_field($parts[2]),
                'price' => sanitize_text_field($parts[3]),
                'image' => isset($parts[4]) ? esc_url($parts[4]) : '',
            ];
        }

        return $items;
    }

    public function home_shortcode()
    {
        $settings = $this->settings();

        ob_start();
        ?>
        <div class="methaq-site" dir="rtl" lang="ar">
            <section class="methaq-hero" id="methaq-top" style="--methaq-hero-image:url('<?php echo esc_url($settings['hero_image']); ?>')">
                <div class="methaq-hero-content">
                    <span class="methaq-kicker"><?php echo esc_html($settings['tagline']); ?></span>
                    <h1><?php echo esc_html($settings['restaurant_name']); ?></h1>
                    <p><?php echo esc_html($settings['description']); ?></p>
                    <a class="methaq-button" href="#methaq-menu">استعرض قائمة الطعام</a>
                </div>
            </section>

            <?php echo $this->menu_shortcode(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

            <section class="methaq-about" id="methaq-about">
                <span class="methaq-kicker">حكايتنا</span>
                <h2>نكهات تجمعنا</h2>
                <p><?php echo esc_html($settings['about']); ?></p>
            </section>

            <?php echo $this->contact_shortcode(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public function menu_shortcode()
    {
        $settings = $this->settings();
        $items = $this->menu_items();
        $categories = [];

        foreach ($items as $item) {
            if ($item['category'] !== '' && !in_array($item['category'], $categories, true)) {
                $categories[] = $item['category'];
            }
        }

        ob_start();
        ?>
        <section class="methaq-menu" id="methaq-menu" dir="rtl">
            <div class="methaq-section-head">
                <span class="methaq-kicker">مختارة بعناية</span>
                <h2>قائمة الطعام</h2>
                <p>اختر من أطباقنا المحضّرة بعناية.</p>
            </div>

            <?php if (!empty($items)) : ?>
                <div class="methaq-filters" aria-label="تصنيفات قائمة الطعام">
                    <button type="button" class="is-active" data-filter="all">الكل</button>
                    <?php foreach ($categories as $category) : ?>
                        <button type="button" data-filter="<?php echo esc_attr(sanitize_title($category)); ?>">
                            <?php echo esc_html($category); ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="methaq-grid">
                    <?php foreach ($items as $item) : ?>
                        <article class="methaq-card" data-category="<?php echo esc_attr(sanitize_title($item['category'])); ?>">
                            <?php if ($item['image'] !== '') : ?>
                                <img class="methaq-card-image" src="<?php echo esc_url($item['image']); ?>"
                                    alt="<?php echo esc_attr($item['name']); ?>" loading="lazy">
                            <?php endif; ?>
                            <div class="methaq-card-body">
                                <span class="methaq-card-category"><?php echo esc_html($item['category']); ?></span>
                                <h3><?php echo esc_html($item['name']); ?></h3>
                                <p><?php echo esc_html($item['description']); ?></p>
                                <strong><?php echo esc_html($item['price'] . ' ' . $settings['currency']); ?></strong>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p class="methaq-empty">أضف أصناف قائمة الطعام من إعدادات موقع المطعم في لوحة WordPress.</p>
            <?php endif; ?>
        </section>
        <?php
        return ob_get_clean();
    }

    public function info_shortcode()
    {
        $settings = $this->settings();
        return '<section class="methaq-about" dir="rtl"><span class="methaq-kicker">حكايتنا</span><h2>عن المطعم</h2><p>' . esc_html($settings['about']) . '</p></section>';
    }

    public function contact_shortcode()
    {
        $settings = $this->settings();
        $phone_link = preg_replace('/[^0-9+]/', '', $settings['phone']);

        return '<section class="methaq-contact" id="methaq-contact" dir="rtl">'
            . '<span class="methaq-kicker">يسعدنا تواصلك</span><h2>زورونا أو اتصلوا بنا</h2>'
            . '<div class="methaq-contact-grid">'
            . '<p><b>الهاتف</b><br>' . ($settings['phone'] !== '' ? '<a href="tel:' . esc_attr($phone_link) . '">' . esc_html($settings['phone']) . '</a>' : 'أضف رقم الهاتف من الإعدادات') . '</p>'
            . '<p><b>العنوان</b><br>' . esc_html($settings['address']) . '</p>'
            . '<p><b>ساعات العمل</b><br>' . esc_html($settings['opening_hours']) . '</p>'
            . '</div></section>';
    }
}

new Methaq_Restaurant_Plugin();
