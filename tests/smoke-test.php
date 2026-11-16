<?php
define('ABSPATH', __DIR__ . '/');
define('OBJECT', 'OBJECT');
$GLOBALS['mock_created_pages'] = [];
$GLOBALS['mock_options'] = [
    'methaq_restaurant_settings' => [
        'api_url' => 'https://cms.example.test',
        'api_token' => 'KEEP-SECRET',
        'cache_ttl' => 0,
        'currency' => 'ر.ي',
    ],
];
$GLOBALS['mock_requests'] = [];

class WP_Error
{
    public $code;
    public function __construct($code = '', $message = '')
    {
        $this->code = $code;
    }
}

function is_wp_error($value) { return $value instanceof WP_Error; }
function add_action(...$args) {}
function register_activation_hook(...$args) {}
function add_shortcode(...$args) {}
function get_stylesheet() { return 'methaq-vw-restaurant'; }
function get_registered_nav_menus() { return ['primary' => 'Primary']; }
function get_nav_menu_locations() { return $GLOBALS['mock_theme_mod']['nav_menu_locations'] ?? []; }
function wp_get_nav_menu_object($name) { return null; }
function wp_create_nav_menu($name) { $GLOBALS['mock_created_menu'] = $name; return 900; }
function wp_update_nav_menu_item($menu_id, $item_id, $args) { $GLOBALS['mock_menu_items'][] = $args; return count($GLOBALS['mock_menu_items']); }
function set_theme_mod($key, $value) { $GLOBALS['mock_theme_mod'][$key] = $value; }
function register_setting(...$args) {}
function add_options_page(...$args) {}
function plugin_dir_url($file) { return 'https://site.example.test/wp-content/plugins/methaq-restaurant/'; }
function wp_parse_args($args, $defaults = []) { return array_merge($defaults, is_array($args) ? $args : []); }
function get_option($key, $default = false) { return $GLOBALS['mock_options'][$key] ?? $default; }
function update_option($key, $value) { $GLOBALS['mock_options'][$key] = $value; return true; }
function get_page_by_path($path, $output = OBJECT, $post_type = 'page') { return null; }
function wp_insert_post($postarr, $wp_error = false) { $id = 100 + count($GLOBALS['mock_created_pages']) + 1; $GLOBALS['mock_created_pages'][] = ['id' => $id, 'post' => $postarr]; return $id; }
function get_permalink($post_id) { return 'https://site.example.test/?page_id=' . $post_id; }
function admin_url($path = '') { return 'https://site.example.test/wp-admin/' . $path; }
function get_transient($key) { return false; }
function set_transient(...$args) {}
function trailingslashit($value) { return rtrim($value, '/') . '/'; }
function untrailingslashit($value) { return rtrim($value, '/'); }
function esc_url_raw($value) { return $value; }
function esc_url($value) { return $value; }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_attr($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_title($value) { return strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', (string) $value), '-')); }
function absint($value) { return abs((int) $value); }
function wp_safe_remote_get($url, $args)
{
    $GLOBALS['mock_requests'][] = ['url' => $url, 'headers' => $args['headers'] ?? []];
    if (strpos($url, '/api/restaurant?') !== false) {
        $data = ['data' => ['id' => 1, 'attributes' => [
            'name' => 'مطعم اختبار',
            'description' => 'وصف تجريبي',
            'about' => 'عن المطعم',
            'phone' => '+967700000000',
            'address' => 'عدن',
            'openingHours' => 'يومياً',
            'image' => ['data' => null],
        ]]];
    } elseif (strpos($url, '/api/categories?') !== false) {
        $data = ['data' => [['id' => 1, 'attributes' => ['name' => 'الرئيسية', 'slug' => 'main-dishes']]]];
    } elseif (strpos($url, '/api/menu-items?') !== false) {
        $data = ['data' => [
            ['id' => 1, 'attributes' => [
                'name' => 'طبق متاح',
                'description' => 'وصف الطبق',
                'price' => 1200,
                'isAvailable' => true,
                'category' => ['data' => ['id' => 1, 'attributes' => ['slug' => 'main-dishes']]],
                'image' => ['data' => null],
            ]],
            ['id' => 2, 'attributes' => [
                'name' => 'طبق غير متاح',
                'description' => 'مخفي',
                'price' => 500,
                'isAvailable' => false,
                'category' => ['data' => ['id' => 1, 'attributes' => ['slug' => 'main-dishes']]],
                'image' => ['data' => null],
            ]],
        ]];
    } else {
        $data = ['data' => []];
    }
    return ['response' => ['code' => 200], 'body' => json_encode($data, JSON_UNESCAPED_UNICODE)];
}
function wp_remote_retrieve_response_code($response) { return $response['response']['code'] ?? 200; }
function wp_remote_retrieve_body($response) { return $response['body'] ?? ''; }
function get_bloginfo($key) { return 'Test Site'; }
function wp_date($format) { return '2026'; }
function number_format_i18n($number, $decimals = 0) { return number_format($number, $decimals); }
function wp_enqueue_style(...$args) {}
function wp_enqueue_script(...$args) {}
function current_user_can($capability) { return true; }
function settings_fields($group) { echo '<input type="hidden">'; }
function submit_button($label) { echo '<button>' . $label . '</button>'; }

require __DIR__ . '/../methaq-restaurant/methaq-restaurant.php';

$plugin = new Methaq_Restaurant_Plugin();
$html = $plugin->home_shortcode();

if (strpos($html, 'مطعم اختبار') === false) {
    throw new RuntimeException('Restaurant content is missing.');
}
if (strpos($html, 'طبق متاح') === false) {
    throw new RuntimeException('Available menu item is missing.');
}
if (strpos($html, 'طبق غير متاح') !== false) {
    throw new RuntimeException('Unavailable menu item was rendered.');
}
if (strpos($html, 'data-category="main-dishes"') === false) {
    throw new RuntimeException('Category relation was not parsed.');
}

$sanitized = $plugin->sanitize_settings([
    'api_url' => 'https://cms.example.test/api',
    'api_token' => '',
    'cache_ttl' => '0',
    'currency' => 'ر.ي',
]);
if ($sanitized['api_token'] !== 'KEEP-SECRET') {
    throw new RuntimeException('Blank token did not preserve the saved value.');
}
if ($sanitized['api_url'] !== 'https://cms.example.test') {
    throw new RuntimeException('API base URL normalization failed.');
}

ob_start();
$plugin->settings_page();
$settings_html = ob_get_clean();
if (strpos($settings_html, 'KEEP-SECRET') !== false) {
    throw new RuntimeException('Saved API token was exposed in settings HTML.');
}
if (strpos($settings_html, 'Leave blank to keep the saved token') === false) {
    throw new RuntimeException('Token placeholder is missing.');
}

foreach ($GLOBALS['mock_requests'] as $request) {
    if (($request['headers']['Authorization'] ?? '') !== 'Bearer KEEP-SECRET') {
        throw new RuntimeException('Authorization header was not sent server-side.');
    }
}

echo "Smoke tests passed: homepage, Strapi data, availability filter, category relation, token preservation and token hiding.\n";


// Verify that activation creates the four pages and selects a homepage only when none exists.
$plugin->ensure_pages_exist();
if (count($GLOBALS['mock_created_pages']) !== 4) {
    throw new RuntimeException('The plugin did not create all four restaurant pages.');
}
if ((int) get_option('page_on_front') !== 101) {
    throw new RuntimeException('The plugin did not set the generated homepage.');
}
if (strpos($GLOBALS['mock_created_pages'][0]['post']['post_content'], '[restaurant_home]') === false) {
    throw new RuntimeException('The generated homepage does not contain its shortcode.');
}
if (count($GLOBALS['mock_menu_items']) !== 4) {
    throw new RuntimeException('The plugin did not generate four primary navigation items.');
}
if ((int) ($GLOBALS['mock_theme_mod']['nav_menu_locations']['primary'] ?? 0) !== 900) {
    throw new RuntimeException('The generated restaurant navigation was not assigned to the primary location.');
}

// Verify the pre-connection preview is visible before Strapi is configured.
$GLOBALS['mock_options']['methaq_restaurant_settings']['api_url'] = '';
$preview = $plugin->home_shortcode();
if (strpos($preview, 'معاينة تجريبية') === false || strpos($preview, 'كبسة الدجاج') === false) {
    throw new RuntimeException('The pre-connection restaurant preview is not visible.');
}

echo "Page creation and pre-connection preview tests passed.\n";
