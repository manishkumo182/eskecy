<?php
/**
 * Customer self-service order cancellation.
 *
 * WooCommerce's own cancel-order flow only works while an order is still
 * "pending"/"failed" — once payment is confirmed (processing/on-hold) the
 * built-in cancel link disappears entirely. This store takes COD and eSewa,
 * so customers routinely want to cancel after that point but before an order
 * ships. This adds a Cancel action (Orders list + single order page) for
 * exactly that window, with a reason and an admin notification.
 *
 * Cancelling moves the order to WooCommerce's own native "cancelled" status,
 * which inc/points.php already listens for to reverse any points that order
 * earned or spent — no extra wiring needed for that part.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ─── ELIGIBILITY ───────────────────────────────────────────────────────── */

function stanray_order_can_be_cancelled_by_customer( $order ) {
    return $order instanceof WC_Order && $order->has_status( [ 'processing', 'on-hold' ] );
}

/* ─── SHARED MODAL MARKUP ───────────────────────────────────────────────── */
// Same .confirm-modal structure as the wishlist "Remove All" modal (see
// eskecy_wishlist shortcode in inc/woocommerce.php), plus a reason textarea.
// One instance per page is enough — the Orders list has multiple trigger
// buttons (one per eligible row) sharing this single modal, same convention
// as the wishlist's one shared "Remove All" modal for its whole grid.

function stanray_render_cancel_order_modal() {
    ?>
    <div class="confirm-modal" id="cancel-order-confirm" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Cancel order', 'stanray-custom' ); ?>">
        <div class="confirm-modal__overlay"></div>
        <div class="confirm-modal__box">
            <p class="confirm-modal__message"><?php esc_html_e( 'Cancel this order? This cannot be undone.', 'stanray-custom' ); ?></p>
            <textarea class="confirm-modal__reason" placeholder="<?php esc_attr_e( 'Reason (optional)', 'stanray-custom' ); ?>"></textarea>
            <div class="confirm-modal__actions">
                <button type="button" class="btn btn--outline confirm-modal__cancel"><?php esc_html_e( 'Keep Order', 'stanray-custom' ); ?></button>
                <button type="button" class="btn btn--primary confirm-modal__confirm"><?php esc_html_e( 'Cancel Order', 'stanray-custom' ); ?></button>
            </div>
        </div>
    </div>
    <?php
}

/* ─── VIEW ORDER PAGE: trigger button + modal ──────────────────────────── */
// woocommerce_view_order fires at the very end of myaccount/view-order.php
// (core template) — woocommerce_order_details_table is the only default
// callback on it, at priority 10, so 20 lands this right after the order
// details table without needing a full template override.

add_action( 'woocommerce_view_order', function( $order_id ) {
    $order = wc_get_order( $order_id );
    if ( ! stanray_order_can_be_cancelled_by_customer( $order ) ) return;
    ?>
    <p class="stanray-cancel-order-wrap">
        <button type="button" class="btn btn--outline cancel-order-trigger"
            data-order-id="<?php echo esc_attr( $order->get_id() ); ?>"
            data-nonce="<?php echo esc_attr( wp_create_nonce( 'eskecy_cancel_order' ) ); ?>">
            <?php esc_html_e( 'Cancel Order', 'stanray-custom' ); ?>
        </button>
    </p>
    <?php
    stanray_render_cancel_order_modal();
}, 20 );

/* ─── AJAX ──────────────────────────────────────────────────────────────── */
// nopriv registered only so a logged-out visitor gets the structured
// login_required error below instead of WordPress's generic "invalid action"
// failure — same reasoning as the wishlist/points AJAX handlers.

add_action( 'wp_ajax_eskecy_cancel_order', 'eskecy_cancel_order_handler' );
add_action( 'wp_ajax_nopriv_eskecy_cancel_order', 'eskecy_cancel_order_handler' );

function eskecy_cancel_order_handler() {
    check_ajax_referer( 'eskecy_cancel_order', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [
            'code'    => 'login_required',
            'message' => 'Please log in to manage your orders.',
        ] );
    }

    $order = wc_get_order( absint( $_POST['order_id'] ?? 0 ) );

    // Same error for "doesn't exist" and "isn't yours" — don't leak which.
    if ( ! $order || (int) $order->get_customer_id() !== get_current_user_id() ) {
        wp_send_json_error( [ 'message' => 'Order not found.' ] );
    }

    if ( ! stanray_order_can_be_cancelled_by_customer( $order ) ) {
        wp_send_json_error( [ 'message' => 'This order can no longer be cancelled.' ] );
    }

    $reason = sanitize_textarea_field( $_POST['reason'] ?? '' );

    if ( $reason ) {
        $order->update_meta_data( '_eskecy_cancel_reason', $reason );
    }
    $order->add_order_note( $reason
        ? sprintf( 'Order cancelled by customer. Reason: %s', $reason )
        : 'Order cancelled by customer.'
    );
    $order->save();

    // Fires woocommerce_order_status_cancelled, which inc/points.php already
    // hooks to reverse any points this order earned or spent.
    $order->update_status( 'cancelled' );

    stanray_notify_order_cancelled_by_customer( $order, $reason );

    wp_send_json_success();
}

/* ─── ADMIN EMAIL ───────────────────────────────────────────────────────── */
// Mirrors stanray_send_payment_email()'s style exactly (inc/notify-payment-success.php).

function stanray_notify_order_cancelled_by_customer( $order, $reason ) {
    $to      = get_option( 'admin_email' );
    $subject = sprintf( 'Order cancelled by customer — Order #%s', $order->get_order_number() );
    $body    = sprintf(
        "A customer cancelled their order.\n\nOrder: #%s\nCustomer: %s\nTotal: %s\nReason: %s\n\nView order: %s",
        $order->get_order_number(),
        $order->get_formatted_billing_full_name(),
        $order->get_formatted_order_total(),
        $reason ?: '(none given)',
        admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' )
    );

    wp_mail( $to, $subject, $body );
}
