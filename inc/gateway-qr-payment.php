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

        const PROOF_MAX_SIZE = 5242880; // 5MB
        const PROOF_MIMES    = [
            'jpg|jpeg' => 'image/jpeg',
            'png'      => 'image/png',
            'pdf'      => 'application/pdf',
        ];

        public function __construct() {
            $this->id                 = 'qr_payment';
            $this->icon               = '';
            $this->has_fields         = true;
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

            // The file itself is uploaded separately via AJAX (see
            // stanray_handle_qr_payment_proof_upload()) because WooCommerce's
            // checkout submission serializes the form with jQuery .serialize(),
            // which silently drops <input type="file"> values. Only the
            // resulting attachment ID (a plain hidden field) travels with the
            // actual "Place order" submission.
            ?>
            <p class="form-row form-row-wide qr-payment-proof-field" style="margin-top:12px;">
                <label for="qr_payment_proof">
                    <?php esc_html_e( 'Upload payment screenshot or PDF', 'stanray-custom' ); ?>&nbsp;<span class="required">*</span>
                </label>
                <input type="file" id="qr_payment_proof" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf" />
                <input type="hidden" id="qr_payment_proof_id" name="qr_payment_proof_id" value="" />
                <input type="hidden" id="qr_payment_proof_nonce" value="<?php echo esc_attr( wp_create_nonce( 'qr_payment_proof_upload' ) ); ?>" />
                <span id="qr_payment_proof_status" class="description" style="display:block;font-size:12px;margin-top:4px;">
                    <?php esc_html_e( 'JPG, PNG, or PDF. Max 5MB.', 'stanray-custom' ); ?>
                </span>
            </p>
            <?php
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

            $attachment_id = stanray_get_verified_qr_proof_attachment();
            if ( $attachment_id ) {
                update_post_meta( $attachment_id, '_qr_payment_order_id', $order_id );
                $order->update_meta_data( '_qr_payment_proof_id', $attachment_id );
                $order->save();
            }

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

// Ties an uploaded proof attachment to whoever uploaded it (guest session hash
// or logged-in user ID), so the checkout hidden field can't be tampered with
// to reference another customer's uploaded file.
function stanray_get_qr_payment_session_token() {
    if ( function_exists( 'WC' ) && WC()->session ) {
        $id = WC()->session->get_customer_id();
        if ( $id ) {
            return (string) $id;
        }
    }
    return (string) get_current_user_id();
}

// Reads & validates the attachment ID the checkout form submitted, confirming
// it was uploaded by the current shopper and isn't already tied to another order.
function stanray_get_verified_qr_proof_attachment() {
    $attachment_id = ! empty( $_POST['qr_payment_proof_id'] ) ? absint( $_POST['qr_payment_proof_id'] ) : 0;

    if ( ! $attachment_id || get_post_type( $attachment_id ) !== 'attachment' ) {
        return 0;
    }

    if ( get_post_meta( $attachment_id, '_qr_payment_session', true ) !== stanray_get_qr_payment_session_token() ) {
        return 0;
    }

    if ( get_post_meta( $attachment_id, '_qr_payment_order_id', true ) ) {
        return 0;
    }

    return $attachment_id;
}

// Handles the out-of-band upload triggered when the shopper picks a file.
// Registered for both logged-in and guest checkouts.
add_action( 'wp_ajax_stanray_qr_payment_proof_upload', 'stanray_handle_qr_payment_proof_upload' );
add_action( 'wp_ajax_nopriv_stanray_qr_payment_proof_upload', 'stanray_handle_qr_payment_proof_upload' );
function stanray_handle_qr_payment_proof_upload() {
    if ( ! check_ajax_referer( 'qr_payment_proof_upload', 'nonce', false ) ) {
        wp_send_json_error( [ 'message' => __( 'Security check failed. Please refresh the page and try again.', 'stanray-custom' ) ] );
    }

    if ( empty( $_FILES['file']['name'] ) ) {
        wp_send_json_error( [ 'message' => __( 'No file received.', 'stanray-custom' ) ] );
    }

    $file = $_FILES['file'];

    if ( ! empty( $file['error'] ) && $file['error'] !== UPLOAD_ERR_OK ) {
        wp_send_json_error( [ 'message' => __( 'Upload failed. Please try again.', 'stanray-custom' ) ] );
    }

    if ( $file['size'] > WC_Gateway_QR_Payment::PROOF_MAX_SIZE ) {
        wp_send_json_error( [ 'message' => __( 'File is too large. Maximum size is 5MB.', 'stanray-custom' ) ] );
    }

    // Checks the real file content (not just the extension/declared type), so a
    // renamed .php or .exe can't slip through as a "PNG".
    $filetype = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], WC_Gateway_QR_Payment::PROOF_MIMES );
    if ( empty( $filetype['ext'] ) || empty( $filetype['type'] ) ) {
        wp_send_json_error( [ 'message' => __( 'File must be a JPG, PNG, or PDF.', 'stanray-custom' ) ] );
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $attachment_id = media_handle_upload( 'file', 0, [], [
        'test_form' => false,
        'mimes'     => WC_Gateway_QR_Payment::PROOF_MIMES,
    ] );

    if ( is_wp_error( $attachment_id ) ) {
        wp_send_json_error( [ 'message' => __( 'Upload failed. Please try again.', 'stanray-custom' ) ] );
    }

    update_post_meta( $attachment_id, '_qr_payment_session', stanray_get_qr_payment_session_token() );

    wp_send_json_success( [ 'attachment_id' => $attachment_id ] );
}

// Wires up the file input on the checkout page to upload immediately on
// selection (see comment in payment_fields() for why this can't just ride
// along with the normal "Place order" submission).
add_action( 'wp_enqueue_scripts', 'stanray_enqueue_qr_payment_proof_script', 20 );
function stanray_enqueue_qr_payment_proof_script() {
    if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) return;

    wp_enqueue_script( 'jquery' );
    wp_add_inline_script( 'jquery', stanray_qr_payment_proof_upload_js() );
}

function stanray_qr_payment_proof_upload_js() {
    ob_start();
    ?>
    jQuery(function ($) {
        $(document.body).on('change', '#qr_payment_proof', function () {
            var input = this;
            var $status = $('#qr_payment_proof_status');
            var $hiddenId = $('#qr_payment_proof_id');
            $hiddenId.val('');

            if (!input.files || !input.files[0]) {
                $status.text(<?php echo wp_json_encode( __( 'JPG, PNG, or PDF. Max 5MB.', 'stanray-custom' ) ); ?>);
                return;
            }

            var file = input.files[0];
            if (file.size > <?php echo (int) WC_Gateway_QR_Payment::PROOF_MAX_SIZE; ?>) {
                $status.text(<?php echo wp_json_encode( __( 'File is too large. Maximum size is 5MB.', 'stanray-custom' ) ); ?>);
                input.value = '';
                return;
            }

            var formData = new FormData();
            formData.append('action', 'stanray_qr_payment_proof_upload');
            formData.append('nonce', $('#qr_payment_proof_nonce').val());
            formData.append('file', file);

            $status.text(<?php echo wp_json_encode( __( 'Uploading…', 'stanray-custom' ) ); ?>);

            $.ajax({
                url: <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false
            }).done(function (response) {
                if (response && response.success) {
                    $hiddenId.val(response.data.attachment_id);
                    $status.text(<?php echo wp_json_encode( __( 'Uploaded:', 'stanray-custom' ) ); ?> + ' ' + file.name);
                } else {
                    var msg = (response && response.data && response.data.message) ? response.data.message : <?php echo wp_json_encode( __( 'Upload failed. Please try again.', 'stanray-custom' ) ); ?>;
                    $status.text(msg);
                    input.value = '';
                }
            }).fail(function () {
                $status.text(<?php echo wp_json_encode( __( 'Upload failed. Please try again.', 'stanray-custom' ) ); ?>);
                input.value = '';
            });
        });
    });
    <?php
    return ob_get_clean();
}

// Require + validate the proof upload before the order is allowed to place.
// By this point the file itself is already uploaded (see
// stanray_handle_qr_payment_proof_upload()); this just confirms checkout
// received a valid, unclaimed attachment ID that belongs to this shopper.
add_action( 'woocommerce_checkout_process', 'stanray_validate_qr_payment_proof' );
function stanray_validate_qr_payment_proof() {
    if ( empty( $_POST['payment_method'] ) || $_POST['payment_method'] !== 'qr_payment' ) {
        return;
    }

    if ( ! stanray_get_verified_qr_proof_attachment() ) {
        wc_add_notice( __( 'Please upload a screenshot or PDF of your payment confirmation.', 'stanray-custom' ), 'error' );
    }
}

// Shows the uploaded proof on the admin order edit screen so staff can verify
// it before moving the order from "on-hold" to "processing".
add_action( 'woocommerce_admin_order_data_after_billing_address', 'stanray_display_qr_payment_proof' );
function stanray_display_qr_payment_proof( $order ) {
    if ( $order->get_payment_method() !== 'qr_payment' ) return;

    $attachment_id = $order->get_meta( '_qr_payment_proof_id' );
    if ( ! $attachment_id ) return;

    $url = wp_get_attachment_url( $attachment_id );
    if ( ! $url ) return;

    $mime = get_post_mime_type( $attachment_id );

    echo '<p class="form-field"><strong>' . esc_html__( 'Payment Proof:', 'stanray-custom' ) . '</strong><br>';
    if ( strpos( (string) $mime, 'image/' ) === 0 ) {
        $thumb_url = wp_get_attachment_image_url( $attachment_id, 'medium' );
        echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer"><img src="' . esc_url( $thumb_url ) . '" style="max-width:200px;display:block;margin-top:6px;border:1px solid #ddd;" /></a>';
    } else {
        echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'View PDF', 'stanray-custom' ) . '</a>';
    }
    echo '</p>';
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
