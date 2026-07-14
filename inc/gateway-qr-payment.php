<?php
/**
 * Custom WooCommerce payment gateway: "Scan to Pay (QR)"
 * An offline gateway (like Bank Transfer / COD) that shows a QR
 * code at checkout for the customer to scan and pay manually.
 * Configured from WooCommerce → Settings → Payments, same as the
 * other payment methods (eSewa, Direct Bank Transfer, etc.).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_filter( 'woocommerce_payment_gateways', 'stanray_add_qr_payment_gateway' );
function stanray_add_qr_payment_gateway( $gateways ) {
    $gateways[] = 'WC_Gateway_QR_Payment';
    return $gateways;
}

// NOTE: theme functions.php loads AFTER the 'plugins_loaded' action has
// already fired, so hooking 'plugins_loaded' here would never run. WooCommerce's
// classes are already available by the time this file loads, but we still
// delay to 'init' (which hasn't fired yet) to stay consistent with how
// WooCommerce itself expects gateways to register.
add_action( 'init', 'stanray_init_qr_payment_gateway' );
function stanray_init_qr_payment_gateway() {

    if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;
    if ( class_exists( 'WC_Gateway_QR_Payment' ) ) return;

    class WC_Gateway_QR_Payment extends WC_Payment_Gateway {

        public function __construct() {
            $this->id                 = 'qr_payment';
            $this->icon               = '';
            $this->has_fields         = false;
            $this->method_title       = 'Scan to Pay (QR)';
            $this->method_description = 'Let customers pay by scanning a QR code (eSewa/bank/wallet). The order is held until you manually confirm payment was received.';

            $this->init_form_fields();
            $this->init_settings();

            $this->title       = $this->get_option( 'title' );
            $this->description = $this->get_option( 'description' );
            $this->instructions = $this->get_option( 'instructions', $this->description );

            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
            add_action( 'woocommerce_thankyou_' . $this->id, [ $this, 'thankyou_page' ] );
            add_action( 'woocommerce_email_before_order_table', [ $this, 'email_instructions' ], 10, 3 );
        }

        public function init_form_fields() {
            $this->form_fields = [
                'enabled' => [
                    'title'   => 'Enable/Disable',
                    'type'    => 'checkbox',
                    'label'   => 'Enable Scan to Pay (QR)',
                    'default' => 'yes',
                ],
                'title' => [
                    'title'       => 'Title',
                    'type'        => 'text',
                    'description' => 'Payment method title the customer sees at checkout.',
                    'default'     => 'Scan to Pay (QR)',
                    'desc_tip'    => true,
                ],
                'description' => [
                    'title'       => 'Description',
                    'type'        => 'textarea',
                    'description' => 'Shown at checkout, above the QR code, when this method is selected.',
                    'default'     => 'Scan the QR code below with your banking, eSewa, or wallet app to complete payment. Enter your Order ID as the payment reference.',
                    'desc_tip'    => true,
                ],
                'instructions' => [
                    'title'       => 'Thank You / Email Instructions',
                    'type'        => 'textarea',
                    'description' => 'Shown on the order confirmation page and in the order email.',
                    'default'     => 'Thanks for your order! Please scan the QR code and complete payment. Your order will be processed once payment is confirmed.',
                    'desc_tip'    => true,
                ],
                'qr_image' => [
                    'title'       => 'QR Code Image',
                    'type'        => 'image',
                    'description' => 'The QR code customers scan to pay.',
                    'default'     => '',
                ],
            ];
        }

        // Custom "image" field type rendered in WooCommerce → Settings → Payments → Scan to Pay (QR)
        public function generate_image_html( $key, $data ) {
            $field_key = $this->get_field_key( $key );
            $image_id  = $this->get_option( $key );
            $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';

            ob_start();
            ?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="<?php echo esc_attr( $field_key ); ?>"><?php echo esc_html( $data['title'] ); ?></label>
                </th>
                <td class="forminp">
                    <img id="<?php echo esc_attr( $field_key ); ?>_preview"
                         src="<?php echo esc_url( $image_url ); ?>"
                         style="max-width:160px;display:<?php echo $image_url ? 'block' : 'none'; ?>;margin-bottom:8px;border:1px solid #ddd;">
                    <input type="hidden"
                           id="<?php echo esc_attr( $field_key ); ?>"
                           name="<?php echo esc_attr( $field_key ); ?>"
                           value="<?php echo esc_attr( $image_id ); ?>">
                    <p>
                        <button type="button" class="button stanray-qr-upload" data-target="<?php echo esc_attr( $field_key ); ?>"><?php esc_html_e( 'Choose QR Image', 'stanray-custom' ); ?></button>
                        <button type="button" class="button stanray-qr-remove" data-target="<?php echo esc_attr( $field_key ); ?>" style="<?php echo $image_url ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Remove', 'stanray-custom' ); ?></button>
                    </p>
                    <?php if ( ! empty( $data['description'] ) ) : ?>
                        <p class="description"><?php echo wp_kses_post( $data['description'] ); ?></p>
                    <?php endif; ?>
                </td>
            </tr>
            <?php
            return ob_get_clean();
        }

        // Renders under the radio button at checkout, like BACS instructions
        public function payment_fields() {
            if ( $this->description ) {
                echo wpautop( wptexturize( $this->description ) );
            }

            $qr_id = $this->get_option( 'qr_image' );
            if ( $qr_id ) {
                $img_url = wp_get_attachment_image_url( $qr_id, 'medium' );
                if ( $img_url ) {
                    echo '<img src="' . esc_url( $img_url ) . '" alt="' . esc_attr__( 'QR Code', 'stanray-custom' ) . '" style="max-width:220px;margin-top:10px;display:block;" />';
                }
            }
        }

        public function thankyou_page( $order_id ) {
            if ( $this->instructions ) {
                echo wpautop( wptexturize( $this->instructions ) );
            }
            $qr_id = $this->get_option( 'qr_image' );
            if ( $qr_id ) {
                $img_url = wp_get_attachment_image_url( $qr_id, 'medium' );
                if ( $img_url ) {
                    echo '<img src="' . esc_url( $img_url ) . '" alt="' . esc_attr__( 'QR Code', 'stanray-custom' ) . '" style="max-width:220px;margin-top:10px;display:block;" />';
                }
            }
        }

        public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
            if ( $sent_to_admin || ! $order->has_status( 'on-hold' ) || $order->get_payment_method() !== $this->id ) return;
            if ( $this->instructions ) {
                echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
            }
        }

        public function process_payment( $order_id ) {
            $order = wc_get_order( $order_id );

            if ( $order->get_total() > 0 ) {
                $order->update_status( 'on-hold', __( 'Awaiting QR payment confirmation.', 'stanray-custom' ) );
            } else {
                $order->payment_complete();
            }

            WC()->cart->empty_cart();

            return [
                'result'   => 'success',
                'redirect' => $this->get_return_url( $order ),
            ];
        }
    }
}

// Media uploader JS for the QR image field on the gateway settings page
add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( ! isset( $_GET['page'], $_GET['tab'], $_GET['section'] ) ) return;
    if ( $_GET['page'] !== 'wc-settings' || $_GET['tab'] !== 'checkout' || $_GET['section'] !== 'qr_payment' ) return;

    wp_enqueue_media();
    add_action( 'admin_footer', function () {
        ?>
        <script>
        jQuery(function ($) {
            var frame;
            $(document).on('click', '.stanray-qr-upload', function (e) {
                e.preventDefault();
                var button = $(this);
                var target = button.data('target');
                frame = wp.media({
                    title: <?php echo wp_json_encode( __( 'Select QR Code Image', 'stanray-custom' ) ); ?>,
                    multiple: false,
                    library: { type: 'image' }
                });
                frame.on('select', function () {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#' + target).val(attachment.id);
                    $('#' + target + '_preview').attr('src', attachment.url).show();
                    button.siblings('.stanray-qr-remove').show();
                });
                frame.open();
            });
            $(document).on('click', '.stanray-qr-remove', function (e) {
                e.preventDefault();
                var target = $(this).data('target');
                $('#' + target).val('');
                $('#' + target + '_preview').hide();
                $(this).hide();
            });
        });
        </script>
        <?php
    } );
} );
