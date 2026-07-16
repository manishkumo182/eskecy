<?php
/**
 * Single Product Rating — Stanray PDP redesign
 * Stars + numeric average + "(N Review)" link into the Review tab.
 *
 * Overrides woocommerce/templates/single-product/rating.php
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! wc_review_ratings_enabled() ) {
    return;
}

$rating_count = $product->get_rating_count();
$review_count = $product->get_review_count();
$average      = $product->get_average_rating();

if ( $rating_count > 0 ) : ?>
    <div class="woocommerce-product-rating pdp-rating">
        <?php echo wc_get_rating_html( $average, $rating_count ); ?>
        <span class="pdp-rating__avg"><?php echo esc_html( $average ); ?></span>
        <?php if ( comments_open() ) : ?>
            <a href="#tab-reviews" class="woocommerce-review-link pdp-rating__count" rel="nofollow">
                (<?php printf( /* translators: %s: review count */ esc_html( _n( '%s Review', '%s Reviews', $review_count, 'stanray-custom' ) ), '<span class="count">' . esc_html( $review_count ) . '</span>' ); ?>)
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>
