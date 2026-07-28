<?php
/**
 * Single Product Image — Stanray PDP redesign
 * Large image with prev/next arrows + a thumbnail strip below.
 *
 * Overrides woocommerce/templates/single-product/product-image.php
 */

defined( 'ABSPATH' ) || exit;

global $product;

$post_thumbnail_id = $product->get_image_id();
$gallery_ids        = $product->get_gallery_image_ids();
$all_ids            = $post_thumbnail_id ? array_merge( [ $post_thumbnail_id ], $gallery_ids ) : $gallery_ids;

if ( empty( $all_ids ) ) {
    $all_ids = [ 0 ]; // placeholder
}

$product_id = $product->get_id();
// Single product pages are cacheable (not one of WC's auto-excluded My
// Account/Cart/Checkout pages), so this always renders "not wished" —
// baking in this request's session state would leak into the cached HTML
// for every other visitor. main.js hydrates the real state client-side
// after load (see hydrateWishlistIcons).
?>
<div class="pdp-gallery" data-count="<?php echo esc_attr( count( $all_ids ) ); ?>">

    <div class="pdp-gallery__main">
        <button
            class="product-card__wish pdp-gallery__wish"
            data-product-id="<?php echo esc_attr( $product_id ); ?>"
            data-nonce="<?php echo esc_attr( wp_create_nonce( 'eskecy_wishlist' ) ); ?>"
            aria-label="Save to Wishlist"
            title="Save to Wishlist"
        ><?php echo eskecy_wishlist_heart_svg( false ); ?></button>

        <?php if ( count( $all_ids ) > 1 ) : ?>
        <button type="button" class="pdp-gallery__arrow pdp-gallery__arrow--prev" aria-label="<?php esc_attr_e( 'Previous image', 'stanray-custom' ); ?>">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
        </button>
        <?php endif; ?>

        <div class="pdp-gallery__slides">
            <?php foreach ( $all_ids as $i => $img_id ) :
                if ( $img_id ) {
                    $src  = wp_get_attachment_image_url( $img_id, 'woocommerce_single' );
                    $full = wp_get_attachment_image_url( $img_id, 'full' );
                    $alt  = get_post_meta( $img_id, '_wp_attachment_image_alt', true ) ?: get_the_title();
                } else {
                    $src  = wc_placeholder_img_src( 'woocommerce_single' );
                    $full = $src;
                    $alt  = esc_html__( 'Awaiting product image', 'woocommerce' );
                }
            ?>
            <img class="pdp-gallery__slide<?php echo 0 === $i ? ' is-active' : ''; ?>"
                 src="<?php echo esc_url( $src ); ?>"
                 data-full="<?php echo esc_url( $full ); ?>"
                 alt="<?php echo esc_attr( $alt ); ?>"
                 loading="<?php echo 0 === $i ? 'eager' : 'lazy'; ?>">
            <?php endforeach; ?>
        </div>

        <?php if ( count( $all_ids ) > 1 ) : ?>
        <button type="button" class="pdp-gallery__arrow pdp-gallery__arrow--next" aria-label="<?php esc_attr_e( 'Next image', 'stanray-custom' ); ?>">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </button>
        <?php endif; ?>
    </div>

    <?php if ( count( $all_ids ) > 1 ) : ?>
    <div class="pdp-gallery__thumbs">
        <?php foreach ( $all_ids as $i => $img_id ) :
            $thumb = $img_id ? wp_get_attachment_image_url( $img_id, 'woocommerce_gallery_thumbnail' ) : wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' );
        ?>
        <button type="button" class="pdp-gallery__thumb<?php echo 0 === $i ? ' is-active' : ''; ?>" data-index="<?php echo esc_attr( $i ); ?>" aria-label="<?php printf( esc_attr__( 'Show image %d', 'stanray-custom' ), $i + 1 ); ?>">
            <img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy">
        </button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>
