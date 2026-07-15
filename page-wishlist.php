<?php
/**
 * Template Name: Wishlist Page
 * Shows all products the user has saved to their wishlist
 */
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
