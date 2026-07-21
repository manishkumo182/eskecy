<?php
/**
 * Template Part: Customer Reviews Slider
 */

$reviews = get_posts([
    'post_type'      => 'eskecy_review',
    'posts_per_page' => 20,
    'post_status'    => 'publish',
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
]);

if ( empty( $reviews ) ) return;
?>

<section class="cr" aria-label="Customer Reviews">

    <div class="cr__header">
        <span class="cr__eyebrow">Community</span>
        <h2 class="cr__title">Loved by Real People</h2>
        <p class="cr__sub">Street culture, worn daily.</p>
    </div>

    <div class="cr__slider swiper js-cr-slider">
        <div class="swiper-wrapper">
            <?php foreach ( $reviews as $review ) :
                $name          = get_the_title( $review );
                $product_label = get_post_meta( $review->ID, '_review_product_label', true );
                $product_id    = (int) get_post_meta( $review->ID, '_review_product_id', true );
                $product_url   = $product_id ? get_permalink( $product_id ) : '';
                $quote         = get_post_meta( $review->ID, '_review_quote', true );
                $img_url       = get_the_post_thumbnail_url( $review->ID, 'large' );
                if ( ! $img_url ) continue;
            ?>
            <div class="swiper-slide cr__card">
                <div class="cr__card-img-wrap">
                    <img src="<?php echo esc_url( $img_url ); ?>"
                         alt="<?php echo esc_attr( $name ); ?> wearing Eskecy"
                         class="cr__card-img"
                         loading="lazy">
                    <div class="cr__card-overlay"></div>
                </div>
                <div class="cr__card-info">
                    <?php if ( $quote ) : ?>
                    <p class="cr__card-quote">&ldquo;<?php echo esc_html( $quote ); ?>&rdquo;</p>
                    <?php endif; ?>
                    <span class="cr__card-name"><?php echo esc_html( $name ); ?></span>
                    <?php if ( $product_label && $product_url ) : ?>
                    <a href="<?php echo esc_url( $product_url ); ?>" class="cr__card-label cr__card-label--link"><?php echo esc_html( $product_label ); ?></a>
                    <?php elseif ( $product_label ) : ?>
                    <span class="cr__card-label"><?php echo esc_html( $product_label ); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="cr__controls">
        <button class="cr__btn cr__btn--prev" aria-label="Previous review">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M13 4L7 10L13 16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="cr__pagination js-cr-pagination"></div>
        <button class="cr__btn cr__btn--next" aria-label="Next review">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M7 4L13 10L7 16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </div>

</section>
