<?php
/**
 * Single variation cart button — Stanray PDP redesign
 * Adds a "Buy Now" button alongside the native Add to Cart button, reusing
 * the same hidden add-to-cart/product_id/variation_id fields so both submit
 * buttons work off one form.
 *
 * Overrides woocommerce/templates/single-product/add-to-cart/variation-add-to-cart-button.php
 */

defined( 'ABSPATH' ) || exit;

global $product;
?>
<div class="woocommerce-variation-add-to-cart variations_button pdp-atc-row">
	<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

	<?php
	do_action( 'woocommerce_before_add_to_cart_quantity' );

	woocommerce_quantity_input(
		[
			'min_value'   => $product->get_min_purchase_quantity(),
			'max_value'   => $product->get_max_purchase_quantity(),
			'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(),
		]
	);

	do_action( 'woocommerce_after_add_to_cart_quantity' );
	?>

	<button type="submit" class="pdp-btn pdp-btn--cart single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
	<button type="submit" name="buy_now" value="1" class="pdp-btn pdp-btn--buy"><?php esc_html_e( 'Buy Now', 'stanray-custom' ); ?></button>

	<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

	<input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
	<input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
	<input type="hidden" name="variation_id" class="variation_id" value="0" />
</div>
