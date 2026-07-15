<?php
/**
 * Template Part: Editorial Hero Banner
 */

$hero_products = wc_get_products(['status' => 'publish', 'limit' => 1, 'featured' => true, 'orderby' => 'date', 'order' => 'DESC']);
if ( empty($hero_products) ) {
    $hero_products = wc_get_products(['status' => 'publish', 'limit' => 1, 'orderby' => 'date', 'order' => 'DESC']);
}
$hero_product = ! empty($hero_products) ? $hero_products[0] : null;
$hero_img     = $hero_product ? wp_get_attachment_image_url($hero_product->get_image_id(), 'large') : '';
$hero_name    = $hero_product ? $hero_product->get_name()      : '';
$hero_url     = $hero_product ? $hero_product->get_permalink() : home_url('/shop');
$shop_url     = class_exists('WooCommerce') ? wc_get_page_permalink('shop') : home_url('/shop');
?>

<section class="hb" aria-label="Hero Banner">

    <!-- Giant watermark word — intentionally overflows both sides -->
    <div class="hb__wm" aria-hidden="true">ESKECY</div>

    <!-- Transparent card with V.02 and double curve inside -->
    <div class="hb__card-group" aria-hidden="true">
        <div class="hb__card">
            <div class="hb__card-footer">
                <span class="hb__card-badge"> SK.26</span>
                <svg height="120" width="300" class="hb__card-curve" viewBox="0 0 420 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="hbLineFade" gradientUnits="userSpaceOnUse" x1="0" y1="180" x2="420" y2="180">
                            <stop offset="0%" stop-color="#e8192c" stop-opacity="0"/>
                            <stop offset="50%" stop-color="#e8192c" stop-opacity="0.9"/>
                            <stop offset="100%" stop-color="#e8192c" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                    <!-- Thick S-curve 1 — flat ends -->
                    <path d="M0 62 C100 62 130 18 210 32 C290 46 320 80 420 72" stroke="#7f8182" stroke-width="20" stroke-linecap="round" opacity="0.25"/>
                    <!-- Thick S-curve 2 — flat ends -->
                    <path d="M0 128 C100 128 130 84 210 98 C290 112 320 146 420 138" stroke="#e8192c" stroke-width="20" stroke-linecap="round" opacity="0.25"/>
                    <!-- Straight line, faded at both ends, with gap below the curves -->
                    <line x1="0" y1="180" x2="420" y2="180" stroke="url(#hbLineFade)" stroke-width="3" stroke-linecap="round"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Spec label: top-left -->
    <div class="hb__spec hb__spec--tl">
        <span class="hb__spec-key">Collection</span>
        <span class="hb__spec-val">Ultra Premium</span>
    </div>

    <!-- Spec label: top-right -->
    <div class="hb__spec hb__spec--tr">
        <span class="hb__spec-key">Shipping</span>
        <span class="hb__spec-val">Free Delivery</span>
    </div>

    <!-- Spec label: mid-left (with red accent line) -->
    <div class="hb__spec hb__spec--ml hb__spec--accent">
        <span class="hb__spec-key">Design</span>
        <span class="hb__spec-val">Modern Fit</span>
    </div>

    <!-- Spec label: mid-right -->
    <div class="hb__spec hb__spec--mr">
        <span class="hb__spec-key">Quality</span>
        <span class="hb__spec-val">Graphene Fabric</span>
    </div>

    <!-- Center: floating product image -->
    <div class="hb__product-wrap">
        <?php if ( $hero_img ) : ?>
        <a href="<?php echo esc_url($hero_url); ?>" class="hb__product-link" tabindex="0">
            <img src="<?php echo esc_url($hero_img); ?>"
                 alt="<?php echo esc_attr($hero_name); ?>"
                 class="hb__product-img">
        </a>
        <?php endif; ?>
    </div>

    <!-- Small red dot indicator (right-center) -->
  

    <!-- Bottom-left: headline + description -->
    <div class="hb__content">
        <h1 class="hb__headline">
            <span class="hb__line--black">Wear the Look.</span>
            <span class="hb__line--red">Own the Style.</span>
        </h1>
        <p class="hb__desc">Crafted for the elite. Eskecy streetwear combines premium fabric with modern fit and unmatched street culture.</p>
    </div>

    <!-- Bottom-right: stats + CTA circle -->
    <div class="hb__br">
        <div class="hb__stats">
            <div class="hb__stat">
                <span class="hb__stat-num">50+</span>
                <span class="hb__stat-lbl">Styles</span>
            </div>
            <div class="hb__stat">
                <span class="hb__stat-num">100%</span>
                <span class="hb__stat-lbl">Authentic</span>
            </div>
        </div>
        <a href="<?php echo esc_url($shop_url); ?>" class="hb__cta">
            <span class="hb__cta-label">SHOP NOW</span>
            <span class="hb__cta-arrow">&#8594;</span>
        </a>
    </div>

</section>
