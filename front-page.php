<?php
/**
 * Homepage template — IMPROVED
 * Sections: Hero, Shoppable Banner, Trust Bar, Collections, Featured, New Arrivals
 */
get_header();
?>

<main id="main" class="site-main homepage" role="main">

    <!-- 1. HERO SECTION -->
    <!-- <section class="hero" aria-label="Hero">
        <div class="hero__media">
            <?php
            $hero_video    = get_theme_mod( 'hero_video_url', '' );
            $hero_image_id = get_theme_mod( 'hero_image_id', '' );
            ?>
            <?php if ( $hero_video ) : ?>
                <video class="hero__video" autoplay muted loop playsinline>
                    <source src="<?php echo esc_url( $hero_video ); ?>" type="video/mp4">
                </video>
            <?php elseif ( $hero_image_id ) : ?>
                <img src="<?php echo esc_url( wp_get_attachment_image_url( $hero_image_id, 'full' ) ); ?>" alt="" class="hero__image" loading="eager">
            <?php else : ?>
                <div class="hero__placeholder"></div>
            <?php endif; ?>
            <div class="hero__overlay"></div>
        </div>
        <div class="hero__content">
            <p class="hero__eyebrow eyebrow">New Season · 2026</p>
            <?php $headline = get_theme_mod( 'hero_headline', 'Wear the Movement' ); ?>
            <h1 class="hero__headline"><?php echo esc_html( $headline ); ?></h1>
            <?php $subtext = get_theme_mod( 'hero_subtext', 'Street-ready pieces built for those who refuse to give up.' ); ?>
            <p class="hero__subtext"><?php echo esc_html( $subtext ); ?></p>
            <div class="hero__ctas">
                <?php
                $cta_text = get_theme_mod( 'hero_cta_text', 'Shop New Arrivals' );
                $cta_link = get_theme_mod( 'hero_cta_link', class_exists('WooCommerce') ? wc_get_page_permalink('shop') : home_url('/shop') );
                ?>
                <a href="<?php echo esc_url( $cta_link ); ?>" class="btn btn--outline-white"><?php echo esc_html( $cta_text ); ?></a>
                <a href="<?php echo esc_url( home_url('/new-arrival') ); ?>" class="btn hero__btn-ghost">What's New &rarr;</a>
            </div>
        </div>
    </section> -->

    <!-- 2. HERO BANNER (new editorial design) -->
    <?php get_template_part('template-parts/hero-banner'); ?>


    <!-- 4. COLLECTIONS -->
    <!-- <?php
    $product_cats = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
        'number'     => 4,
        'exclude'    => get_option( 'default_product_cat' ),
        'orderby'    => 'menu_order',
    ]);
    if ( ! is_wp_error( $product_cats ) && ! empty( $product_cats ) ) : ?>
    <section class="section collections" aria-label="Shop by Collection">
        <div class="container-fluid">
            <div class="section__header">
                <h2 class="section__title_collection">Collections</h2>
                <a href="<?php echo esc_url( class_exists('WooCommerce') ? wc_get_page_permalink('shop') : home_url('/shop') ); ?>" class="section__link">Shop All &rarr;</a>
            </div>
            <div class="collections__grid">
                <?php foreach ( $product_cats as $cat ) :
                    $thumbnail_id = get_term_meta( $cat->term_id, 'thumbnail_id', true );
                    $img_url      = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'stanray-editorial' ) : get_template_directory_uri() . '/assets/images/placeholder.jpg';
                ?>
                <a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="collection-card">
                    <div class="collection-card__image-wrap">
                        <img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $cat->name ); ?>" class="collection-card__image" loading="lazy">
                    </div>
                    <div class="collection-card__info">
                        <h3 class="collection-card__name"><?php echo esc_html( $cat->name ); ?></h3>
                        <span class="collection-card__count"><?php echo $cat->count; ?> <?php echo _n( 'product', 'products', $cat->count, 'stanray-custom' ); ?></span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?> -->

  
    <!-- 6. NEW ARRIVALS / MOST POPULAR -->
    <?php get_template_part('template-parts/dual-tabs'); ?>
   <?php get_template_part('template-parts/shoppable-banner'); ?>
    <!-- 8. CATEGORY TABS -->
    <?php get_template_part('template-parts/category-tabs'); ?>
      <!-- 9. CUSTOMER REVIEWS SLIDER -->
    <?php get_template_part('template-parts/customer-reviews'); ?>

    <!-- 8. HOMEPAGE VIDEO -->
    <?php
    // Editable from Homepage Sections → Homepage Video in the wp-admin sidebar.
    $hv_video_id  = absint( get_option( 'stanray_hv_video_id', 0 ) );
    $hv_video_url = $hv_video_id ? wp_get_attachment_url( $hv_video_id ) : '';
    if ( ! $hv_video_url ) {
        $hv_video_url = get_template_directory_uri() . '/assets/videos/SKC-Live-Web-Banner-.mp4';
    }
    $hv_poster_id  = absint( get_option( 'stanray_hv_poster_id', 0 ) );
    $hv_poster_url = $hv_poster_id ? wp_get_attachment_image_url( $hv_poster_id, 'large' ) : '';
    ?>
    <section class="homepage-video" aria-label="Featured Video">
        <video class="homepage-video__player" autoplay muted loop playsinline preload="metadata"<?php echo $hv_poster_url ? ' poster="' . esc_url( $hv_poster_url ) . '"' : ''; ?>>
            <source src="<?php echo esc_url( $hv_video_url ); ?>" type="video/mp4">
        </video>
    </section>

</main>

<?php get_footer(); ?>
