<?php
/**
 * Dashboard admin page: Footer "Support" column links (site-wide footer —
 * "Shipping Info", "Refund Policy", …).
 * Previously these labels were hardcoded in footer.php and their URLs
 * pointed at theme_mods with no Customizer control, so editors had no way
 * to change them. This lets an editor set the label + URL for each link,
 * and add or remove links (up to STANRAY_FSL_MAX), from the wp-admin
 * sidebar instead of editing footer.php directly.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'STANRAY_FSL_MAX', 6 );

function stanray_fsl_admin_menu() {
    add_submenu_page(
        'stanray-hero-banner',
        __( 'Footer Support Links', 'stanray-custom' ),
        __( 'Footer Support Links', 'stanray-custom' ),
        'manage_options',
        'stanray-footer-support-links',
        'stanray_fsl_admin_page'
    );
}
add_action( 'admin_menu', 'stanray_fsl_admin_menu' );

function stanray_fsl_default_links() {
    return [
        [ 'label' => 'Shipping Info',  'url' => home_url( '/shipping' ) ],
        [ 'label' => 'Refund Policy',  'url' => home_url( '/refund' ) ],
    ];
}

// Front-end call to get the saved (or default) support links.
function stanray_fsl_get_links() {
    $links = get_option( 'stanray_footer_support_links', stanray_fsl_default_links() );
    return is_array( $links ) ? $links : stanray_fsl_default_links();
}

function stanray_fsl_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $saved = false;

    if ( isset( $_POST['stanray_fsl_save'] ) && check_admin_referer( 'stanray_fsl_save_action', 'stanray_fsl_nonce' ) ) {
        $post_label = $_POST['stanray_fsl_label'] ?? [];
        $post_url   = $_POST['stanray_fsl_url'] ?? [];

        $links = [];
        for ( $i = 0; $i < STANRAY_FSL_MAX; $i++ ) {
            $label = sanitize_text_field( $post_label[ $i ] ?? '' );
            if ( '' === $label ) continue; // blank label = skip this row

            $links[] = [
                'label' => $label,
                'url'   => esc_url_raw( $post_url[ $i ] ?? '' ),
            ];
        }
        update_option( 'stanray_footer_support_links', $links );

        $saved = true;
    }

    $links = get_option( 'stanray_footer_support_links', stanray_fsl_default_links() );
    if ( ! is_array( $links ) ) $links = stanray_fsl_default_links();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Footer Support Links', 'stanray-custom' ); ?></h1>
        <p><?php esc_html_e( 'This controls the "Support" column links (e.g. "Shipping Info", "Refund Policy") shown in the site footer on every page. Set a label and URL for each link. Leave a label blank to remove that link.', 'stanray-custom' ); ?></p>

        <?php if ( $saved ) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Saved.', 'stanray-custom' ); ?></p></div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field( 'stanray_fsl_save_action', 'stanray_fsl_nonce' ); ?>

            <table class="widefat" style="max-width:800px;">
                <thead>
                    <tr>
                        <th style="width:40px;"><?php esc_html_e( '#', 'stanray-custom' ); ?></th>
                        <th style="width:260px;"><?php esc_html_e( 'Label', 'stanray-custom' ); ?></th>
                        <th><?php esc_html_e( 'URL', 'stanray-custom' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ( $i = 0; $i < STANRAY_FSL_MAX; $i++ ) :
                        $row = $links[ $i ] ?? [ 'label' => '', 'url' => '' ];
                    ?>
                    <tr>
                        <td><?php echo (int) ( $i + 1 ); ?></td>
                        <td><input type="text" name="stanray_fsl_label[<?php echo $i; ?>]" value="<?php echo esc_attr( $row['label'] ); ?>" class="regular-text"></td>
                        <td><input type="url" name="stanray_fsl_url[<?php echo $i; ?>]" value="<?php echo esc_attr( $row['url'] ); ?>" class="large-text"></td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>

            <?php submit_button( __( 'Save Changes', 'stanray-custom' ), 'primary', 'stanray_fsl_save' ); ?>
        </form>
    </div>
    <?php
}
