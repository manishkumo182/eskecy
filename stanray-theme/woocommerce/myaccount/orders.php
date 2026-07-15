<?php
/**
 * PART 3 — Custom Order History Page
 *
 * FILE LOCATION: /woocommerce/myaccount/orders.php
 * Overrides WooCommerce default orders list.
 */

defined( 'ABSPATH' ) || exit;

$customer_orders = wc_get_orders( apply_filters( 'woocommerce_my_account_my_orders_query', [
    'customer' => get_current_user_id(),
    'page'     => isset( $_GET['order-page'] ) ? absint( $_GET['order-page'] ) : 1,
    'paginate' => true,
    'limit'    => 10,
] ) );
?>

<div class="stanray-orders-page">

    <div class="stanray-page-header">
        <h2 class="stanray-page-title">Order History</h2>
        <?php if ( $customer_orders->total > 0 ) : ?>
        <span class="stanray-page-count"><?php echo $customer_orders->total; ?> orders</span>
        <?php endif; ?>
    </div>

    <?php if ( $customer_orders->orders ) : ?>

        <!-- ── Filter Tabs ──────────────────────────────────── -->
        <div class="stanray-order-filters">
            <button class="stanray-filter-tab is-active" data-status="all">All</button>
            <button class="stanray-filter-tab" data-status="processing">Processing</button>
            <button class="stanray-filter-tab" data-status="completed">Completed</button>
            <button class="stanray-filter-tab" data-status="on-hold">On Hold</button>
            <button class="stanray-filter-tab" data-status="cancelled">Cancelled</button>
        </div>

        <!-- ── Orders Table ────────────────────────────────── -->
        <div class="stanray-orders-table">

            <div class="stanray-orders-table__head">
                <span>Order</span>
                <span>Date</span>
                <span>Status</span>
                <span>Items</span>
                <span>Total</span>
                <span></span>
            </div>

            <?php foreach ( $customer_orders->orders as $customer_order ) :
                $order      = wc_get_order( $customer_order );
                $item_count = $order->get_item_count();
                $status     = $order->get_status();
            ?>
            <div class="stanray-orders-table__row" data-status="<?php echo esc_attr( $status ); ?>">

                <div class="stanray-orders-table__cell stanray-orders-table__cell--order">
                    <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="stanray-order-number">
                        #<?php echo $order->get_order_number(); ?>
                    </a>
                </div>

                <div class="stanray-orders-table__cell stanray-orders-table__cell--date">
                    <?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
                </div>

                <div class="stanray-orders-table__cell">
                    <span class="stanray-status stanray-status--<?php echo esc_attr( $status ); ?>">
                        <?php echo esc_html( wc_get_order_status_name( $status ) ); ?>
                    </span>
                </div>

                <div class="stanray-orders-table__cell stanray-orders-table__cell--items">
                    <?php
                    $item_names = [];
                    foreach ( $order->get_items() as $item ) {
                        $item_names[] = $item->get_name();
                    }
                    echo esc_html( implode( ', ', array_slice( $item_names, 0, 2 ) ) );
                    if ( count( $item_names ) > 2 ) {
                        echo ' <span class="stanray-more">+' . ( count( $item_names ) - 2 ) . ' more</span>';
                    }
                    ?>
                </div>

                <div class="stanray-orders-table__cell stanray-orders-table__cell--total">
                    <?php echo wp_kses_post( $order->get_formatted_order_total() ); ?>
                </div>

                <div class="stanray-orders-table__cell stanray-orders-table__cell--actions">
                    <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="stanray-btnn stanray-btn--xs">View</a>
                    <?php if ( $order->needs_payment() ) : ?>
                    <a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="stanray-btn stanray-btn--xs stanray-btn--primary">Pay</a>
                    <?php endif; ?>
                </div>

            </div>
            <?php endforeach; ?>
        </div>

        <!-- ── Pagination ──────────────────────────────────── -->
        <?php if ( $customer_orders->max_num_pages > 1 ) : ?>
        <div class="stanray-pagination">
            <?php
            $current_page = isset( $_GET['order-page'] ) ? absint( $_GET['order-page'] ) : 1;
            for ( $i = 1; $i <= $customer_orders->max_num_pages; $i++ ) :
                $url = add_query_arg( 'order-page', $i );
            ?>
            <a href="<?php echo esc_url( $url ); ?>" class="stanray-page-btn <?php echo $i === $current_page ? 'is-active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

    <?php else : ?>
        <div class="stanray-dashboard__empty">
            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24" style="opacity:.3;margin-bottom:16px"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p>No orders yet.</p>
            <a href="<?php echo esc_url( wc_get_page_permalink('shop') ); ?>" class="stanray-btn">Start Shopping</a>
        </div>
    <?php endif; ?>

</div>

<script>
// Filter tabs JS
document.querySelectorAll('.stanray-filter-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.stanray-filter-tab').forEach(t => t.classList.remove('is-active'));
        this.classList.add('is-active');
        var status = this.dataset.status;
        document.querySelectorAll('.stanray-orders-table__row').forEach(function(row) {
            row.style.display = (status === 'all' || row.dataset.status === status) ? '' : 'none';
        });
    });
});
</script>