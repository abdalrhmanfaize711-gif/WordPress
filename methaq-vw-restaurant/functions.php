<?php
/**
 * Functions for the Methaq Restaurant child theme.
 */

if (!defined('ABSPATH')) {
    exit;
}

function methaq_vw_restaurant_enqueue_styles()
{
    $parent_theme = wp_get_theme(get_template());
    $parent_version = $parent_theme->get('Version');

    wp_enqueue_style(
        'vw-restaurant-lite-parent',
        get_template_directory_uri() . '/style.css',
        [],
        $parent_version ? $parent_version : null
    );

    wp_enqueue_style(
        'methaq-vw-restaurant',
        get_stylesheet_uri(),
        ['vw-restaurant-lite-parent'],
        wp_get_theme()->get('Version')
    );

    wp_enqueue_style(
        'methaq-vw-restaurant-rtl',
        get_stylesheet_directory_uri() . '/rtl.css',
        ['methaq-vw-restaurant'],
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'methaq_vw_restaurant_enqueue_styles', 20);

function methaq_vw_restaurant_setup()
{
    load_child_theme_textdomain(
        'methaq-vw-restaurant',
        get_stylesheet_directory() . '/languages'
    );

    add_theme_support('custom-logo', [
        'height' => 160,
        'width' => 260,
        'flex-height' => true,
        'flex-width' => true,
    ]);
}
add_action('after_setup_theme', 'methaq_vw_restaurant_setup', 20);

/**
 * Avoid a sidebar squeezing the restaurant landing page.
 */
function methaq_vw_restaurant_home_body_class($classes)
{
    if (is_front_page()) {
        $classes[] = 'methaq-restaurant-front-page';
    }
    return $classes;
}
add_filter('body_class', 'methaq_vw_restaurant_home_body_class');
