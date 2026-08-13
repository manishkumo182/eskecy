<?php
/**
 * Eskecy Points — customer loyalty program.
 *
 * Earning: customers earn points on the base-currency order total once an
 * order reaches "Completed" (payment confirmed) — same trigger point as the
 * payment-success notification in notify-payment-success.php. Order total is
 * used rather than a display-converted amount because the WBW currency
 * switcher forces checkout back to the store's base currency by default (see
 * modules/currency/mod.php getCurrentCurrency()), so `get_total()` is stable;
 * the currency check below is a defensive guard in case that ever changes.
 *
 * Redemption: a customer can redeem points on the Cart page for a discount,
 * applied as a negative cart fee (no coupon/fee mechanism existed in the
 * theme before this, so there's nothing to conflict with). The chosen point
 * amount lives in the WC session while shopping and is only actually debited
 * from their balance once an order is placed, so an abandoned cart never
 * costs them points.
 *
 * Storage: user meta, same convention as the wishlist's _eskecy_wishlist
 * (see WISHLIST section in woocommerce.php) — a balance int plus a capped
 * history log, no custom table needed at this store's scale.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ─── SETTINGS HELPERS ──────────────────────────────────────────────────── */

function stanray_points_enabled() {
    return '1' === get_option( 'stanray_points_enabled', '1' );
}

/**
 * Discount value for a given number of points against a subtotal, capped so
 * points can never cover more than stanray_points_max_redeem_percent of the
 * order (otherwise a large enough balance could zero out an order entirely).
 */
function stanray_points_calc_discount( $points, $subtotal ) {
    $rate         = (float) get_option( 'stanray_points_redeem_rate', 0.5 );
    $discount     = $points * $rate;
    $max_percent  = (float) get_option( 'stanray_points_max_redeem_percent', 50 );
    $max_discount = $subtotal * ( $max_percent / 100 );
    return round( min( $discount, max( 0, $max_discount ) ), 2 );
}

/* ─── STORAGE ───────────────────────────────────────────────────────────── */

function stanray_points_get_balance( $user_id = 0 ) {
    $user_id = $user_id ?: get_current_user_id();
    if ( ! $user_id ) return 0;
    return (int) get_user_meta( $user_id, '_eskecy_points_balance', true );
}

function stanray_points_get_history( $user_id = 0 ) {
    $user_id = $user_id ?: get_current_user_id();
    if ( ! $user_id ) return [];
    $history = get_user_meta( $user_id, '_eskecy_points_history', true );
    return is_array( $history ) ? $history : [];
}

// Newest first, capped so the meta row can't grow unbounded for long-time customers.
function stanray_points_add_history_entry( $user_id, $entry ) {
    $history = stanray_points_get_history( $user_id );
    array_unshift( $history, $entry );
    update_user_meta( $user_id, '_eskecy_points_history', array_slice( $history, 0, 200 ) );
}

/* ─── EARNING ───────────────────────────────────────────────────────────── */

add_action( 'woocommerce_order_status_completed', 'stanray_award_points_for_order' );

function stanray_award_points_for_order( $order_id ) {
    if ( ! stanray_points_enabled() ) return;

    $order = wc_get_order( $order_id );
    if ( ! $order ) return;

    // Guard: only award once per order, no matter how many times Completed fires.
    if ( $order->get_meta( '_eskecy_points_awarded' ) !== '' ) return;

    $user_id = $order->get_user_id();
    if ( ! $user_id ) return; // guest checkout — no account to credit

    $base_currency = get_option( 'woocommerce_currency' );
    if ( $order->get_currency() !== $base_currency ) {
        // Shouldn't happen (see file header), but don't silently mis-price points if it does.
        error_log( sprintf(
            'Eskecy Points: order #%d currency (%s) does not match store base currency (%s) — points not awarded.',
            $order_id, $order->get_currency(), $base_currency
        ) );
        return;
    }

    $divisor = (float) get_option( 'stanray_points_earn_divisor', 100 );
    if ( $divisor <= 0 ) return;

    $points = (int) floor( $order->get_total() / $divisor );

    $order->update_meta_data( '_eskecy_points_awarded', $points );
    $order->save();

    if ( $points <= 0 ) return;

    update_user_meta( $user_id, '_eskecy_points_balance', stanray_points_get_balance( $user_id ) + $points );
    stanray_points_add_history_entry( $user_id, [
        'type'     => 'earned',
        'points'   => $points,
        'order_id' => $order_id,
        'date'     => current_time( 'mysql' ),
    ] );
}

/* ─── REVERSAL (cancel / refund) ────────────────────────────────────────── */

add_action( 'woocommerce_order_status_cancelled', 'stanray_reverse_points_for_order' );
add_action( 'woocommerce_order_status_refunded', 'stanray_reverse_points_for_order' );

function stanray_reverse_points_for_order( $order_id ) {
    $order = wc_get_order( $order_id );
    if ( ! $order ) return;

    $user_id = $order->get_user_id();
    if ( ! $user_id ) return;

    $save = false;

    // Claw back anything earned on this order. Deleting the meta after acts as
    // the double-fire guard, same idea as the _eskecy_points_awarded set-once check above.
    $awarded = (int) $order->get_meta( '_eskecy_points_awarded' );
    if ( $awarded > 0 ) {
        update_user_meta( $user_id, '_eskecy_points_balance', max( 0, stanray_points_get_balance( $user_id ) - $awarded ) );
        stanray_points_add_history_entry( $user_id, [
            'type' => 'reversed', 'points' => $awarded, 'order_id' => $order_id, 'date' => current_time( 'mysql' ),
        ] );
        $order->delete_meta_data( '_eskecy_points_awarded' );
        $save = true;
    }

    // Restore anything this order spent, once.
    $redeemed = (int) $order->get_meta( '_eskecy_points_redeemed' );
    if ( $redeemed > 0 && '' === $order->get_meta( '_eskecy_points_redeemed_restored' ) ) {
        update_user_meta( $user_id, '_eskecy_points_balance', stanray_points_get_balance( $user_id ) + $redeemed );
        stanray_points_add_history_entry( $user_id, [
            'type' => 'redeem_reversed', 'points' => $redeemed, 'order_id' => $order_id, 'date' => current_time( 'mysql' ),
        ] );
        $order->update_meta_data( '_eskecy_points_redeemed_restored', 'yes' );
        $save = true;
    }

    if ( $save ) $order->save();
}

/* ─── REDEMPTION: cart fee ──────────────────────────────────────────────── */

add_action( 'woocommerce_cart_calculate_fees', 'stanray_apply_points_redemption_fee' );

function stanray_apply_points_redemption_fee( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;
    if ( ! stanray_points_enabled() ) return;
    if ( ! WC()->session ) return;

    $points = (int) WC()->session->get( 'eskecy_points_redeem', 0 );
    if ( $points <= 0 ) return;

    $discount = stanray_points_calc_discount( $points, $cart->get_subtotal() );
    if ( $discount <= 0 ) return;

    $cart->add_fee( __( 'Eskecy Points Discount', 'stanray-custom' ), -$discount, false );
}

/* ─── REDEMPTION: AJAX apply / remove ───────────────────────────────────── */
// Registered with nopriv variants (like the wishlist AJAX handlers) purely so
// a logged-out visitor gets the structured login_required response instead
// of WordPress's generic "invalid action" failure.

add_action( 'wp_ajax_eskecy_apply_points_redeem', 'eskecy_apply_points_redeem_handler' );
add_action( 'wp_ajax_nopriv_eskecy_apply_points_redeem', 'eskecy_apply_points_redeem_handler' );

function eskecy_apply_points_redeem_handler() {
    check_ajax_referer( 'eskecy_points', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [
            'code'    => 'login_required',
            'message' => 'Please log in to redeem points.',
        ] );
    }

    if ( ! stanray_points_enabled() ) {
        wp_send_json_error( [ 'message' => 'Points redemption is currently unavailable.' ] );
    }

    if ( ! WC()->cart || WC()->cart->is_empty() ) {
        wp_send_json_error( [ 'message' => 'Your cart is empty.' ] );
    }

    $user_id   = get_current_user_id();
    $balance   = stanray_points_get_balance( $user_id );
    $min       = (int) get_option( 'stanray_points_min_redeem', 100 );
    $requested = absint( $_POST['points'] ?? 0 );

    if ( $requested < $min ) {
        wp_send_json_error( [ 'message' => sprintf( 'Minimum %d points required to redeem.', $min ) ] );
    }
    if ( $requested > $balance ) {
        wp_send_json_error( [ 'message' => 'You do not have enough points.' ] );
    }

    WC()->session->set( 'eskecy_points_redeem', $requested );
    WC()->cart->calculate_totals();

    wp_send_json_success( [
        'points'   => $requested,
        'discount' => wc_price( stanray_points_calc_discount( $requested, WC()->cart->get_subtotal() ) ),
        'total'    => wc_price( WC()->cart->get_total( 'edit' ) ),
    ] );
}

add_action( 'wp_ajax_eskecy_remove_points_redeem', 'eskecy_remove_points_redeem_handler' );
add_action( 'wp_ajax_nopriv_eskecy_remove_points_redeem', 'eskecy_remove_points_redeem_handler' );

function eskecy_remove_points_redeem_handler() {
    check_ajax_referer( 'eskecy_points', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'code' => 'login_required', 'message' => 'Please log in to manage points.' ] );
    }

    if ( WC()->session ) WC()->session->__unset( 'eskecy_points_redeem' );
    if ( WC()->cart ) WC()->cart->calculate_totals();

    wp_send_json_success( [
        'total' => WC()->cart ? wc_price( WC()->cart->get_total( 'edit' ) ) : '',
    ] );
}

/* ─── REDEMPTION: debit balance once the order is actually placed ──────── */
// This store's Cart/Checkout pages are the WooCommerce Blocks versions (Store
// API), not the classic [woocommerce_cart]/[woocommerce_checkout] templates —
// confirmed via the "wp-block-woocommerce-cart"/"proceed-to-checkout-block"
// markup and PayPal's block-specific checkout JS being enqueued. The Blocks
// checkout flow does NOT call WC_Checkout::process_checkout(), so the classic
// woocommerce_checkout_order_processed hook never fires for it — WooCommerce
// core's own comment on this says as much (see
// wp-content/plugins/woocommerce/src/StoreApi/Routes/V1/Checkout.php). Its
// replacement, woocommerce_store_api_checkout_order_processed, passes only
// the order object (no $order_id/$posted_data), hence the two thin wrappers
// below sharing one real handler. Both are registered so this still works if
// the store is ever switched back to the classic checkout shortcode/template.

add_action( 'woocommerce_checkout_order_processed', function( $order_id, $posted_data, $order ) {
    stanray_debit_redeemed_points_for_order( $order );
}, 20, 3 );

add_action( 'woocommerce_store_api_checkout_order_processed', 'stanray_debit_redeemed_points_for_order', 20, 1 );

function stanray_debit_redeemed_points_for_order( $order ) {
    if ( ! $order instanceof WC_Order ) return;
    if ( ! WC()->session ) return;

    $points = (int) WC()->session->get( 'eskecy_points_redeem', 0 );
    if ( $points <= 0 ) return;

    // Cleared up front: if both hooks somehow fired for the same order, the
    // second call sees an empty session and no-ops instead of double-debiting.
    WC()->session->__unset( 'eskecy_points_redeem' );

    $user_id = $order->get_user_id();
    if ( ! $user_id ) return; // guest checkout can't have a balance to spend

    // Clamp in case the balance changed between "Apply" and placing the order.
    $balance = stanray_points_get_balance( $user_id );
    $points  = min( $points, $balance );
    if ( $points <= 0 ) return;

    $discount = stanray_points_calc_discount( $points, $order->get_subtotal() );

    update_user_meta( $user_id, '_eskecy_points_balance', max( 0, $balance - $points ) );

    $order->update_meta_data( '_eskecy_points_redeemed', $points );
    $order->update_meta_data( '_eskecy_points_redeemed_discount', $discount );
    $order->save();

    stanray_points_add_history_entry( $user_id, [
        'type' => 'redeemed', 'points' => $points, 'order_id' => $order->get_id(), 'date' => current_time( 'mysql' ),
    ] );
}

/* ─── CART & CHECKOUT: redeem widget ────────────────────────────────────── */
// Account-only, same reasoning as the wishlist: a points balance belongs to
// a logged-in customer, not a browser/cookie.
//
// The Cart page is the Blocks/Store-API version (confirmed by its
// "wp-block-woocommerce-cart-*" markup), so woocommerce_before_cart_totals
// — which only exists inside the classic cart-totals.php template — never
// fires there; render_block_{$block_name} (WordPress core's generic
// per-block filter) is the real entry point on that page.
//
// The Checkout page, however, is still the CLASSIC [woocommerce_checkout]
// shortcode (confirmed by dumping its post_content — it's literally just
// that shortcode, not a Blocks page), so it needs a classic action instead;
// no render_block filter would ever match there. This theme also moves the
// coupon form itself from the top of the page down to just above the Place
// Order button (see "CHECKOUT: Move coupon form..." in inc/woocommerce.php,
// which re-hooks woocommerce_checkout_coupon_form onto
// woocommerce_review_order_before_submit) — so the widget follows it onto
// that same hook, priority 15, to land directly after it.

add_action( 'woocommerce_before_cart_totals', 'stanray_render_points_redeem_widget' );
add_action( 'woocommerce_review_order_before_submit', 'stanray_render_points_redeem_widget', 15 );

add_filter( 'render_block_woocommerce/cart-totals-block', function( $block_content, $block ) {
    if ( ! stanray_points_enabled() || ! is_user_logged_in() ) return $block_content;
    ob_start();
    stanray_render_points_redeem_widget();
    return ob_get_clean() . $block_content;
}, 10, 2 );

function stanray_render_points_redeem_widget() {
    if ( ! stanray_points_enabled() || ! is_user_logged_in() ) return;

    $user_id = get_current_user_id();
    $balance = stanray_points_get_balance( $user_id );
    $min     = (int) get_option( 'stanray_points_min_redeem', 100 );
    if ( $balance < $min ) return; // nothing usable to offer

    $rate    = (float) get_option( 'stanray_points_redeem_rate', 0.5 );
    $applied = WC()->session ? (int) WC()->session->get( 'eskecy_points_redeem', 0 ) : 0;

    // Collapsed-by-default toggle bar + sliding panel, mirroring the "Have a
    // coupon?" bar/form pair right above it (woocommerce/templates/checkout/
    // form-coupon.php) — same look, same click-to-slide-open interaction —
    // rather than always showing as a static open box next to a collapsed one.
    // Starts open if points are already applied, so that state isn't hidden.
    echo '<div class="points-redeem-widget" id="points-redeem-widget" data-nonce="' . esc_attr( wp_create_nonce( 'eskecy_points' ) ) . '" data-min="' . esc_attr( $min ) . '" data-balance="' . esc_attr( $balance ) . '">';

    echo '<div class="points-redeem-widget__toggle">';
    echo '<span>' . esc_html__( 'Have Eskecy Points?', 'stanray-custom' ) . '</span> ';
    echo '<a href="#" role="button" aria-controls="points-redeem-panel" aria-expanded="' . ( $applied > 0 ? 'true' : 'false' ) . '" class="points-redeem-widget__toggle-link">';
    echo $applied > 0
        ? esc_html__( 'View redemption', 'stanray-custom' )
        : esc_html__( 'Click here to redeem', 'stanray-custom' );
    echo '</a>';
    echo '</div>';

    echo '<div class="points-redeem-widget__panel" id="points-redeem-panel"' . ( $applied > 0 ? '' : ' style="display:none"' ) . '>';
    echo '<p class="points-redeem-widget__balance">' . sprintf(
        esc_html__( 'You have %1$s points (worth %2$s).', 'stanray-custom' ),
        '<strong>' . esc_html( number_format_i18n( $balance ) ) . '</strong>',
        wc_price( $balance * $rate )
    ) . '</p>';

    if ( $applied > 0 ) {
        echo '<p class="points-redeem-widget__applied">' . sprintf(
            esc_html__( 'Redeeming %s points on this order.', 'stanray-custom' ),
            '<strong>' . esc_html( $applied ) . '</strong>'
        ) . '</p>';
        echo '<button type="button" class="btn btn--outline btn--sm points-redeem-remove">' . esc_html__( 'Remove', 'stanray-custom' ) . '</button>';
    } else {
        echo '<div class="points-redeem-widget__form">';
        echo '<input type="number" min="' . esc_attr( $min ) . '" max="' . esc_attr( $balance ) . '" step="1" class="points-redeem-input" placeholder="' . esc_attr( $min ) . '">';
        echo '<button type="button" class="btn btn--primary btn--sm points-redeem-apply">' . esc_html__( 'Apply', 'stanray-custom' ) . '</button>';
        echo '</div>';
    }
    echo '<p class="points-redeem-widget__message" aria-live="polite"></p>';
    echo '</div>'; // .points-redeem-widget__panel

    echo '</div>'; // .points-redeem-widget
}

/* ─── MY ACCOUNT: "My Points" endpoint ─────────────────────────────────── */
// WooCommerce auto-registers a rewrite endpoint for every key added via this
// filter (WC_Query::add_endpoints()), so no explicit add_rewrite_endpoint()
// call is needed here — same as the saved-address endpoint in address-book.php.

add_filter( 'woocommerce_get_query_vars', function( $vars ) {
    $vars['eskecy-points'] = 'eskecy-points';
    return $vars;
} );

// Themes have no activation hook, so a fresh deploy needs one explicit rewrite
// flush or the new endpoint 404s until permalinks are resaved by hand.
// Version-gated so it only ever runs once (mirrors stanray_address_book_rewrite_v).
add_action( 'init', function() {
    if ( '1' !== get_option( 'stanray_points_rewrite_v' ) ) {
        flush_rewrite_rules();
        update_option( 'stanray_points_rewrite_v', '1' );
    }
}, 20 );

add_action( 'woocommerce_account_eskecy-points_endpoint', function() {
    wc_get_template( 'myaccount/eskecy-points.php' );
} );

/* ─── JS DATA ────────────────────────────────────────────────────────────── */

add_action( 'wp_enqueue_scripts', function() {
    wp_localize_script( 'stanray-main', 'eskecyPoints', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'eskecy_points' ),
    ] );
}, 20 );
