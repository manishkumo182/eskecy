<?php
/**
 * Template Part: Shoppable Banner
 * Left: image with + hotspot pins
 * Right: product list + detail card on click
 *
 * Banner image, text, and hotspots are editable from the
 * "Shop the Look" item under Homepage Sections in the wp-admin
 * sidebar. Falls back to theme defaults if nothing is saved yet.
 */

$hotspots = get_option( 'stanray_sb_hotspots', [
    ['product_id' => 157, 'top' => 78, 'left' => 60],
    ['product_id' => 41,  'top' => 85, 'left' => 80],
    ['product_id' => 155, 'top' => 78, 'left' => 40],
] );

$sb_eyebrow  = get_option( 'stanray_sb_eyebrow', 'Shop the Look' );
$sb_title    = get_option( 'stanray_sb_title', 'Styled by the Street.' );
$sb_subtitle = get_option( 'stanray_sb_subtitle', 'Click a hotspot to explore the piece.' );

// Banner image URL — falls back to the theme's default image
$sb_image_id       = absint( get_option( 'stanray_sb_banner_image', 0 ) );
$banner_image_url  = $sb_image_id ? wp_get_attachment_image_url( $sb_image_id, 'large' ) : '';
if ( ! $banner_image_url ) {
    $banner_image_url = get_template_directory_uri() . '/assets/images/banner.png';
}

// Build products array with sequential indices
$products = [];
foreach ( $hotspots as $spot ) {
    $product = wc_get_product( $spot['product_id'] );
    if ( ! $product ) continue;

    $cats    = wp_get_post_terms( $product->get_id(), 'product_cat', ['fields' => 'names'] );
    $cat_str = ( ! is_wp_error( $cats ) && ! empty( $cats ) ) ? implode( ' · ', array_slice( $cats, 0, 3 ) ) : '';

    $raw_desc = $product->get_short_description() ?: $product->get_description();
    $desc     = wp_trim_words( wp_strip_all_tags( $raw_desc ), 18, '…' );
    $image    = wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' ) ?: wc_placeholder_img_src();

    $products[] = [
        'name'  => $product->get_name(),
        'price' => $product->get_price_html(),
        'url'   => $product->get_permalink(),
        'cats'  => $cat_str,
        'desc'  => $desc,
        'image' => $image,
        'top'   => $spot['top'],
        'left'  => $spot['left'],
    ];
}

if ( empty( $products ) ) return;

// Unique ID for this section instance
$sb_id = 'sb-' . uniqid();
?>

<section class="sb" id="<?php echo esc_attr( $sb_id ); ?>" aria-label="Shop the Look">

    <div class="sb__inner">

        <!-- LEFT: image panel with hotspot pins -->
        <div class="sb__media">

            <div class="sb__media-label" aria-hidden="true">
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><rect width="12" height="12" rx="2" fill="#e8192c"/></svg>
                <span>SB_ESKECY_V1.0</span>
            </div>

            <img src="<?php echo esc_url( $banner_image_url ); ?>"
                 alt="Shop the look"
                 class="sb__img">

            <?php foreach ( $products as $idx => $p ) : ?>
            <button
                class="sb__pin<?php echo $idx === 0 ? ' is-active' : ''; ?>"
                style="top:<?php echo esc_attr( $p['top'] ); ?>%;left:<?php echo esc_attr( $p['left'] ); ?>%;"
                data-sb-index="<?php echo esc_attr( $idx ); ?>"
                onclick="sbActivate('<?php echo esc_js( $sb_id ); ?>', <?php echo esc_attr( $idx ); ?>)"
                aria-label="View <?php echo esc_attr( $p['name'] ); ?>"
            >
                <span class="sb__pin-icon" aria-hidden="true">+</span>
                <span class="sb__pin-label" aria-hidden="true"><?php echo esc_html( $p['name'] ); ?></span>
            </button>
            <?php endforeach; ?>

        </div><!-- /.sb__media -->

        <!-- RIGHT: product panel -->
        <div class="sb__panel">

            <div class="sb__panel-head">
                <p class="sb__panel-eyebrow"><?php echo esc_html( $sb_eyebrow ); ?></p>
                <h2 class="sb__panel-title"><?php echo esc_html( $sb_title ); ?></h2>
                <p class="sb__panel-sub"><?php echo esc_html( $sb_subtitle ); ?></p>
            </div>

            <!-- Product list -->
            <ul class="sb__list" role="list">
                <?php foreach ( $products as $idx => $p ) : ?>
                <li class="sb__list-item<?php echo $idx === 0 ? ' is-active' : ''; ?>"
                    data-sb-index="<?php echo esc_attr( $idx ); ?>"
                    onclick="sbActivate('<?php echo esc_js( $sb_id ); ?>', <?php echo esc_attr( $idx ); ?>)"
                    role="button"
                    tabindex="0"
                    aria-label="View <?php echo esc_attr( $p['name'] ); ?>"
                >
                    <span class="sb__list-name"><?php echo esc_html( $p['name'] ); ?></span>
                    <span class="sb__list-indicator" aria-hidden="true"></span>
                </li>
                <?php endforeach; ?>
            </ul>

            <!-- Detail cards -->
            <div class="sb__detail">
                <?php foreach ( $products as $idx => $p ) : ?>
                <div class="sb__detail-pane"
                     data-sb-index="<?php echo esc_attr( $idx ); ?>"
                     style="display:<?php echo $idx === 0 ? 'block' : 'none'; ?>">

                    <div class="sb__detail-body">

                        <img src="<?php echo esc_url( $p['image'] ); ?>"
                             alt="<?php echo esc_attr( $p['name'] ); ?>"
                             class="sb__detail-thumb"
                             loading="lazy">

                        <div class="sb__detail-content">

                            <?php if ( $p['cats'] ) : ?>
                            <div class="sb__detail-tags"><?php echo esc_html( $p['cats'] ); ?></div>
                            <?php endif; ?>

                            <h3 class="sb__detail-title"><?php echo esc_html( $p['name'] ); ?></h3>

                            <?php if ( $p['desc'] ) : ?>
                            <p class="sb__detail-desc"><?php echo esc_html( $p['desc'] ); ?></p>
                            <?php endif; ?>

                            <div class="sb__detail-footer">
                                <div class="sb__detail-price"><?php echo $p['price']; ?></div>
                                <a href="<?php echo esc_url( $p['url'] ); ?>" class="sb__detail-btn">
                                    SHOP NOW <span aria-hidden="true">→</span>
                                </a>
                            </div>

                        </div><!-- /.sb__detail-content -->

                    </div><!-- /.sb__detail-body -->

                </div>
                <?php endforeach; ?>
            </div>

        </div><!-- /.sb__panel -->

    </div><!-- /.sb__inner -->

</section>

<script>
function sbActivate(sectionId, index) {
    var section = document.getElementById(sectionId);
    if (!section) return;

    // Pins
    section.querySelectorAll('.sb__pin').forEach(function(pin) {
        if (parseInt(pin.dataset.sbIndex) === index) {
            pin.classList.add('is-active');
        } else {
            pin.classList.remove('is-active');
        }
    });

    // List items
    section.querySelectorAll('.sb__list-item').forEach(function(li) {
        if (parseInt(li.dataset.sbIndex) === index) {
            li.classList.add('is-active');
        } else {
            li.classList.remove('is-active');
        }
    });

    // Detail panes — show/hide with inline style (overrides everything)
    section.querySelectorAll('.sb__detail-pane').forEach(function(pane) {
        if (parseInt(pane.dataset.sbIndex) === index) {
            pane.style.setProperty('display', 'block', 'important');
            pane.style.animation = 'none';
            pane.offsetHeight; // reflow
            pane.style.animation = '';
        } else {
            pane.style.setProperty('display', 'none', 'important');
        }
    });
}
</script>
