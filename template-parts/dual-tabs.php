<?php
/**
 * Template Part: Dual Tabs — single product showcase
 * Tab 1: Latest product  |  Tab 2: Most Popular product
 *
 * Products + labels are editable from the "Select Your Style" item
 * in the wp-admin sidebar menu. If a tab is left on "Automatic"
 * (or the chosen product no longer exists/publishes), this falls
 * back to the newest / best-selling product.
 */

// Manual picks from the admin page, if any
$dt_manual_new_id     = absint( get_option( 'stanray_sys_new_arrival_product', '' ) );
$dt_manual_popular_id = absint( get_option( 'stanray_sys_popular_product', '' ) );

$dt_new = null;
if ( $dt_manual_new_id ) {
    $candidate = wc_get_product( $dt_manual_new_id );
    if ( $candidate && $candidate->get_status() === 'publish' ) {
        $dt_new = $candidate;
    }
}
if ( ! $dt_new ) {
    $dt_new_results = wc_get_products(['status' => 'publish', 'limit' => 1, 'orderby' => 'date', 'order' => 'DESC']);
    $dt_new         = ! empty($dt_new_results) ? $dt_new_results[0] : null;
}

$dt_popular = null;
if ( $dt_manual_popular_id ) {
    $candidate = wc_get_product( $dt_manual_popular_id );
    if ( $candidate && $candidate->get_status() === 'publish' ) {
        $dt_popular = $candidate;
    }
}
if ( ! $dt_popular ) {
    // Exclude the newest so both tabs always show different products
    $dt_popular_results = wc_get_products([
        'status'  => 'publish',
        'limit'   => 1,
        'orderby' => 'popularity',
        'order'   => 'DESC',
        'exclude' => $dt_new ? [ $dt_new->get_id() ] : [],
    ]);
    // If only one product exists in the store, fall back to it
    $dt_popular = ! empty($dt_popular_results) ? $dt_popular_results[0] : $dt_new;
}

if ( ! $dt_new && ! $dt_popular ) return;

$dt_section_title   = get_option( 'stanray_sys_section_title', 'Select Your Style' );
$dt_section_eyebrow = get_option( 'stanray_sys_section_eyebrow', 'Our Collection' );
$dt_new_label       = get_option( 'stanray_sys_new_arrival_label', 'New Arrival' );
$dt_popular_label   = get_option( 'stanray_sys_popular_label', 'Most Popular' );
?>

<section class="dt-section section" aria-label="Featured Products">
    <div class="container">

        <!-- Centered heading -->
        <div class="dt__heading-block">
            <p class="dt__eyebrow"><?php echo esc_html( $dt_section_eyebrow ); ?></p>
            <h2 class="dt__title"><?php echo esc_html( $dt_section_title ); ?></h2>
            <div class="dt__rule"></div>
        </div>

        <!-- Tab switcher -->
        <div class="dt__tabs" role="tablist">
            <button class="dt-tab is-active" data-tab="new" role="tab" aria-selected="true">
                <?php echo esc_html( $dt_new_label ); ?> <span class="dt-tab__badge">NEW</span>
            </button>
            <button class="dt-tab" data-tab="popular" role="tab" aria-selected="false">
                <?php echo esc_html( $dt_popular_label ); ?>
            </button>
        </div>

        <!-- Panels -->
        <?php foreach ( ['new' => $dt_new, 'popular' => $dt_popular] as $key => $product ) :
            if ( ! $product ) continue;

            $product_id  = $product->get_id();
            $img_id      = $product->get_image_id();
            $gallery_ids = $product->get_gallery_image_ids();
            $img_url     = $img_id ? wp_get_attachment_image_url( $img_id, 'large' ) : wc_placeholder_img_src();
            $price_raw   = $product->get_price();
            $price_html  = $product->get_price_html();
            $short_desc  = $product->get_short_description();
            if ( ! $short_desc ) {
                $short_desc = wp_strip_all_tags( $product->get_description() );
            }
            $permalink   = $product->get_permalink();
            $attrs       = $product->get_attributes();

            // Primary category
            $p_terms  = get_the_terms( $product_id, 'product_cat' );
            $cat_name = ( $p_terms && ! is_wp_error($p_terms) ) ? $p_terms[0]->name : '';

            $eyebrow_label = $key === 'new' ? 'Latest Arrival' : 'Best Seller';
        ?>
        <div class="dt__panel<?php echo $key === 'new' ? ' is-active' : ''; ?>" data-panel="<?php echo esc_attr($key); ?>">
            <div class="dt__inner">

                <!-- Left: image card -->
                <div class="dt__media">
                    <div class="dt__img-card">
                      
                            
                        
                        <div class="dt__img-wrap">
                            <img src="<?php echo esc_url( $img_url ); ?>"
                                 alt="<?php echo esc_attr( $product->get_name() ); ?>"
                                 class="dt__img" loading="lazy">
                        </div>
                    </div>
                    <?php if ( ! empty($gallery_ids) ) : ?>
                    <div class="dt__thumbs">
                        <?php foreach ( array_slice($gallery_ids, 0, 4) as $gid ) :
                            $thumb = wp_get_attachment_image_url( $gid, 'thumbnail' );
                            if ( ! $thumb ) continue;
                        ?>
                        <button class="dt__thumb" data-full="<?php echo esc_url( wp_get_attachment_image_url($gid, 'large') ); ?>" aria-label="View image">
                            <img src="<?php echo esc_url($thumb); ?>" alt="" loading="lazy">
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Right: product details -->
                <div class="dt__details">
                    <p class="dt__detail-eyebrow"><?php echo esc_html( $eyebrow_label ); ?></p>
                    <h3 class="dt__detail-name"><?php echo esc_html( $product->get_name() ); ?></h3>

                    <?php if ( $short_desc ) : ?>
                    <div class="dt__detail-desc">
                        <p><?php echo wp_kses_post( $short_desc ); ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ( ! empty($attrs) ) : ?>
                    <ul class="dt__specs">
                        <?php foreach ( $attrs as $attr ) :
                            $attr_label = wc_attribute_label( $attr->get_name() );
                            $terms = $attr->get_terms();
                            if ( $terms ) {
                                $val = implode( ', ', array_map( fn($t) => $t->name, $terms ) );
                            } else {
                                $val = implode( ', ', $attr->get_options() );
                            }
                            if ( ! $val ) continue;
                        ?>
                        <li class="dt__spec-row">
                            <span class="dt__spec-label"><?php echo esc_html( $attr_label ); ?></span>
                            <span class="dt__spec-value"><?php echo esc_html( $val ); ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>

                    <div class="dt__actions">
                        <a href="<?php echo esc_url( $permalink ); ?>" class="dt__btn-cart">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            Shop Now &mdash; <?php echo $price_html; ?>
                        </a>
                        <span class="dt__trust">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Free Shipping Included
                        </span>
                    </div>
                </div>

            </div>
        </div>
        <?php endforeach; ?>

    </div>
</section>

<script>
(function () {
    var tabs   = document.querySelectorAll('.dt-tab');
    var panels = document.querySelectorAll('.dt__panel');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var target = this.dataset.tab;

            tabs.forEach(function (t) {
                t.classList.remove('is-active');
                t.setAttribute('aria-selected', 'false');
            });
            this.classList.add('is-active');
            this.setAttribute('aria-selected', 'true');

            panels.forEach(function (p) {
                p.classList.toggle('is-active', p.dataset.panel === target);
            });
        });
    });

    // Gallery thumbnail swap
    document.querySelectorAll('.dt__thumb').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var panel = this.closest('.dt__panel');
            var mainImg = panel.querySelector('.dt__img');
            if (mainImg && this.dataset.full) {
                mainImg.src = this.dataset.full;
            }
            panel.querySelectorAll('.dt__thumb').forEach(function (b) { b.classList.remove('is-active'); });
            this.classList.add('is-active');
        });
    });
}());
</script>
