<?php
/**
 * Shop Archive
 * Layout: Continuous 4-col editorial grid, with looping-video banner tiles
 *         (2-col wide) interspersed and alternating left/right.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$is_category = is_product_category();
$cat_obj     = $is_category ? get_queried_object() : null;
$cat_slug    = $cat_obj ? $cat_obj->slug : '';

$shop_url  = get_permalink( wc_get_page_id( 'shop' ) );
$shop_cats = get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0, 'exclude' => [ get_option( 'default_product_cat' ) ], 'number' => 12 ] );
if ( is_wp_error( $shop_cats ) ) $shop_cats = [];

$price_bounds = stanray_get_shop_price_bounds();

/* Collect all product IDs from the query */
$all_ids = [];
if ( woocommerce_product_loop() ) {
    wc_setup_loop( [ 'columns' => 4 ] );
    while ( have_posts() ) {
        the_post();
        $all_ids[] = get_the_ID();
    }
}

$editorial_banners = stanray_get_editorial_banners();
?>

<div class="container">
<div class="ovo-shop" id="ovo-shop"
     data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
     data-nonce="<?php echo esc_attr( wp_create_nonce( 'stanray_nonce' ) ); ?>"
     data-shop-url="<?php echo esc_url( $shop_url ); ?>"
     data-current-cat="<?php echo esc_attr( $cat_slug ); ?>"
     data-price-min="<?php echo esc_attr( $price_bounds['min'] ); ?>"
     data-price-max="<?php echo esc_attr( $price_bounds['max'] ); ?>">

    <!-- ── Sort & Filter trigger ────────────────────────────────────────── -->
    <div class="ovo-toolbar">
        <button type="button" class="ovo-toolbar__trigger" id="ovo-filter-trigger" aria-haspopup="dialog" aria-expanded="false" aria-controls="ovo-filter-drawer">
            Sort &amp; Filter
        </button>
    </div>

    <!-- ── Sort & Filter drawer ─────────────────────────────────────────── -->
    <div class="ovo-filter-overlay" id="ovo-filter-overlay" hidden></div>
    <aside class="ovo-filter-drawer" id="ovo-filter-drawer" aria-hidden="true" aria-label="Sort and filter products">
        <div class="ovo-filter-drawer__header">
            <h2>Sort &amp; Filter</h2>
            <button type="button" class="ovo-filter-drawer__close" id="ovo-filter-close" aria-label="Close">&times;</button>
        </div>

        <div class="ovo-filter-drawer__body">

            <div class="ovo-filter-section is-open" data-section="sort">
                <button type="button" class="ovo-filter-section__toggle">Sort By</button>
                <div class="ovo-filter-section__content">
                    <label class="ovo-filter-option"><input type="radio" name="ovo-sort" value="menu_order" checked> Featured</label>
                    <label class="ovo-filter-option"><input type="radio" name="ovo-sort" value="date"> Newest</label>
                    <label class="ovo-filter-option"><input type="radio" name="ovo-sort" value="popularity"> Best selling</label>
                    <label class="ovo-filter-option"><input type="radio" name="ovo-sort" value="price"> Price, low to high</label>
                    <label class="ovo-filter-option"><input type="radio" name="ovo-sort" value="price-desc"> Price, high to low</label>
                </div>
            </div>

            <?php if ( ! empty( $shop_cats ) ) : ?>
            <div class="ovo-filter-section" data-section="category">
                <button type="button" class="ovo-filter-section__toggle">Product Type</button>
                <div class="ovo-filter-section__content">
                    <?php foreach ( $shop_cats as $cat ) : ?>
                    <label class="ovo-filter-option">
                        <input type="checkbox" name="ovo-category" value="<?php echo esc_attr( $cat->slug ); ?>" <?php checked( $cat_slug, $cat->slug ); ?>>
                        <?php echo esc_html( $cat->name ); ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="ovo-filter-section" data-section="price">
                <button type="button" class="ovo-filter-section__toggle">Price</button>
                <div class="ovo-filter-section__content">
                    <div class="ovo-filter-price">
                        <input type="number" id="ovo-price-min" placeholder="£<?php echo esc_attr( $price_bounds['min'] ); ?>" min="0">
                        <span>&ndash;</span>
                        <input type="number" id="ovo-price-max" placeholder="£<?php echo esc_attr( $price_bounds['max'] ); ?>" min="0">
                    </div>
                </div>
            </div>

        </div>

        <div class="ovo-filter-drawer__footer">
            <button type="button" class="ovo-filter-clear" id="ovo-filter-clear">Clear all</button>
            <button type="button" class="ovo-filter-apply" id="ovo-filter-apply">View Results</button>
        </div>
    </aside>

    <!-- ── Loading skeleton ─────────────────────────────────────────────── -->
    <div class="ovo-loading" id="ovo-loading" hidden>
        <ul class="ovo-grid ovo-grid--editorial">
            <?php for ( $i = 0; $i < 8; $i++ ) : ?>
            <li class="ovo-skeleton-card">
                <div class="ovo-skeleton-media"></div>
                <div class="ovo-skeleton-info">
                    <div class="ovo-skeleton-line ovo-skeleton-line--name"></div>
                    <div class="ovo-skeleton-line ovo-skeleton-line--price"></div>
                </div>
            </li>
            <?php endfor; ?>
        </ul>
    </div>

    <?php if ( ! empty( $all_ids ) ) : ?>

    <!-- ── Continuous editorial grid ────────────────────────────────────── -->
    <ul class="ovo-grid products ovo-grid--editorial" id="ovo-grid">
        <?php stanray_render_shop_grid( $all_ids, $editorial_banners ); ?>
    </ul>

    <?php wc_reset_loop(); ?>

    <?php else : ?>

    <div class="ovo-empty">
        <p>No products found.</p>
        <a href="<?php echo esc_url( $shop_url ); ?>" class="ovo-empty__link">View all</a>
    </div>
    <ul id="ovo-grid" hidden></ul>

    <?php endif; ?>

    <div class="ovo-pagination" id="ovo-pagination">
        <?php woocommerce_pagination(); ?>
    </div>

</div>
</div>

<?php get_footer(); ?>
