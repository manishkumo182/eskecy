<?php
/**
 * Dashboard admin page: Footer Perks bar (site-wide strip above the footer —
 * "Free Dispatch / Easy Returns / 24/7 Enquiry / Worldwide Shipping").
 * Lets an editor change the icon, title, and description of each perk, and
 * add or remove perks (up to STANRAY_FP_MAX), from the wp-admin sidebar
 * instead of editing footer.php directly.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'STANRAY_FP_MAX', 6 );

function stanray_fp_admin_menu() {
    add_submenu_page(
        'stanray-hero-banner',
        __( 'Footer Perks', 'stanray-custom' ),
        __( 'Footer Perks', 'stanray-custom' ),
        'manage_options',
        'stanray-footer-perks',
        'stanray_fp_admin_page'
    );
}
add_action( 'admin_menu', 'stanray_fp_admin_menu' );

// Shared icon library — used by both the admin picker and the front-end
// render, keyed so the option only stores a short slug, not markup.
function stanray_fp_icons() {
    return [
        'truck'    => [
            'label' => __( 'Truck (Dispatch)', 'stanray-custom' ),
            'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>',
        ],
        'returns'  => [
            'label' => __( 'Refresh Arrows (Returns)', 'stanray-custom' ),
            'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"></polyline><polyline points="23 20 23 14 17 14"></polyline><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"></path></svg>',
        ],
        'question' => [
            'label' => __( 'Question Mark (Enquiry)', 'stanray-custom' ),
            'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 1 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
        ],
        'globe'    => [
            'label' => __( 'Globe (Worldwide Shipping)', 'stanray-custom' ),
            'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>',
        ],
        'shield'   => [
            'label' => __( 'Shield (Guarantee)', 'stanray-custom' ),
            'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>',
        ],
        'lock'     => [
            'label' => __( 'Lock (Secure Payment)', 'stanray-custom' ),
            'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>',
        ],
        'clock'    => [
            'label' => __( 'Clock (Fast / 24hr)', 'stanray-custom' ),
            'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>',
        ],
        'gift'     => [
            'label' => __( 'Gift (Rewards)', 'stanray-custom' ),
            'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 12 20 22 4 22 4 12"></polyline><rect x="2" y="7" width="20" height="5"></rect><line x1="12" y1="22" x2="12" y2="7"></line><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path></svg>',
        ],
        'tag'      => [
            'label' => __( 'Tag (Best Price)', 'stanray-custom' ),
            'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41L13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>',
        ],
        'chat'     => [
            'label' => __( 'Chat Bubble (Support)', 'stanray-custom' ),
            'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>',
        ],
    ];
}

function stanray_fp_default_perks() {
    return [
        [ 'icon' => 'truck',    'title' => 'Free Dispatch',      'desc' => 'All orders above $200' ],
        [ 'icon' => 'returns',  'title' => 'Easy Returns',       'desc' => '30-day free returns' ],
        [ 'icon' => 'question', 'title' => '24/7 Enquiry',       'desc' => 'Live chat & secure tickets' ],
        [ 'icon' => 'globe',    'title' => 'Worldwide Shipping', 'desc' => 'Delivered to your door' ],
    ];
}

// Front-end + admin preview both call this to get the saved (or default) perks.
function stanray_fp_get_perks() {
    $perks = get_option( 'stanray_footer_perks', stanray_fp_default_perks() );
    return is_array( $perks ) ? $perks : stanray_fp_default_perks();
}

// Front-end call to render one perk's icon markup safely (icon key is always
// validated against the whitelist on save, so this never touches raw input).
function stanray_fp_icon_svg( $key ) {
    $icons = stanray_fp_icons();
    return isset( $icons[ $key ] ) ? $icons[ $key ]['svg'] : $icons['truck']['svg'];
}

function stanray_fp_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $icons   = stanray_fp_icons();
    $saved   = false;

    if ( isset( $_POST['stanray_fp_save'] ) && check_admin_referer( 'stanray_fp_save_action', 'stanray_fp_nonce' ) ) {
        $post_icon  = $_POST['stanray_fp_icon'] ?? [];
        $post_title = $_POST['stanray_fp_title'] ?? [];
        $post_desc  = $_POST['stanray_fp_desc'] ?? [];

        $perks = [];
        for ( $i = 0; $i < STANRAY_FP_MAX; $i++ ) {
            $title = sanitize_text_field( $post_title[ $i ] ?? '' );
            if ( '' === $title ) continue; // blank title = skip this row

            $icon = sanitize_key( $post_icon[ $i ] ?? 'truck' );
            if ( ! isset( $icons[ $icon ] ) ) $icon = 'truck';

            $perks[] = [
                'icon'  => $icon,
                'title' => $title,
                'desc'  => sanitize_text_field( $post_desc[ $i ] ?? '' ),
            ];
        }
        update_option( 'stanray_footer_perks', $perks );

        $saved = true;
    }

    $perks = get_option( 'stanray_footer_perks', stanray_fp_default_perks() );
    if ( ! is_array( $perks ) ) $perks = stanray_fp_default_perks();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Footer Perks Bar', 'stanray-custom' ); ?></h1>
        <p><?php esc_html_e( 'This controls the strip of guarantees ("Free Dispatch", "Easy Returns", …) shown above the site footer on every page. Pick an icon, a title, and a short description for each perk. Leave a title blank to remove that perk.', 'stanray-custom' ); ?></p>

        <?php if ( $saved ) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Saved.', 'stanray-custom' ); ?></p></div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field( 'stanray_fp_save_action', 'stanray_fp_nonce' ); ?>

            <table class="widefat" style="max-width:900px;">
                <thead>
                    <tr>
                        <th style="width:40px;"><?php esc_html_e( '#', 'stanray-custom' ); ?></th>
                        <th style="width:220px;"><?php esc_html_e( 'Icon', 'stanray-custom' ); ?></th>
                        <th><?php esc_html_e( 'Title', 'stanray-custom' ); ?></th>
                        <th><?php esc_html_e( 'Description', 'stanray-custom' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ( $i = 0; $i < STANRAY_FP_MAX; $i++ ) :
                        $row = $perks[ $i ] ?? [ 'icon' => 'truck', 'title' => '', 'desc' => '' ];
                    ?>
                    <tr>
                        <td><?php echo (int) ( $i + 1 ); ?></td>
                        <td>
                            <select name="stanray_fp_icon[<?php echo $i; ?>]">
                                <?php foreach ( $icons as $key => $icon ) : ?>
                                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $row['icon'], $key ); ?>><?php echo esc_html( $icon['label'] ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="text" name="stanray_fp_title[<?php echo $i; ?>]" value="<?php echo esc_attr( $row['title'] ); ?>" class="regular-text"></td>
                        <td><input type="text" name="stanray_fp_desc[<?php echo $i; ?>]" value="<?php echo esc_attr( $row['desc'] ); ?>" class="regular-text"></td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>

            <?php submit_button( __( 'Save Changes', 'stanray-custom' ), 'primary', 'stanray_fp_save' ); ?>
        </form>
    </div>
    <?php
}
