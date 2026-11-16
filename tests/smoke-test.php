<?php
define('ABSPATH', __DIR__ . '/');
define('OBJECT', 'OBJECT');

$GLOBALS['mock_options'] = [
    'methaq_restaurant_settings' => [
        'api_url' => 'https://old-cms.example.test',
        'api_token' => 'OLD-SECRET',
    ],
];
$GLOBALS['mock_created_pages'] = [];
$GLOBALS['mock_menu_items'] = [];
$GLOBALS['mock_theme_mod'] = [];

class WP_Error
{
    public $code;
    public function __construct($code = '', $message = '') { $this->code = $code; }
}

function is_wp_error($value) { return $value instanceof WP_Error; }
function add_action(...$args) {}
function add_shortcode(...$args) {}
function register_activation_hook(...$args) {}
function add_menu_page(...$args) {}
function register_setting(...$args) {}
function plugin_dir_url($file) { return 'https://site.example.test/wp-content/plugins/methaq-restaurant/'; }
function wp_parse_args($args, $defaults = []) { return array_merge($defaults, is_array($args) ? $args : []); }
function get_option($key, $default = false) { return array_key_exists($key, $GLOBALS['mock_options']) ? $GLOBALS['mock_options'][$key] : $default; }
function update_option($key, $value) { $GLOBALS['mock_options'][$key] = $value; return true; }
function delete_option($key) { unset($GLOBALS['mock_options'][$key]); return true; }
function get_post($id) { return null; }
function get_page_by_path($path, $output = OBJECT, $post_type = 'page') { return null; }
function wp_insert_post($postarr, $wp_error = false) {
    $id = 100 + count($GLOBALS['mock_created_pages']) + 1;
    $GLOBALS['mock_created_pages'][] = ['id' => $id, 'post' => $postarr];
    return $id;
}
function get_permalink($post_id) { return 'https://site.example.test/?page_id=' . $post_id; }
function admin_url($path = '') { return 'https://site.example.test/wp-admin/' . $path; }
function get_stylesheet() { return 'methaq-vw-restaurant'; }
function get_registered_nav_menus() { return ['primary' => 'Primary']; }
function get_nav_menu_locations() { return $GLOBALS['mock_theme_mod']['nav_menu_locations'] ?? []; }
function wp_get_nav_menu_object($name) { return null; }
function wp_create_nav_menu($name) { $GLOBALS['mock_created_menu'] = $name; return 900; }
function wp_update_nav_menu_item($menu_id, $item_id, $args) {
    $GLOBALS['mock_menu_items'][] = $args;
    return count($GLOBALS['mock_menu_items']);
}
function set_theme_mod($key, $value) { $GLOBALS['mock_theme_mod'][$key] = $value; }
function current_user_can($capability) { return true; }
function settings_fields($group) { echo '<input type="hidden">'; }
function submit_button($label) { echo '<button>' . esc_html($label) . '</button>'; }
function esc_url_raw($value) { return filter_var($value, FILTER_SANITIZE_URL); }
function esc_url($value) { return filter_var($value, FILTER_SANITIZE_URL); }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_attr($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_textarea($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_textarea_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_title($value) {
    $value = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', (string) $value), '-'));
    return $value === '' ? 'menu-category' : $value;
}
function wp_enqueue_style(...$args) {}
function wp_enqueue_script(...$args) {}

require __DIR__ . '/../methaq-restaurant/methaq-restaurant.php';

$plugin = new Methaq_Restaurant_Plugin();
$plugin->activate();

// The old connector settings must be removed in display-only mode.
if (get_option('methaq_restaurant_settings', null) !== null) {
    throw new RuntimeException('Legacy external CMS settings were not removed.');
}

// Activation creates the public restaurant pages and selects a homepage only when none exists.
if (count($GLOBALS['mock_created_pages']) !== 4) {
    throw new RuntimeException('The plugin did not create all four public pages.');
}
if ((int) get_option('page_on_front') !== 101) {
    throw new RuntimeException('The plugin did not assign the generated homepage.');
}
if (strpos($GLOBALS['mock_created_pages'][0]['post']['post_content'], '[restaurant_home]') === false) {
    throw new RuntimeException('Homepage shortcode is missing.');
}
if (count($GLOBALS['mock_menu_items']) !== 4) {
    throw new RuntimeException('Four navigation items were not generated.');
}

// The homepage renders locally stored defaults with no external CMS dependency.
$html = $plugin->home_shortcode();
foreach (['مذاق', 'كبسة الدجاج', 'سلطة طازجة', 'عصير الليمون والنعناع', 'قائمة الطعام', 'زورونا أو اتصلوا بنا'] as $expected) {
    if (strpos($html, $expected) === false) {
        throw new RuntimeException('Expected display content is missing: ' . $expected);
    }
}
if (strpos($html, '2500') === false) {
    throw new RuntimeException('Menu price was not rendered.');
}
if (strpos($html, 'methaq-hero-media') === false) {
    throw new RuntimeException('Hero image element is missing.');
}

// Settings are sanitized and editable from WordPress.
$clean = $plugin->sanitize_settings([
    'restaurant_name' => '<b>مطعم جديد</b>',
    'tagline' => 'أهلاً بكم',
    'description' => 'وصف الموقع',
    'about' => 'نبذة عن المطعم',
    'phone' => '+967 700 111 222',
    'address' => 'عدن',
    'opening_hours' => 'يوميًا',
    'currency' => 'ر.ي',
    'hero_image' => 'https://example.com/hero.jpg',
    'menu_items' => "الرئيسية | طبق جديد | وصف جديد | 3500 | https://example.com/dish.jpg",
]);
if ($clean['restaurant_name'] !== 'مطعم جديد') {
    throw new RuntimeException('Restaurant name was not sanitized correctly.');
}
if (strpos($clean['menu_items'], 'طبق جديد') === false) {
    throw new RuntimeException('Menu item was not retained in settings.');
}

update_option('methaq_restaurant_display_settings', $clean);
$custom_html = $plugin->home_shortcode();
if (strpos($custom_html, 'طبق جديد') === false || strpos($custom_html, '3500') === false) {
    throw new RuntimeException('Updated WordPress-local menu settings were not rendered.');
}
if (strpos($custom_html, 'مطعم جديد') === false) {
    throw new RuntimeException('Updated restaurant title was not rendered.');
}

ob_start();
$plugin->settings_page();
$settings_html = ob_get_clean();
if (strpos($settings_html, 'Strapi') !== false || strpos($settings_html, 'API Token') !== false) {
    throw new RuntimeException('External CMS settings are still present in the admin UI.');
}
if (strpos($settings_html, 'menu_items') === false) {
    throw new RuntimeException('Local menu editor is missing from settings.');

}

$plugin_source = file_get_contents(__DIR__ . '/../methaq-restaurant/methaq-restaurant.php');
foreach (['Strapi', 'wp_safe_remote_get', 'wp_remote_get', 'api_token', 'api_url'] as $forbidden) {
    if (stripos($plugin_source, $forbidden) !== false) {
        throw new RuntimeException('External integration code remains in plugin: ' . $forbidden);
    }
}

echo "Display-only smoke tests passed: local content, menu/prices, contact, settings sanitization, automatic pages/navigation and no external CMS/API code.\n";
