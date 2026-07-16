<?php
/**
 * Simple product add to cart — Stanray PDP redesign
 * Quantity stepper + Add To Cart + Buy Now buttons on one row.
 *
 * Overrides woocommerce/templates/single-product/add-to-cart/simple.php
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product->is_purchasable() ) {
	return;
}

echo wc_get_stock_html( $product );

if ( $product->is_in_stock() ) : ?>

	<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

	<form class="cart pdp-atc-form" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype="multipart/form-data">
		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

		<div class="pdp-atc-row">
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

			<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>">
			<button type="submit" class="pdp-btn pdp-btn--cart single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
			<button type="submit" name="buy_now" value="1" class="pdp-btn pdp-btn--buy"><?php esc_html_e( 'Buy Now', 'stanray-custom' ); ?></button>
		</div>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
	</form>

	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php endif; ?>
