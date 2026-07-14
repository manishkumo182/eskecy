<?php
/**
 * Theme helper functions
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get template part with variables
 */
function stanray_get_template( $slug, $vars = [] ) {
    if ( ! empty( $vars ) ) {
        extract( $vars );
    }
    get_template_part( 'template-parts/' . $slug );
}

/**
 * Render SVG icon from /assets/images/icons/
 */
function stanray_icon( $name, $class = '' ) {
    $path = STANRAY_DIR . '/assets/images/icons/' . $name . '.svg';
    if ( file_exists( $path ) ) {
        $svg = file_get_contents( $path );
        if ( $class ) {
            $svg = str_replace( '<svg', '<svg class="icon icon--' . esc_attr( $class ) . '"', $svg );
        }
        echo $svg;
    }
}

/**
 * Get the header cart count HTML
 */
function stanray_header_cart() {
    $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    ?>
    <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="cart-icon" aria-label="Cart">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <path d="M16 10a4 4 0 0 1-8 0"/>
        </svg>
        <span class="cart-icon__count"><?php echo esc_html( $count ); ?></span>
    </a>
    <?php
}

/**
 * Get ACF field with fallback (safe if ACF not installed)
 */
function stanray_field( $key, $post_id = false, $fallback = '' ) {
    if ( function_exists( 'get_field' ) ) {
        $val = get_field( $key, $post_id );
        return $val ?: $fallback;
    }
    return $fallback;
}

/**
 * Output pagination
 */
function stanray_pagination() {
    $args = [
        'prev_text' => '&larr; Previous',
        'next_text' => 'Next &rarr;',
        'mid_size'  => 2,
    ];
    echo '<div class="pagination">';
    echo paginate_links( $args );
    echo '</div>';
}

/**
 * Format price cleanly
 */
function stanray_price( $price ) {
    return wc_price( $price );
}

/**
 * Check if current page is any WooCommerce page
 */
function stanray_is_woo_page() {
    return ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) );
}

/**
 * Get category image URL
 */
function stanray_category_image( $term_id, $size = 'stanray-editorial' ) {
    $thumbnail_id = get_term_meta( $term_id, 'thumbnail_id', true );
    if ( $thumbnail_id ) {
        return wp_get_attachment_image_url( $thumbnail_id, $size );
    }
    return STANRAY_URI . '/assets/images/placeholder.jpg';
}
