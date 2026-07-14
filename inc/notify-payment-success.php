<?php
/**
 * Notify on payment success — email + SMS.
 *
 * Fires once per order, the first time it becomes "paid":
 *  - woocommerce_payment_complete    → instant gateways (eSewa, PayPal, etc.)
 *  - order status → processing/completed → manual gateways (Scan to Pay QR,
 *    Bank Transfer) where an admin confirms payment by hand on the order screen.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'woocommerce_payment_complete', 'stanray_notify_payment_success' );
add_action( 'woocommerce_order_status_processing', 'stanray_notify_payment_success' );
add_action( 'woocommerce_order_status_completed', 'stanray_notify_payment_success' );

function stanray_notify_payment_success( $order_id ) {
    $order = wc_get_order( $order_id );
    if ( ! $order ) return;

    // Guard: only notify once per order, no matter how many of the hooks above fire.
    if ( $order->get_meta( '_stanray_payment_notified' ) ) return;
    $order->update_meta_data( '_stanray_payment_notified', 'yes' );
    $order->save();

    stanray_send_payment_email( $order );
    stanray_send_payment_sms( $order );
}

function stanray_send_payment_email( $order ) {
    $to      = get_option( 'admin_email' );
    $subject = sprintf( 'Payment received — Order #%s', $order->get_order_number() );
    $body    = sprintf(
        "New payment confirmed.\n\nOrder: #%s\nCustomer: %s\nTotal: %s\nPayment method: %s\n\nView order: %s",
        $order->get_order_number(),
        $order->get_formatted_billing_full_name(),
        $order->get_formatted_order_total(),
        $order->get_payment_method_title(),
        admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' )
    );

    wp_mail( $to, $subject, $body );
}

function stanray_send_payment_sms( $order ) {
    $phone = $order->get_billing_phone();
    if ( ! $phone ) return;

    $message = sprintf(
        'Payment received for order #%s (%s). Thank you!',
        $order->get_order_number(),
        $order->get_formatted_order_total()
    );

    // TODO: replace with your SMS provider's API (e.g. Sparrow SMS, Twilio).
    // Example shape for a generic REST SMS API:
    wp_remote_post( 'https://api.your-sms-provider.com/send', [
        'timeout' => 10,
        'body'    => [
            'token' => 'YOUR_API_KEY',
            'to'    => $phone,
            'text'  => $message,
        ],
    ] );
}
