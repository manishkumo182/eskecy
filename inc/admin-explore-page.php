<?php
/**
 * Dashboard admin page: "Explore" page
 * Lets an editor set the entrance image, the studio room image, and
 * up to 5 hotspot products (with pin position) from the wp-admin
 * sidebar — same pattern as the "Shop the Look" homepage section.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'STANRAY_EXPLORE_MAX_HOTSPOTS', 5 );

function stanray_explore_admin_menu() {
    add_submenu_page(
        'stanray-hero-banner',
        __( 'Explore Page', 'stanray-custom' ),
        __( 'Explore Page', 'stanray-custom' ),
        'manage_options',
        'stanray-explore-page',
        'stanray_explore_admin_page'
    );
}
add_action( 'admin_menu', 'stanray_explore_admin_menu' );

function stanray_explore_admin_enqueue() {
    if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'stanray-explore-page' ) return;
    wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'stanray_explore_admin_enqueue' );

function stanray_explore_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $saved = false;

    if ( isset( $_POST['stanray_explore_save'] ) && check_admin_referer( 'stanray_explore_save_action', 'stanray_explore_nonce' ) ) {
        update_option( 'stanray_explore_entrance_image', absint( $_POST['stanray_explore_entrance_image'] ?? 0 ) );
        update_option( 'stanray_explore_studio_image', absint( $_POST['stanray_explore_studio_image'] ?? 0 ) );

        $hotspots     = [];
        $post_product = $_POST['stanray_explore_hotspot_product'] ?? [];
        $post_top     = $_POST['stanray_explore_hotspot_top'] ?? [];
        $post_left    = $_POST['stanray_explore_hotspot_left'] ?? [];

        for ( $i = 0; $i < STANRAY_EXPLORE_MAX_HOTSPOTS; $i++ ) {
            $product_id = absint( $post_product[ $i ] ?? 0 );
            if ( ! $product_id ) continue;

            $hotspots[] = [
                'product_id' => $product_id,
                'top'        => max( 0, min( 100, (int) ( $post_top[ $i ] ?? 50 ) ) ),
                'left'       => max( 0, min( 100, (int) ( $post_left[ $i ] ?? 50 ) ) ),
            ];
        }
        update_option( 'stanray_explore_hotspots', $hotspots );

        $saved = true;
    }

    $entrance_id  = get_option( 'stanray_explore_entrance_image', 0 );
    $entrance_url = $entrance_id ? wp_get_attachment_image_url( $entrance_id, 'large' ) : get_template_directory_uri() . '/assets/images/explore-entrance.jpg';

    $studio_id  = get_option( 'stanray_explore_studio_image', 0 );
    $studio_url = $studio_id ? wp_get_attachment_image_url( $studio_id, 'large' ) : get_template_directory_uri() . '/assets/images/banner.png';

    $hotspots = get_option( 'stanray_explore_hotspots', [
        [ 'product_id' => 321, 'top' => 78, 'left' => 40 ],
        [ 'product_id' => 52,  'top' => 78, 'left' => 60 ],
        [ 'product_id' => 41,  'top' => 85, 'left' => 80 ],
    ] );

    $choices = function_exists( 'stanray_sb_get_product_choices' ) ? stanray_sb_get_product_choices() : [ '' => __( '— None —', 'stanray-custom' ) ];
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Explore Page', 'stanray-custom' ); ?></h1>
        <p><?php esc_html_e( 'This controls the "Explore" and "Studio" pages: the dusk entrance image on Explore (click the door to enter), the studio room image on Studio, and its clickable hotspot pins linked to products.', 'stanray-custom' ); ?></p>

        <?php if ( $saved ) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Saved.', 'stanray-custom' ); ?></p></div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field( 'stanray_explore_save_action', 'stanray_explore_nonce' ); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Entrance Image', 'stanray-custom' ); ?></th>
                    <td>
                        <img id="stanray_explore_entrance_preview" src="<?php echo esc_url( $entrance_url ); ?>" style="max-width:320px;display:<?php echo $entrance_url ? 'block' : 'none'; ?>;margin-bottom:10px;border:1px solid #ddd;">
                        <input type="hidden" id="stanray_explore_entrance_image" name="stanray_explore_entrance_image" value="<?php echo esc_attr( $entrance_id ); ?>">
                        <p>
                            <button type="button" class="button" id="stanray_explore_entrance_upload"><?php esc_html_e( 'Choose Image', 'stanray-custom' ); ?></button>
                            <button type="button" class="button" id="stanray_explore_entrance_remove" style="<?php echo $entrance_id ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Reset to Default', 'stanray-custom' ); ?></button>
                        </p>
                        <p class="description"><?php esc_html_e( 'The door glow and "Click to Enter" prompt are positioned at 58% from the left, ~50% from the top of this image — use a similar composition (door roughly centered-right) so the glow still lines up.', 'stanray-custom' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Studio Room Image', 'stanray-custom' ); ?></th>
                    <td>
                        <img id="stanray_explore_studio_preview" src="<?php echo esc_url( $studio_url ); ?>" style="max-width:320px;display:<?php echo $studio_url ? 'block' : 'none'; ?>;margin-bottom:10px;border:1px solid #ddd;">
                        <input type="hidden" id="stanray_explore_studio_image" name="stanray_explore_studio_image" value="<?php echo esc_attr( $studio_id ); ?>">
                        <p>
                            <button type="button" class="button" id="stanray_explore_studio_upload"><?php esc_html_e( 'Choose Image', 'stanray-custom' ); ?></button>
                            <button type="button" class="button" id="stanray_explore_studio_remove" style="<?php echo $studio_id ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Reset to Default', 'stanray-custom' ); ?></button>
                        </p>
                        <p class="description"><?php esc_html_e( 'Shown after the door is clicked. This is independent of the "Shop the Look" homepage banner — changing one does not affect the other.', 'stanray-custom' ); ?></p>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Hotspot Pins', 'stanray-custom' ); ?></h2>
            <p><?php esc_html_e( 'Pick a product for each pin on the studio room image. Position is a percentage from the top-left of the image (0–100) — adjust and re-check the Explore page until the pin sits on the right spot. Leave "None" to skip a pin.', 'stanray-custom' ); ?></p>
            <p class="description"><?php esc_html_e( 'Variable products (with size/color options) link out to "Select Options" on the product page; simple products get an instant "Add to Bag" on the card itself.', 'stanray-custom' ); ?></p>

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
                    <?php for ( $i = 0; $i < STANRAY_EXPLORE_MAX_HOTSPOTS; $i++ ) :
                        $row = $hotspots[ $i ] ?? [ 'product_id' => '', 'top' => 50, 'left' => 50 ];
                    ?>
                    <tr>
                        <td><?php echo (int) ( $i + 1 ); ?></td>
                        <td>
                            <select name="stanray_explore_hotspot_product[<?php echo $i; ?>]">
                                <?php foreach ( $choices as $id => $name ) : ?>
                                    <option value="<?php echo esc_attr( $id ); ?>" <?php selected( (string) $row['product_id'], (string) $id ); ?>><?php echo esc_html( $name ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="number" min="0" max="100" name="stanray_explore_hotspot_top[<?php echo $i; ?>]" value="<?php echo esc_attr( $row['top'] ); ?>" style="width:70px;"></td>
                        <td><input type="number" min="0" max="100" name="stanray_explore_hotspot_left[<?php echo $i; ?>]" value="<?php echo esc_attr( $row['left'] ); ?>" style="width:70px;"></td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>

            <?php submit_button( __( 'Save Changes', 'stanray-custom' ), 'primary', 'stanray_explore_save' ); ?>
        </form>
    </div>
    <script>
    jQuery(function ($) {
        function wireImagePicker(fieldKey, defaultUrl) {
            var frame;
            var $input   = $('#stanray_explore_' + fieldKey + '_image');
            var $preview = $('#stanray_explore_' + fieldKey + '_preview');
            var $upload  = $('#stanray_explore_' + fieldKey + '_upload');
            var $remove  = $('#stanray_explore_' + fieldKey + '_remove');

            $upload.on('click', function (e) {
                e.preventDefault();
                if (frame) { frame.open(); return; }
                frame = wp.media({
                    title: <?php echo wp_json_encode( __( 'Select Image', 'stanray-custom' ) ); ?>,
                    multiple: false,
                    library: { type: 'image' }
                });
                frame.on('select', function () {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $input.val(attachment.id);
                    $preview.attr('src', attachment.url).show();
                    $remove.show();
                });
                frame.open();
            });

            $remove.on('click', function (e) {
                e.preventDefault();
                $input.val('');
                $preview.attr('src', defaultUrl).show();
                $(this).hide();
            });
        }

        wireImagePicker('entrance', <?php echo wp_json_encode( get_template_directory_uri() . '/assets/images/explore-entrance.jpg' ); ?>);
        wireImagePicker('studio', <?php echo wp_json_encode( get_template_directory_uri() . '/assets/images/banner.png' ); ?>);
    });
    </script>
    <?php
}
