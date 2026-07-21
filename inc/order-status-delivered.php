<?php
/**
 * Custom "Delivered" order status.
 *
 * WooCommerce's built-in statuses stop at "Completed", which this store
 * reaches as soon as an order is processed/shipped — not once the customer
 * actually has the product in hand. That gap matters for anything that
 * should wait for real receipt (e.g. the post-purchase review prompt in
 * post-purchase-review.php). "Delivered" is a manual status: staff set it
 * on the order edit screen (or bulk-edit on the Orders list) once the
 * customer has confirmed/tracking shows receipt.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function() {
    register_post_status( 'wc-delivered', [
        'label'                     => _x( 'Delivered', 'Order status', 'stanray-custom' ),
        'public'                    => false,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop(
            'Delivered <span class="count">(%s)</span>',
            'Delivered <span class="count">(%s)</span>',
            'stanray-custom'
        ),
    ] );
} );

// Insert "Delivered" right after "Completed" in every status list WooCommerce
// builds from this filter — order edit screen dropdown, Orders list bulk
// actions, and the status filter links above the Orders list.
add_filter( 'wc_order_statuses', function( $order_statuses ) {
    $with_delivered = [];
    foreach ( $order_statuses as $key => $label ) {
        $with_delivered[ $key ] = $label;
        if ( 'wc-completed' === $key ) {
            $with_delivered['wc-delivered'] = _x( 'Delivered', 'Order status', 'stanray-custom' );
        }
    }
    return $with_delivered;
} );

// Moving an order from Completed to Delivered shouldn't make it vanish from
// revenue reports, "paying customer" checks, or downloadable-file access —
// all of which key off this "is it paid" list rather than a single status.
add_filter( 'woocommerce_order_is_paid_statuses', function( $statuses ) {
    $statuses[] = 'delivered';
    return $statuses;
} );

// Admin order list badge colour — mirrors WC's own "completed" green so
// Delivered doesn't render as the unstyled default grey.
add_action( 'admin_head', function() {
    $screen = get_current_screen();
    if ( ! $screen || ! in_array( $screen->id, [ 'edit-shop_order', 'woocommerce_page_wc-orders' ], true ) ) return;
    ?>
    <style>
        .order-status.status-delivered,
        mark.order-status.status-delivered {
            background: #c6e1c6;
            color: #5b841b;
        }
    </style>
    <?php
} );
