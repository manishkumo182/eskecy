<?php
/* Template Name: Studio Page */
get_header();

// Hotspot placement, and the room image itself, are editable from wp-admin:
// Homepage Sections → Explore Page (same screen that controls the entrance
// image on the Explore page, since the two pages are one connected flow).
$hotspots = get_option( 'stanray_explore_hotspots', [
    [ 'product_id' => 321, 'top' => 78, 'left' => 40 ], // Eskecy Socks — simple product, instant add
    [ 'product_id' => 52,  'top' => 78, 'left' => 60 ], // Eskecy Fire and Ice Hoodie — variable, needs size
    [ 'product_id' => 41,  'top' => 85, 'left' => 80 ], // Dark Matter Hoodie Re-vamped — variable, needs size
] );

$studio_image_id = absint( get_option( 'stanray_explore_studio_image', 0 ) );
$studio_image     = $studio_image_id ? wp_get_attachment_image_url( $studio_image_id, 'large' ) : '';
if ( ! $studio_image ) {
    $studio_image = get_template_directory_uri() . '/assets/images/banner.png';
}

// The entrance page is where the "back" arrow returns to.
$entrance_page = get_page_by_path( 'explore' );
$entrance_url  = $entrance_page ? get_permalink( $entrance_page ) : home_url( '/explore/' );

$products = [];
foreach ( $hotspots as $spot ) {
    $product = wc_get_product( $spot['product_id'] );
    if ( ! $product ) continue;

    $products[] = [
        'id'     => $product->get_id(),
        'name'   => $product->get_name(),
        'price'  => $product->get_price_html(),
        'url'    => $product->get_permalink(),
        'image'  => wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' ) ?: wc_placeholder_img_src(),
        // Variable products need a size/variation picked on the product page —
        // a flat one-click Add to Bag can't add those, so the card links out
        // instead of pretending to add them directly.
        'simple' => $product->is_type( 'simple' ),
        'top'    => $spot['top'],
        'left'   => $spot['left'],
    ];
}
?>

<div class="explore" id="explore-root">

    <section class="explore__scene explore__scene--studio" id="explore-studio" aria-label="Studio room">

        <img src="<?php echo esc_url( $studio_image ); ?>" alt="Eskecy studio room" class="explore__bg">
        <div class="explore__vignette" aria-hidden="true"></div>

        <div class="explore__badge" aria-hidden="true">
            <span class="explore__badge-dot"></span>
            EX_ESKECY_V1.0
        </div>

        <a href="<?php echo esc_url( $entrance_url ); ?>" class="explore__back" id="explore-back" aria-label="Back to entrance">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 18l-6-6 6-6"/>
            </svg>
        </a>

        <?php foreach ( $products as $idx => $p ) : ?>
        <div class="explore__hotspot" data-explore-index="<?php echo esc_attr( $idx ); ?>" style="top:<?php echo esc_attr( $p['top'] ); ?>%;left:<?php echo esc_attr( $p['left'] ); ?>%;">

            <button type="button" class="explore__pin" aria-expanded="false" aria-label="View <?php echo esc_attr( $p['name'] ); ?>">
                <span aria-hidden="true">+</span>
            </button>

            <div class="explore__card" role="dialog" aria-label="<?php echo esc_attr( $p['name'] ); ?>">

                <button type="button" class="explore__card-close" aria-label="Close">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M4 4l16 16M20 4L4 20"/></svg>
                </button>

                <div class="explore__card-body">
                    <img src="<?php echo esc_url( $p['image'] ); ?>" alt="" class="explore__card-thumb" loading="lazy">
                    <div class="explore__card-info">
                        <div class="explore__card-name"><?php echo esc_html( $p['name'] ); ?></div>
                        <div class="explore__card-price"><?php echo $p['price']; ?></div>
                    </div>
                </div>

                <?php if ( $p['simple'] ) : ?>
                    <button type="button"
                            class="explore__card-add"
                            data-product-id="<?php echo esc_attr( $p['id'] ); ?>">
                        Add to Bag
                    </button>
                    <a href="<?php echo esc_url( $p['url'] ); ?>" class="explore__card-view">View Product</a>
                <?php else : ?>
                    <a href="<?php echo esc_url( $p['url'] ); ?>" class="explore__card-add explore__card-add--link">
                        Select Options
                    </a>
                <?php endif; ?>

            </div>
        </div>
        <?php endforeach; ?>

    </section>

</div>

<?php get_footer(); ?>
