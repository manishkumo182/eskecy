<?php
/**
 * Template Part: Category Tabs — tabbed product browser by category
 */

$tab_cats_exclude = [ get_option('default_product_cat') ];
$tab_cats_eskecy  = get_term_by('slug', 'eskecy', 'product_cat');
if ( $tab_cats_eskecy ) {
    $tab_cats_exclude[] = $tab_cats_eskecy->term_id;
}

$tab_cats = get_terms([
    'taxonomy'   => 'product_cat',
    'hide_empty' => false,
    'exclude'    => $tab_cats_exclude,
    'orderby'    => 'count',
    'order'      => 'DESC',
    'number'     => 6,
]);

if ( is_wp_error($tab_cats) || empty($tab_cats) ) return;

$tab_products = wc_get_products([
    'status'  => 'publish',
    'limit'   => 60,
    'orderby' => 'date',
    'order'   => 'DESC',
]);

if ( empty($tab_products) ) return;
?>

<section class="cat-tabs section" aria-label="Shop by Category">
    <div class="container">

        <div class="cat-tabs__header">
            <div class="cat-tabs__heading">
                <p class="cat-tabs__eyebrow">New Arrivals</p>
                <h2 class="cat-tabs__title">The Best<br>Products</h2>
            </div>
            <div class="cat-tabs__pills" role="tablist" aria-label="Filter by category">
                <button class="cat-tab-pill is-active" data-cat="all" role="tab" aria-selected="true">All</button>
                <?php foreach ( $tab_cats as $cat ) : ?>
                <button class="cat-tab-pill" data-cat="<?php echo esc_attr( $cat->slug ); ?>" role="tab" aria-selected="false">
                    <?php echo esc_html( $cat->name ); ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="cat-tabs__grid product-grid product-grid--3col" role="tabpanel">
            <?php foreach ( $tab_products as $product ) :
                $product_id  = $product->get_id();
                $img_id      = $product->get_image_id();
                $gallery_ids = $product->get_gallery_image_ids();
                $img_url     = $img_id ? wp_get_attachment_image_url( $img_id, 'stanray-product-grid' ) : wc_placeholder_img_src();
                $hover_url   = ! empty( $gallery_ids ) ? wp_get_attachment_image_url( $gallery_ids[0], 'stanray-product-grid' ) : null;

                $p_terms    = get_the_terms( $product_id, 'product_cat' );
                $cat_slugs  = [];
                $badge_name = '';
                if ( $p_terms && ! is_wp_error( $p_terms ) ) {
                    foreach ( $p_terms as $t ) {
                        $cat_slugs[] = $t->slug;
                        if ( ! $badge_name ) $badge_name = $t->name;
                    }
                }

                $in_wish = in_array( $product_id, eskecy_get_wishlist() );
            ?>
            <article class="product-card cat-tabs__card"
                     data-cats="<?php echo esc_attr( implode( ' ', $cat_slugs ) ); ?>"
                     data-product-id="<?php echo esc_attr( $product_id ); ?>">
                <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="product-card__link">
                    <div class="product-card__image-wrap cat-tabs__img-wrap">
                        <img src="<?php echo esc_url( $img_url ); ?>"
                             alt="<?php echo esc_attr( $product->get_name() ); ?>"
                             class="product-card__img" loading="lazy">
                        <?php if ( $hover_url ) : ?>
                        <img src="<?php echo esc_url( $hover_url ); ?>" alt="" class="product-card__img--hover" loading="lazy" aria-hidden="true">
                        <?php endif; ?>
                        <?php if ( $badge_name ) : ?>
                        <span class="cat-tabs__badge"><?php echo esc_html( $badge_name ); ?></span>
                        <?php endif; ?>
                        <?php if ( $product->is_on_sale() ) : ?>
                        <span class="product-card__badge">Sale</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-card__info">
                        <h3 class="product-card__title"><?php echo esc_html( $product->get_name() ); ?></h3>
                        <div class="product-card__price"><?php echo $product->get_price_html(); ?></div>
                    </div>
                </a>
                <button class="product-card__wish<?php echo $in_wish ? ' is-wished' : ''; ?>"
                    data-product-id="<?php echo esc_attr( $product_id ); ?>"
                    data-nonce="<?php echo esc_attr( wp_create_nonce( 'eskecy_wishlist' ) ); ?>"
                    aria-label="<?php echo $in_wish ? 'Remove from Wishlist' : 'Save to Wishlist'; ?>"
                    title="<?php echo $in_wish ? 'Remove from Wishlist' : 'Save to Wishlist'; ?>"
                ><?php echo eskecy_wishlist_heart_svg( $in_wish ); ?></button>
            </article>
            <?php endforeach; ?>
        </div>

        <nav class="cat-tabs__pagination" id="catTabsPagination" aria-label="Product pagination"></nav>

    </div>
</section>

<script>
(function () {
    var PER_PAGE = 9;
    var pills    = document.querySelectorAll('.cat-tab-pill');
    var cards    = Array.prototype.slice.call(document.querySelectorAll('.cat-tabs__card'));
    var pager    = document.getElementById('catTabsPagination');

    var activeCat   = 'all';
    var currentPage = 1;

    function filteredCards() {
        if (activeCat === 'all') return cards;
        return cards.filter(function (card) {
            var cats = card.dataset.cats ? card.dataset.cats.split(' ') : [];
            return cats.indexOf(activeCat) !== -1;
        });
    }

    function renderPagination(totalPages) {
        pager.innerHTML = '';
        if (totalPages <= 1) return;

        function makeBtn(label, page, isCurrent, isDisabled) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'page-numbers' + (isCurrent ? ' current' : '');
            btn.textContent = label;
            if (isDisabled) {
                btn.disabled = true;
            } else {
                btn.addEventListener('click', function () {
                    currentPage = page;
                    renderPage();
                    pager.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                });
            }
            return btn;
        }

        pager.appendChild(makeBtn('←', currentPage - 1, false, currentPage === 1));
        for (var p = 1; p <= totalPages; p++) {
            pager.appendChild(makeBtn(String(p), p, p === currentPage, false));
        }
        pager.appendChild(makeBtn('→', currentPage + 1, false, currentPage === totalPages));
    }

    function renderPage() {
        var filtered    = filteredCards();
        var totalPages  = Math.max(1, Math.ceil(filtered.length / PER_PAGE));
        if (currentPage > totalPages) currentPage = totalPages;

        cards.forEach(function (card) { card.style.display = 'none'; });
        filtered
            .slice((currentPage - 1) * PER_PAGE, currentPage * PER_PAGE)
            .forEach(function (card) { card.style.display = ''; });

        renderPagination(totalPages);
    }

    pills.forEach(function (pill) {
        pill.addEventListener('click', function () {
            pills.forEach(function (p) {
                p.classList.remove('is-active');
                p.setAttribute('aria-selected', 'false');
            });
            this.classList.add('is-active');
            this.setAttribute('aria-selected', 'true');

            activeCat   = this.dataset.cat;
            currentPage = 1;
            renderPage();
        });
    });

    renderPage();
}());
</script>
