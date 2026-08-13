<?php
/**
 * Template Part: Editorial Hero Banner
 *
 * Editable from Homepage Sections → Hero Banner in the wp-admin
 * sidebar. Falls back to the original hardcoded defaults if nothing
 * has been saved yet.
 */

$hb_manual_product_id = absint( get_option( 'stanray_hb_product', 0 ) );
$hero_product = null;
if ( $hb_manual_product_id ) {
    $candidate = wc_get_product( $hb_manual_product_id );
    if ( $candidate && $candidate->get_status() === 'publish' ) {
        $hero_product = $candidate;
    }
}
if ( ! $hero_product ) {
    $hero_products = wc_get_products(['status' => 'publish', 'limit' => 1, 'featured' => true, 'orderby' => 'date', 'order' => 'DESC']);
    if ( empty($hero_products) ) {
        $hero_products = wc_get_products(['status' => 'publish', 'limit' => 1, 'orderby' => 'date', 'order' => 'DESC']);
    }
    $hero_product = ! empty($hero_products) ? $hero_products[0] : null;
}
$hero_img     = $hero_product ? wp_get_attachment_image_url($hero_product->get_image_id(), 'large') : '';
$hero_name    = $hero_product ? $hero_product->get_name()      : '';
$hero_url     = $hero_product ? $hero_product->get_permalink() : home_url('/shop');
$shop_url     = class_exists('WooCommerce') ? wc_get_page_permalink('shop') : home_url('/shop');

$hb_watermark      = get_option( 'stanray_hb_watermark', 'ESKECY' );
$hb_badge          = get_option( 'stanray_hb_badge', 'SK.26' );
$hb_badge_image_id  = absint( get_option( 'stanray_hb_badge_image', 0 ) );
$hb_badge_image_src = $hb_badge_image_id ? wp_get_attachment_image_src( $hb_badge_image_id, 'medium' ) : false;
$hb_badge_image     = $hb_badge_image_src ? $hb_badge_image_src[0] : '';
$hb_badge_image_ar  = $hb_badge_image_src ? $hb_badge_image_src[1] . ' / ' . $hb_badge_image_src[2] : '1 / 1';
$hb_spec_tl_key = get_option( 'stanray_hb_spec_tl_key', 'Collection' );
$hb_spec_tl_val = get_option( 'stanray_hb_spec_tl_val', 'Ultra Premium' );
$hb_spec_tr_key = get_option( 'stanray_hb_spec_tr_key', 'Shipping' );
$hb_spec_tr_val = get_option( 'stanray_hb_spec_tr_val', 'Free Delivery' );
$hb_spec_ml_key = get_option( 'stanray_hb_spec_ml_key', 'Design' );
$hb_spec_ml_val = get_option( 'stanray_hb_spec_ml_val', 'Modern Fit' );
$hb_spec_mr_key = get_option( 'stanray_hb_spec_mr_key', 'Quality' );
$hb_spec_mr_val = get_option( 'stanray_hb_spec_mr_val', 'Graphene Fabric' );
$hb_headline_1  = get_option( 'stanray_hb_headline_1', 'Wear the Look.' );
$hb_headline_2  = get_option( 'stanray_hb_headline_2', 'Own the Style.' );
$hb_desc        = get_option( 'stanray_hb_desc', 'Crafted for the elite. Eskecy streetwear combines premium fabric with modern fit and unmatched street culture.' );
$hb_stat1_num   = get_option( 'stanray_hb_stat1_num', '50+' );
$hb_stat1_lbl   = get_option( 'stanray_hb_stat1_lbl', 'Styles' );
$hb_stat2_num   = get_option( 'stanray_hb_stat2_num', '100%' );
$hb_stat2_lbl   = get_option( 'stanray_hb_stat2_lbl', 'Authentic' );
$hb_cta_text    = get_option( 'stanray_hb_cta_text', 'SHOP NOW' );
$hb_cta_link    = get_option( 'stanray_hb_cta_link', '' ) ?: $shop_url;
?>

<section class="hb" aria-label="Hero Banner">

    <!-- Giant watermark word — intentionally overflows both sides -->
    <div class="hb__wm" aria-hidden="true"><?php echo esc_html( $hb_watermark ); ?></div>

    <!-- Transparent card with badge (text or image) and double curve inside -->
    <div class="hb__card-group" aria-hidden="true">
        <div class="hb__card">
            <div class="hb__card-footer">
                <?php if ( $hb_badge_image ) : ?>
                    <span class="hb__card-badge-image" style="--hb-badge-mask:url('<?php echo esc_url( $hb_badge_image ); ?>'); aspect-ratio:<?php echo esc_attr( $hb_badge_image_ar ); ?>;"></span>
                <?php else : ?>
                    <span class="hb__card-badge"> <?php echo esc_html( $hb_badge ); ?></span>
                <?php endif; ?>
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
        <span class="hb__spec-key"><?php echo esc_html( $hb_spec_tl_key ); ?></span>
        <span class="hb__spec-val"><?php echo esc_html( $hb_spec_tl_val ); ?></span>
    </div>

    <!-- Spec label: top-right -->
    <div class="hb__spec hb__spec--tr">
        <span class="hb__spec-key"><?php echo esc_html( $hb_spec_tr_key ); ?></span>
        <span class="hb__spec-val"><?php echo esc_html( $hb_spec_tr_val ); ?></span>
    </div>

    <!-- Spec label: mid-left (with red accent line) -->
    <div class="hb__spec hb__spec--ml hb__spec--accent">
        <span class="hb__spec-key"><?php echo esc_html( $hb_spec_ml_key ); ?></span>
        <span class="hb__spec-val"><?php echo esc_html( $hb_spec_ml_val ); ?></span>
    </div>

    <!-- Spec label: mid-right -->
    <div class="hb__spec hb__spec--mr">
        <span class="hb__spec-key"><?php echo esc_html( $hb_spec_mr_key ); ?></span>
        <span class="hb__spec-val"><?php echo esc_html( $hb_spec_mr_val ); ?></span>
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
            <span class="hb__line--black"><?php echo esc_html( $hb_headline_1 ); ?></span>
            <span class="hb__line--red"><?php echo esc_html( $hb_headline_2 ); ?></span>
        </h1>
        <p class="hb__desc"><?php echo esc_html( $hb_desc ); ?></p>
    </div>

    <!-- Bottom-right: stats + CTA circle -->
    <div class="hb__br">
        <div class="hb__stats">
            <div class="hb__stat">
                <span class="hb__stat-num"><?php echo esc_html( $hb_stat1_num ); ?></span>
                <span class="hb__stat-lbl"><?php echo esc_html( $hb_stat1_lbl ); ?></span>
            </div>
            <div class="hb__stat">
                <span class="hb__stat-num"><?php echo esc_html( $hb_stat2_num ); ?></span>
                <span class="hb__stat-lbl"><?php echo esc_html( $hb_stat2_lbl ); ?></span>
            </div>
        </div>
        <a href="<?php echo esc_url($hb_cta_link); ?>" class="hb__cta">
            <span class="hb__cta-label"><?php echo esc_html( $hb_cta_text ); ?></span>
            <span class="hb__cta-arrow">&#8594;</span>
        </a>
    </div>

</section>
