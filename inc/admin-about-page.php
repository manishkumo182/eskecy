<?php
/**
 * Dashboard admin page: About Us page (about.php)
 * Lets an editor change the eyebrow tag, heading, description, and the
 * 5 editorial images from the wp-admin sidebar instead of editing
 * about.php directly.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function stanray_about_admin_menu() {
    add_submenu_page(
        'stanray-hero-banner',
        __( 'About Page', 'stanray-custom' ),
        __( 'About Page', 'stanray-custom' ),
        'manage_options',
        'stanray-about-page',
        'stanray_about_admin_page'
    );
}
add_action( 'admin_menu', 'stanray_about_admin_menu' );

function stanray_about_admin_enqueue() {
    if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'stanray-about-page' ) return;
    wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'stanray_about_admin_enqueue' );

function stanray_about_image_field( $key, $label, $desc = '' ) {
    $id  = get_option( $key, 0 );
    $url = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
    ?>
    <tr>
        <th scope="row"><?php echo esc_html( $label ); ?></th>
        <td>
            <img id="<?php echo esc_attr( $key ); ?>_preview" src="<?php echo esc_url( $url ); ?>" style="max-width:220px;display:<?php echo $url ? 'block' : 'none'; ?>;margin-bottom:10px;border:1px solid #ddd;">
            <input type="hidden" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $id ); ?>">
            <p>
                <button type="button" class="button stanray-about-upload" data-field="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Choose Image', 'stanray-custom' ); ?></button>
                <button type="button" class="button stanray-about-remove" data-field="<?php echo esc_attr( $key ); ?>" style="<?php echo $url ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Remove', 'stanray-custom' ); ?></button>
            </p>
            <?php if ( $desc ) : ?><p class="description"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
        </td>
    </tr>
    <?php
}

function stanray_about_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $saved = false;

    if ( isset( $_POST['stanray_about_save'] ) && check_admin_referer( 'stanray_about_save_action', 'stanray_about_nonce' ) ) {
        update_option( 'stanray_about_tag',   sanitize_text_field( $_POST['stanray_about_tag'] ?? '' ) );
        update_option( 'stanray_about_title', sanitize_text_field( $_POST['stanray_about_title'] ?? '' ) );
        update_option( 'stanray_about_desc',  sanitize_textarea_field( $_POST['stanray_about_desc'] ?? '' ) );

        foreach ( [ 'stanray_about_img_big', 'stanray_about_img_1', 'stanray_about_img_2', 'stanray_about_img_3', 'stanray_about_img_4' ] as $key ) {
            update_option( $key, absint( $_POST[ $key ] ?? 0 ) );
        }

        $saved = true;
    }

    $v = function ( $key, $default = '' ) {
        return get_option( $key, $default );
    };
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'About Page', 'stanray-custom' ); ?></h1>
        <p><?php esc_html_e( 'This controls the content on the About Us page: the eyebrow tag, heading, description, and the 5 editorial images (1 large + 4 small).', 'stanray-custom' ); ?></p>

        <?php if ( $saved ) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Saved.', 'stanray-custom' ); ?></p></div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field( 'stanray_about_save_action', 'stanray_about_nonce' ); ?>

            <h2><?php esc_html_e( 'Header', 'stanray-custom' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="stanray_about_tag"><?php esc_html_e( 'Eyebrow Tag', 'stanray-custom' ); ?></label></th>
                    <td><input type="text" id="stanray_about_tag" name="stanray_about_tag" value="<?php echo esc_attr( $v( 'stanray_about_tag', 'RECAP' ) ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="stanray_about_title"><?php esc_html_e( 'Heading', 'stanray-custom' ); ?></label></th>
                    <td><input type="text" id="stanray_about_title" name="stanray_about_title" value="<?php echo esc_attr( $v( 'stanray_about_title', 'ESKECY 2025–2026' ) ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="stanray_about_desc"><?php esc_html_e( 'Description', 'stanray-custom' ); ?></label></th>
                    <td><textarea id="stanray_about_desc" name="stanray_about_desc" rows="3" class="large-text"><?php echo esc_textarea( $v( 'stanray_about_desc', 'Explore our seasonal editorial featuring the latest trends and styles.' ) ); ?></textarea></td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Images', 'stanray-custom' ); ?></h2>
            <table class="form-table" role="presentation">
                <?php
                stanray_about_image_field( 'stanray_about_img_big', __( 'Large Image', 'stanray-custom' ), __( 'The big featured image on the left.', 'stanray-custom' ) );
                stanray_about_image_field( 'stanray_about_img_1', __( 'Small Image 1', 'stanray-custom' ) );
                stanray_about_image_field( 'stanray_about_img_2', __( 'Small Image 2', 'stanray-custom' ) );
                stanray_about_image_field( 'stanray_about_img_3', __( 'Small Image 3', 'stanray-custom' ) );
                stanray_about_image_field( 'stanray_about_img_4', __( 'Small Image 4', 'stanray-custom' ) );
                ?>
            </table>

            <?php submit_button( __( 'Save Changes', 'stanray-custom' ), 'primary', 'stanray_about_save' ); ?>
        </form>
    </div>
    <script>
    jQuery(function ($) {
        var frame;
        $('.stanray-about-upload').on('click', function (e) {
            e.preventDefault();
            var field = $(this).data('field');
            frame = wp.media({
                title: <?php echo wp_json_encode( __( 'Select Image', 'stanray-custom' ) ); ?>,
                multiple: false,
                library: { type: 'image' }
            });
            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#' + field).val(attachment.id);
                $('#' + field + '_preview').attr('src', attachment.url).show();
                $('.stanray-about-remove[data-field="' + field + '"]').show();
            });
            frame.open();
        });
        $('.stanray-about-remove').on('click', function (e) {
            e.preventDefault();
            var field = $(this).data('field');
            $('#' + field).val('');
            $('#' + field + '_preview').hide();
            $(this).hide();
        });
    });
    </script>
    <?php
}
