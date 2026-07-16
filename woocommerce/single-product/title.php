<?php
/**
 * Single Product Title — Stanray PDP redesign
 * Category eyebrow + title + inline stock pill, all in one block.
 *
 * Overrides woocommerce/templates/single-product/title.php
 */

defined( 'ABSPATH' ) || exit;

global $product;

$cat_name = '';
$terms    = get_the_terms( $product->get_id(), 'product_cat' );
if ( $terms && ! is_wp_error( $terms ) ) {
    $exclude = get_option( 'default_product_cat' );
    foreach ( $terms as $term ) {
        if ( $term->term_id != $exclude && strtolower( $term->slug ) !== 'uncategorized' ) {
            $cat_name = $term->name;
            break;
        }
    }
}

$in_stock = $product->is_in_stock();
?>
<?php if ( $cat_name ) : ?>
<p class="pdp-eyebrow"><?php echo esc_html( $cat_name ); ?></p>
<?php endif; ?>

<h1 class="product_title entry-title pdp-title">
    <?php the_title(); ?>
    <span class="pdp-stock-pill <?php echo $in_stock ? 'pdp-stock-pill--in' : 'pdp-stock-pill--out'; ?>">
        <?php echo $in_stock ? esc_html__( 'In Stock', 'stanray-custom' ) : esc_html__( 'Out of Stock', 'stanray-custom' ); ?>
    </span>
</h1>
