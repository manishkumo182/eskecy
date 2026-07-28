<?php
/**
 * Order Customer Details — Stanray override
 *
 * Core prints the address as one unlabeled mailing-address blob (name,
 * street, city/postcode, country all run together with no indication of
 * which line is which) plus bare phone/email lines. This lists every field
 * — Name, Address, City, Postcode, Province, Country, Phone, Email — each
 * with its own label, skipping whichever are empty (this store's checkout
 * no longer collects street address/province, but older orders placed
 * before that change still have them, so those still show up labeled too).
 *
 * Overrides woocommerce/templates/order/order-details-customer.php
 */

defined( 'ABSPATH' ) || exit;

$show_shipping = ! wc_ship_to_billing_address_only() && $order->needs_shipping_address();
?>
<section class="woocommerce-customer-details">

	<?php if ( $show_shipping ) : ?>

	<section class="woocommerce-columns woocommerce-columns--2 woocommerce-columns--addresses col2-set addresses">
		<div class="woocommerce-column woocommerce-column--1 woocommerce-column--billing-address col-1">

	<?php endif; ?>

	<h3 class="woocommerce-column__title"><?php esc_html_e( 'Billing address', 'woocommerce' ); ?></h3>

	<address>
		<?php foreach ( stanray_order_address_lines( $order, 'billing' ) as $label => $value ) : ?>
			<p class="woocommerce-customer-details--field"><strong><?php echo esc_html( $label ); ?>:</strong> <?php echo esc_html( $value ); ?></p>
		<?php endforeach; ?>

		<?php
			/**
			 * Action hook fired after an address in the order customer details.
			 *
			 * @since 8.7.0
			 * @param string $address_type Type of address (billing or shipping).
			 * @param WC_Order $order Order object.
			 */
			do_action( 'woocommerce_order_details_after_customer_address', 'billing', $order );
		?>
	</address>

	<?php if ( $show_shipping ) : ?>

		</div><!-- /.col-1 -->

		<div class="woocommerce-column woocommerce-column--2 woocommerce-column--shipping-address col-2">
			<h3 class="woocommerce-column__title"><?php esc_html_e( 'Shipping address', 'woocommerce' ); ?></h3>
			<address>
				<?php foreach ( stanray_order_address_lines( $order, 'shipping' ) as $label => $value ) : ?>
					<p class="woocommerce-customer-details--field"><strong><?php echo esc_html( $label ); ?>:</strong> <?php echo esc_html( $value ); ?></p>
				<?php endforeach; ?>

				<?php
					/**
					 * Action hook fired after an address in the order customer details.
					 *
					 * @since 8.7.0
					 * @param string $address_type Type of address (billing or shipping).
					 * @param WC_Order $order Order object.
					 */
					do_action( 'woocommerce_order_details_after_customer_address', 'shipping', $order );
				?>
			</address>
		</div><!-- /.col-2 -->

	</section><!-- /.col2-set -->

	<?php endif; ?>

	<?php do_action( 'woocommerce_order_details_after_customer_details', $order ); ?>

</section>
