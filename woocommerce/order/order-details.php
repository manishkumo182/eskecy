<?php
/**
 * Order details — Stanray override
 *
 * Identical to WooCommerce core (10.9.0) except the totals section: core
 * renders Subtotal/Shipping/Status/Total/Payment method/Note/Actions as a
 * <table> <tfoot>, which only has two ways to lay out a label + right-aligned
 * value — table auto-layout (lets the label column shrink and wrap
 * mid-phrase at narrow widths) or `display:flex` on a <tr> (breaks out of
 * the table's border-collapse model — segmented/broken row borders). Neither
 * is acceptable, so the totals are plain divs (.stanray-order-totals) below
 * the items table instead. The items table itself (and every hook/filter
 * around it — downloads, purchase notes, refunded-qty display, order-item
 * meta) is untouched so nothing about how items render changes.
 *
 * Overrides woocommerce/templates/order/order-details.php
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.9.0
 *
 * @var bool $show_downloads Controls whether the downloads table should be rendered.
 */

defined( 'ABSPATH' ) || exit;

$order = wc_get_order( $order_id );

if ( ! $order ) {
	return;
}

$order_items        = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
$show_purchase_note = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
$downloads          = $order->get_downloadable_items();
$actions            = array_filter(
	wc_get_account_orders_actions( $order ),
	function ( $key ) {
		return 'view' !== $key;
	},
	ARRAY_FILTER_USE_KEY
);

// We make sure the order belongs to the user. This will also be true if the user is a guest, and the order belongs to a guest (userID === 0).
$show_customer_details = $order->get_user_id() === get_current_user_id();

if ( $show_downloads ) {
	wc_get_template(
		'order/order-downloads.php',
		array(
			'downloads'  => $downloads,
			'show_title' => true,
		)
	);
}
?>
<section class="woocommerce-order-details">
	<?php do_action( 'woocommerce_order_details_before_order_table', $order ); ?>

	<h2 class="woocommerce-order-details__title"><?php esc_html_e( 'Order details', 'woocommerce' ); ?></h2>

	<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">

		<thead>
			<tr>
				<th class="woocommerce-table__product-name product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
				<th class="woocommerce-table__product-table product-total"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php
			do_action( 'woocommerce_order_details_before_order_table_items', $order );

			foreach ( $order_items as $item_id => $item ) {
				$product = $item->get_product();

				wc_get_template(
					'order/order-details-item.php',
					array(
						'order'              => $order,
						'item_id'            => $item_id,
						'item'               => $item,
						'show_purchase_note' => $show_purchase_note,
						'purchase_note'      => $product ? $product->get_purchase_note() : '',
						'product'            => $product,
					)
				);
			}

			do_action( 'woocommerce_order_details_after_order_table_items', $order );
			?>
		</tbody>

	</table>

	<div class="stanray-order-totals">
		<?php
		foreach ( $order->get_order_item_totals() as $key => $total ) {
			$is_total = 'order_total' === $key;
			?>
			<div class="stanray-order-totals__row<?php echo $is_total ? ' stanray-order-totals__row--total' : ''; ?>">
				<span class="stanray-order-totals__label"><?php echo esc_html( $total['label'] ); ?></span>
				<span class="stanray-order-totals__value"><?php echo wp_kses_post( $total['value'] ); ?></span>
			</div>
			<?php
		}
		?>
		<?php if ( $order->get_customer_note() ) : ?>
			<div class="stanray-order-totals__row">
				<span class="stanray-order-totals__label"><?php esc_html_e( 'Note:', 'woocommerce' ); ?></span>
				<span class="stanray-order-totals__value">
				<?php
				$customer_note = wc_wptexturize_order_note( $order->get_customer_note() );
				echo wp_kses( nl2br( $customer_note ), array( 'br' => array() ) );
				?>
				</span>
			</div>
		<?php endif; ?>
		<?php if ( ! empty( $actions ) ) : ?>
			<div class="stanray-order-totals__row stanray-order-totals__row--actions">
				<span class="stanray-order-totals__label order-actions--heading"><?php esc_html_e( 'Actions', 'woocommerce' ); ?>:</span>
				<span class="stanray-order-totals__value">
					<?php
					$wp_button_class = wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '';
					foreach ( $actions as $key => $action ) {
						if ( empty( $action['aria-label'] ) ) {
							/* translators: %1$s Action name, %2$s Order number. */
							$action_aria_label = sprintf( __( '%1$s order number %2$s', 'woocommerce' ), $action['name'], $order->get_order_number() );
						} else {
							$action_aria_label = $action['aria-label'];
						}
						echo '<a href="' . esc_url( $action['url'] ) . '" class="woocommerce-button' . esc_attr( $wp_button_class ) . ' button order-actions-button ' . sanitize_html_class( $key ) . '" aria-label="' . esc_attr( $action_aria_label ) . '">' . esc_html( $action['name'] ) . '</a>';
						unset( $action_aria_label );
					}
					?>
				</span>
			</div>
		<?php endif; ?>
	</div>

	<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>
</section>

<?php
/**
 * Action hook fired after the order details.
 *
 * @since 4.4.0
 * @param WC_Order $order Order data.
 */
do_action( 'woocommerce_after_order_details', $order );

if ( $show_customer_details ) {
	wc_get_template( 'order/order-details-customer.php', array( 'order' => $order ) );
}
