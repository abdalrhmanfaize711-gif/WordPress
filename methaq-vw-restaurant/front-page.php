<?php
/**
 * Front page template.
 *
 * Uses the VW Restaurant Lite parent theme header/footer and places the
 * Strapi-powered [restaurant_home] sections in the content area.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<div class="methaq-theme-front-page">
    <main id="primary" class="site-main methaq-theme-front-content">
        <?php
        while (have_posts()) :
            the_post();
            the_content();
        endwhile;
        ?>
    </main>
</div>
<?php
get_footer();
