<?php
/**
 * Single Product Meta — Stanray PDP redesign
 * SKU + Tags (no Categories row) + Share icons.
 *
 * Overrides woocommerce/templates/single-product/meta.php
 */

defined( 'ABSPATH' ) || exit;

global $product;

$share_url   = rawurlencode( get_permalink( $product->get_id() ) );
$share_title = rawurlencode( $product->get_name() );
?>
<div class="product_meta pdp-meta">

    <?php do_action( 'woocommerce_product_meta_start' ); ?>

    <?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>
        <span class="sku_wrapper"><?php esc_html_e( 'SKU:', 'woocommerce' ); ?> <span class="sku"><?php echo ( $sku = $product->get_sku() ) ? esc_html( $sku ) : esc_html__( 'N/A', 'woocommerce' ); ?></span></span>
    <?php endif; ?>

    <?php echo wc_get_product_tag_list( $product->get_id(), ', ', '<span class="tagged_as">' . _n( 'Tags:', 'Tags:', count( $product->get_tag_ids() ), 'woocommerce' ) . ' ', '</span>' ); ?>

    <span class="pdp-share">
        <span class="pdp-share__label"><?php esc_html_e( 'Share:', 'stanray-custom' ); ?></span>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url; ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Share on Facebook', 'stanray-custom' ); ?>" class="pdp-share__icon">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M22 12a10 10 0 1 0-11.56 9.87v-6.98H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.78-3.89 1.1 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.98A10 10 0 0 0 22 12Z"/></svg>
        </a>
        <a href="https://twitter.com/intent/tweet?url=<?php echo $share_url; ?>&text=<?php echo $share_title; ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Share on X', 'stanray-custom' ); ?>" class="pdp-share__icon">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M18.9 2H22l-7.6 8.7L23.3 22h-6.9l-5.4-6.6L4.8 22H1.7l8.1-9.3L1 2h7l4.9 6.1L18.9 2Zm-1.2 18h1.7L7.4 4H5.5l12.2 16Z"/></svg>
        </a>
        <a href="https://pinterest.com/pin/create/button/?url=<?php echo $share_url; ?>&description=<?php echo $share_title; ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Share on Pinterest', 'stanray-custom' ); ?>" class="pdp-share__icon">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M12.02 2C6.5 2 2 6.35 2 11.73c0 4.09 2.5 7.59 6.06 9.03-.08-.77-.16-1.94.03-2.78.18-.76 1.16-4.83 1.16-4.83s-.3-.59-.3-1.46c0-1.37.8-2.39 1.8-2.39.85 0 1.26.63 1.26 1.39 0 .85-.55 2.11-.83 3.29-.24 1 .5 1.81 1.48 1.81 1.78 0 3.15-1.87 3.15-4.56 0-2.38-1.72-4.05-4.17-4.05-2.84 0-4.51 2.11-4.51 4.29 0 .85.33 1.76.74 2.25.08.1.09.18.07.28-.08.31-.25 1-.28 1.14-.05.18-.15.22-.35.13-1.3-.6-2.11-2.48-2.11-4 0-3.25 2.37-6.24 6.84-6.24 3.59 0 6.38 2.55 6.38 5.97 0 3.56-2.25 6.42-5.36 6.42-1.05 0-2.03-.54-2.37-1.19l-.64 2.45c-.23.9-.86 2.02-1.28 2.7A10 10 0 1 0 12.02 2Z"/></svg>
        </a>
    </span>

    <?php do_action( 'woocommerce_product_meta_end' ); ?>

</div>
