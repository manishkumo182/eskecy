<?php
/**
 * Dashboard admin page: Eskecy Points
 * Lets an admin change the earn/redeem rates without touching code — same
 * hand-rolled settings-page pattern as admin-hero-banner.php (this theme
 * doesn't use the WP Settings API anywhere).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function stanray_points_admin_menu() {
    add_menu_page(
        __( 'Eskecy Points', 'stanray-custom' ),
        __( 'Eskecy Points', 'stanray-custom' ),
        'manage_options',
        'stanray-points',
        'stanray_points_admin_page',
        'dashicons-tickets-alt',
        59
    );
}
add_action( 'admin_menu', 'stanray_points_admin_menu' );

function stanray_points_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $saved = false;

    if ( isset( $_POST['stanray_points_save'] ) && check_admin_referer( 'stanray_points_save_action', 'stanray_points_nonce' ) ) {
        update_option( 'stanray_points_enabled', isset( $_POST['stanray_points_enabled'] ) ? '1' : '0' );

        $fields = [
            'stanray_points_earn_divisor'      => 'floatval',
            'stanray_points_redeem_rate'       => 'floatval',
            'stanray_points_min_redeem'        => 'absint',
            'stanray_points_max_redeem_percent'=> 'floatval',
        ];
        foreach ( $fields as $key => $sanitizer ) {
            update_option( $key, call_user_func( $sanitizer, $_POST[ $key ] ?? '' ) );
        }

        $saved = true;
    }

    $v = function ( $key, $default = '' ) {
        return get_option( $key, $default );
    };

    $enabled = stanray_points_enabled();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Eskecy Points — Loyalty Program Settings', 'stanray-custom' ); ?></h1>
        <p><?php esc_html_e( 'Controls how customers earn and redeem Eskecy Points storewide.', 'stanray-custom' ); ?></p>

        <?php if ( $saved ) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Saved.', 'stanray-custom' ); ?></p></div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field( 'stanray_points_save_action', 'stanray_points_nonce' ); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Enable Points Program', 'stanray-custom' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="stanray_points_enabled" value="1" <?php checked( $enabled ); ?>>
                            <?php esc_html_e( 'Customers can earn and redeem points', 'stanray-custom' ); ?>
                        </label>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Earning', 'stanray-custom' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="stanray_points_earn_divisor"><?php esc_html_e( 'Amount spent per point', 'stanray-custom' ); ?></label></th>
                    <td>
                        <input type="number" step="0.01" min="0.01" id="stanray_points_earn_divisor" name="stanray_points_earn_divisor" value="<?php echo esc_attr( $v( 'stanray_points_earn_divisor', 100 ) ); ?>" class="small-text">
                        <p class="description"><?php esc_html_e( 'E.g. 100 means a customer earns 1 point for every Rs 100 spent (order total, floored to a whole point).', 'stanray-custom' ); ?></p>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Redemption', 'stanray-custom' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="stanray_points_redeem_rate"><?php esc_html_e( 'Value per point', 'stanray-custom' ); ?></label></th>
                    <td>
                        <input type="number" step="0.01" min="0" id="stanray_points_redeem_rate" name="stanray_points_redeem_rate" value="<?php echo esc_attr( $v( 'stanray_points_redeem_rate', 0.5 ) ); ?>" class="small-text">
                        <p class="description"><?php esc_html_e( 'E.g. 0.5 means 1 point is worth Rs 0.5 off at checkout.', 'stanray-custom' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="stanray_points_min_redeem"><?php esc_html_e( 'Minimum points to redeem', 'stanray-custom' ); ?></label></th>
                    <td>
                        <input type="number" step="1" min="0" id="stanray_points_min_redeem" name="stanray_points_min_redeem" value="<?php echo esc_attr( $v( 'stanray_points_min_redeem', 100 ) ); ?>" class="small-text">
                        <p class="description"><?php esc_html_e( 'A customer must redeem at least this many points at once.', 'stanray-custom' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="stanray_points_max_redeem_percent"><?php esc_html_e( 'Max % of order coverable by points', 'stanray-custom' ); ?></label></th>
                    <td>
                        <input type="number" step="1" min="0" max="100" id="stanray_points_max_redeem_percent" name="stanray_points_max_redeem_percent" value="<?php echo esc_attr( $v( 'stanray_points_max_redeem_percent', 50 ) ); ?>" class="small-text">
                        <p class="description"><?php esc_html_e( 'Prevents points from covering an entire order. E.g. 50 means points can discount at most half the cart subtotal.', 'stanray-custom' ); ?></p>
                    </td>
                </tr>
            </table>

            <?php submit_button( __( 'Save Changes', 'stanray-custom' ), 'primary', 'stanray_points_save' ); ?>
        </form>
    </div>
    <?php
}
