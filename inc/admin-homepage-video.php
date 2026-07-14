<?php
/**
 * Dashboard admin page: Homepage Video section (before the footer)
 * Lets an editor upload/pick the background video from the Media
 * Library, instead of it being a hardcoded file in the theme.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function stanray_hv_admin_menu() {
    add_submenu_page(
        'stanray-hero-banner',
        __( 'Homepage Video', 'stanray-custom' ),
        __( 'Homepage Video', 'stanray-custom' ),
        'manage_options',
        'stanray-homepage-video',
        'stanray_hv_admin_page'
    );
}
add_action( 'admin_menu', 'stanray_hv_admin_menu' );

function stanray_hv_admin_enqueue() {
    if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'stanray-homepage-video' ) return;
    wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'stanray_hv_admin_enqueue' );

function stanray_hv_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $saved = false;

    if ( isset( $_POST['stanray_hv_save'] ) && check_admin_referer( 'stanray_hv_save_action', 'stanray_hv_nonce' ) ) {
        update_option( 'stanray_hv_video_id', absint( $_POST['stanray_hv_video_id'] ?? 0 ) );
        update_option( 'stanray_hv_poster_id', absint( $_POST['stanray_hv_poster_id'] ?? 0 ) );
        $saved = true;
    }

    $video_id  = get_option( 'stanray_hv_video_id', 0 );
    $video_url = $video_id ? wp_get_attachment_url( $video_id ) : '';
    $poster_id  = get_option( 'stanray_hv_poster_id', 0 );
    $poster_url = $poster_id ? wp_get_attachment_image_url( $poster_id, 'large' ) : '';
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Homepage Video — Section Before Footer', 'stanray-custom' ); ?></h1>
        <p><?php esc_html_e( 'This controls the autoplaying video section at the bottom of the homepage, just above the footer.', 'stanray-custom' ); ?></p>

        <?php if ( $saved ) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Saved.', 'stanray-custom' ); ?></p></div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field( 'stanray_hv_save_action', 'stanray_hv_nonce' ); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Video File', 'stanray-custom' ); ?></th>
                    <td>
                        <video id="stanray_hv_video_preview" src="<?php echo esc_url( $video_url ); ?>" controls style="max-width:360px;display:<?php echo $video_url ? 'block' : 'none'; ?>;margin-bottom:10px;background:#000;"></video>
                        <input type="hidden" id="stanray_hv_video_id" name="stanray_hv_video_id" value="<?php echo esc_attr( $video_id ); ?>">
                        <p>
                            <button type="button" class="button" id="stanray_hv_video_upload"><?php esc_html_e( 'Choose Video', 'stanray-custom' ); ?></button>
                            <button type="button" class="button" id="stanray_hv_video_remove" style="<?php echo $video_url ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Remove', 'stanray-custom' ); ?></button>
                        </p>
                        <p class="description"><?php esc_html_e( 'MP4 recommended. Falls back to the theme\'s default video if none is set.', 'stanray-custom' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Poster Image', 'stanray-custom' ); ?></th>
                    <td>
                        <img id="stanray_hv_poster_preview" src="<?php echo esc_url( $poster_url ); ?>" style="max-width:320px;display:<?php echo $poster_url ? 'block' : 'none'; ?>;margin-bottom:10px;border:1px solid #ddd;">
                        <input type="hidden" id="stanray_hv_poster_id" name="stanray_hv_poster_id" value="<?php echo esc_attr( $poster_id ); ?>">
                        <p>
                            <button type="button" class="button" id="stanray_hv_poster_upload"><?php esc_html_e( 'Choose Image', 'stanray-custom' ); ?></button>
                            <button type="button" class="button" id="stanray_hv_poster_remove" style="<?php echo $poster_url ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Remove', 'stanray-custom' ); ?></button>
                        </p>
                        <p class="description"><?php esc_html_e( 'Optional — shown briefly while the video loads.', 'stanray-custom' ); ?></p>
                    </td>
                </tr>
            </table>

            <?php submit_button( __( 'Save Changes', 'stanray-custom' ), 'primary', 'stanray_hv_save' ); ?>
        </form>
    </div>
    <script>
    jQuery(function ($) {
        function bindMediaField(uploadBtnId, removeBtnId, fieldId, previewId, mediaType, previewAttr) {
            var frame;
            $('#' + uploadBtnId).on('click', function (e) {
                e.preventDefault();
                if (frame) { frame.open(); return; }
                frame = wp.media({
                    title: <?php echo wp_json_encode( __( 'Select File', 'stanray-custom' ) ); ?>,
                    multiple: false,
                    library: { type: mediaType }
                });
                frame.on('select', function () {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#' + fieldId).val(attachment.id);
                    $('#' + previewId).attr(previewAttr, attachment.url).show();
                    $('#' + removeBtnId).show();
                });
                frame.open();
            });
            $('#' + removeBtnId).on('click', function (e) {
                e.preventDefault();
                $('#' + fieldId).val('');
                $('#' + previewId).hide();
                $(this).hide();
            });
        }

        bindMediaField('stanray_hv_video_upload', 'stanray_hv_video_remove', 'stanray_hv_video_id', 'stanray_hv_video_preview', 'video', 'src');
        bindMediaField('stanray_hv_poster_upload', 'stanray_hv_poster_remove', 'stanray_hv_poster_id', 'stanray_hv_poster_preview', 'image', 'src');
    });
    </script>
    <?php
}
