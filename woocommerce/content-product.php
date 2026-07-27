<?php
/**
 * Product Card Template — OVO-style editorial grid card
 * Overrides: woocommerce/templates/content-product.php
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
    return;
}

$product_url   = get_the_permalink();
$product_title = get_the_title();
$is_on_sale    = $product->is_on_sale();
$is_new        = ( strtotime( $product->get_date_created() ) > strtotime( '-30 days' ) );
$discount_pct  = stanray_get_discount_percent( $product );
$colour_count  = stanray_get_colour_count( $product );

/* Wishlist state */
$product_id = $product->get_id();
$wishlist   = eskecy_get_wishlist();
$in_wish    = in_array( $product_id, $wishlist );

/* Grab primary + hover image */
$main_img_id  = $product->get_image_id();
$gallery_ids  = $product->get_gallery_image_ids();
$hover_img_id = ! empty( $gallery_ids ) ? $gallery_ids[0] : 0;
?>
<li <?php wc_product_class( 'ovo-card', $product ); ?>>

    <div class="ovo-card__media">

        <a href="<?php echo esc_url( $product_url ); ?>" class="ovo-card__link" aria-label="<?php echo esc_attr( $product_title ); ?>">

            <?php if ( $main_img_id ) : ?>
                <?php echo wp_get_attachment_image( $main_img_id, 'stanray-product-grid', false, [
                    'class'          => 'ovo-card__img ovo-card__img--main',
                    'loading'        => 'eager',
                    'decoding'       => 'async',
                    'fetchpriority'  => 'high',
                ] ); ?>
            <?php else : ?>
                <img src="<?php echo esc_url( wc_placeholder_img_src( 'stanray-product-grid' ) ); ?>"
                     alt="<?php echo esc_attr( $product_title ); ?>"
                     class="ovo-card__img ovo-card__img--main">
            <?php endif; ?>

            <?php if ( $hover_img_id ) : ?>
                <?php echo wp_get_attachment_image( $hover_img_id, 'stanray-product-grid', false, [
                    'class'   => 'ovo-card__img ovo-card__img--hover',
                    'loading' => 'lazy',
                ] ); ?>
            <?php endif; ?>

            <!-- Hover CTA -->
            <div class="ovo-card__overlay">
                <span class="ovo-card__cta">Quick Shop</span>
            </div>
        </a>

        <!-- Badges -->
        <div class="ovo-card__badges">
            <?php if ( $discount_pct > 0 ) : ?>
                <span class="ovo-badge ovo-badge--discount"><?php echo esc_html( $discount_pct ); ?>% OFF</span>
            <?php elseif ( $is_new ) : ?>
                <span class="ovo-badge ovo-badge--new">New</span>
            <?php endif; ?>
            <?php if ( ! $product->is_in_stock() ) : ?>
                <span class="ovo-badge ovo-badge--sold">Sold Out</span>
            <?php endif; ?>
        </div>

        <!-- Wishlist -->
        <button
            class="ovo-card__wish product-card__wish<?php echo $in_wish ? ' is-wished' : ''; ?>"
            data-product-id="<?php echo esc_attr( $product_id ); ?>"
            data-nonce="<?php echo esc_attr( wp_create_nonce( 'eskecy_wishlist' ) ); ?>"
            aria-label="<?php echo $in_wish ? 'Remove from Wishlist' : 'Save to Wishlist'; ?>"
            title="<?php echo $in_wish ? 'Remove from Wishlist' : 'Save to Wishlist'; ?>"
        ><?php echo $in_wish ? '&#x2665;' : '&#x2661;'; ?></button>

    </div>

    <a href="<?php echo esc_url( $product_url ); ?>" class="ovo-card__info-link">
        <div class="ovo-card__info">
            <h3 class="ovo-card__name"><?php echo esc_html( $product_title ); ?></h3>
            <div class="ovo-card__price">
                <?php echo $product->get_price_html(); ?>
            </div>
            <?php if ( $colour_count > 0 ) : ?>
                <span class="ovo-card__colours"><?php echo esc_html( $colour_count ); ?> Colour<?php echo $colour_count > 1 ? 's' : ''; ?></span>
            <?php endif; ?>
        </div>
    </a>

</li>
