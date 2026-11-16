<?php
/**
 * Plugin Name: Methaq Restaurant — Strapi Connector
 * Description: Responsive Arabic RTL restaurant website powered by Strapi CMS.
 * Version: 1.0.0
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Author: Methaq
 * License: GPL-2.0-or-later
 * Text Domain: methaq-restaurant
 */
if (!defined('ABSPATH')) { exit; }
define('METHAQ_RESTAURANT_VERSION', '1.0.0');
define('METHAQ_RESTAURANT_FILE', __FILE__);
define('METHAQ_RESTAURANT_URL', plugin_dir_url(__FILE__));

final class Methaq_Restaurant_Plugin {
    const OPTION = 'methaq_restaurant_settings';
    public function __construct() {
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_enqueue_scripts', [$this, 'assets']);
        add_shortcode('restaurant_home', [$this, 'home_shortcode']);
        add_shortcode('restaurant_menu', [$this, 'menu_shortcode']);
        add_shortcode('restaurant_info', [$this, 'info_shortcode']);
        add_shortcode('restaurant_contact', [$this, 'contact_shortcode']);
    }
    public function defaults() {
        return ['api_url'=>'','api_token'=>'','cache_ttl'=>300,'currency'=>'ر.ي'];
    }
    public function settings() {
        return wp_parse_args(get_option(self::OPTION, []), $this->defaults());
    }
    public function admin_menu() {
        add_options_page('Methaq Restaurant', 'Methaq Restaurant', 'manage_options', 'methaq-restaurant', [$this,'settings_page']);
    }
    public function register_settings() {
        register_setting('methaq_restaurant_group', self::OPTION, [$this,'sanitize_settings']);
    }
    public function sanitize_settings($input) {
        $out = $this->defaults();
        $out['api_url'] = isset($input['api_url']) ? esc_url_raw(untrailingslashit(trim($input['api_url']))) : '';
        $out['api_token'] = isset($input['api_token']) ? sanitize_text_field($input['api_token']) : '';
        $out['cache_ttl'] = isset($input['cache_ttl']) ? max(0, min(86400, absint($input['cache_ttl']))) : 300;
        $out['currency'] = isset($input['currency']) ? sanitize_text_field($input['currency']) : 'ر.ي';
        return $out;
    }
    public function settings_page() {
        if (!current_user_can('manage_options')) return;
        $s = $this->settings(); ?>
        <div class="wrap"><h1>Methaq Restaurant — Strapi</h1>
        <p>Connect this plugin to your Strapi REST API. Keep the API token private.</p>
        <form method="post" action="options.php"><?php settings_fields('methaq_restaurant_group'); ?>
        <table class="form-table"><tr><th><label for="methaq-api-url">Strapi base URL</label></th><td><input class="regular-text" type="url" id="methaq-api-url" name="<?php echo esc_attr(self::OPTION); ?>[api_url]" value="<?php echo esc_attr($s['api_url']); ?>" placeholder="https://cms.example.com"></td></tr>
        <tr><th><label for="methaq-api-token">API token</label></th><td><input class="regular-text" type="password" autocomplete="new-password" id="methaq-api-token" name="<?php echo esc_attr(self::OPTION); ?>[api_token]" value="<?php echo esc_attr($s['api_token']); ?>"><p class="description">Use a Strapi read-only token. Requests are made server-side.</p></td></tr>
        <tr><th><label for="methaq-cache-ttl">Cache lifetime (seconds)</label></th><td><input type="number" min="0" max="86400" id="methaq-cache-ttl" name="<?php echo esc_attr(self::OPTION); ?>[cache_ttl]" value="<?php echo esc_attr($s['cache_ttl']); ?>"></td></tr>
        <tr><th><label for="methaq-currency">Currency label</label></th><td><input type="text" id="methaq-currency" name="<?php echo esc_attr(self::OPTION); ?>[currency]" value="<?php echo esc_attr($s['currency']); ?>"></td></tr></table>
        <?php submit_button('Save settings'); ?></form></div><?php
    }
    public function assets() {
        wp_enqueue_style('methaq-restaurant', METHAQ_RESTAURANT_URL . 'assets/css/restaurant.css', [], METHAQ_RESTAURANT_VERSION);
        wp_enqueue_script('methaq-restaurant', METHAQ_RESTAURANT_URL . 'assets/js/restaurant.js', [], METHAQ_RESTAURANT_VERSION, true);
    }
    private function api_get($path, $cache_key) {
        $s = $this->settings();
        if (empty($s['api_url'])) return new WP_Error('methaq_no_api','Strapi URL is not configured.');
        $cached = get_transient($cache_key);
        if (false !== $cached) return $cached;
        $url = trailingslashit($s['api_url']) . 'api/' . ltrim($path, '/');
        $args = ['timeout'=>12, 'headers'=>['Accept'=>'application/json']];
        if (!empty($s['api_token'])) $args['headers']['Authorization'] = 'Bearer ' . $s['api_token'];
        $response = wp_safe_remote_get($url, $args);
        if (is_wp_error($response)) return $response;
        $code = wp_remote_retrieve_response_code($response);
        if ($code < 200 || $code >= 300) return new WP_Error('methaq_api_http','Strapi returned HTTP ' . $code);
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($data)) return new WP_Error('methaq_api_json','Strapi response was not valid JSON.');
        set_transient($cache_key, $data, (int)$s['cache_ttl']);
        return $data;
    }
    private function data($path, $key) {
        $result = $this->api_get($path, 'methaq_' . md5($key . $path));
        return is_wp_error($result) ? [] : (isset($result['data']) ? $result['data'] : []);
    }
    private function attrs($item) {
        return isset($item['attributes']) && is_array($item['attributes']) ? $item['attributes'] : $item;
    }
    private function image_url($attrs) {
        $media = $attrs['image']['data'] ?? ($attrs['image'] ?? null);
        if (is_array($media) && isset($media['attributes']['url'])) $url = $media['attributes']['url'];
        elseif (is_array($media) && isset($media['url'])) $url = $media['url'];
        else return '';
        if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) return esc_url($url);
        $base = $this->settings()['api_url'];
        return esc_url(untrailingslashit($base) . '/' . ltrim($url, '/'));
    }
    private function restaurant() {
        $items = $this->data('restaurant?populate=deep', 'restaurant');
        if (isset($items['attributes'])) return $items['attributes'];
        if (isset($items[0])) return $this->attrs($items[0]);
        return [];
    }
    public function home_shortcode() {
        $r = $this->restaurant();
        $name = $r['name'] ?? get_bloginfo('name');
        $desc = $r['description'] ?? 'أطباق طازجة ونكهات تجمعنا.';
        $logo = $this->image_url($r);
        ob_start(); ?>
        <main class="methaq-site" dir="rtl" lang="ar">
          <header class="methaq-nav"><a href="#methaq-top" class="methaq-brand"><?php if($logo): ?><img src="<?php echo $logo; ?>" alt=""><?php endif; ?><span><?php echo esc_html($name); ?></span></a><nav><a href="#methaq-menu">قائمة الطعام</a><a href="#methaq-about">عن المطعم</a><a href="#methaq-contact">تواصل معنا</a></nav></header>
          <section class="methaq-hero" id="methaq-top"><div><span class="methaq-kicker">أهلاً وسهلاً بكم</span><h1><?php echo esc_html($name); ?></h1><p><?php echo esc_html($desc); ?></p><a class="methaq-button" href="#methaq-menu">اكتشف قائمتنا</a></div></section>
          <?php echo $this->menu_shortcode(); ?>
          <section class="methaq-about" id="methaq-about"><h2>حكايتنا</h2><p><?php echo esc_html($r['about'] ?? $desc); ?></p></section>
          <?php echo $this->contact_shortcode(); ?>
          <footer class="methaq-footer"><?php echo esc_html($name); ?> © <?php echo esc_html(wp_date('Y')); ?></footer>
        </main><?php return ob_get_clean();
    }
    public function menu_shortcode() {
        $categories = $this->data('categories?sort=sortOrder:asc', 'categories');
        $items = $this->data('menu-items?populate=*&sort=sortOrder:asc', 'menu-items');
        $currency = $this->settings()['currency'];
        ob_start(); ?>
        <section class="methaq-menu" id="methaq-menu"><div class="methaq-section-head"><span class="methaq-kicker">مختارة بعناية</span><h2>قائمة الطعام</h2></div>
        <?php if (empty($items)): ?><p class="methaq-empty">لا توجد أصناف منشورة حاليًا. يرجى مراجعة إعدادات Strapi.</p><?php else: ?>
        <div class="methaq-filters"><button class="is-active" data-filter="all">الكل</button>
        <?php foreach($categories as $cat): $ca=$this->attrs($cat); ?><button data-filter="<?php echo esc_attr($ca['slug'] ?? sanitize_title($ca['name'] ?? '')); ?>"><?php echo esc_html($ca['name'] ?? ''); ?></button><?php endforeach; ?></div>
        <div class="methaq-grid"><?php foreach($items as $item): $a=$this->attrs($item); $cat=$a['category']['data']['attributes']['slug'] ?? ($a['category']['slug'] ?? ''); $img=$this->image_url($a); ?>
        <article class="methaq-card" data-category="<?php echo esc_attr($cat); ?>"><?php if($img): ?><img class="methaq-card-image" src="<?php echo $img; ?>" alt="<?php echo esc_attr($a['name'] ?? ''); ?>" loading="lazy"><?php endif; ?><div class="methaq-card-body"><h3><?php echo esc_html($a['name'] ?? ''); ?></h3><p><?php echo esc_html($a['description'] ?? ''); ?></p><strong><?php echo esc_html(number_format_i18n((float)($a['price'] ?? 0), 2) . ' ' . $currency); ?></strong></div></article>
        <?php endforeach; ?></div><?php endif; ?></section><?php return ob_get_clean();
    }
    public function info_shortcode() {
        $r=$this->restaurant(); return '<section class="methaq-about" dir="rtl"><h2>عن المطعم</h2><p>'.esc_html($r['about'] ?? $r['description'] ?? '').'</p></section>';
    }
    public function contact_shortcode() {
        $r=$this->restaurant(); $phone=$r['phone'] ?? ''; $address=$r['address'] ?? ''; $hours=$r['openingHours'] ?? '';
        return '<section class="methaq-contact" id="methaq-contact" dir="rtl"><span class="methaq-kicker">نسعد بتواصلكم</span><h2>زورونا أو اتصلوا بنا</h2><div class="methaq-contact-grid"><p><b>الهاتف</b><br>'.($phone ? '<a href="tel:'.esc_attr(preg_replace('/[^0-9+]/','',$phone)).'">'.esc_html($phone).'</a>' : '—').'</p><p><b>العنوان</b><br>'.esc_html($address ?: '—').'</p><p><b>ساعات العمل</b><br>'.esc_html($hours ?: '—').'</p></div></section>';
    }
}
new Methaq_Restaurant_Plugin();
