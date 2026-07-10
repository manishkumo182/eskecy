<?php
/**
 * PART 2 — Custom Account Dashboard
 *
 * FILE LOCATION: /woocommerce/myaccount/dashboard.php
 * Overrides the default WooCommerce dashboard shown after login.
 */

defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();
$first_name   = $current_user->first_name ?: $current_user->display_name;

// Stats
$customer_orders = wc_get_orders([
    'customer_id' => get_current_user_id(),
    'limit'       => -1,
    'status'      => [ 'wc-completed', 'wc-processing', 'wc-on-hold' ],
]);
$total_orders  = count( $customer_orders );
$total_spent   = array_sum( array_map( fn($o) => $o->get_total(), $customer_orders ) );
$pending_count = count( wc_get_orders([
    'customer_id' => get_current_user_id(),
    'limit'       => -1,
    'status'      => [ 'wc-processing', 'wc-on-hold' ],
]) );

// Recent orders (last 3)
$recent_orders = wc_get_orders([
    'customer_id' => get_current_user_id(),
    'limit'       => 3,
    'orderby'     => 'date',
    'order'       => 'DESC',
]);
?>

<div class="stanray-dashboard">

    <!-- ── Welcome Bar ─────────────────────────────────────── -->
    <div class="stanray-dashboard__welcome">
        <div class="stanray-dashboard__avatar">
            <?php echo strtoupper( substr( $first_name, 0, 1 ) ); ?>
        </div>
        <div>
            <h2 class="stanray-dashboard__greeting">Welcome back, <?php echo esc_html( $first_name ); ?></h2>
            <p class="stanray-dashboard__email"><?php echo esc_html( $current_user->user_email ); ?></p>
        </div>
        <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-account' ) ); ?>" class="stanray-btn stanray-btn--ghost stanray-dashboard__edit-btn">
            Edit Profile
        </a>
    </div>

    <!-- ── Stats Row ───────────────────────────────────────── -->
    <div class="stanray-stats">
        <div class="stanray-stat">
            <span class="stanray-stat__number"><?php echo $total_orders; ?></span>
            <span class="stanray-stat__label">Total Orders</span>
        </div>
        <div class="stanray-stat">
            <span class="stanray-stat__number"><?php echo get_woocommerce_currency_symbol() . number_format( $total_spent, 0 ); ?></span>
            <span class="stanray-stat__label">Total Spent</span>
        </div>
        <div class="stanray-stat">
            <span class="stanray-stat__number"><?php echo $pending_count; ?></span>
            <span class="stanray-stat__label">Active Orders</span>
        </div>
    </div>

    <!-- ── Quick Nav ───────────────────────────────────────── -->
    <div class="stanray-quick-nav">
        <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="stanray-quick-nav__item">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span>Orders</span>
        </a>
        <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>" class="stanray-quick-nav__item">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span>Addresses</span>
        </a>
        <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-account' ) ); ?>" class="stanray-quick-nav__item">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span>Account</span>
        </a>
        <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'customer-logout' ) ); ?>" class="stanray-quick-nav__item stanray-quick-nav__item--danger">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            <span>Sign Out</span>
        </a>
    </div>

    <!-- ── Recent Orders ───────────────────────────────────── -->
    <?php if ( $recent_orders ) : ?>
    <div class="stanray-dashboard__section">
        <div class="stanray-dashboard__section-header">
            <h3>Recent Orders</h3>
            <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>">View All &rarr;</a>
        </div>
        <div class="stanray-orders-mini">
            <?php foreach ( $recent_orders as $order ) : ?>
            <div class="stanray-order-row">
                <div class="stanray-order-row__id">
                    <span class="stanray-order-row__label">Order</span>
                    <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">#<?php echo $order->get_order_number(); ?></a>
                </div>
                <div class="stanray-order-row__date"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></div>
                <div class="stanray-order-row__status">
                    <span class="stanray-status stanray-status--<?php echo esc_attr( $order->get_status() ); ?>">
                        <?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
                    </span>
                </div>
                <div class="stanray-order-row__total"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></div>
                <div class="stanray-order-row__action">
                    <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="stanray-btnn stanray-btn--xs">View</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else : ?>
    <div class="stanray-dashboard__empty">
        <p>You haven't placed any orders yet.</p>
        <a href="<?php echo esc_url( wc_get_page_permalink('shop') ); ?>" class="stanray-btn">Start Shopping</a>
    </div>
    <?php endif; ?>

</div>