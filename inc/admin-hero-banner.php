<?php
/**
 * Dashboard admin page: Hero Banner (top of homepage)
 * Lets an editor change the watermark text, spec labels, headline,
 * description, stats, CTA, and featured product from the wp-admin
 * sidebar instead of editing template-parts/hero-banner.php directly.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Parent menu — groups all editable homepage sections in one place.
// Owns the top-level "Homepage Sections" registration so Hero Banner is the
// first item and the default landing page. (WordPress requires the first
// submenu's slug to match the parent's own slug, or it auto-inserts a
// duplicate "Homepage Sections" row above it — so whichever page should be
// first/default must be the one that calls add_menu_page().)
function stanray_hb_admin_menu() {
    add_menu_page(
        __( 'Homepage Sections', 'stanray-custom' ),
        __( 'Homepage Sections', 'stanray-custom' ),
        'manage_options',
        'stanray-hero-banner',
        'stanray_hb_admin_page',
        'dashicons-layout',
        58
    );

    add_submenu_page(
        'stanray-hero-banner',
        __( 'Hero Banner', 'stanray-custom' ),
        __( 'Hero Banner', 'stanray-custom' ),
        'manage_options',
        'stanray-hero-banner',
        'stanray_hb_admin_page'
    );
}
// Priority 5: must run before any add_submenu_page() call that targets this
// parent slug, since WordPress needs the parent registered first to compute
// those pages' render hooks correctly (see notify comment above).
add_action( 'admin_menu', 'stanray_hb_admin_menu', 5 );

function stanray_hb_get_product_choices() {
    $choices = [ '' => __( '— Automatic (newest featured product) —', 'stanray-custom' ) ];

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

function stanray_hb_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $saved = false;

    if ( isset( $_POST['stanray_hb_save'] ) && check_admin_referer( 'stanray_hb_save_action', 'stanray_hb_nonce' ) ) {
        $fields = [
            'stanray_hb_watermark'    => 'sanitize_text_field',
            'stanray_hb_badge'        => 'sanitize_text_field',
            'stanray_hb_spec_tl_key'  => 'sanitize_text_field',
            'stanray_hb_spec_tl_val'  => 'sanitize_text_field',
            'stanray_hb_spec_tr_key'  => 'sanitize_text_field',
            'stanray_hb_spec_tr_val'  => 'sanitize_text_field',
            'stanray_hb_spec_ml_key'  => 'sanitize_text_field',
            'stanray_hb_spec_ml_val'  => 'sanitize_text_field',
            'stanray_hb_spec_mr_key'  => 'sanitize_text_field',
            'stanray_hb_spec_mr_val'  => 'sanitize_text_field',
            'stanray_hb_headline_1'   => 'sanitize_text_field',
            'stanray_hb_headline_2'   => 'sanitize_text_field',
            'stanray_hb_desc'         => 'sanitize_textarea_field',
            'stanray_hb_stat1_num'    => 'sanitize_text_field',
            'stanray_hb_stat1_lbl'    => 'sanitize_text_field',
            'stanray_hb_stat2_num'    => 'sanitize_text_field',
            'stanray_hb_stat2_lbl'    => 'sanitize_text_field',
            'stanray_hb_cta_text'     => 'sanitize_text_field',
            'stanray_hb_cta_link'     => 'esc_url_raw',
        ];
        foreach ( $fields as $key => $sanitizer ) {
            update_option( $key, call_user_func( $sanitizer, $_POST[ $key ] ?? '' ) );
        }
        update_option( 'stanray_hb_product', absint( $_POST['stanray_hb_product'] ?? 0 ) );

        $saved = true;
    }

    $v = function ( $key, $default = '' ) {
        return get_option( $key, $default );
    };

    $product_id = $v( 'stanray_hb_product', '' );
    $choices    = stanray_hb_get_product_choices();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Hero Banner — Top of Homepage', 'stanray-custom' ); ?></h1>
        <p><?php esc_html_e( 'This controls the large editorial section at the very top of the homepage: watermark text, spec labels, headline, description, stats, CTA button, and the featured product image.', 'stanray-custom' ); ?></p>

        <?php if ( $saved ) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Saved.', 'stanray-custom' ); ?></p></div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field( 'stanray_hb_save_action', 'stanray_hb_nonce' ); ?>

            <h2><?php esc_html_e( 'Product', 'stanray-custom' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="stanray_hb_product"><?php esc_html_e( 'Featured Product', 'stanray-custom' ); ?></label></th>
                    <td>
                        <select id="stanray_hb_product" name="stanray_hb_product">
                            <?php foreach ( $choices as $id => $name ) : ?>
                                <option value="<?php echo esc_attr( $id ); ?>" <?php selected( (string) $product_id, (string) $id ); ?>><?php echo esc_html( $name ); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e( 'The image floating in the center of the hero. Automatic picks the newest featured product (or newest product if none are featured).', 'stanray-custom' ); ?></p>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Watermark & Badge', 'stanray-custom' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="stanray_hb_watermark"><?php esc_html_e( 'Giant Watermark Word', 'stanray-custom' ); ?></label></th>
                    <td><input type="text" id="stanray_hb_watermark" name="stanray_hb_watermark" value="<?php echo esc_attr( $v( 'stanray_hb_watermark', 'ESKECY' ) ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="stanray_hb_badge"><?php esc_html_e( 'Card Badge Text', 'stanray-custom' ); ?></label></th>
                    <td><input type="text" id="stanray_hb_badge" name="stanray_hb_badge" value="<?php echo esc_attr( $v( 'stanray_hb_badge', 'SK.26' ) ); ?>" class="regular-text"></td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Spec Labels (4 corners)', 'stanray-custom' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Top-Left', 'stanray-custom' ); ?></th>
                    <td>
                        <input type="text" name="stanray_hb_spec_tl_key" value="<?php echo esc_attr( $v( 'stanray_hb_spec_tl_key', 'Collection' ) ); ?>" placeholder="<?php esc_attr_e( 'Label', 'stanray-custom' ); ?>" class="regular-text">
                        <input type="text" name="stanray_hb_spec_tl_val" value="<?php echo esc_attr( $v( 'stanray_hb_spec_tl_val', 'Ultra Premium' ) ); ?>" placeholder="<?php esc_attr_e( 'Value', 'stanray-custom' ); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Top-Right', 'stanray-custom' ); ?></th>
                    <td>
                        <input type="text" name="stanray_hb_spec_tr_key" value="<?php echo esc_attr( $v( 'stanray_hb_spec_tr_key', 'Shipping' ) ); ?>" placeholder="<?php esc_attr_e( 'Label', 'stanray-custom' ); ?>" class="regular-text">
                        <input type="text" name="stanray_hb_spec_tr_val" value="<?php echo esc_attr( $v( 'stanray_hb_spec_tr_val', 'Free Delivery' ) ); ?>" placeholder="<?php esc_attr_e( 'Value', 'stanray-custom' ); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Mid-Left (accent)', 'stanray-custom' ); ?></th>
                    <td>
                        <input type="text" name="stanray_hb_spec_ml_key" value="<?php echo esc_attr( $v( 'stanray_hb_spec_ml_key', 'Design' ) ); ?>" placeholder="<?php esc_attr_e( 'Label', 'stanray-custom' ); ?>" class="regular-text">
                        <input type="text" name="stanray_hb_spec_ml_val" value="<?php echo esc_attr( $v( 'stanray_hb_spec_ml_val', 'Modern Fit' ) ); ?>" placeholder="<?php esc_attr_e( 'Value', 'stanray-custom' ); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Mid-Right', 'stanray-custom' ); ?></th>
                    <td>
                        <input type="text" name="stanray_hb_spec_mr_key" value="<?php echo esc_attr( $v( 'stanray_hb_spec_mr_key', 'Quality' ) ); ?>" placeholder="<?php esc_attr_e( 'Label', 'stanray-custom' ); ?>" class="regular-text">
                        <input type="text" name="stanray_hb_spec_mr_val" value="<?php echo esc_attr( $v( 'stanray_hb_spec_mr_val', 'Graphene Fabric' ) ); ?>" placeholder="<?php esc_attr_e( 'Value', 'stanray-custom' ); ?>" class="regular-text">
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Headline & Description', 'stanray-custom' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="stanray_hb_headline_1"><?php esc_html_e( 'Headline Line 1 (black)', 'stanray-custom' ); ?></label></th>
                    <td><input type="text" id="stanray_hb_headline_1" name="stanray_hb_headline_1" value="<?php echo esc_attr( $v( 'stanray_hb_headline_1', 'Wear the Look.' ) ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="stanray_hb_headline_2"><?php esc_html_e( 'Headline Line 2 (red)', 'stanray-custom' ); ?></label></th>
                    <td><input type="text" id="stanray_hb_headline_2" name="stanray_hb_headline_2" value="<?php echo esc_attr( $v( 'stanray_hb_headline_2', 'Own the Style.' ) ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="stanray_hb_desc"><?php esc_html_e( 'Description', 'stanray-custom' ); ?></label></th>
                    <td><textarea id="stanray_hb_desc" name="stanray_hb_desc" rows="3" class="large-text"><?php echo esc_textarea( $v( 'stanray_hb_desc', 'Crafted for the elite. Eskecy streetwear combines premium fabric with modern fit and unmatched street culture.' ) ); ?></textarea></td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Stats', 'stanray-custom' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Stat 1', 'stanray-custom' ); ?></th>
                    <td>
                        <input type="text" name="stanray_hb_stat1_num" value="<?php echo esc_attr( $v( 'stanray_hb_stat1_num', '50+' ) ); ?>" placeholder="<?php esc_attr_e( 'Number', 'stanray-custom' ); ?>" style="width:100px;">
                        <input type="text" name="stanray_hb_stat1_lbl" value="<?php echo esc_attr( $v( 'stanray_hb_stat1_lbl', 'Styles' ) ); ?>" placeholder="<?php esc_attr_e( 'Label', 'stanray-custom' ); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Stat 2', 'stanray-custom' ); ?></th>
                    <td>
                        <input type="text" name="stanray_hb_stat2_num" value="<?php echo esc_attr( $v( 'stanray_hb_stat2_num', '100%' ) ); ?>" placeholder="<?php esc_attr_e( 'Number', 'stanray-custom' ); ?>" style="width:100px;">
                        <input type="text" name="stanray_hb_stat2_lbl" value="<?php echo esc_attr( $v( 'stanray_hb_stat2_lbl', 'Authentic' ) ); ?>" placeholder="<?php esc_attr_e( 'Label', 'stanray-custom' ); ?>" class="regular-text">
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Call to Action', 'stanray-custom' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="stanray_hb_cta_text"><?php esc_html_e( 'Button Text', 'stanray-custom' ); ?></label></th>
                    <td><input type="text" id="stanray_hb_cta_text" name="stanray_hb_cta_text" value="<?php echo esc_attr( $v( 'stanray_hb_cta_text', 'SHOP NOW' ) ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="stanray_hb_cta_link"><?php esc_html_e( 'Button Link', 'stanray-custom' ); ?></label></th>
                    <td>
                        <input type="url" id="stanray_hb_cta_link" name="stanray_hb_cta_link" value="<?php echo esc_attr( $v( 'stanray_hb_cta_link', '' ) ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Leave blank to link to the Shop page', 'stanray-custom' ); ?>">
                    </td>
                </tr>
            </table>

            <?php submit_button( __( 'Save Changes', 'stanray-custom' ), 'primary', 'stanray_hb_save' ); ?>
        </form>
    </div>
    <?php
}
