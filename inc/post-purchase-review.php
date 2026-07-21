<?php
/**
 * Post-purchase review popup.
 *
 * The first time a customer's order is marked Delivered (see
 * inc/order-status-delivered.php — a custom status set manually once the
 * customer actually has the product), we pick one purchased product they
 * haven't reviewed yet and remember it against their account. The next
 * time that customer loads any page on the site, a modal prompts them for
 * a star rating + comment, submitted as a real WooCommerce product review
 * (via wp_new_comment, comment_type=review) — not a private form — so it
 * actually counts toward the product's rating.
 *
 * Deliberately not hooked to processing/completed: those fire as soon as
 * payment/fulfillment is done, before the customer has the item in hand —
 * asking "how's your order?" at that point is premature.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'woocommerce_order_status_delivered', 'stanray_queue_post_purchase_review' );

function stanray_queue_post_purchase_review( $order_id ) {
    $order = wc_get_order( $order_id );
    if ( ! $order ) return;

    // Guard: only decide once per order, no matter how many of the hooks above fire.
    if ( $order->get_meta( '_stanray_review_popup_queued' ) ) return;
    $order->update_meta_data( '_stanray_review_popup_queued', 'yes' );
    $order->save();

    // Reviews are tied to an account so we can recognise "the customer is back".
    $user_id = $order->get_customer_id();
    if ( ! $user_id ) return;

    $product_id = stanray_pick_review_product( $order, $user_id );
    if ( ! $product_id ) return;

    update_user_meta( $user_id, '_stanray_pending_review', [
        'order_id'   => $order->get_id(),
        'product_id' => $product_id,
    ] );
}

/**
 * Highest-value line item the customer hasn't already reviewed.
 */
function stanray_pick_review_product( $order, $user_id ) {
    $best_id    = 0;
    $best_total = -1;

    foreach ( $order->get_items() as $item ) {
        // get_product_id() always resolves to the parent product, even for
        // variations — reviews attach to the parent (post_type=product), and
        // WooCommerce's own review-rating hook silently ignores comments on
        // any other post type, so variations must never be used here.
        $product_id = $item->get_product_id();
        $product    = $product_id ? wc_get_product( $product_id ) : null;
        if ( ! $product || ! $product->is_visible() ) continue;

        if ( stanray_user_has_reviewed( $product_id, $user_id ) ) continue;

        $total = (float) $item->get_total();
        if ( $total > $best_total ) {
            $best_total = $total;
            $best_id    = $product_id;
        }
    }

    return $best_id;
}

function stanray_user_has_reviewed( $product_id, $user_id ) {
    $count = get_comments( [
        'post_id' => $product_id,
        'user_id' => $user_id,
        'type'    => 'review',
        'status'  => 'all',
        'count'   => true,
    ] );
    return $count > 0;
}

/**
 * Render the popup markup once per page load for a logged-in customer with
 * a pending review. JS (main.js) opens it on DOMContentLoaded.
 */
add_action( 'wp_footer', 'stanray_render_post_purchase_review_modal' );
function stanray_render_post_purchase_review_modal() {
    if ( is_admin() || ! is_user_logged_in() ) return;

    $user_id = get_current_user_id();
    $pending = get_user_meta( $user_id, '_stanray_pending_review', true );
    if ( empty( $pending['product_id'] ) ) return;

    $product = wc_get_product( $pending['product_id'] );
    if ( ! $product ) {
        delete_user_meta( $user_id, '_stanray_pending_review' );
        return;
    }

    // A review may have landed some other way (e.g. product page) since queueing.
    if ( stanray_user_has_reviewed( $product->get_id(), $user_id ) ) {
        delete_user_meta( $user_id, '_stanray_pending_review' );
        return;
    }

    $image = wp_get_attachment_image_url( $product->get_image_id(), 'stanray-product-grid' );
    ?>
    <div class="review-popup-modal is-open" id="review-popup-modal" role="dialog" aria-modal="true" aria-label="Rate your purchase">
        <div class="review-popup-modal__overlay" id="review-popup-overlay"></div>
        <div class="review-popup-modal__box">
            <button type="button" class="review-popup-modal__close" id="review-popup-close" aria-label="Close">&times;</button>
            <h2>How's your order?</h2>
            <p class="review-popup-modal__sub">Tell us what you think of this piece — it helps other shoppers.</p>

            <div class="review-popup-modal__product">
                <?php if ( $image ) : ?>
                    <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>">
                <?php endif; ?>
                <span><?php echo esc_html( $product->get_name() ); ?></span>
            </div>

            <form id="review-popup-form">
                <div class="review-popup-modal__stars" data-rating="0">
                    <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                        <button type="button" class="review-popup-star" data-value="<?php echo esc_attr( $i ); ?>" aria-label="<?php echo esc_attr( $i ); ?> star">&#9733;</button>
                    <?php endfor; ?>
                </div>
                <textarea name="comment" id="review-popup-comment" rows="4" placeholder="Optional: share a few words about the fit, quality, whatever stood out." maxlength="1000"></textarea>
                <input type="hidden" name="product_id" value="<?php echo esc_attr( $product->get_id() ); ?>">
                <input type="hidden" name="order_id" value="<?php echo esc_attr( $pending['order_id'] ); ?>">
                <p class="review-popup-modal__error" id="review-popup-error" hidden></p>
                <div class="review-popup-modal__actions">
                    <button type="button" class="review-popup-modal__skip" id="review-popup-skip">Maybe later</button>
                    <button type="submit" class="review-popup-modal__submit">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
    <?php
}
