<?php
/**
 * Dashboard admin page: "Shop the Look" homepage section
 * Lets an editor set the banner image and up to 5 hotspot
 * products (with pin position) from the wp-admin sidebar.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'STANRAY_SB_MAX_HOTSPOTS', 5 );

function stanray_sb_admin_menu() {
    add_submenu_page(
        'stanray-hero-banner',
        __( 'Shop the Look', 'stanray-custom' ),
        __( 'Shop the Look', 'stanray-custom' ),
        'manage_options',
        'stanray-shop-the-look',
        'stanray_sb_admin_page'
    );
}
add_action( 'admin_menu', 'stanray_sb_admin_menu' );

function stanray_sb_admin_enqueue( $hook ) {
    if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'stanray-shop-the-look' ) return;
    wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'stanray_sb_admin_enqueue' );

function stanray_sb_get_product_choices() {
    $choices = [ '' => __( '— None —', 'stanray-custom' ) ];

    if ( ! function_exists( 'wc_get_products' ) ) {
        return $choices;
    }

    $products = wc_get_products( [
        'status'  => 'publish',
        'limit'   => -1,
        'orderby' => 'title',
        'order'   => 'ASC',
    ] );

    foreach ( $products as $product ) {
        $choices[ (string) $product->get_id() ] = $product->get_name();
    }

    return $choices;
}

function stanray_sb_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $saved = false;

    if ( isset( $_POST['stanray_sb_save'] ) && check_admin_referer( 'stanray_sb_save_action', 'stanray_sb_nonce' ) ) {
        update_option( 'stanray_sb_eyebrow', sanitize_text_field( $_POST['stanray_sb_eyebrow'] ?? '' ) );
        update_option( 'stanray_sb_title', sanitize_text_field( $_POST['stanray_sb_title'] ?? '' ) );
        update_option( 'stanray_sb_subtitle', sanitize_text_field( $_POST['stanray_sb_subtitle'] ?? '' ) );
        update_option( 'stanray_sb_banner_image', absint( $_POST['stanray_sb_banner_image'] ?? 0 ) );

        $hotspots     = [];
        $post_product = $_POST['stanray_sb_hotspot_product'] ?? [];
        $post_top     = $_POST['stanray_sb_hotspot_top'] ?? [];
        $post_left    = $_POST['stanray_sb_hotspot_left'] ?? [];

        for ( $i = 0; $i < STANRAY_SB_MAX_HOTSPOTS; $i++ ) {
            $product_id = absint( $post_product[ $i ] ?? 0 );
            if ( ! $product_id ) continue;

            $hotspots[] = [
                'product_id' => $product_id,
                'top'        => max( 0, min( 100, (int) ( $post_top[ $i ] ?? 50 ) ) ),
                'left'       => max( 0, min( 100, (int) ( $post_left[ $i ] ?? 50 ) ) ),
            ];
        }
        update_option( 'stanray_sb_hotspots', $hotspots );

        $saved = true;
    }

    $eyebrow      = get_option( 'stanray_sb_eyebrow', 'Shop the Look' );
    $title        = get_option( 'stanray_sb_title', 'Styled by the Street.' );
    $subtitle     = get_option( 'stanray_sb_subtitle', 'Click a hotspot to explore the piece.' );
    $image_id     = get_option( 'stanray_sb_banner_image', 0 );
    $image_url    = $image_id ? wp_get_attachment_image_url( $image_id, 'large' ) : '';
    $hotspots     = get_option( 'stanray_sb_hotspots', [
        [ 'product_id' => 157, 'top' => 78, 'left' => 60 ],
        [ 'product_id' => 41,  'top' => 85, 'left' => 80 ],
        [ 'product_id' => 155, 'top' => 78, 'left' => 40 ],
    ] );
    $choices = stanray_sb_get_product_choices();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Shop the Look — Homepage Section', 'stanray-custom' ); ?></h1>
        <p><?php esc_html_e( 'This controls the "Styled by the Street" shoppable banner on the homepage: the banner image, and the clickable hotspot pins linked to products.', 'stanray-custom' ); ?></p>

        <?php if ( $saved ) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Saved.', 'stanray-custom' ); ?></p></div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field( 'stanray_sb_save_action', 'stanray_sb_nonce' ); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="stanray_sb_eyebrow"><?php esc_html_e( 'Eyebrow Label', 'stanray-custom' ); ?></label></th>
                    <td><input type="text" id="stanray_sb_eyebrow" name="stanray_sb_eyebrow" value="<?php echo esc_attr( $eyebrow ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="stanray_sb_title"><?php esc_html_e( 'Title', 'stanray-custom' ); ?></label></th>
                    <td><input type="text" id="stanray_sb_title" name="stanray_sb_title" value="<?php echo esc_attr( $title ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="stanray_sb_subtitle"><?php esc_html_e( 'Subtitle', 'stanray-custom' ); ?></label></th>
                    <td><input type="text" id="stanray_sb_subtitle" name="stanray_sb_subtitle" value="<?php echo esc_attr( $subtitle ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Banner Image', 'stanray-custom' ); ?></th>
                    <td>
                        <img id="stanray_sb_banner_preview" src="<?php echo esc_url( $image_url ); ?>" style="max-width:320px;display:<?php echo $image_url ? 'block' : 'none'; ?>;margin-bottom:10px;border:1px solid #ddd;">
                        <input type="hidden" id="stanray_sb_banner_image" name="stanray_sb_banner_image" value="<?php echo esc_attr( $image_id ); ?>">
                        <p>
                            <button type="button" class="button" id="stanray_sb_banner_upload"><?php esc_html_e( 'Choose Image', 'stanray-custom' ); ?></button>
                            <button type="button" class="button" id="stanray_sb_banner_remove" style="<?php echo $image_id ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Remove', 'stanray-custom' ); ?></button>
                        </p>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Hotspot Pins', 'stanray-custom' ); ?></h2>
            <p><?php esc_html_e( 'Pick a product for each pin. Position is a percentage from the top-left of the banner image (0–100) — adjust and re-check the homepage until the pin sits on the right spot. Leave "None" to skip a pin.', 'stanray-custom' ); ?></p>

            <table class="widefat" style="max-width:800px;">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Pin', 'stanray-custom' ); ?></th>
                        <th><?php esc_html_e( 'Product', 'stanray-custom' ); ?></th>
                        <th><?php esc_html_e( 'Top %', 'stanray-custom' ); ?></th>
                        <th><?php esc_html_e( 'Left %', 'stanray-custom' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ( $i = 0; $i < STANRAY_SB_MAX_HOTSPOTS; $i++ ) :
                        $row = $hotspots[ $i ] ?? [ 'product_id' => '', 'top' => 50, 'left' => 50 ];
                    ?>
                    <tr>
                        <td><?php echo (int) ( $i + 1 ); ?></td>
                        <td>
                            <select name="stanray_sb_hotspot_product[<?php echo $i; ?>]">
                                <?php foreach ( $choices as $id => $name ) : ?>
                                    <option value="<?php echo esc_attr( $id ); ?>" <?php selected( (string) $row['product_id'], (string) $id ); ?>><?php echo esc_html( $name ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="number" min="0" max="100" name="stanray_sb_hotspot_top[<?php echo $i; ?>]" value="<?php echo esc_attr( $row['top'] ); ?>" style="width:70px;"></td>
                        <td><input type="number" min="0" max="100" name="stanray_sb_hotspot_left[<?php echo $i; ?>]" value="<?php echo esc_attr( $row['left'] ); ?>" style="width:70px;"></td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>

            <?php submit_button( __( 'Save Changes', 'stanray-custom' ), 'primary', 'stanray_sb_save' ); ?>
        </form>
    </div>
    <script>
    jQuery(function ($) {
        var frame;
        $('#stanray_sb_banner_upload').on('click', function (e) {
            e.preventDefault();
            if (frame) { frame.open(); return; }
            frame = wp.media({
                title: <?php echo wp_json_encode( __( 'Select Banner Image', 'stanray-custom' ) ); ?>,
                multiple: false,
                library: { type: 'image' }
            });
            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#stanray_sb_banner_image').val(attachment.id);
                $('#stanray_sb_banner_preview').attr('src', attachment.url).show();
                $('#stanray_sb_banner_remove').show();
            });
            frame.open();
        });
        $('#stanray_sb_banner_remove').on('click', function (e) {
            e.preventDefault();
            $('#stanray_sb_banner_image').val('');
            $('#stanray_sb_banner_preview').hide();
            $(this).hide();
        });
    });
    </script>
    <?php
}
