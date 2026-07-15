<?php
/**
 * Template Part: Product Spotlight — image with hotspots + feature accordion
 */

$sp_products = wc_get_products(['status' => 'publish', 'limit' => 1, 'featured' => true, 'orderby' => 'date', 'order' => 'DESC']);
if (empty($sp_products)) {
    $sp_products = wc_get_products(['status' => 'publish', 'limit' => 1, 'orderby' => 'popularity', 'order' => 'DESC']);
}
if (empty($sp_products)) return;

$sp       = $sp_products[0];
$sp_img   = wp_get_attachment_image_url($sp->get_image_id(), 'large');
$sp_name  = $sp->get_name();
$sp_url   = $sp->get_permalink();
$sp_price = $sp->get_price_html();
$sp_desc  = $sp->get_short_description();
if (!$sp_desc) {
    $sp_desc = wp_trim_words(wp_strip_all_tags($sp->get_description()), 22, '...');
}

$sp_terms = get_the_terms($sp->get_id(), 'product_cat');
$sp_cat   = ($sp_terms && !is_wp_error($sp_terms)) ? $sp_terms[0]->name : 'Collection';

// Build feature list from attributes; fall back to static items
$sp_features = [];
foreach ($sp->get_attributes() as $attr) {
    $label = wc_attribute_label($attr->get_name());
    $terms = $attr->get_terms();
    $val   = $terms ? implode(', ', array_map(fn($t) => $t->name, $terms)) : implode(', ', $attr->get_options());
    if ($val) $sp_features[] = ['label' => $label, 'value' => $val];
}
if (empty($sp_features)) {
    $sp_features = [
        ['label' => 'Material',  'value' => 'Premium Graphene Fabric'],
        ['label' => 'Fit',       'value' => 'Modern Oversized Fit'],
        ['label' => 'Shipping',  'value' => 'Free Delivery Included'],
        ['label' => 'Authentic', 'value' => '100% Genuine Eskecy'],
    ];
}

// Hotspot positions (% top / % left) — up to 3
$sp_dots = [
    ['top' => '28%', 'left' => '38%'],
    ['top' => '55%', 'left' => '62%'],
    ['top' => '74%', 'left' => '32%'],
];
?>

<section class="sp section" aria-label="Product Spotlight">
    <div class="container">

        <!-- Header row -->
        <div class="sp__head">
            <div class="sp__head-left">
                <p class="sp__eyebrow">Our Collection</p>
                <h2 class="sp__title">Wear the<br>Future.</h2>
            </div>
            <div class="sp__head-right">
                <p class="sp__lead"><?php echo esc_html($sp_desc ?: 'Eskecy streetwear combines premium fabric with modern fit and unmatched street culture.'); ?></p>
            </div>
        </div>

        <!-- Body: image left, features right -->
        <div class="sp__body">

            <!-- Left: image card -->
            <div class="sp__img-panel">
                <div class="sp__img-card">

                    <!-- Top label bar -->
                    <div class="sp__img-label">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        <span>ESKECY_<?php echo esc_html(strtoupper($sp_cat)); ?>_V1.0</span>
                    </div>

                    <!-- Image + hotspot dots -->
                    <div class="sp__img-wrap">
                        <?php if ($sp_img) : ?>
                        <img src="<?php echo esc_url($sp_img); ?>"
                             alt="<?php echo esc_attr($sp_name); ?>"
                             class="sp__img">
                        <?php endif; ?>
                        <?php foreach (array_slice($sp_dots, 0, min(3, count($sp_features))) as $i => $dot) : ?>
                        <button class="sp__dot<?php echo $i === 0 ? ' is-active' : ''; ?>"
                                style="top:<?php echo $dot['top']; ?>;left:<?php echo $dot['left']; ?>"
                                data-sp-feat="<?php echo $i; ?>"
                                aria-label="View feature <?php echo $i + 1; ?>">
                            <span class="sp__dot-icon">+</span>
                        </button>
                        <?php endforeach; ?>
                    </div>

                    <!-- Bottom meta bar -->
                    <div class="sp__img-meta">
                        <span class="sp__img-meta-name"><?php echo esc_html($sp_name); ?></span>
                        <span class="sp__img-meta-price"><?php echo $sp_price; ?></span>
                    </div>

                </div>
            </div>

            <!-- Right: feature list + detail card -->
            <div class="sp__feat-panel">

                <ul class="sp__feat-list" role="tablist">
                    <?php foreach ($sp_features as $i => $feat) : ?>
                    <li class="sp__feat-item<?php echo $i === 0 ? ' is-active' : ''; ?>"
                        data-sp-feat="<?php echo $i; ?>"
                        role="tab"
                        aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>">
                        <span class="sp__feat-name"><?php echo esc_html($feat['label']); ?></span>
                        <span class="sp__feat-dot"></span>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <div class="sp__detail">
                    <p class="sp__detail-tag"><?php echo esc_html(strtoupper($sp_cat)); ?></p>
                    <h3 class="sp__detail-title"><?php echo esc_html(strtoupper($sp_name)); ?></h3>
                    <?php foreach ($sp_features as $i => $feat) : ?>
                    <p class="sp__detail-val<?php echo $i === 0 ? ' is-active' : ''; ?>"
                       data-sp-desc="<?php echo $i; ?>">
                        <?php echo esc_html($feat['value']); ?>
                    </p>
                    <?php endforeach; ?>
                    <a href="<?php echo esc_url($sp_url); ?>" class="sp__detail-cta">
                        Shop Now <span>→</span>
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>

<script>
(function () {
    var dots  = document.querySelectorAll('.sp__dot');
    var items = document.querySelectorAll('.sp__feat-item');
    var descs = document.querySelectorAll('.sp__detail-val');

    function activate(idx) {
        dots.forEach(function (d) { d.classList.toggle('is-active', +d.dataset.spFeat === idx); });
        items.forEach(function (it) {
            var active = +it.dataset.spFeat === idx;
            it.classList.toggle('is-active', active);
            it.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        descs.forEach(function (d) { d.classList.toggle('is-active', +d.dataset.spDesc === idx); });
    }

    dots.forEach(function (d) {
        d.addEventListener('click', function () { activate(+this.dataset.spFeat); });
    });
    items.forEach(function (it) {
        it.addEventListener('click', function () { activate(+this.dataset.spFeat); });
    });
}());
</script>
