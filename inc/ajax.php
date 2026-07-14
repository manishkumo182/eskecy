<?php
/**
 * AJAX handlers
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Quick add to cart via AJAX
 */
function stanray_ajax_add_to_cart() {
    check_ajax_referer( 'stanray_nonce', 'nonce' );

    $product_id   = absint( $_POST['product_id'] ?? 0 );
    $quantity     = absint( $_POST['quantity'] ?? 1 );
    $variation_id = absint( $_POST['variation_id'] ?? 0 );
    $variation    = $_POST['variation'] ?? [];

    if ( ! $product_id ) {
        wp_send_json_error( [ 'message' => 'Invalid product' ] );
    }

    $added = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );

    if ( $added ) {
        wp_send_json_success([
            'count'   => WC()->cart->get_cart_contents_count(),
            'message' => 'Added to cart',
        ]);
    } else {
        $notices = wc_get_notices( 'error' );
        wc_clear_notices();
        $message = ! empty( $notices ) ? wp_strip_all_tags( $notices[0]['notice'] ) : 'Could not add to cart';
        wp_send_json_error([ 'message' => $message ]);
    }
}
add_action( 'wp_ajax_stanray_add_to_cart',        'stanray_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_stanray_add_to_cart', 'stanray_ajax_add_to_cart' );

/**
 * Get cart HTML for mini cart
 */
function stanray_ajax_get_cart() {
    check_ajax_referer( 'stanray_nonce', 'nonce' );
    ob_start();
    woocommerce_mini_cart();
    $html = ob_get_clean();
    wp_send_json_success([
        'html'  => $html,
        'count' => WC()->cart->get_cart_contents_count(),
        'total' => WC()->cart->get_cart_total(),
    ]);
}
add_action( 'wp_ajax_stanray_get_cart',        'stanray_ajax_get_cart' );
add_action( 'wp_ajax_nopriv_stanray_get_cart', 'stanray_ajax_get_cart' );

/**
 * AJAX product filter — powers the OVO-style same-page category tabs
 */
function stanray_filter_products() {
    check_ajax_referer( 'stanray_nonce', 'nonce' );

    $category   = sanitize_key( $_POST['category'] ?? '' );
    $categories = isset( $_POST['categories'] ) && is_array( $_POST['categories'] )
        ? array_map( 'sanitize_key', $_POST['categories'] )
        : ( $category ? [ $category ] : [] );
    $categories = array_filter( $categories );

    $min_price = isset( $_POST['min_price'] ) && $_POST['min_price'] !== '' ? (float) $_POST['min_price'] : null;
    $max_price = isset( $_POST['max_price'] ) && $_POST['max_price'] !== '' ? (float) $_POST['max_price'] : null;

    $paged    = max( 1, absint( $_POST['paged'] ?? 1 ) );
    $orderby  = sanitize_text_field( $_POST['orderby'] ?? 'date' );
    $per_page = absint( get_option( 'posts_per_page', 24 ) );

    $args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $paged,
    ];

    if ( ! empty( $categories ) ) {
        $args['tax_query'] = [ [
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => $categories,
        ] ];
    }

    if ( $min_price !== null || $max_price !== null ) {
        $price_query = [ 'key' => '_price', 'type' => 'NUMERIC' ];
        if ( $min_price !== null && $max_price !== null ) {
            $price_query['value']   = [ $min_price, $max_price ];
            $price_query['compare'] = 'BETWEEN';
        } elseif ( $min_price !== null ) {
            $price_query['value']   = $min_price;
            $price_query['compare'] = '>=';
        } else {
            $price_query['value']   = $max_price;
            $price_query['compare'] = '<=';
        }
        $args['meta_query'] = [ $price_query ];
    }

    // Map orderby values to WP_Query args
    switch ( $orderby ) {
        case 'menu_order':
            $args['orderby'] = 'menu_order title';
            $args['order']   = 'ASC';
            break;
        case 'price':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = '_price';
            $args['order']    = 'ASC';
            break;
        case 'price-desc':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = '_price';
            $args['order']    = 'DESC';
            break;
        case 'title':
            $args['orderby'] = 'title';
            $args['order']   = 'ASC';
            break;
        case 'popularity':
            $args['meta_key'] = 'total_sales';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'DESC';
            break;
        case 'rating':
            $args['meta_key'] = '_wc_average_rating';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'DESC';
            break;
        default:
            $args['orderby'] = 'date';
            $args['order']   = 'DESC';
    }

    $query = new WP_Query( $args );

    if ( $query->have_posts() ) {
        wc_setup_loop( [
            'total'        => $query->found_posts,
            'total_pages'  => $query->max_num_pages,
            'current_page' => $paged,
            'per_page'     => $per_page,
            'columns'      => 4,
        ] );

        $all_ids = [];
        while ( $query->have_posts() ) {
            $query->the_post();
            $all_ids[] = get_the_ID();
        }
        wp_reset_postdata();

        $editorial_banners = stanray_get_editorial_banners();

        ob_start();
        stanray_render_shop_grid( $all_ids, $editorial_banners );
        $html_grid = ob_get_clean();

        wc_reset_loop();

        $pagination_html = '';
        if ( $query->max_num_pages > 1 ) {
            $base  = $category
                ? trailingslashit( get_term_link( $category, 'product_cat' ) )
                : trailingslashit( get_permalink( wc_get_page_id( 'shop' ) ) );
            $links = paginate_links( [
                'base'      => $base . '%_%',
                'format'    => 'page/%#%/',
                'current'   => $paged,
                'total'     => $query->max_num_pages,
                'prev_text' => '&#8592;',
                'next_text' => '&#8594;',
                'type'      => 'list',
            ] );
            if ( $links ) $pagination_html = '<nav class="woocommerce-pagination">' . $links . '</nav>';
        }

        wp_send_json_success( [
            'html_grid'  => $html_grid,
            'pagination' => $pagination_html,
            'found'      => $query->found_posts,
            'pages'      => $query->max_num_pages,
        ] );

    } else {
        wp_reset_postdata();
        wp_send_json_success( [
            'html_grid'  => '',
            'pagination' => '',
            'found'      => 0,
            'pages'      => 0,
        ] );
    }
}
add_action( 'wp_ajax_stanray_filter_products',        'stanray_filter_products' );
add_action( 'wp_ajax_nopriv_stanray_filter_products', 'stanray_filter_products' );

/**
 * Flush queued WC session notices (add to cart / remove / qty update) as
 * rendered HTML. Cart actions happen via background AJAX with no page
 * reload, so without this, notices just queue up in the session until
 * whatever page next calls wc_print_notices() (e.g. My Account) dumps
 * out the entire backlog at once.
 */
function stanray_flush_notices() {
    check_ajax_referer( 'stanray_nonce', 'nonce' );

    if ( ! function_exists( 'wc_print_notices' ) ) {
        wp_send_json_success( [ 'html' => '' ] );
    }

    ob_start();
    wc_print_notices();
    $html = ob_get_clean();

    wp_send_json_success( [ 'html' => $html ] );
}
add_action( 'wp_ajax_stanray_flush_notices',        'stanray_flush_notices' );
add_action( 'wp_ajax_nopriv_stanray_flush_notices', 'stanray_flush_notices' );
