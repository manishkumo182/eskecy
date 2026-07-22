<?php
/**
 * StanRay Custom Theme - functions.php
 * Core theme setup, WooCommerce support, and asset loading
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ─── THEME CONSTANTS ──────────────────────────────────────────────────────────
define( 'STANRAY_VERSION', '1.0.11' );
define( 'STANRAY_DIR', get_template_directory() );
define( 'STANRAY_URI', get_template_directory_uri() );


// ─── THEME SETUP ──────────────────────────────────────────────────────────────
function stanray_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
    add_theme_support( 'custom-logo', [
        'height'      => 60,
        'width'       => 200,
        'flex-width'  => true,
        'flex-height' => true,
    ] );

    // WooCommerce
    add_theme_support( 'woocommerce', [
        'thumbnail_image_width' => 800,
        'single_image_width'    => 1200,
        'product_grid'          => [
            'default_rows'    => 4,
            'min_rows'        => 1,
            'max_rows'        => 10,
            'default_columns' => 3,
            'min_columns'     => 1,
            'max_columns'     => 6,
        ],
    ] );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );

    // Image sizes
    add_image_size( 'stanray-product-grid',   600,  750,  true );
    add_image_size( 'stanray-product-single', 900,  1125, true );
    add_image_size( 'stanray-hero',           1920, 1080, true );
    add_image_size( 'stanray-editorial',      900,  1200, false );

    // ── Menus — ALL locations registered here, in one place ──
    register_nav_menus( [
        'primary'   => __( 'Header Menu',              'stanray-custom' ),
        'secondary' => __( 'Secondary / Account Nav',  'stanray-custom' ),
        'footer'    => __( 'Footer Bottom Bar',        'stanray-custom' ),
    ] );

    load_theme_textdomain( 'stanray-custom', STANRAY_DIR . '/languages' );
}
add_action( 'after_setup_theme', 'stanray_setup' );




// ─── ENQUEUE STYLES & SCRIPTS ─────────────────────────────────────────────────
function stanray_enqueue_assets() {

    // Fonts — EB Garamond (display/serif), Barlow (body/sans), Inter + JetBrains Mono (hero/editorial)
    wp_enqueue_style(
        'stanray-fonts',
        'https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Barlow:wght@300;400;500;600;700&family=Inter:wght@400;500;700;900&family=JetBrains+Mono:wght@400;500;700&display=swap',
        [],
        null
    );

    // Main CSS
    wp_enqueue_style( 'stanray-main', STANRAY_URI . '/assets/css/main.css?v1', [ 'stanray-fonts' ], STANRAY_VERSION );

    // Hero + Header CSS
    wp_enqueue_style( 'stanray-hero-header', STANRAY_URI . '/assets/css/hero-header.css?v1', [ 'stanray-main' ], STANRAY_VERSION );

    // Top Marquee Bar CSS
    wp_enqueue_style( 'stanray-marquee-bar', STANRAY_URI . '/assets/css/marquee-bar.css?v1', [ 'stanray-main' ], STANRAY_VERSION );

    // WooCommerce CSS — only on WC pages
    if ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) {
        wp_enqueue_style( 'stanray-woo', STANRAY_URI . '/assets/css/woocommerce.css?v1', [ 'stanray-main' ], STANRAY_VERSION );
    }

    // Account CSS — on account pages, and everywhere for guests (powers the
    // wishlist login modal, which reuses the same Login/Register markup).
    if ( is_account_page() || ! is_user_logged_in() ) {
        wp_enqueue_style( 'stanray-account', STANRAY_URI . '/assets/css/account.css?v1', [ 'stanray-main' ], STANRAY_VERSION );
    }

    // Main JS
    wp_enqueue_script( 'stanray-main', STANRAY_URI . '/assets/js/main.js', [ 'jquery' ], STANRAY_VERSION, true );

    // WooCommerce cart fragments
    if ( class_exists( 'WooCommerce' ) ) {
        wp_enqueue_script( 'wc-cart-fragments' );
    }

    // Pass data to JS
    wp_localize_script( 'stanray-main', 'stanrayData', [
        'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
        'nonce'      => wp_create_nonce( 'stanray_nonce' ),
        'cartUrl'    => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart' ),
        'currency'   => function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '$',
        'isLoggedIn' => is_user_logged_in(),
    ] );

    // Comment reply
    if ( is_singular() && comments_open() ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'stanray_enqueue_assets' );


// ─── ADMIN: Match storefront font (Barlow) on the wp-admin Profile page ───────
add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( ! in_array( $hook, [ 'profile.php', 'user-edit.php' ], true ) ) return;

    wp_enqueue_style(
        'stanray-admin-fonts',
        'https://fonts.googleapis.com/css2?family=Barlow:wght@300;400;500;600;700&display=swap',
        [],
        null
    );

    $css = "
        body.profile-php #wpbody-content,
        body.user-edit-php #wpbody-content {
            font-family: 'Barlow', 'Helvetica Neue', sans-serif;
        }
        body.profile-php #wpbody-content input,
        body.profile-php #wpbody-content select,
        body.profile-php #wpbody-content textarea,
        body.profile-php #wpbody-content button,
        body.user-edit-php #wpbody-content input,
        body.user-edit-php #wpbody-content select,
        body.user-edit-php #wpbody-content textarea,
        body.user-edit-php #wpbody-content button {
            font-family: inherit;
        }
    ";
    wp_add_inline_style( 'stanray-admin-fonts', $css );
} );


// ─── WIDGET AREAS ─────────────────────────────────────────────────────────────
// All sidebars registered ONCE in this single function.
function stanray_widgets_init() {

    // Shop sidebar
    register_sidebar( [
        'name'          => __( 'Shop Sidebar', 'stanray-custom' ),
        'id'            => 'shop-sidebar',
        'description'   => __( 'Widgets for the shop/category sidebar.', 'stanray-custom' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget__title">',
        'after_title'   => '</h3>',
    ] );

    // Footer columns
    register_sidebar( [
        'name'          => __( 'Footer Col 1', 'stanray-custom' ),
        'id'            => 'footer-col-1',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<p class="col-title">',
        'after_title'   => '</p>',
    ] );
    register_sidebar( [
        'name'          => __( 'Footer Col 2', 'stanray-custom' ),
        'id'            => 'footer-col-2',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<p class="col-title">',
        'after_title'   => '</p>',
    ] );
    register_sidebar( [
        'name'          => __( 'Footer Col 3', 'stanray-custom' ),
        'id'            => 'footer-col-3',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<p class="col-title">',
        'after_title'   => '</p>',
    ] );
}
add_action( 'widgets_init', 'stanray_widgets_init' );


// ─── INCLUDE FILES ────────────────────────────────────────────────────────────
require_once STANRAY_DIR . '/inc/helpers.php';
require_once STANRAY_DIR . '/inc/woocommerce.php';
require_once STANRAY_DIR . '/inc/ajax.php';
require_once STANRAY_DIR . '/inc/customizer.php';
require_once STANRAY_DIR . '/inc/admin-hero-banner.php';
require_once STANRAY_DIR . '/inc/admin-select-style.php';
require_once STANRAY_DIR . '/inc/admin-shop-the-look.php';
require_once STANRAY_DIR . '/inc/admin-homepage-video.php';
require_once STANRAY_DIR . '/inc/admin-events-hero.php';
require_once STANRAY_DIR . '/inc/admin-about-page.php';
require_once STANRAY_DIR . '/inc/gateway-qr-payment.php';
require_once STANRAY_DIR . '/inc/notify-payment-success.php';
require_once STANRAY_DIR . '/inc/order-status-delivered.php';
require_once STANRAY_DIR . '/inc/post-purchase-review.php';
require_once STANRAY_DIR . '/inc/wishlist-login-modal.php';
require_once STANRAY_DIR . '/inc/address-book.php';


// ─── BODY CLASSES ─────────────────────────────────────────────────────────────
function stanray_body_classes( $classes ) {
    if ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) $classes[] = 'is-shop';
    if ( is_product() )      $classes[] = 'is-product';
    if ( is_cart() )         $classes[] = 'is-cart';
    if ( is_checkout() )     $classes[] = 'is-checkout';
    if ( is_account_page() ) $classes[] = 'is-account';
    if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) $classes[] = 'is-order-received';
    return $classes;
}
add_filter( 'body_class', 'stanray_body_classes' );


// ─── REMOVE WP BLOAT ──────────────────────────────────────────────────────────
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
add_filter( 'the_generator', '__return_false' );


// ─── EXCERPT ──────────────────────────────────────────────────────────────────
add_filter( 'excerpt_length', fn() => 20 );
add_filter( 'excerpt_more',   fn() => '&hellip;' );


// ─── CUSTOMIZER ───────────────────────────────────────────────────────────────
// All customize_register hooks merged into ONE function.
add_action( 'customize_register', function ( WP_Customize_Manager $wp_customize ) {

    // ── Hero Section ──────────────────────────────────────────
    $wp_customize->add_section( 'stanray_hero', [
        'title'    => __( 'Hero Section', 'stanray-custom' ),
        'priority' => 30,
    ] );

    $wp_customize->add_setting( 'hero_image_id', [
        'default'           => '',
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'hero_image_id', [
        'label'     => __( 'Hero Background Image', 'stanray-custom' ),
        'section'   => 'stanray_hero',
        'mime_type' => 'image',
        'priority'  => 10,
    ] ) );

    $wp_customize->add_setting( 'hero_video_url', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'hero_video_url', [
        'label'    => __( 'Hero Video URL (.mp4) — overrides image', 'stanray-custom' ),
        'section'  => 'stanray_hero',
        'type'     => 'url',
        'priority' => 20,
    ] );

    $wp_customize->add_setting( 'hero_headline', [
        'default'           => get_bloginfo( 'name' ),
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ] );
    $wp_customize->add_control( 'hero_headline', [
        'label'    => __( 'Headline', 'stanray-custom' ),
        'section'  => 'stanray_hero',
        'type'     => 'text',
        'priority' => 30,
    ] );

    $wp_customize->add_setting( 'hero_subtext', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ] );
    $wp_customize->add_control( 'hero_subtext', [
        'label'    => __( 'Subtext', 'stanray-custom' ),
        'section'  => 'stanray_hero',
        'type'     => 'text',
        'priority' => 40,
    ] );

    $wp_customize->add_setting( 'hero_cta_text', [
        'default'           => 'Shop Now',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ] );
    $wp_customize->add_control( 'hero_cta_text', [
        'label'    => __( 'Button Text', 'stanray-custom' ),
        'section'  => 'stanray_hero',
        'type'     => 'text',
        'priority' => 50,
    ] );

    $wp_customize->add_setting( 'hero_cta_link', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'hero_cta_link', [
        'label'    => __( 'Button Link URL', 'stanray-custom' ),
        'section'  => 'stanray_hero',
        'type'     => 'url',
        'priority' => 60,
    ] );

    // ── Social Media Section ───────────────────────────────────
    $wp_customize->add_section( 'stanray_social', [
        'title'    => __( 'Social Media', 'stanray-custom' ),
        'priority' => 35,
    ] );

    $socials = [
        'instagram' => 'Instagram URL',
        'facebook'  => 'Facebook URL',
        'twitter'   => 'Twitter / X URL',
        'pinterest' => 'Pinterest URL',
        'tiktok'    => 'TikTok URL',
        'youtube'   => 'YouTube URL',
    ];

    foreach ( $socials as $key => $label ) {
        $wp_customize->add_setting( 'social_' . $key, [
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ] );
        $wp_customize->add_control( 'social_' . $key, [
            'label'   => __( $label, 'stanray-custom' ),
            'section' => 'stanray_social',
            'type'    => 'url',
        ] );
    }

    // WhatsApp (number, not URL)
    $wp_customize->add_setting( 'social_whatsapp', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 'social_whatsapp', [
        'label'       => __( 'WhatsApp Number', 'stanray-custom' ),
        'description' => __( 'Full number with country code, no spaces or + sign. E.g. 9779800000000', 'stanray-custom' ),
        'section'     => 'stanray_social',
        'type'        => 'text',
    ] );

    // ── Footer Copyright ──────────────────────────────────────
    $wp_customize->add_section( 'stanray_footer', [
        'title'    => __( 'Footer', 'stanray-custom' ),
        'priority' => 40,
    ] );

    $wp_customize->add_setting( 'footer_copyright', [
        'default'           => '© ' . date( 'Y' ) . ' ' . get_bloginfo( 'name' ) . '. All rights reserved.',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ] );
    $wp_customize->add_control( 'footer_copyright', [
        'label'   => __( 'Copyright Text', 'stanray-custom' ),
        'section' => 'stanray_footer',
        'type'    => 'text',
    ] );
} );


// ─── CUSTOMIZER LIVE PREVIEW ───────────────────────────────────────────────────
add_action( 'customize_preview_init', function () {
    wp_add_inline_script( 'customize-preview', "
        ( function( $ ) {
            wp.customize( 'hero_headline', function( value ) {
                value.bind( function( v ) { $( '.hero__headline' ).text( v ); } );
            });
            wp.customize( 'hero_subtext', function( value ) {
                value.bind( function( v ) { $( '.hero__subtext' ).text( v ); } );
            });
            wp.customize( 'hero_cta_text', function( value ) {
                value.bind( function( v ) { $( '.hero__cta' ).text( v ); } );
            });
            wp.customize( 'footer_copyright', function( value ) {
                value.bind( function( v ) { $( '.footer-copyright' ).text( v ); } );
            });
        } )( jQuery );
    " );
} );

// video//
function create_video_post_type() {
    register_post_type('video_gallery', [
        'labels' => [
            'name' => 'Videos',
            'singular_name' => 'Video'
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'events'],
        'supports' => ['title', 'thumbnail', 'editor'],
        'menu_icon' => 'dashicons-video-alt3'
    ]);
}
add_action('init', 'create_video_post_type');

// Paginate the video archive 6 per page
function stanray_video_archive_query( $query ) {
    if ( ! is_admin() && $query->is_main_query() && is_post_type_archive( 'video_gallery' ) ) {
        $query->set( 'posts_per_page', 6 );
    }
}
add_action( 'pre_get_posts', 'stanray_video_archive_query' );
// Add Meta Box
function add_video_meta_box() {
    add_meta_box(
        'video_meta_box',
        'Video Settings',
        'video_meta_box_callback',
        'video_gallery',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_video_meta_box');

// Meta Box HTML
function video_meta_box_callback($post) {
    wp_nonce_field('video_meta_box_save', 'video_meta_nonce');
    $youtube_url = get_post_meta($post->ID, '_youtube_url', true);
    ?>
    <p>
        <label>YouTube URL:</label>
        <input type="text" name="youtube_url" value="<?php echo esc_attr($youtube_url); ?>" style="width:100%;" />
    </p>
    <?php
}

// Save Meta
function save_video_meta_box($post_id) {
    if ( ! isset( $_POST['video_meta_nonce'] ) || ! wp_verify_nonce( $_POST['video_meta_nonce'], 'video_meta_box_save' ) ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    if ( isset( $_POST['youtube_url'] ) ) {
        update_post_meta( $post_id, '_youtube_url', esc_url_raw( $_POST['youtube_url'] ) );
    }
}
add_action('save_post', 'save_video_meta_box');

// tour dates //
function create_tour_date_post_type() {
    register_post_type('tour_date', [
        'labels' => [
            'name' => 'Tour Dates',
            'singular_name' => 'Tour Date',
            'add_new_item' => 'Add New Tour Date',
            'edit_item' => 'Edit Tour Date',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'supports' => ['title'],
        'menu_icon' => 'dashicons-tickets-alt',
        'menu_position' => 21,
    ]);
}
add_action('init', 'create_tour_date_post_type');

// Add Meta Box
function add_tour_date_meta_box() {
    add_meta_box(
        'tour_date_meta_box',
        'Event Details',
        'tour_date_meta_box_callback',
        'tour_date',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_tour_date_meta_box');

// Country list for the Tour Date flag picker (ISO 3166-1 alpha-2 => name)
function stanray_get_countries() {
    return [
        'NP' => 'Nepal', 'IN' => 'India', 'US' => 'United States', 'GB' => 'United Kingdom',
        'AU' => 'Australia', 'CA' => 'Canada', 'AE' => 'United Arab Emirates', 'QA' => 'Qatar',
        'SA' => 'Saudi Arabia', 'KR' => 'South Korea', 'JP' => 'Japan', 'CN' => 'China',
        'SG' => 'Singapore', 'MY' => 'Malaysia', 'TH' => 'Thailand', 'BD' => 'Bangladesh',
        'PK' => 'Pakistan', 'LK' => 'Sri Lanka', 'BT' => 'Bhutan', 'MM' => 'Myanmar',
        'DE' => 'Germany', 'FR' => 'France', 'IT' => 'Italy', 'ES' => 'Spain',
        'NL' => 'Netherlands', 'BE' => 'Belgium', 'CH' => 'Switzerland', 'SE' => 'Sweden',
        'NO' => 'Norway', 'DK' => 'Denmark', 'FI' => 'Finland', 'IE' => 'Ireland',
        'PT' => 'Portugal', 'PL' => 'Poland', 'AT' => 'Austria', 'GR' => 'Greece',
        'RU' => 'Russia', 'TR' => 'Turkey', 'IL' => 'Israel', 'EG' => 'Egypt',
        'ZA' => 'South Africa', 'NG' => 'Nigeria', 'KE' => 'Kenya', 'BR' => 'Brazil',
        'MX' => 'Mexico', 'AR' => 'Argentina', 'NZ' => 'New Zealand', 'PH' => 'Philippines',
        'ID' => 'Indonesia', 'VN' => 'Vietnam', 'HK' => 'Hong Kong', 'KW' => 'Kuwait',
        'BH' => 'Bahrain', 'OM' => 'Oman',
    ];
}

// Convert an ISO 3166-1 alpha-2 country code to its flag emoji
function stanray_country_flag_emoji( $country_code ) {
    $country_code = strtoupper( trim( (string) $country_code ) );
    if ( strlen( $country_code ) !== 2 ) return '';
    $flag = '';
    foreach ( str_split( $country_code ) as $char ) {
        $flag .= mb_convert_encoding( '&#' . ( 127397 + ord( $char ) ) . ';', 'UTF-8', 'HTML-ENTITIES' );
    }
    return $flag;
}

// Meta Box HTML
function tour_date_meta_box_callback($post) {
    wp_nonce_field('tour_date_meta_box_save', 'tour_date_meta_nonce');
    $event_date    = get_post_meta($post->ID, '_event_date', true);
    $event_venue   = get_post_meta($post->ID, '_event_venue', true);
    $event_country = get_post_meta($post->ID, '_event_country', true);
    $ticket_url    = get_post_meta($post->ID, '_ticket_url', true);
    $video_id      = get_post_meta($post->ID, '_linked_video_id', true);

    $videos = get_posts(['post_type' => 'video_gallery', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC']);
    ?>
    <p>
        <label>Event Date:</label><br>
        <input type="date" name="event_date" value="<?php echo esc_attr($event_date); ?>" />
    </p>
    <p>
        <label>Venue / City:</label><br>
        <input type="text" name="event_venue" value="<?php echo esc_attr($event_venue); ?>" style="width:100%;" placeholder="e.g. Kathmandu, NP" />
    </p>
    <p>
        <label>Country (shows a flag next to the venue):</label><br>
        <select name="event_country" style="width:100%;">
            <option value="">— None —</option>
            <?php foreach ( stanray_get_countries() as $code => $name ) : ?>
                <option value="<?php echo esc_attr($code); ?>" <?php selected($event_country, $code); ?>>
                    <?php echo esc_html( stanray_country_flag_emoji($code) . ' ' . $name ); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label>Ticket URL (optional — leave blank to link to the related video instead):</label><br>
        <input type="url" name="ticket_url" value="<?php echo esc_attr($ticket_url); ?>" style="width:100%;" />
    </p>
    <p>
        <label>Related Video (optional):</label><br>
        <select name="linked_video_id" style="width:100%;">
            <option value="">— None —</option>
            <?php foreach ($videos as $video) : ?>
                <option value="<?php echo esc_attr($video->ID); ?>" <?php selected($video_id, $video->ID); ?>>
                    <?php echo esc_html($video->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <?php
}

// Save Meta
function save_tour_date_meta_box($post_id) {
    if ( ! isset( $_POST['tour_date_meta_nonce'] ) || ! wp_verify_nonce( $_POST['tour_date_meta_nonce'], 'tour_date_meta_box_save' ) ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    if ( isset( $_POST['event_date'] ) ) {
        update_post_meta( $post_id, '_event_date', sanitize_text_field( $_POST['event_date'] ) );
    }
    if ( isset( $_POST['event_venue'] ) ) {
        update_post_meta( $post_id, '_event_venue', sanitize_text_field( $_POST['event_venue'] ) );
    }
    if ( isset( $_POST['event_country'] ) ) {
        update_post_meta( $post_id, '_event_country', sanitize_text_field( $_POST['event_country'] ) );
    }
    if ( isset( $_POST['ticket_url'] ) ) {
        update_post_meta( $post_id, '_ticket_url', esc_url_raw( $_POST['ticket_url'] ) );
    }
    if ( isset( $_POST['linked_video_id'] ) ) {
        update_post_meta( $post_id, '_linked_video_id', absint( $_POST['linked_video_id'] ) );
    }
}
add_action('save_post', 'save_tour_date_meta_box');

// ─── ACCOUNT — SAVE CUSTOM FIELDS ─────────────────────────────────────────────
add_action( 'woocommerce_created_customer', function ( $customer_id ) {
    if ( ! empty( $_POST['billing_first_name'] ) ) {
        update_user_meta( $customer_id, 'first_name',         sanitize_text_field( $_POST['billing_first_name'] ) );
        update_user_meta( $customer_id, 'billing_first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
    }
    if ( ! empty( $_POST['billing_last_name'] ) ) {
        update_user_meta( $customer_id, 'last_name',         sanitize_text_field( $_POST['billing_last_name'] ) );
        update_user_meta( $customer_id, 'billing_last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
    }
    if ( ! empty( $_POST['billing_phone'] ) ) {
        update_user_meta( $customer_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
    }
} );

add_filter( 'woocommerce_registration_errors', function ( $errors, $username, $email ) {
    if ( empty( $_POST['billing_first_name'] ) ) {
        $errors->add( 'billing_first_name_error', __( 'First name is required.', 'stanray-custom' ) );
    }
    if ( empty( $_POST['billing_last_name'] ) ) {
        $errors->add( 'billing_last_name_error', __( 'Last name is required.', 'stanray-custom' ) );
    }
    return $errors;
}, 10, 3 );

add_action( 'woocommerce_save_account_details', function ( $user_id ) {
    if ( ! empty( $_POST['billing_phone'] ) ) {
        update_user_meta( $user_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
    }
} );

function new_arrival_styles() {
    if (is_page('new-arrival')) {
        wp_enqueue_style(
            'new-arrival-style',
            get_template_directory_uri() . '/css/new-arrival.css',
            array(),
            '1.0'
        );
    }
}
add_action('wp_enqueue_scripts', 'new_arrival_styles');

function stanray_gallery_lightbox_assets() {
    if ( is_page_template( 'page-gallery.php' ) ) {
        wp_enqueue_script(
            'stanray-gallery-lightbox',
            STANRAY_URI . '/assets/js/gallery-lightbox.js',
            [],
            STANRAY_VERSION,
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'stanray_gallery_lightbox_assets' );
function enqueue_swiper_assets() {
    wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css');
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_swiper_assets');

function handle_contact_form() {

    if ( ! isset( $_POST['contact_form_submitted'] ) ) {
        return;
    }

    // CSRF protection.
    if ( ! isset( $_POST['contact_nonce'] ) || ! wp_verify_nonce( $_POST['contact_nonce'], 'stanray_contact_form' ) ) {
        $GLOBALS['contact_form_errors'] = [ 'Your session expired. Please try again.' ];
        return;
    }

    // Honeypot: bots tend to fill every field, humans never see or fill this one.
    if ( ! empty( $_POST['contact_website'] ) ) {
        return;
    }

    // Field is posted as "full_name" (not "name") because "name" collides with
    // WordPress's own public query var and breaks page-slug resolution on submit.
    $name    = sanitize_text_field( wp_unslash( $_POST['full_name'] ?? '' ) );
    $email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
    $reason  = sanitize_text_field( wp_unslash( $_POST['reason'] ?? '' ) );
    $message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );

    $errors = [];

    if ( empty( $name ) ) {
        $errors[] = 'Please enter your name.';
    }
    if ( empty( $email ) || ! is_email( $email ) ) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ( empty( $message ) ) {
        $errors[] = 'Please enter a message.';
    }

    if ( ! empty( $errors ) ) {
        $GLOBALS['contact_form_errors'] = $errors;
        $GLOBALS['contact_form_data']   = compact( 'name', 'email', 'reason', 'message' );
        return;
    }

    $to      = 'maharjanilina5@gmail.com';
    $subject = 'New Contact Form: ' . $reason;

    $body  = "Name: $name\n";
    $body .= "Email: $email\n";
    $body .= "Reason: $reason\n\n";
    $body .= "Message:\n$message";

    $site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
    $headers   = [
        'Content-Type: text/plain; charset=UTF-8',
        sprintf( 'From: %s <%s>', $site_name, 'wordpress@' . wp_parse_url( home_url(), PHP_URL_HOST ) ),
        sprintf( 'Reply-To: %s <%s>', $name, $email ),
    ];

    $sent = wp_mail( $to, $subject, $body, $headers );

    if ( ! $sent ) {
        // Don't redirect: keep the submitted values so the visitor doesn't retype everything.
        $GLOBALS['contact_form_errors'] = [ 'Sorry, your message could not be sent. Please try again later.' ];
        $GLOBALS['contact_form_data']   = compact( 'name', 'email', 'reason', 'message' );
        return;
    }

    // Redirect to avoid resubmission on refresh.
    wp_redirect( add_query_arg( 'success', '1', get_permalink() ) );
    exit;
}
add_action( 'init', 'handle_contact_form' );

// shoppable category//

/**
 * Add this to your theme's functions.php
 * =========================================
 */

// 1. Enqueue CSS and JS for the shoppable banner
function eskecy_shoppable_banner_assets() {
    wp_enqueue_style(
        'eskecy-shoppable-banner',
        get_template_directory_uri() . '/assets/css/shoppable-banner.css',
        [],
        filemtime( get_template_directory() . '/assets/css/shoppable-banner.css' )
    );
    wp_enqueue_script(
        'eskecy-shoppable-banner',
        get_template_directory_uri() . '/assets/js/shoppable-banner.js',
        [],
        filemtime( get_template_directory() . '/assets/js/shoppable-banner.js' ),
        true
    );
}
add_action('wp_enqueue_scripts', 'eskecy_shoppable_banner_assets');


// cart
add_action('wp_ajax_update_cart_qty', 'update_cart_qty');
add_action('wp_ajax_nopriv_update_cart_qty', 'update_cart_qty');

function update_cart_qty() {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'stanray_nonce' ) ) {
        wp_send_json_error( 'Invalid nonce' );
    }
    $cart_key = sanitize_text_field( $_POST['cart_key'] ?? '' );
    $qty      = absint( $_POST['qty'] ?? 0 );

    if ( $cart_key && $qty > 0 ) {
        WC()->cart->set_quantity( $cart_key, $qty, true );
    }

    WC_AJAX::get_refreshed_fragments();
    wp_die();
}

add_filter('woocommerce_available_payment_gateways', function($gateways) {
    if (isset($gateways['esewa'])) {
        $gateways['esewa']->enabled = 'yes';
    }
    return $gateways;
});
// ─── DISABLE WC CSS (we handle all styling ourselves) ─────────────────────────
add_filter( 'woocommerce_get_catalog_ordering_args', function( $args ) {

    if ( isset( $_GET['orderby'] ) ) {

        $orderby = wc_clean( wp_unslash( $_GET['orderby'] ) );

        switch ( $orderby ) {

            case 'price':
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'asc';
                $args['meta_key'] = '_price';
                break;

            case 'price-desc':
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'desc';
                $args['meta_key'] = '_price';
                break;

            case 'date':
                $args['orderby'] = 'date';
                $args['order']   = 'desc';
                break;

            case 'popularity':
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'desc';
                $args['meta_key'] = 'total_sales';
                break;
        }
    }

    return $args;
});


// ─── IMPROVEMENT 1: ANNOUNCEMENT BAR (set default text so it shows) ──────────
add_action( 'after_setup_theme', function() {
    if ( ! get_theme_mod( 'header_announcement' ) ) {
        set_theme_mod( 'header_announcement', "Discount 40% for first order\nFree shipping over Rs. 5000\nMade in Nepal\nLimited drops\nNew arrivals now live" );
    }
}, 20 );

// ─── IMPROVEMENT 2/3: size guide link + stock indicator now live only in
// inc/woocommerce.php (they were duplicated there, causing the size-guide
// link and stock pill to render twice on the product page).

// ─── IMPROVEMENT 4: ENQUEUE size guide CSS/JS ─────────────────────────────────
add_action( 'wp_enqueue_scripts', function() {
    if ( is_product() ) {
        wp_enqueue_style( 'stanray-size-guide', get_template_directory_uri() . '/assets/css/size-guide.css', [], STANRAY_VERSION );
        wp_enqueue_script( 'stanray-size-guide', get_template_directory_uri() . '/assets/js/size-guide.js', [], STANRAY_VERSION, true );
    }
}, 20 );

// ─── IMPROVEMENT 5: "YOU MAY ALSO LIKE" - force 4 products ───────────────────
add_filter( 'woocommerce_output_related_products_args', function( $args ) {
    $args['posts_per_page'] = 4;
    $args['columns']        = 4;
    return $args;
}, 99 );

// ─── IMPROVEMENT 6: Add category badge BELOW product title on cards ──────────
add_action( 'woocommerce_after_shop_loop_item_title', function() {
    global $product;
    $terms = get_the_terms( $product->get_id(), 'product_cat' );
    if ( $terms && ! is_wp_error( $terms ) ) {
        $exclude = get_option( 'default_product_cat' );
        foreach ( $terms as $term ) {
            if ( $term->term_id != $exclude && strtolower($term->slug) !== 'uncategorized' ) {
                echo '<span class="product-card__cat">' . esc_html( $term->name ) . '</span>';
                break;
            }
        }
    }
}, 6 );

// ─── CUSTOMER REVIEWS CPT ─────────────────────────────────────────────────────
add_action( 'init', function() {
    register_post_type( 'eskecy_review', [
        'labels' => [
            'name'               => 'Customer Reviews',
            'singular_name'      => 'Customer Review',
            'add_new'            => 'Add New Review',
            'add_new_item'       => 'Add New Customer Review',
            'edit_item'          => 'Edit Review',
            'view_item'          => 'View Review',
            'all_items'          => 'All Reviews',
            'search_items'       => 'Search Reviews',
            'not_found'          => 'No reviews found.',
            'menu_name'          => 'Customer Reviews',
        ],
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => true,
        'menu_icon'     => 'dashicons-format-quote',
        'menu_position' => 25,
        'supports'      => [ 'title', 'thumbnail', 'page-attributes' ],
        'rewrite'       => false,
    ] );
} );

add_action( 'add_meta_boxes', function() {
    add_meta_box(
        'eskecy_review_details',
        'Review Details',
        'stanray_review_meta_callback',
        'eskecy_review',
        'normal',
        'high'
    );
} );

// ─── ADDRESS BOOK CPT ───────────────────────────────────────────────────────
// One post per saved billing/shipping address. Customer PII, not store content
// like eskecy_review above — no admin UI, no public queryability. See
// inc/address-book.php for everything else (CRUD, endpoints, checkout picker).
add_action( 'init', function() {
    register_post_type( 'stanray_address', [
        'labels'        => [ 'name' => 'Saved Addresses', 'singular_name' => 'Saved Address' ],
        'public'        => false,
        'show_ui'       => false,
        'show_in_menu'  => false,
        'supports'      => [ 'title' ],
        'rewrite'       => false,
        'capability_type' => 'post',
    ] );
} );

function stanray_review_meta_callback( $post ) {
    wp_nonce_field( 'eskecy_review_save', 'eskecy_review_nonce' );
    $product_label = get_post_meta( $post->ID, '_review_product_label', true );
    $product_id    = (int) get_post_meta( $post->ID, '_review_product_id', true );
    $quote         = get_post_meta( $post->ID, '_review_quote', true );

    $products = get_posts( [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ] );
    ?>
    <style>
        .eskecy-meta-table th { width: 160px; padding: 12px 8px; vertical-align: top; }
        .eskecy-meta-table td { padding: 10px 8px; }
        .eskecy-meta-note { color: #666; font-style: italic; margin-top: 10px; padding: 8px 12px; background: #f9f9f9; border-left: 3px solid #e8192c; }
    </style>
    <table class="form-table eskecy-meta-table">
        <tr>
            <th><label for="review_product_id">Linked Product</label></th>
            <td>
                <select id="review_product_id" name="review_product_id" class="widefat">
                    <option value="">— None (label only, no link) —</option>
                    <?php foreach ( $products as $product ) : ?>
                        <option value="<?php echo esc_attr( $product->ID ); ?>" <?php selected( $product_id, $product->ID ); ?>>
                            <?php echo esc_html( $product->post_title ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description">If set, clicking the product name/label on the slide card takes the visitor to this product's page.</p>
            </td>
        </tr>
        <tr>
            <th><label for="review_product_label">Product Name / Label</label></th>
            <td>
                <input type="text" id="review_product_label" name="review_product_label"
                       value="<?php echo esc_attr( $product_label ); ?>"
                       class="widefat"
                       placeholder="e.g. Eskecy Black Oversized Tee — SK.26">
                <p class="description">Shown below the customer's name on the slide card.</p>
            </td>
        </tr>
        <tr>
            <th><label for="review_quote">Short Quote <span style="color:#999">(optional)</span></label></th>
            <td>
                <textarea id="review_quote" name="review_quote" class="widefat" rows="3"
                          placeholder="e.g. "Best streetwear I've ever owned.""><?php echo esc_textarea( $quote ); ?></textarea>
            </td>
        </tr>
    </table>
    <p class="eskecy-meta-note">
        ⬆ Set the <strong>Featured Image</strong> (right sidebar) to the customer's photo wearing the product.<br>
        Use the <strong>Title</strong> field above for the customer's name.<br>
        Drag posts to reorder slides using the <strong>Order</strong> field in Page Attributes.
    </p>
    <?php
}

add_action( 'save_post_eskecy_review', function( $post_id ) {
    if ( ! isset( $_POST['eskecy_review_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['eskecy_review_nonce'], 'eskecy_review_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['review_product_label'] ) ) {
        update_post_meta( $post_id, '_review_product_label', sanitize_text_field( $_POST['review_product_label'] ) );
    }
    if ( isset( $_POST['review_product_id'] ) ) {
        $product_id = absint( $_POST['review_product_id'] );
        if ( $product_id && get_post_type( $product_id ) === 'product' ) {
            update_post_meta( $post_id, '_review_product_id', $product_id );
        } else {
            delete_post_meta( $post_id, '_review_product_id' );
        }
    }
    if ( isset( $_POST['review_quote'] ) ) {
        update_post_meta( $post_id, '_review_quote', sanitize_textarea_field( $_POST['review_quote'] ) );
    }
} );

