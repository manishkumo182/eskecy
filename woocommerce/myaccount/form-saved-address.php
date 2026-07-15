<?php
/**
 * Add/edit a single saved address-book entry.
 * Rendered by the `saved-address` account endpoint — see inc/address-book.php.
 *
 * Mirrors the styling approach of woocommerce/myaccount/form-edit-address.php
 * (same .woocommerce-address-fields / woocommerce_form_field() pattern), but
 * posts to its own handler, not WC_Form_Handler::save_address() — see
 * inc/address-book.php for why that distinction matters.
 */

defined( 'ABSPATH' ) || exit;

$user_id = get_current_user_id();

global $wp;
$post_id = absint( $wp->query_vars['saved-address'] ?? '' );
$type    = isset( $_GET['type'] ) && in_array( $_GET['type'], [ 'billing', 'shipping' ], true ) ? $_GET['type'] : 'billing';

$existing = null;
if ( $post_id ) {
    $existing = stanray_get_address_post( $post_id, $user_id );
    if ( ! $existing ) {
        wc_add_notice( __( 'Address not found.', 'stanray-custom' ), 'error' );
        wp_safe_redirect( wc_get_account_endpoint_url( 'edit-address' ) );
        exit;
    }
    $type = get_post_meta( $post_id, '_address_type', true );
}

$country = $existing ? get_post_meta( $post_id, '_country', true ) : '';
$country = $country ?: WC()->countries->get_base_country();

$values = $existing ? stanray_get_address_fields_array( $post_id ) : [ 'country' => $country ];
$label  = $existing ? get_the_title( $post_id ) : '';
$is_default = $existing && stanray_get_default_address_id( $user_id, $type ) === $post_id;

$type_label = 'billing' === $type ? __( 'Billing', 'stanray-custom' ) : __( 'Shipping', 'stanray-custom' );
?>

<div class="stanray-page-header">
    <h2 class="stanray-page-title">
        <?php echo $existing
            ? esc_html( sprintf( __( 'Edit %s address', 'stanray-custom' ), $type_label ) )
            : esc_html( sprintf( __( 'Add %s address', 'stanray-custom' ), $type_label ) ); ?>
    </h2>
</div>

<form method="post" novalidate class="stanray-address-form">

    <div class="woocommerce-address-fields stanray-form-section">

        <div class="stanray-field">
            <label for="label"><?php esc_html_e( 'Label', 'stanray-custom' ); ?> <span class="stanray-optional">(<?php esc_html_e( 'optional, e.g. "Home", "Office"', 'stanray-custom' ); ?>)</span></label>
            <input type="text" class="stanray-input" name="label" id="label" value="<?php echo esc_attr( $label ); ?>" placeholder="Home">
        </div>

        <div class="woocommerce-address-fields__field-wrapper">
            <?php
            $fields = WC()->countries->get_address_fields( $country, $type . '_' );
            foreach ( $fields as $key => $field ) {
                $unprefixed = substr( $key, strlen( $type ) + 1 );
                woocommerce_form_field( $unprefixed, $field, $values[ $unprefixed ] ?? '' );
            }
            ?>
        </div>

        <label class="stanray-checkbox">
            <input type="checkbox" name="is_default" value="1" <?php checked( $is_default ); ?>>
            <?php esc_html_e( 'Set as default', 'stanray-custom' ); ?>
        </label>

        <div class="stanray-form-actions">
            <?php wp_nonce_field( 'stanray-save-address', 'stanray-save-address-nonce' ); ?>
            <input type="hidden" name="action" value="stanray_save_address">
            <input type="hidden" name="address_type" value="<?php echo esc_attr( $type ); ?>">
            <input type="hidden" name="address_id" value="<?php echo esc_attr( $post_id ); ?>">
            <button type="submit" class="stanray-btn" name="save_address" value="<?php esc_attr_e( 'Save address', 'stanray-custom' ); ?>">
                <?php esc_html_e( 'Save address', 'stanray-custom' ); ?>
            </button>
            <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>" class="stanray-btn stanray-btn--ghost">
                <?php esc_html_e( 'Cancel', 'stanray-custom' ); ?>
            </a>
        </div>
    </div>

</form>
