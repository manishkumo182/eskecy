<?php
/**
 * Dashboard admin page: "Select Your Style" homepage section
 * Lets an editor pick the New Arrival / Most Popular products
 * and edit the section text directly from the wp-admin sidebar,
 * without going through the Customizer.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Parent menu ("Homepage Sections") is registered in inc/admin-hero-banner.php,
// since Hero Banner is the first/default item. This just adds our own entry
// to it.
function stanray_sys_admin_menu() {
    add_submenu_page(
        'stanray-hero-banner',
        __( 'Select Your Style', 'stanray-custom' ),
        __( 'Select Your Style', 'stanray-custom' ),
        'manage_options',
        'stanray-select-style',
        'stanray_sys_admin_page'
    );
}
add_action( 'admin_menu', 'stanray_sys_admin_menu' );

function stanray_sys_get_product_choices() {
    $choices = [ '' => __( '— Automatic —', 'stanray-custom' ) ];

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

function stanray_sys_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $saved = false;

    if ( isset( $_POST['stanray_sys_save'] ) && check_admin_referer( 'stanray_sys_save_action', 'stanray_sys_nonce' ) ) {
        update_option( 'stanray_sys_section_title', sanitize_text_field( $_POST['stanray_sys_section_title'] ?? '' ) );
        update_option( 'stanray_sys_section_eyebrow', sanitize_text_field( $_POST['stanray_sys_section_eyebrow'] ?? '' ) );
        update_option( 'stanray_sys_new_arrival_product', absint( $_POST['stanray_sys_new_arrival_product'] ?? 0 ) );
        update_option( 'stanray_sys_new_arrival_label', sanitize_text_field( $_POST['stanray_sys_new_arrival_label'] ?? '' ) );
        update_option( 'stanray_sys_popular_product', absint( $_POST['stanray_sys_popular_product'] ?? 0 ) );
        update_option( 'stanray_sys_popular_label', sanitize_text_field( $_POST['stanray_sys_popular_label'] ?? '' ) );
        $saved = true;
    }

    $section_title   = get_option( 'stanray_sys_section_title', 'Select Your Style' );
    $section_eyebrow = get_option( 'stanray_sys_section_eyebrow', 'Our Collection' );
    $new_product_id  = get_option( 'stanray_sys_new_arrival_product', '' );
    $new_label       = get_option( 'stanray_sys_new_arrival_label', 'New Arrival' );
    $popular_id      = get_option( 'stanray_sys_popular_product', '' );
    $popular_label   = get_option( 'stanray_sys_popular_label', 'Most Popular' );

    $choices = stanray_sys_get_product_choices();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Select Your Style — Homepage Section', 'stanray-custom' ); ?></h1>
        <p><?php esc_html_e( 'This controls the "New Arrival / Most Popular" product showcase on the homepage. Pick the exact products for each tab, or leave on Automatic to let the store choose the newest / best-selling product.', 'stanray-custom' ); ?></p>

        <?php if ( $saved ) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Saved.', 'stanray-custom' ); ?></p></div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field( 'stanray_sys_save_action', 'stanray_sys_nonce' ); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="stanray_sys_section_title"><?php esc_html_e( 'Section Title', 'stanray-custom' ); ?></label></th>
                    <td><input type="text" id="stanray_sys_section_title" name="stanray_sys_section_title" value="<?php echo esc_attr( $section_title ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="stanray_sys_section_eyebrow"><?php esc_html_e( 'Eyebrow Label', 'stanray-custom' ); ?></label></th>
                    <td><input type="text" id="stanray_sys_section_eyebrow" name="stanray_sys_section_eyebrow" value="<?php echo esc_attr( $section_eyebrow ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th colspan="2"><h2><?php esc_html_e( '"New Arrival" Tab', 'stanray-custom' ); ?></h2></th>
                </tr>
                <tr>
                    <th scope="row"><label for="stanray_sys_new_arrival_product"><?php esc_html_e( 'Product', 'stanray-custom' ); ?></label></th>
                    <td>
                        <select id="stanray_sys_new_arrival_product" name="stanray_sys_new_arrival_product">
                            <?php foreach ( $choices as $id => $name ) : ?>
                                <option value="<?php echo esc_attr( $id ); ?>" <?php selected( (string) $new_product_id, (string) $id ); ?>><?php echo esc_html( $name ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="stanray_sys_new_arrival_label"><?php esc_html_e( 'Tab Button Text', 'stanray-custom' ); ?></label></th>
                    <td><input type="text" id="stanray_sys_new_arrival_label" name="stanray_sys_new_arrival_label" value="<?php echo esc_attr( $new_label ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th colspan="2"><h2><?php esc_html_e( '"Most Popular" Tab', 'stanray-custom' ); ?></h2></th>
                </tr>
                <tr>
                    <th scope="row"><label for="stanray_sys_popular_product"><?php esc_html_e( 'Product', 'stanray-custom' ); ?></label></th>
                    <td>
                        <select id="stanray_sys_popular_product" name="stanray_sys_popular_product">
                            <?php foreach ( $choices as $id => $name ) : ?>
                                <option value="<?php echo esc_attr( $id ); ?>" <?php selected( (string) $popular_id, (string) $id ); ?>><?php echo esc_html( $name ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="stanray_sys_popular_label"><?php esc_html_e( 'Tab Button Text', 'stanray-custom' ); ?></label></th>
                    <td><input type="text" id="stanray_sys_popular_label" name="stanray_sys_popular_label" value="<?php echo esc_attr( $popular_label ); ?>" class="regular-text"></td>
                </tr>
            </table>

            <?php submit_button( __( 'Save Changes', 'stanray-custom' ), 'primary', 'stanray_sys_save' ); ?>
        </form>
    </div>
    <?php
}
