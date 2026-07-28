<?php
/**
 * Template Name: Wishlist Page
 * Shows all products the user has saved to their wishlist
 */

// Per-user personalized content — never cache. This is a standalone custom
// page template, not a WooCommerce endpoint, so LiteSpeed Cache's built-in
// WooCommerce-aware auto-exclusions (cart/checkout/my-account) don't cover
// it. Without this, LiteSpeed's server-level page cache serves whichever
// visitor's wishlist snapshot got cached first to everyone else, and shows
// "removed" items reappearing on refresh, for up to its cache lifetime.
// (Harmless no-op on hosts without LiteSpeed Cache active — e.g. local dev.)
do_action( 'litespeed_control_set_nocache', 'wishlist page — personalized per-user content' );
nocache_headers();

get_header();
?>
<main id="main" class="wishlist-main" role="main">
    <div class="container sr-layout">
        <div class="section__header" style="margin-bottom:2rem;">
           
            <a href="<?php echo esc_url( wc_get_page_permalink('shop') ); ?>" class="section__link">Continue Shopping &rarr;</a>
        </div>
        <?php echo do_shortcode('[eskecy_wishlist]'); ?>
        
    </div>
</main>
<?php get_footer(); ?>
