<?php
/**
 * WooCommerce customizations
 * All WC hooks, filters, and tweaks live here
 * IMPROVED: Stock indicator, Size Guide, Wishlist all properly hooked
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ─── CURRENCY SYMBOL: use literal "Rs" text instead of the ₨ glyph ────────────
// WooCommerce's default NPR symbol is the single Unicode character ₨ (U+20A8).
// In this theme's fonts it renders as a fused R+s shape with no letter-gap of
// its own — letter-spacing can't open a gap inside one character. Using the
// literal two-letter text "Rs" instead makes it real, spaceable characters.
add_filter( 'woocommerce_currency_symbol', function( $currency_symbol, $currency ) {
    if ( 'NPR' === $currency ) {
        return 'Rs';
    }
    return $currency_symbol;
}, 10, 2 );

// ─── NEPAL: "Province" not "State / Zone" ──────────────────────────────────
// WC core hardcodes NP's state field label as "State / Zone" — a holdover
// from Nepal's pre-2015 administrative zones. The country's been organised
// into 7 provinces since the new constitution, so relabel it.
add_filter( 'woocommerce_get_country_locale', function( $locale ) {
    $locale['NP']['state']['label'] = __( 'Province', 'stanray-custom' );
    return $locale;
} );

// WC has no built-in state list for Nepal, so the field falls back to free
// text (anyone can type anything, e.g. the old zone names). Registering the
// 7 provinces here — the country's real administrative divisions since the
// 2015 constitution — turns it into a proper dropdown instead.
add_filter( 'woocommerce_states', function( $states ) {
    $states['NP'] = [
        'Koshi'         => __( 'Koshi', 'stanray-custom' ),
        'Madhesh'       => __( 'Madhesh', 'stanray-custom' ),
        'Bagmati'       => __( 'Bagmati', 'stanray-custom' ),
        'Gandaki'       => __( 'Gandaki', 'stanray-custom' ),
        'Lumbini'       => __( 'Lumbini', 'stanray-custom' ),
        'Karnali'       => __( 'Karnali', 'stanray-custom' ),
        'Sudurpashchim' => __( 'Sudurpashchim', 'stanray-custom' ),
    ];
    return $states;
} );

// ─── ADDRESS: phone required ───────────────────────────────────────────────
// Delivery riders here contact customers by phone, not email — make it a
// required field instead of WC's optional default. WC_Countries::get_address_
// fields() rebuilds billing_phone from scratch from the woocommerce_checkout_
// phone_field option AFTER the generic woocommerce_default_address_fields
// filter runs, silently overriding it — so that filter alone doesn't reach
// billing_phone. woocommerce_billing_fields/woocommerce_shipping_fields run
// last (same function), so force it there instead; this is what both the
// classic checkout AND the address-book form (form-saved-address.php) read.
add_filter( 'woocommerce_billing_fields', function( $fields ) {
    if ( isset( $fields['billing_phone'] ) ) {
        $fields['billing_phone']['required'] = true;
    }
    return $fields;
} );
add_filter( 'woocommerce_shipping_fields', function( $fields ) {
    if ( isset( $fields['shipping_phone'] ) ) {
        $fields['shipping_phone']['required'] = true;
    }
    return $fields;
} );

// ─── VARIATION "MAKE A SELECTION" MESSAGE ─────────────────────────────────────
// Overrides WooCommerce's default variation-selection prompt (shown when the
// customer clicks Add to Cart before picking a size on a variable product).
add_filter( 'wc_add_to_cart_variation_params', function( $params ) {
    $params['i18n_make_a_selection_text'] = 'Please select which size and item you would like to add to your cart.';
    return $params;
} );

// ─── REMOVE DEFAULT WC WRAPPERS ───────────────────────────────────────────────
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content',  'woocommerce_output_content_wrapper_end', 10 );

// ─── ADD OUR OWN WRAPPERS ─────────────────────────────────────────────────────
// Single product page gets the site's standard max-width container, same as
// the shop, wishlist, and videos pages. Other WC pages (cart, checkout) keep
// the full-bleed container-fluid wrapper for now.
add_action( 'woocommerce_before_main_content', function() {
    $wrap_class = is_product() ? 'container' : 'container-fluid';
    echo '<main class="site-main woo-main"><div class="' . esc_attr( $wrap_class ) . '">';
}, 10 );
add_action( 'woocommerce_after_main_content', function() {
    echo '</div></main>';
}, 10 );

// ─── REMOVE DEFAULT SIDEBAR ───────────────────────────────────────────────────
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

// ─── PRODUCT LOOP: Remove defaults we replace ─────────────────────────────────
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );

// ─── PRODUCT CARD: hover image + sale badge ──────────────────────────────────
add_action( 'woocommerce_before_shop_loop_item_title', function() {
    global $product;
    $gallery_ids = $product->get_gallery_image_ids();
    if ( ! empty( $gallery_ids ) ) {
        echo '<div class="product-card__hover-img">';
        echo wp_get_attachment_image( $gallery_ids[0], 'stanray-product-grid', false, ['class' => 'product-card__img--hover'] );
        echo '</div>';
    }
    if ( $product->is_on_sale() ) {
        echo '<span class="product-card__badge">Sale</span>';
    }
}, 5 );

// ─── WISHLIST: per-user storage ───────────────────────────────────────────────
// Account-only feature (see eskecy_toggle_wishlist_handler below) — stored
// against the user's own account, not a cookie. A cookie is scoped to the
// browser, not the account, so a second person logging into the same browser
// would otherwise inherit whoever's session set it last.
function eskecy_get_wishlist( $user_id = 0 ) {
    $user_id  = $user_id ?: get_current_user_id();
    if ( ! $user_id ) return [];
    $wishlist = get_user_meta( $user_id, '_eskecy_wishlist', true );
    return is_array( $wishlist ) ? $wishlist : [];
}

// Same line-art heart used in the header nav (header.php) — reused everywhere
// a wishlist toggle appears (product cards, PDP, wishlist page) instead of
// the ♥/♡ Unicode glyphs previously used there, which render inconsistently
// across platforms/fonts and looked out of place next to the header's icon.
function eskecy_wishlist_heart_svg( $is_wished ) {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="' . ( $is_wished ? 'currentColor' : 'none' ) . '" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>';
}

// ─── PRODUCT CARD: WISHLIST BUTTON ────────────────────────────────────────────
// Hooked before woocommerce_template_loop_product_link_open (priority 10) so the
// button renders as a sibling of the loop link, not nested inside its
// overflow:hidden anchor — otherwise it gets visually clipped.
add_action( 'woocommerce_before_shop_loop_item', function() {
    global $product;
    $product_id = $product->get_id();
    $wishlist   = eskecy_get_wishlist();
    $in_wish    = in_array( $product_id, $wishlist );
    $icon       = eskecy_wishlist_heart_svg( $in_wish );
    $cls        = $in_wish ? ' is-wished' : '';
    $label      = $in_wish ? 'Remove from Wishlist' : 'Save to Wishlist';
    echo '<button
        class="product-card__wish' . $cls . '"
        data-product-id="' . esc_attr( $product_id ) . '"
        data-nonce="' . esc_attr( wp_create_nonce( 'eskecy_wishlist' ) ) . '"
        aria-label="' . esc_attr( $label ) . '"
        title="' . esc_attr( $label ) . '"
    >' . $icon . '</button>';
}, 5 );

// ─── SINGLE PRODUCT: Rearrange summary hooks ──────────────────────────────────
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 25 );

// ─── SINGLE PRODUCT: 1. STOCK INDICATOR (priority 14 — above Add to Cart) ────
add_action( 'woocommerce_single_product_summary', function() {
    global $product;
    if ( ! $product ) return;

    if ( $product->managing_stock() ) {
        $qty = $product->get_stock_quantity();
        if ( $qty !== null && $qty > 0 && $qty <= 10 ) {
            echo '<p class="stock-indicator stock-indicator--low">⚡ Only ' . absint( $qty ) . ' left in stock — order soon!</p>';
        } elseif ( $product->is_in_stock() ) {
            echo '<p class="stock-indicator stock-indicator--in">✔ In stock &mdash; ready to ship</p>';
        } else {
            echo '<p class="stock-indicator stock-indicator--out">✖ Out of stock</p>';
        }
    } elseif ( $product->is_in_stock() ) {
        echo '<p class="stock-indicator stock-indicator--in">✔ In stock &mdash; ready to ship</p>';
    } else {
        echo '<p class="stock-indicator stock-indicator--out">✖ Out of stock</p>';
    }
}, 14 );

// ─── SINGLE PRODUCT: 2. SIZE GUIDE LINK (priority 26 — below size selector) ──
add_action( 'woocommerce_single_product_summary', function() {
    global $product;
    if ( ! $product ) return;
    $attrs = $product->get_attributes();
    // Show size guide if product has a size attribute
    $has_size = false;
    foreach ( $attrs as $key => $attr ) {
        if ( strpos( strtolower( $key ), 'size' ) !== false || strpos( strtolower( $key ), 'pa_size' ) !== false ) {
            $has_size = true;
            break;
        }
    }
    // Also show if it's a variable product (likely has sizes)
    if ( $has_size || $product->is_type('variable') ) {
        echo '<a href="#" class="size-guide-trigger" aria-haspopup="dialog">📏 Size Guide</a>';
    }
}, 26 );

// ─── SINGLE PRODUCT: 3. WISHLIST BUTTON (priority 31 — below Add to Cart) ────
add_action( 'woocommerce_single_product_summary', function() {
    global $product;
    if ( ! $product ) return;
    $product_id = $product->get_id();

    // Check if already in wishlist
    $wishlist   = eskecy_get_wishlist();
    $in_wish    = in_array( $product_id, $wishlist );
    $icon       = eskecy_wishlist_heart_svg( $in_wish );
    $label      = $in_wish ? 'Remove from Wishlist' : 'Save to Wishlist';
    $state_cls  = $in_wish ? ' is-wished' : '';

    echo '<button
        class="wishlist-btn' . $state_cls . '"
        data-product-id="' . esc_attr( $product_id ) . '"
        aria-label="' . esc_attr( $label ) . '"
        data-nonce="' . esc_attr( wp_create_nonce( 'eskecy_wishlist' ) ) . '"
    ><span class="wishlist-btn__icon">' . $icon . '</span>
    <span class="wishlist-btn__label">' . esc_html( $label ) . '</span></button>';
}, 31 );

// ─── SINGLE PRODUCT: Divider before add to cart ───────────────────────────────
add_action( 'woocommerce_single_product_summary', function() {
    echo '<div class="product-summary__divider"></div>';
}, 28 );

// ─── WISHLIST: Render the full wishlist page ──────────────────────────────────
add_shortcode( 'eskecy_wishlist', function() {
    $wishlist   = array_filter( array_map( 'absint', eskecy_get_wishlist() ) );

    if ( empty( $wishlist ) ) {
        return '<div class="wishlist-empty"><p>Your wishlist is empty.</p><a href="' . esc_url( wc_get_page_permalink('shop') ) . '" class="btn btn--primary">Shop Now</a></div>';
    }

    ob_start();
    echo '<div class="wishlist-grid">';
    foreach ( $wishlist as $pid ) {
        $product = wc_get_product( $pid );
        if ( ! $product || ! $product->is_visible() ) continue;
        $img = $product->get_image_id() ? wp_get_attachment_image_url( $product->get_image_id(), 'stanray-product-grid' ) : wc_placeholder_img_src();
        echo '<div class="wishlist-card product-card">';
        echo '<div class="product-card__image-wrap">';
        echo '<a href="' . esc_url( $product->get_permalink() ) . '"><img src="' . esc_url( $img ) . '" alt="' . esc_attr( $product->get_name() ) . '" class="product-card__img"></a>';
        echo '<button
            class="product-card__wish is-wished wishlist-remove"
            data-product-id="' . esc_attr( $pid ) . '"
            data-nonce="' . esc_attr( wp_create_nonce('eskecy_wishlist') ) . '"
            aria-label="Remove from Wishlist"
            title="Remove from Wishlist"
        >' . eskecy_wishlist_heart_svg( true ) . '</button>';
        echo '</div>';
        echo '<div class="product-card__info">';
        echo '<h3 class="product-card__title"><a href="' . esc_url( $product->get_permalink() ) . '">' . esc_html( $product->get_name() ) . '</a></h3>';
        echo '<div class="product-card__price">' . $product->get_price_html() . '</div>';
        echo '</div></div>';
    }
    echo '</div>';
    return ob_get_clean();
} );

// ─── WISHLIST: AJAX toggle ────────────────────────────────────────────────────
add_action( 'wp_ajax_eskecy_toggle_wishlist',        'eskecy_toggle_wishlist_handler' );
add_action( 'wp_ajax_nopriv_eskecy_toggle_wishlist', 'eskecy_toggle_wishlist_handler' );

function eskecy_toggle_wishlist_handler() {
    check_ajax_referer( 'eskecy_wishlist', 'nonce' );

    // Wishlist is account-only — guests get a structured error so the JS can
    // open the login/register modal instead of silently failing.
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [
            'code'    => 'login_required',
            'message' => 'Please log in to save items to your wishlist.',
        ] );
    }

    $product_id = absint( $_POST['product_id'] ?? 0 );
    if ( ! $product_id ) wp_send_json_error();

    $user_id  = get_current_user_id();
    $wishlist = eskecy_get_wishlist( $user_id );

    if ( in_array( $product_id, $wishlist ) ) {
        $wishlist = array_values( array_diff( $wishlist, [ $product_id ] ) );
        $action   = 'removed';
    } else {
        $wishlist[] = $product_id;
        $action     = 'added';
    }

    update_user_meta( $user_id, '_eskecy_wishlist', $wishlist );

    wp_send_json_success([
        'action'  => $action,
        'count'   => count( $wishlist ),
        'wishlist'=> $wishlist,
    ]);
}

// ─── WISHLIST: Pass AJAX URL to JS ────────────────────────────────────────────
add_action( 'wp_enqueue_scripts', function() {
    wp_localize_script( 'stanray-main', 'eskecyWishlist', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'eskecy_wishlist' ),
    ]);
}, 20 );

// ─── HEADER: Wishlist icon count ──────────────────────────────────────────────
// Inject wishlist count into header actions via hook in header.php
add_action( 'stanray_header_actions', function() {
    $count = count( eskecy_get_wishlist() );
    echo '<a href="' . esc_url( home_url('/wishlist') ) . '" class="header-wishlist" aria-label="Wishlist (' . $count . ' items)">';
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>';
    if ( $count > 0 ) echo '<span class="header-wishlist__count">' . absint( $count ) . '</span>';
    echo '</a>';
} );

// ─── BREADCRUMBS ──────────────────────────────────────────────────────────────
add_filter( 'woocommerce_breadcrumb_defaults', function( $defaults ) {
    $defaults['delimiter']   = '<span class="breadcrumb__sep">/</span>';
    $defaults['wrap_before'] = '<nav class="breadcrumb" aria-label="Breadcrumb"><ol>';
    $defaults['wrap_after']  = '</ol></nav>';
    $defaults['before']      = '<li>';
    $defaults['after']       = '</li>';
    return $defaults;
} );

// ─── CART fragments ───────────────────────────────────────────────────────────
function stanray_cart_count() {
    return WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
}
add_filter( 'woocommerce_add_to_cart_fragments', function( $fragments ) {
    $count = WC()->cart->get_cart_contents_count();
    $fragments['.cart-icon__count'] = '<span class="cart-icon__count">' . esc_html( $count ) . '</span>';

    // Ride the notice HTML along on this same fragments response instead of
    // making the browser fire a second, separate admin-ajax.php round trip
    // (stanray_flush_notices) just to fetch it — see stanrayFlushNotices()
    // in main.js. That second hit is what was making the "Added to cart"
    // toast take so long to appear on hosts where admin-ajax.php is slow.
    if ( function_exists( 'wc_print_notices' ) ) {
        ob_start();
        wc_print_notices();
        $fragments['stanray_notices_html'] = ob_get_clean();
    }

    return $fragments;
} );

// ─── MY ACCOUNT: Remove Downloads tab ──────────────────────────────────────────
add_filter( 'woocommerce_account_menu_items', function( $items ) {
    unset( $items['downloads'] );
    return $items;
} );

// ─── CHECKOUT: Remove company field ───────────────────────────────────────────
add_filter( 'woocommerce_checkout_fields', function( $fields ) {
    unset( $fields['billing']['billing_company'] );
    return $fields;
} );

// ─── CHECKOUT: Move coupon form from top of page to just above the Place Order button ──
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
add_action( 'woocommerce_review_order_before_submit', 'woocommerce_checkout_coupon_form', 10 );

// ─── MY ACCOUNT: Order details — status as a colored badge in the table ───────
// myaccount/view-order.php's opening line ("Order #X was placed on Y and is
// currently Z.") buries the status as plain text. Drop that clause and show
// it instead as a colored .stanray-status pill in the order totals table
// (between Shipping and Total), matching how status already reads everywhere
// else in the account area (order list, dashboard).
add_filter( 'woocommerce_order_details_status', function( $text, $order ) {
    return sprintf(
        /* translators: 1: order number 2: order date */
        esc_html__( 'Order #%1$s was placed on %2$s.', 'stanray-custom' ),
        '<mark class="order-number">' . $order->get_order_number() . '</mark>',
        '<mark class="order-date">' . wc_format_datetime( $order->get_date_created() ) . '</mark>'
    );
}, 10, 2 );

add_filter( 'woocommerce_get_order_item_totals', function( $total_rows, $order ) {
    if ( ! is_wc_endpoint_url( 'view-order' ) ) return $total_rows;

    $status = $order->get_status();
    $badge  = '<span class="stanray-status stanray-status--' . esc_attr( $status ) . '">'
        . esc_html( wc_get_order_status_name( $status ) ) . '</span>';

    $with_status = [];
    foreach ( $total_rows as $key => $row ) {
        $with_status[ $key ] = $row;
        if ( 'shipping' === $key ) {
            $with_status['stanray_status'] = [
                'type'  => 'status',
                'label' => __( 'Status:', 'stanray-custom' ),
                'value' => $badge,
            ];
        }
    }
    // Order has no shipping row (e.g. no shipping method needed) — fall back
    // to appending right before the total instead of losing the row entirely.
    if ( ! isset( $with_status['stanray_status'] ) ) {
        $position = array_search( 'order_total', array_keys( $with_status ), true );
        $position = ( false === $position ) ? count( $with_status ) : $position;
        $with_status = array_slice( $with_status, 0, $position, true )
            + [ 'stanray_status' => [ 'type' => 'status', 'label' => __( 'Status:', 'stanray-custom' ), 'value' => $badge ] ]
            + array_slice( $with_status, $position, null, true );
    }

    return $with_status;
}, 10, 2 );

// ─── THANK YOU / ORDER RECEIVED PAGE ──────────────────────────────────────────

// Print-only letterhead — invisible on screen, shown via @media print so the
// printed/"Save as PDF" invoice has a proper header instead of the on-screen hero.
add_action( 'woocommerce_before_thankyou', function( $order_id ) {
    $order = wc_get_order( $order_id );
    if ( ! $order ) return;
    ?>
    <div class="invoice-letterhead">
        <div class="invoice-letterhead__brand">
            <?php if ( has_custom_logo() ) { the_custom_logo(); } else { bloginfo( 'name' ); } ?>
        </div>
        <div class="invoice-letterhead__meta">
            <span class="invoice-letterhead__label">Invoice</span>
            <span>Order #<?php echo esc_html( $order->get_order_number() ); ?></span>
            <span><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></span>
        </div>
    </div>
    <?php
}, 1 );

add_action( 'woocommerce_before_thankyou', function( $order_id ) {
    echo '<div class="thankyou-hero__icon" aria-hidden="true"><svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="20" cy="20" r="19" stroke="currentColor" stroke-width="1.5"/><path d="M12 20.5l5.5 5.5L28 14.5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg></div>';
    echo '<span class="thankyou-hero__eyebrow">' . esc_html__( 'Order Confirmed', 'stanray' ) . '</span>';
}, 5 );

add_action( 'woocommerce_thankyou', function( $order_id ) {
    $shop_url = wc_get_page_permalink( 'shop' );
    echo '<div class="thankyou-actions">';
    echo '<button type="button" class="btn btn--outline order-action" onclick="window.print()">' . esc_html__( 'Print / Save as PDF', 'stanray' ) . '</button>';
    echo '<a href="' . esc_url( $shop_url ) . '" class="btn btn--primary order-action">' . esc_html__( 'Continue Shopping', 'stanray' ) . '</a>';
    if ( is_user_logged_in() ) {
        echo '<a href="' . esc_url( wc_get_account_endpoint_url( 'orders' ) ) . '" class="btn btn--outline order-action">' . esc_html__( 'View Order History', 'stanray' ) . '</a>';
    }
    echo '</div>';
}, 20 );

// ─── SHOP settings ────────────────────────────────────────────────────────────
add_filter( 'loop_shop_per_page', fn() => 24, 20 );
add_filter( 'woocommerce_default_catalog_orderby', fn() => 'date' );
add_filter( 'woocommerce_get_image_size_gallery_thumbnail', function( $size ) {
    return [ 'width' => 150, 'height' => 188, 'crop' => 1 ];
} );
add_filter( 'woocommerce_output_related_products_args', function( $args ) {
    $args['posts_per_page'] = 4;
    $args['columns']        = 4;
    return $args;
} );

// ─── DISABLE WC default CSS ───────────────────────────────────────────────────
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

// ─── OVO SHOP GRID: discount %, colour count helpers ──────────────────────────
function stanray_get_discount_percent( $product ) {
    if ( ! $product instanceof WC_Product || ! $product->is_on_sale() ) return 0;

    if ( $product->is_type( 'variable' ) ) {
        $regular = (float) $product->get_variation_regular_price( 'min', true );
        $sale    = (float) $product->get_variation_sale_price( 'min', true );
    } else {
        $regular = (float) $product->get_regular_price();
        $sale    = (float) $product->get_sale_price();
    }

    if ( $regular <= 0 || $sale <= 0 || $sale >= $regular ) return 0;

    return (int) round( ( ( $regular - $sale ) / $regular ) * 100 );
}

function stanray_get_colour_count( $product ) {
    if ( ! $product instanceof WC_Product ) return 0;

    $terms = wc_get_product_terms( $product->get_id(), 'pa_color', [ 'fields' => 'ids' ] );
    if ( empty( $terms ) ) {
        $terms = wc_get_product_terms( $product->get_id(), 'pa_colour', [ 'fields' => 'ids' ] );
    }
    if ( ! empty( $terms ) ) return count( $terms );

    foreach ( $product->get_attributes() as $key => $attribute ) {
        if ( ! is_a( $attribute, 'WC_Product_Attribute' ) ) continue;
        if ( stripos( $key, 'colour' ) === false && stripos( $key, 'color' ) === false ) continue;

        if ( $attribute->is_taxonomy() ) {
            $attr_terms = $attribute->get_terms();
            return is_array( $attr_terms ) ? count( $attr_terms ) : 0;
        }
        $options = $attribute->get_options();
        return is_array( $options ) ? count( $options ) : 0;
    }

    return 0;
}

// ─── OVO SHOP GRID: editorial banner tiles (per product category) ─────────────
function stanray_get_editorial_banners( $limit = 6 ) {
    $cats = get_terms( [
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
        'parent'     => 0,
        'exclude'    => [ get_option( 'default_product_cat' ) ],
        'number'     => $limit,
        'orderby'    => 'count',
        'order'      => 'DESC',
    ] );
    if ( is_wp_error( $cats ) || empty( $cats ) ) return [];

    $banners = [];
    foreach ( $cats as $cat ) {
        $video_id  = get_term_meta( $cat->term_id, '_stanray_banner_video', true );
        $video_url = $video_id ? wp_get_attachment_url( $video_id ) : '';

        $img_id = get_term_meta( $cat->term_id, 'thumbnail_id', true );
        $poster = $img_id ? wp_get_attachment_image_url( $img_id, 'stanray-hero' ) : '';

        if ( ! $video_url && ! $poster ) continue;

        $label = get_term_meta( $cat->term_id, '_stanray_banner_label', true );
        if ( ! $label ) $label = $cat->name;

        $banners[] = [
            'video'  => $video_url,
            'poster' => $poster,
            'label'  => $label,
            'url'    => get_term_link( $cat ),
        ];
    }
    return $banners;
}

function stanray_render_banner_tile( $banner ) {
    ?>
    <li class="ovo-banner-tile" aria-hidden="true">
        <a href="<?php echo esc_url( $banner['url'] ); ?>" class="ovo-banner-tile__link">
            <?php if ( ! empty( $banner['video'] ) ) : ?>
                <video class="ovo-banner-tile__video"
                       src="<?php echo esc_url( $banner['video'] ); ?>"
                       <?php if ( ! empty( $banner['poster'] ) ) : ?>poster="<?php echo esc_url( $banner['poster'] ); ?>"<?php endif; ?>
                       autoplay muted loop playsinline></video>
            <?php elseif ( ! empty( $banner['poster'] ) ) : ?>
                <img class="ovo-banner-tile__img" src="<?php echo esc_url( $banner['poster'] ); ?>" alt="" loading="lazy">
            <?php endif; ?>
            <span class="ovo-banner-tile__label"><?php echo esc_html( $banner['label'] ); ?></span>
        </a>
    </li>
    <?php
}

/**
 * Renders the continuous OVO shop grid, interspersing editorial banner tiles
 * every 12 products (after the 2nd, then after the 6th) so 2-col-wide banners
 * land alternately on the right then left as the 4-col grid wraps rows:
 *   Row1: P P [Banner-right]   Row2: P P P P   Row3: [Banner-left] P P   Row4: P P P P
 */
function stanray_render_shop_grid( $ids, $banners = [] ) {
    $banner_count = count( $banners );
    $bi           = 0;
    $emitted      = 0;

    foreach ( $ids as $pid ) {
        $GLOBALS['product'] = wc_get_product( $pid );
        if ( ! $GLOBALS['product'] || ! $GLOBALS['product']->is_visible() ) continue;

        global $post;
        $post = get_post( $pid );
        setup_postdata( $post );
        wc_get_template_part( 'content', 'product' );
        $emitted++;

        if ( $banner_count && ( $emitted % 12 === 2 || $emitted % 12 === 6 ) ) {
            stanray_render_banner_tile( $banners[ $bi % $banner_count ] );
            $bi++;
        }
    }
    wp_reset_postdata();
}

// ─── OVO SHOP GRID: price bounds for the Sort & Filter price range ────────────
function stanray_get_shop_price_bounds() {
    global $wpdb;

    $row = $wpdb->get_row(
        "SELECT MIN(CAST(pm.meta_value AS DECIMAL(10,2))) AS min_price,
                MAX(CAST(pm.meta_value AS DECIMAL(10,2))) AS max_price
         FROM {$wpdb->postmeta} pm
         INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE pm.meta_key = '_price'
           AND p.post_status = 'publish'
           AND p.post_type IN ('product', 'product_variation')"
    );

    $min = $row && $row->min_price !== null ? (float) $row->min_price : 0;
    $max = $row && $row->max_price !== null ? (float) $row->max_price : 0;

    return [ 'min' => floor( $min ), 'max' => ceil( $max ) ];
}

// ─── ADMIN: Editorial banner video field on product category terms ────────────
add_action( 'product_cat_add_form_fields', function( $taxonomy ) { ?>
    <div class="form-field">
        <label for="stanray_banner_video">Editorial Banner Video</label>
        <input type="hidden" id="stanray_banner_video" name="stanray_banner_video" value="" class="stanray-banner-video-input">
        <button type="button" class="button stanray-banner-video-select">Select Video</button>
        <button type="button" class="button stanray-banner-video-remove" style="display:none;">Remove</button>
        <p class="stanray-banner-video-preview"></p>
        <p class="description">Optional looping video shown as an editorial banner tile in the Shop page grid for this category (mp4 recommended). Falls back to the category image if left empty.</p>
    </div>
    <div class="form-field">
        <label for="stanray_banner_label">Banner Label</label>
        <input type="text" id="stanray_banner_label" name="stanray_banner_label" value="">
        <p class="description">Text overlay on the banner tile. Defaults to the category name.</p>
    </div>
<?php } );

add_action( 'product_cat_edit_form_fields', function( $term ) {
    $video_id  = get_term_meta( $term->term_id, '_stanray_banner_video', true );
    $label     = get_term_meta( $term->term_id, '_stanray_banner_label', true );
    $video_url = $video_id ? wp_get_attachment_url( $video_id ) : '';
    ?>
    <tr class="form-field">
        <th scope="row"><label for="stanray_banner_video">Editorial Banner Video</label></th>
        <td>
            <input type="hidden" id="stanray_banner_video" name="stanray_banner_video" value="<?php echo esc_attr( $video_id ); ?>" class="stanray-banner-video-input">
            <button type="button" class="button stanray-banner-video-select">Select Video</button>
            <button type="button" class="button stanray-banner-video-remove" style="<?php echo $video_id ? '' : 'display:none;'; ?>">Remove</button>
            <p class="stanray-banner-video-preview"><?php echo $video_url ? esc_html( basename( $video_url ) ) : ''; ?></p>
            <p class="description">Optional looping video shown as an editorial banner tile in the Shop page grid for this category (mp4 recommended). Falls back to the category image if left empty.</p>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row"><label for="stanray_banner_label">Banner Label</label></th>
        <td>
            <input type="text" id="stanray_banner_label" name="stanray_banner_label" value="<?php echo esc_attr( $label ); ?>">
            <p class="description">Text overlay on the banner tile. Defaults to the category name.</p>
        </td>
    </tr>
<?php } );

function stanray_save_banner_video_field( $term_id ) {
    if ( isset( $_POST['stanray_banner_video'] ) ) {
        update_term_meta( $term_id, '_stanray_banner_video', absint( $_POST['stanray_banner_video'] ) );
    }
    if ( isset( $_POST['stanray_banner_label'] ) ) {
        update_term_meta( $term_id, '_stanray_banner_label', sanitize_text_field( wp_unslash( $_POST['stanray_banner_label'] ) ) );
    }
}
add_action( 'created_product_cat', 'stanray_save_banner_video_field' );
add_action( 'edited_product_cat',  'stanray_save_banner_video_field' );

// ─── PDP REDESIGN: enqueue dedicated JS on single product pages only ──────────
add_action( 'wp_enqueue_scripts', function() {
    if ( is_product() ) {
        wp_enqueue_script(
            'stanray-single-product',
            STANRAY_URI . '/assets/js/single-product.js',
            [ 'jquery' ],
            STANRAY_VERSION,
            true
        );
    }
}, 20 );

// ─── PDP REDESIGN: tabs — drop "Additional information", add "Shipping & Return" ──
add_filter( 'woocommerce_product_tabs', function( $tabs ) {
    unset( $tabs['additional_information'] );

    if ( isset( $tabs['reviews'] ) ) {
        $tabs['reviews']['title'] = __( 'Review', 'stanray-custom' );
    }

    $tabs['shipping_return'] = [
        'title'    => __( 'Shipping & Return', 'stanray-custom' ),
        'priority' => 20,
        'callback' => 'stanray_shipping_return_tab_content',
    ];

    return $tabs;
}, 98 );

function stanray_shipping_return_tab_content() {
    $default = "Standard shipping takes 3–5 business days. Express shipping is available at checkout.\n\nReturns are accepted within 30 days of delivery. Items must be unused, unworn, and in original packaging.";
    $content = get_theme_mod( 'product_shipping_return_content', $default );
    echo '<div class="shipping-return-content">' . wp_kses_post( wpautop( $content ) ) . '</div>';
}

// ─── PDP REDESIGN: add a "Review title" field to the review form ──────────────
add_filter( 'woocommerce_product_review_comment_form_args', function( $comment_form ) {
    $title_field = '<p class="comment-form-review-title">'
        . '<label for="review_title">' . esc_html__( 'Review title', 'stanray-custom' ) . '</label>'
        . '<input id="review_title" name="review_title" type="text" maxlength="120" placeholder="' . esc_attr__( 'Sum up your review in a few words', 'stanray-custom' ) . '">'
        . '</p>';
    $comment_form['comment_field'] = $title_field . $comment_form['comment_field'];
    return $comment_form;
}, 20 );

add_action( 'comment_post', function( $comment_id ) {
    if ( isset( $_POST['review_title'] ) ) {
        update_comment_meta( $comment_id, 'review_title', sanitize_text_field( wp_unslash( $_POST['review_title'] ) ) );
    }
} );

// ─── PDP REDESIGN: "Sort by" support for the review list (?review_sort=) ──────
add_filter( 'comments_template_query_args', function( $args ) {
    if ( ! is_product() ) return $args;

    $sort = isset( $_GET['review_sort'] ) ? sanitize_key( $_GET['review_sort'] ) : 'newest';

    switch ( $sort ) {
        case 'oldest':
            $args['order'] = 'ASC';
            break;
        case 'highest':
            $args['meta_key'] = 'rating';
            $args['orderby']  = [ 'meta_value_num' => 'DESC', 'comment_date' => 'DESC' ];
            break;
        case 'lowest':
            $args['meta_key'] = 'rating';
            $args['orderby']  = [ 'meta_value_num' => 'ASC', 'comment_date' => 'DESC' ];
            break;
        default:
            $args['order'] = 'DESC';
            break;
    }

    return $args;
} );

// ─── PDP REDESIGN: star-by-star rating breakdown (for the review summary bars) ──
function stanray_get_rating_breakdown( $product_id ) {
    global $wpdb;

    $rows = $wpdb->get_results( $wpdb->prepare(
        "SELECT cm.meta_value AS rating, COUNT(*) AS cnt
         FROM {$wpdb->comments} c
         INNER JOIN {$wpdb->commentmeta} cm ON cm.comment_id = c.comment_ID AND cm.meta_key = 'rating'
         WHERE c.comment_post_ID = %d
           AND c.comment_approved = '1'
         GROUP BY cm.meta_value",
        $product_id
    ) );

    $counts = [ 5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0 ];
    $total  = 0;
    foreach ( $rows as $row ) {
        $r = (int) round( (float) $row->rating );
        if ( $r >= 1 && $r <= 5 ) {
            $counts[ $r ] += (int) $row->cnt;
            $total        += (int) $row->cnt;
        }
    }

    $breakdown = [];
    foreach ( [ 5, 4, 3, 2, 1 ] as $star ) {
        $breakdown[ $star ] = [
            'count'   => $counts[ $star ],
            'percent' => $total ? round( ( $counts[ $star ] / $total ) * 100 ) : 0,
        ];
    }

    return [ 'total' => $total, 'breakdown' => $breakdown ];
}

// ─── PDP REDESIGN: "Buy Now" — add to cart, then straight to checkout ─────────
// Also fixes a site-wide bug: WooCommerce's classic (non-AJAX) add-to-cart
// form doesn't redirect anywhere by default (the "Redirect to cart page"
// setting is off), so the response the browser is looking at IS the raw POST
// request. Refreshing then trips the browser's "Confirm Form Resubmission"
// prompt, and confirming it re-adds the item — the cart total climbs by one
// every refresh. Redirecting back to the product page after a normal add
// (Post/Redirect/Get) makes the current URL a plain GET again, so refreshing
// is safe. The "added to cart" notice still shows because WC stores it in
// the session, not in the URL.
//
// wp_get_referer() can't be used for the redirect target here — the
// add-to-cart form posts to the product's own permalink, so WP's
// self-referential guard (it refuses to return a referer equal to the
// current request URL) always makes it return false in this exact case.
// The product itself is passed as the filter's 2nd argument, so build the
// redirect from that instead.
add_filter( 'woocommerce_add_to_cart_redirect', function( $url, $product = null ) {
    if ( isset( $_REQUEST['buy_now'] ) ) {
        return wc_get_checkout_url();
    }
    if ( $url ) {
        return $url;
    }
    return ( $product instanceof WC_Product ) ? $product->get_permalink() : $url;
}, 10, 2 );

// "Buy Now" submits the exact same add-to-cart form as the regular button —
// it just also redirects to checkout afterward (above) — so it was adding
// this item to whatever's ALREADY in the cart, and checkout showed every
// pre-existing item too, not just the one thing the customer meant to buy
// right now. WC_Form_Handler::add_to_cart_action runs on wp_loaded @20, so
// stash the existing cart and empty it here (@15, before that runs) so it
// adds this item to a clean cart instead. Restored below once they're done
// with checkout — either the order goes through, or they wander off
// elsewhere without finishing it.
add_action( 'wp_loaded', function() {
    if ( empty( $_REQUEST['buy_now'] ) || empty( $_REQUEST['add-to-cart'] ) ) return;
    if ( ! WC()->cart || WC()->cart->is_empty() ) return; // nothing to protect

    $stashed = [];
    foreach ( WC()->cart->get_cart() as $item ) {
        $stashed[] = [
            'product_id'   => $item['product_id'],
            'quantity'     => $item['quantity'],
            'variation_id' => $item['variation_id'],
            'variation'    => $item['variation'],
        ];
    }

    WC()->session->set( 'stanray_stashed_cart', $stashed );
    WC()->cart->empty_cart( false ); // false: leave the persistent (cross-session) cart alone
}, 15 );

function stanray_restore_stashed_cart() {
    if ( ! WC()->session ) return;
    $stashed = WC()->session->get( 'stanray_stashed_cart' );
    if ( ! $stashed ) return;

    WC()->session->set( 'stanray_stashed_cart', null );
    foreach ( $stashed as $item ) {
        WC()->cart->add_to_cart( $item['product_id'], $item['quantity'], $item['variation_id'], $item['variation'] );
    }
}
add_action( 'woocommerce_thankyou', 'stanray_restore_stashed_cart' );
add_action( 'template_redirect', function() {
    if ( is_checkout() ) return; // covers checkout + its endpoints (order-pay, order-received, ...)
    stanray_restore_stashed_cart();
}, 5 );

add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( ! in_array( $hook, [ 'edit-tags.php', 'term.php' ], true ) ) return;
    if ( ( $_GET['taxonomy'] ?? '' ) !== 'product_cat' ) return;

    wp_enqueue_media();
    wp_add_inline_script( 'jquery-core', <<<'JS'
    jQuery(function ($) {
        var frame;
        $(document).on('click', '.stanray-banner-video-select', function (e) {
            e.preventDefault();
            var $btn    = $(this);
            var $field  = $btn.closest('.form-field, td');
            var $input  = $field.find('.stanray-banner-video-input');
            var $remove = $field.find('.stanray-banner-video-remove');
            var $preview = $field.find('.stanray-banner-video-preview');

            frame = wp.media({
                title: 'Select Editorial Banner Video',
                library: { type: 'video' },
                multiple: false,
            });
            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                $input.val(attachment.id);
                $preview.text(attachment.filename || attachment.title || '');
                $remove.show();
            });
            frame.open();
        });

        $(document).on('click', '.stanray-banner-video-remove', function (e) {
            e.preventDefault();
            var $btn   = $(this);
            var $field = $btn.closest('.form-field, td');
            $field.find('.stanray-banner-video-input').val('');
            $field.find('.stanray-banner-video-preview').text('');
            $btn.hide();
        });
    });
JS
    , 'after' );
}, 20 );
