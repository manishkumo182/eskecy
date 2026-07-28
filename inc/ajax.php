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

/**
 * Post-purchase review popup — submit.
 *
 * Goes through wp_new_comment() with comment_type=review and a 'rating'
 * $_POST field, exactly like WooCommerce's own review form, so its native
 * comment_post hooks (rating meta, verified-owner flag, average rating
 * recalculation, moderation) all fire the same way a normal review would.
 */
function stanray_submit_post_purchase_review() {
    check_ajax_referer( 'stanray_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'You must be logged in to leave a review.' ] );
    }

    $user_id    = get_current_user_id();
    $product_id = absint( $_POST['product_id'] ?? 0 );
    $order_id   = absint( $_POST['order_id'] ?? 0 );
    $rating     = absint( $_POST['rating'] ?? 0 );
    $comment    = sanitize_textarea_field( wp_unslash( $_POST['comment'] ?? '' ) );

    $pending = get_user_meta( $user_id, '_stanray_pending_review', true );
    if ( empty( $pending['product_id'] )
        || (int) $pending['product_id'] !== $product_id
        || (int) $pending['order_id'] !== $order_id ) {
        wp_send_json_error( [ 'message' => 'This review prompt has expired.' ] );
    }

    $order = wc_get_order( $order_id );
    if ( ! $order
        || (int) $order->get_customer_id() !== $user_id
        || ! wc_customer_bought_product( $order->get_billing_email(), $user_id, $product_id ) ) {
        wp_send_json_error( [ 'message' => 'This product was not part of your order.' ] );
    }

    if ( $rating < 1 || $rating > 5 ) {
        wp_send_json_error( [ 'message' => 'Please choose a star rating.' ] );
    }

    if ( stanray_user_has_reviewed( $product_id, $user_id ) ) {
        delete_user_meta( $user_id, '_stanray_pending_review' );
        wp_send_json_error( [ 'message' => 'You already reviewed this product.' ] );
    }

    $user = get_userdata( $user_id );

    // WooCommerce's own rating-save hook (WC_Comments::add_comment_rating) reads
    // $_POST['comment_post_ID'] directly rather than the commentdata array passed
    // to wp_new_comment() below — that's the field name WordPress's native comment
    // form posts under. Our AJAX body uses 'product_id', so that field is set here
    // so WC's hook actually fires and saves the star rating / recalculates the
    // product's average rating.
    $_POST['comment_post_ID'] = $product_id;

    $comment_id = wp_new_comment( [
        'comment_post_ID'      => $product_id,
        'comment_author'       => $user->display_name,
        'comment_author_email' => $user->user_email,
        'comment_content'      => $comment,
        'comment_type'         => 'review',
        'user_id'              => $user_id,
    ], true );

    if ( is_wp_error( $comment_id ) ) {
        wp_send_json_error( [ 'message' => $comment_id->get_error_message() ] );
    }

    delete_user_meta( $user_id, '_stanray_pending_review' );

    wp_send_json_success( [ 'message' => 'Thanks! Your review has been submitted.' ] );
}
add_action( 'wp_ajax_stanray_submit_post_purchase_review', 'stanray_submit_post_purchase_review' );

/**
 * Post-purchase review popup — "Maybe later" dismiss.
 */
function stanray_dismiss_post_purchase_review() {
    check_ajax_referer( 'stanray_nonce', 'nonce' );
    if ( is_user_logged_in() ) {
        delete_user_meta( get_current_user_id(), '_stanray_pending_review' );
    }
    wp_send_json_success();
}
add_action( 'wp_ajax_stanray_dismiss_post_purchase_review', 'stanray_dismiss_post_purchase_review' );

/**
 * "Write a Review" button on the order's own page (see
 * stanray_render_order_review_prompts in inc/post-purchase-review.php) —
 * re-queues the pending-review meta for a specific product on demand, then
 * the page reload that follows lets the existing popup pick it up normally.
 */
function stanray_start_manual_review() {
    check_ajax_referer( 'stanray_nonce', 'nonce' );
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'You must be logged in to leave a review.' ] );
    }

    $user_id    = get_current_user_id();
    $order_id   = absint( $_POST['order_id'] ?? 0 );
    $product_id = absint( $_POST['product_id'] ?? 0 );
    $order      = wc_get_order( $order_id );

    if ( ! $order || ! $order->has_status( 'delivered' ) || (int) $order->get_customer_id() !== $user_id ) {
        wp_send_json_error( [ 'message' => 'Order not found.' ] );
    }
    if ( ! wc_customer_bought_product( $order->get_billing_email(), $user_id, $product_id ) ) {
        wp_send_json_error( [ 'message' => 'This product was not part of your order.' ] );
    }
    if ( stanray_user_has_reviewed( $product_id, $user_id ) ) {
        wp_send_json_error( [ 'message' => 'You already reviewed this product.' ] );
    }

    update_user_meta( $user_id, '_stanray_pending_review', [
        'order_id'   => $order_id,
        'product_id' => $product_id,
    ] );

    wp_send_json_success();
}
add_action( 'wp_ajax_stanray_start_manual_review', 'stanray_start_manual_review' );
