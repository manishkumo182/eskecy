<?php
/**
 * Checkout Form
 *
 * Overrides WooCommerce core's checkout/form-checkout.php. The only change
 * from core (WC 9.4.0) is the guest-block condition below: core only blocks
 * guests when BOTH guest checkout AND account registration are disabled —
 * since this theme keeps registration enabled (needed for the account-required
 * login modal's "Create Account" panel), core's own condition would never
 * trigger and guests would reach the billing form anyway. This theme requires
 * an account for checkout regardless of the registration setting, so the
 * `! $checkout->is_registration_enabled()` clause is dropped.
 *
 * See inc/wishlist-login-modal.php for the modal this message links to —
 * it auto-opens on this page for guests.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// Account required to checkout — see file header for how this differs from core.
// wp_kses_post() (not core's esc_html()) so the login-modal reopen link in
// inc/wishlist-login-modal.php's woocommerce_checkout_must_be_logged_in_message
// filter survives as a real <a>, not escaped-to-text.
if ( $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo wp_kses_post( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

	<?php if ( $checkout->get_checkout_fields() ) : ?>

		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

		<div class="col2-set" id="customer_details">
			<div class="col-1">
				<?php do_action( 'woocommerce_checkout_billing' ); ?>
			</div>

			<div class="col-2">
				<?php do_action( 'woocommerce_checkout_shipping' ); ?>
			</div>
		</div>

		<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

	<?php endif; ?>

	<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

	<h3 id="order_review_heading"><?php esc_html_e( 'Your order', 'woocommerce' ); ?></h3>

	<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

	<div id="order_review" class="woocommerce-checkout-review-order">
		<?php do_action( 'woocommerce_checkout_order_review' ); ?>
	</div>

	<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
