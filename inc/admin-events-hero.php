<?php
/**
 * Dashboard admin page: Events Page Hero ("In Concert" banner at the top
 * of /events/). Lets an editor change the background video, eyebrow,
 * headline, subtext, and CTA button from wp-admin instead of editing
 * archive-video_gallery.php directly — same pattern as the other
 * Homepage Sections admin pages (inc/admin-hero-banner.php etc.).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function stanray_eh_admin_menu() {
    add_submenu_page(
        'stanray-hero-banner',
        __( 'Events Hero', 'stanray-custom' ),
        __( 'Events Hero', 'stanray-custom' ),
        'manage_options',
        'stanray-events-hero',
        'stanray_eh_admin_page'
    );
}
add_action( 'admin_menu', 'stanray_eh_admin_menu' );

function stanray_eh_admin_enqueue() {
    if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'stanray-events-hero' ) return;
    wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'stanray_eh_admin_enqueue' );

function stanray_eh_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $saved = false;

    if ( isset( $_POST['stanray_eh_save'] ) && check_admin_referer( 'stanray_eh_save_action', 'stanray_eh_nonce' ) ) {
        update_option( 'stanray_eh_video_id', absint( $_POST['stanray_eh_video_id'] ?? 0 ) );
        update_option( 'stanray_eh_eyebrow', sanitize_text_field( $_POST['stanray_eh_eyebrow'] ?? '' ) );
        update_option( 'stanray_eh_headline', sanitize_text_field( $_POST['stanray_eh_headline'] ?? '' ) );
        update_option( 'stanray_eh_subtext', sanitize_textarea_field( $_POST['stanray_eh_subtext'] ?? '' ) );
        update_option( 'stanray_eh_cta_text', sanitize_text_field( $_POST['stanray_eh_cta_text'] ?? '' ) );
        update_option( 'stanray_eh_cta_link', esc_url_raw( $_POST['stanray_eh_cta_link'] ?? '' ) );
        $saved = true;
    }

    $v = function ( $key, $default = '' ) {
        return get_option( $key, $default );
    };

    $video_id  = $v( 'stanray_eh_video_id', 0 );
    $video_url = $video_id ? wp_get_attachment_url( $video_id ) : '';
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Events Hero — Top of /events/', 'stanray-custom' ); ?></h1>
        <p><?php esc_html_e( 'This controls the "In Concert" video banner at the top of the Events page.', 'stanray-custom' ); ?></p>

        <?php if ( $saved ) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Saved.', 'stanray-custom' ); ?></p></div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field( 'stanray_eh_save_action', 'stanray_eh_nonce' ); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Background Video', 'stanray-custom' ); ?></th>
                    <td>
                        <video id="stanray_eh_video_preview" src="<?php echo esc_url( $video_url ); ?>" controls style="max-width:360px;display:<?php echo $video_url ? 'block' : 'none'; ?>;margin-bottom:10px;background:#000;"></video>
                        <input type="hidden" id="stanray_eh_video_id" name="stanray_eh_video_id" value="<?php echo esc_attr( $video_id ); ?>">
                        <p>
                            <button type="button" class="button" id="stanray_eh_video_upload"><?php esc_html_e( 'Choose Video', 'stanray-custom' ); ?></button>
                            <button type="button" class="button" id="stanray_eh_video_remove" style="<?php echo $video_url ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Remove', 'stanray-custom' ); ?></button>
                        </p>
                        <p class="description"><?php esc_html_e( 'MP4 recommended. Falls back to the theme\'s default video if none is set.', 'stanray-custom' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="stanray_eh_eyebrow"><?php esc_html_e( 'Eyebrow', 'stanray-custom' ); ?></label></th>
                    <td><input type="text" id="stanray_eh_eyebrow" name="stanray_eh_eyebrow" value="<?php echo esc_attr( $v( 'stanray_eh_eyebrow', 'Live · Events' ) ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="stanray_eh_headline"><?php esc_html_e( 'Headline', 'stanray-custom' ); ?></label></th>
                    <td><input type="text" id="stanray_eh_headline" name="stanray_eh_headline" value="<?php echo esc_attr( $v( 'stanray_eh_headline', 'In Concert' ) ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="stanray_eh_subtext"><?php esc_html_e( 'Subtext', 'stanray-custom' ); ?></label></th>
                    <td><textarea id="stanray_eh_subtext" name="stanray_eh_subtext" rows="2" class="large-text"><?php echo esc_textarea( $v( 'stanray_eh_subtext', 'Catch every show, every stage, every moment — live and on video.' ) ); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="stanray_eh_cta_text"><?php esc_html_e( 'Button Text', 'stanray-custom' ); ?></label></th>
                    <td><input type="text" id="stanray_eh_cta_text" name="stanray_eh_cta_text" value="<?php echo esc_attr( $v( 'stanray_eh_cta_text', 'New Dates Added' ) ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="stanray_eh_cta_link"><?php esc_html_e( 'Button Link', 'stanray-custom' ); ?></label></th>
                    <td>
                        <input type="text" id="stanray_eh_cta_link" name="stanray_eh_cta_link" value="<?php echo esc_attr( $v( 'stanray_eh_cta_link', '#tour-dates' ) ); ?>" class="regular-text" placeholder="#tour-dates">
                        <p class="description"><?php esc_html_e( 'An in-page anchor like #tour-dates (jumps to the Tour Dates section) or a full URL.', 'stanray-custom' ); ?></p>
                    </td>
                </tr>
            </table>

            <?php submit_button( __( 'Save Changes', 'stanray-custom' ), 'primary', 'stanray_eh_save' ); ?>
        </form>
    </div>
    <script>
    jQuery(function ($) {
        var frame;
        $('#stanray_eh_video_upload').on('click', function (e) {
            e.preventDefault();
            if (frame) { frame.open(); return; }
            frame = wp.media({
                title: <?php echo wp_json_encode( __( 'Select Video', 'stanray-custom' ) ); ?>,
                multiple: false,
                library: { type: 'video' }
            });
            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#stanray_eh_video_id').val(attachment.id);
                $('#stanray_eh_video_preview').attr('src', attachment.url).show();
                $('#stanray_eh_video_remove').show();
            });
            frame.open();
        });
        $('#stanray_eh_video_remove').on('click', function (e) {
            e.preventDefault();
            $('#stanray_eh_video_id').val('');
            $('#stanray_eh_video_preview').hide();
            $(this).hide();
        });
    });
    </script>
    <?php
}
