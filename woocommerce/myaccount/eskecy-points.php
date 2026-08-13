<?php
/**
 * "My Points" account tab — rendered by the eskecy-points endpoint.
 * See inc/points.php for the endpoint registration and points logic.
 */

defined( 'ABSPATH' ) || exit;

$user_id = get_current_user_id();
$balance = stanray_points_get_balance( $user_id );
$history = stanray_points_get_history( $user_id );

$earn_divisor = (float) get_option( 'stanray_points_earn_divisor', 100 );
$redeem_rate  = (float) get_option( 'stanray_points_redeem_rate', 0.5 );
$min_redeem   = (int) get_option( 'stanray_points_min_redeem', 100 );

$labels = [
    'earned'          => __( 'Earned', 'stanray-custom' ),
    'redeemed'        => __( 'Redeemed', 'stanray-custom' ),
    'reversed'        => __( 'Reversed', 'stanray-custom' ),
    'redeem_reversed' => __( 'Refunded', 'stanray-custom' ),
];
?>

<div class="stanray-page-header">
    <h2 class="stanray-page-title"><?php esc_html_e( 'My Points', 'stanray-custom' ); ?></h2>
</div>

<div class="stanray-stats">
    <div class="stanray-stat">
        <span class="stanray-stat__number"><?php echo esc_html( number_format_i18n( $balance ) ); ?></span>
        <span class="stanray-stat__label"><?php esc_html_e( 'Points Balance', 'stanray-custom' ); ?></span>
    </div>
    <div class="stanray-stat">
        <span class="stanray-stat__number"><?php echo wc_price( $balance * $redeem_rate ); ?></span>
        <span class="stanray-stat__label"><?php esc_html_e( 'Redeemable Value', 'stanray-custom' ); ?></span>
    </div>
</div>

<?php if ( stanray_points_enabled() ) : ?>
<p class="stanray-points-explainer">
    <?php echo esc_html( sprintf(
        /* translators: 1: amount spent per point, 2: value per point, 3: minimum points to redeem */
        __( 'Earn 1 point for every %1$s spent. Redeem points at checkout for %2$s off per point (minimum %3$s points).', 'stanray-custom' ),
        wp_strip_all_tags( wc_price( $earn_divisor ) ),
        wp_strip_all_tags( wc_price( $redeem_rate ) ),
        number_format_i18n( $min_redeem )
    ) ); ?>
</p>
<?php endif; ?>

<div class="stanray-dashboard__section">
    <div class="stanray-dashboard__section-header">
        <h3><?php esc_html_e( 'History', 'stanray-custom' ); ?></h3>
    </div>

    <?php if ( empty( $history ) ) : ?>
        <div class="stanray-dashboard__empty">
            <p><?php esc_html_e( "You haven't earned any points yet.", 'stanray-custom' ); ?></p>
            <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="stanray-btn"><?php esc_html_e( 'Start Shopping', 'stanray-custom' ); ?></a>
        </div>
    <?php else : ?>
        <table class="stanray-points-history shop_table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Date', 'stanray-custom' ); ?></th>
                    <th><?php esc_html_e( 'Activity', 'stanray-custom' ); ?></th>
                    <th><?php esc_html_e( 'Order', 'stanray-custom' ); ?></th>
                    <th><?php esc_html_e( 'Points', 'stanray-custom' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $history as $entry ) :
                    $type      = $entry['type'] ?? '';
                    $points    = (int) ( $entry['points'] ?? 0 );
                    $order_id  = absint( $entry['order_id'] ?? 0 );
                    $order     = $order_id ? wc_get_order( $order_id ) : null;
                    $is_credit = in_array( $type, [ 'earned', 'redeem_reversed' ], true );
                    ?>
                    <tr>
                        <td><?php echo esc_html( $entry['date'] ? date_i18n( get_option( 'date_format' ), strtotime( $entry['date'] ) ) : '' ); ?></td>
                        <td><?php echo esc_html( $labels[ $type ] ?? ucfirst( $type ) ); ?></td>
                        <td>
                            <?php if ( $order ) : ?>
                                <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">#<?php echo esc_html( $order->get_order_number() ); ?></a>
                            <?php else : ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                        <td class="stanray-points-history__amount stanray-points-history__amount--<?php echo $is_credit ? 'credit' : 'debit'; ?>">
                            <?php echo $is_credit ? '+' : '-'; ?><?php echo esc_html( number_format_i18n( $points ) ); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
