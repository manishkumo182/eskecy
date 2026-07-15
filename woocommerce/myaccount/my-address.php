<?php
/**
 * My Addresses — address book list.
 *
 * Overrides WooCommerce core's single-address version (theme had no override
 * before). Shows every saved billing/shipping address as a card, not just
 * one of each — see inc/address-book.php for the storage/CRUD logic this
 * renders.
 */

defined( 'ABSPATH' ) || exit;

$user_id     = get_current_user_id();
$show_shipping = ! wc_ship_to_billing_address_only() && wc_shipping_enabled();
$sections    = $show_shipping
    ? [ 'billing' => __( 'Billing Addresses', 'stanray-custom' ), 'shipping' => __( 'Shipping Addresses', 'stanray-custom' ) ]
    : [ 'billing' => __( 'Billing Addresses', 'stanray-custom' ) ];
?>

<p class="bill-address">
    <?php esc_html_e( 'These addresses are available to pick from at checkout.', 'stanray-custom' ); ?>
</p>

<?php foreach ( $sections as $type => $title ) :
    $addresses = stanray_get_user_addresses( $user_id, $type );
    $default_id = stanray_get_default_address_id( $user_id, $type );
    $add_url = add_query_arg( 'type', $type, wc_get_endpoint_url( 'saved-address', '', wc_get_page_permalink( 'myaccount' ) ) );
?>
    <div class="stanray-page-header">
        <h2 class="stanray-page-title"><?php echo esc_html( $title ); ?></h2>
        <a href="<?php echo esc_url( $add_url ); ?>" class="stanray-btn stanray-btn--xs">+ <?php esc_html_e( 'Add address', 'stanray-custom' ); ?></a>
    </div>

    <?php if ( ! $addresses ) : ?>
        <p><?php esc_html_e( 'You have not saved any addresses of this type yet.', 'stanray-custom' ); ?></p>
    <?php else : ?>
        <div class="woocommerce-Addresses">
            <?php foreach ( $addresses as $post ) :
                $is_default = ( (int) $post->ID === $default_id );
                $edit_url   = wc_get_endpoint_url( 'saved-address', $post->ID, wc_get_page_permalink( 'myaccount' ) );
                $delete_url = wp_nonce_url( add_query_arg( 'stanray_delete_address', $post->ID, wc_get_account_endpoint_url( 'edit-address' ) ), 'stanray-delete-address' );
                $default_url = wp_nonce_url( add_query_arg( 'stanray_set_default', $post->ID, wc_get_account_endpoint_url( 'edit-address' ) ), 'stanray-set-default' );
            ?>
                <div class="woocommerce-Address">
                    <header class="woocommerce-Address-title title">
                        <h3>
                            <?php echo esc_html( get_the_title( $post ) ); ?>
                            <?php if ( $is_default ) : ?><span class="stanray-status stanray-status--completed"><?php esc_html_e( 'Default', 'stanray-custom' ); ?></span><?php endif; ?>
                        </h3>
                        <a href="<?php echo esc_url( $edit_url ); ?>" class="edit"><?php esc_html_e( 'Edit', 'stanray-custom' ); ?></a>
                    </header>
                    <address><?php echo wp_kses_post( stanray_format_address_html( $post->ID ) ); ?></address>
                    <div class="stanray-form-actions">
                        <?php if ( ! $is_default ) : ?>
                            <a href="<?php echo esc_url( $default_url ); ?>" class="stanray-btn stanray-btn--ghost stanray-btn--xs"><?php esc_html_e( 'Set as default', 'stanray-custom' ); ?></a>
                        <?php endif; ?>
                        <a href="<?php echo esc_url( $delete_url ); ?>" class="stanray-btn stanray-btn--ghost stanray-btn--xs" onclick="return confirm('<?php echo esc_js( __( 'Remove this address?', 'stanray-custom' ) ); ?>');"><?php esc_html_e( 'Delete', 'stanray-custom' ); ?></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
