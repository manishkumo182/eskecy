
<?php
/**
 * Single Product Template — Stan Ray Style
 * Left: stacked images  |  Right: sticky panel
 *   - Colour swatches (own section)
 *   - Size buttons     (own section)
 *   - Add to cart via WC AJAX (with form fallback)
 *
 * @package StanRay_Custom
 */

get_header();
while ( have_posts() ) : the_post();
global $product;

// ── Product data ───────────────────────────────────────────────
$pid            = $product->get_id();
$is_variable    = $product->is_type( 'variable' );
$image_id       = $product->get_image_id();
$gallery_ids    = $product->get_gallery_image_ids();
$all_imgs       = $image_id ? array_merge( [ $image_id ], $gallery_ids ) : $gallery_ids;
$size_guide_url = get_theme_mod( 'size_guide_url', '' );

// ── For variable products: map attributes + build variation lookup ──
$color_attr  = ''; // attribute key that is colour
$size_attr   = ''; // attribute key that is size
$color_opts  = []; // [ slug => ['label'=>, 'hex'=>] ]
$size_opts   = []; // [ slug => ['label'=>, 'in_stock'=>] ]
$var_lookup  = []; // [ color_slug ][ size_slug ] = [ 'id'=>, 'in_stock'=> ]

if ( $is_variable ) {
    $attrs     = $product->get_attributes();
    $avail     = $product->get_available_variations();

    foreach ( $attrs as $key => $attr ) {
        $l = strtolower( $key );
        if ( strpos( $l, 'colour' ) !== false || strpos( $l, 'color' ) !== false ) {
            $color_attr = $key;
        } elseif ( strpos( $l, 'size' ) !== false ) {
            $size_attr = $key;
        }
    }

    // Build lookup table
    foreach ( $avail as $v ) {
        $c_key = 'attribute_' . sanitize_title( $color_attr );
        $s_key = 'attribute_' . sanitize_title( $size_attr );
        $c     = strtolower( $v['attributes'][ $c_key ] ?? '' );
        $s     = strtolower( $v['attributes'][ $s_key ] ?? '' );
        if ( $c !== '' || $s !== '' ) {
            $var_lookup[ $c ][ $s ] = [
                'id'       => $v['variation_id'],
                'in_stock' => $v['is_in_stock'],
            ];
        }
    }

    // Colour options
    if ( $color_attr && isset( $attrs[ $color_attr ] ) ) {
        $terms = $attrs[ $color_attr ]->get_terms();
        if ( $terms ) {
            foreach ( $terms as $t ) {
                $hex = get_term_meta( $t->term_id, 'color', true )
                    ?: get_term_meta( $t->term_id, 'product_color_hex', true )
                    ?: '';
                $color_opts[ $t->slug ] = [ 'label' => $t->name, 'hex' => $hex ];
            }
        } else {
            foreach ( $attrs[ $color_attr ]->get_slugs() as $slug ) {
                $color_opts[ $slug ] = [ 'label' => ucfirst( str_replace( '-', ' ', $slug ) ), 'hex' => '' ];
            }
        }
    }

    // Size options (stock resolved per-size after colour pick — default in stock)
    if ( $size_attr && isset( $attrs[ $size_attr ] ) ) {
        $terms = $attrs[ $size_attr ]->get_terms();
        if ( $terms ) {
            foreach ( $terms as $t ) {
                $size_opts[ $t->slug ] = [ 'label' => $t->name, 'in_stock' => true ];
            }
        } else {
            foreach ( $attrs[ $size_attr ]->get_slugs() as $slug ) {
                $size_opts[ $slug ] = [ 'label' => $slug, 'in_stock' => true ];
            }
        }
    }
}
?>

<!-- LIGHTBOX -->
<div class="sr-lightbox" id="srLightbox">
    <button class="sr-lightbox__close" id="srLightboxClose" type="button" aria-label="Close">&times;</button>
    <img id="srLightboxImg" src="" alt="">
</div>

<div class="sr-product">

    <!-- Breadcrumb -->
    <nav class="sr-breadcrumb">
        <?php woocommerce_breadcrumb([
            'delimiter'   => '<span class="sr-bc-sep">/</span>',
            'wrap_before' => '', 'wrap_after'  => '',
            'before'      => '', 'after'       => '',
        ]); ?>
    </nav>

    <div class="sr-layout">

        <!-- ═══ LEFT: Stacked images ══════════════════════ -->
        <div class="sr-images">
            <?php foreach ( $all_imgs as $i => $img_id ) :
                $src  = wp_get_attachment_image_url( $img_id, 'woocommerce_single' );
                $full = wp_get_attachment_image_url( $img_id, 'full' );
                $alt  = get_post_meta( $img_id, '_wp_attachment_image_alt', true ) ?: get_the_title();
            ?>
            <div class="sr-img-item">
                <img src="<?php echo esc_url( $src ); ?>"
                     alt="<?php echo esc_attr( $alt ); ?>"
                     loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>">
                <button class="sr-zoom" type="button"
                        data-src="<?php echo esc_url( $full ); ?>"
                        aria-label="Zoom">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7"/>
                        <line x1="21" y1="21" x2="15.65" y2="15.65"/>
                        <line x1="11" y1="8.5" x2="11" y2="13.5"/>
                        <line x1="8.5" y1="11" x2="13.5" y2="11"/>
                    </svg>
                </button>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ═══ RIGHT: Sticky panel ════════════════════════ -->
        <aside class="sr-panel">

            <h1 class="sr-name"><?php the_title(); ?></h1>

            <div class="sr-price" id="srPrice">
                <?php echo $product->get_price_html(); ?>
            </div>

            <?php if ( $is_variable ) : ?>

            <!-- ── COLOUR ─────────────────────────────────── -->
            <?php if ( ! empty( $color_opts ) ) : ?>
            <div class="sr-section">
                <div class="sr-section__head">
                    <span class="sr-section__label">Colour</span>
                    <span class="sr-section__val" id="srColorVal"></span>
                </div>
                <div class="sr-swatches" id="srColorSwatches">
                    <?php foreach ( $color_opts as $slug => $c ) : ?>
                    <button type="button"
                            class="sr-swatch<?php echo ! $c['hex'] ? ' sr-swatch--text' : ''; ?>"
                            data-value="<?php echo esc_attr( $slug ); ?>"
                            data-label="<?php echo esc_attr( $c['label'] ); ?>"
                            <?php if ( $c['hex'] ) : ?>
                            style="background:<?php echo esc_attr( $c['hex'] ); ?>"
                            <?php endif; ?>
                            title="<?php echo esc_attr( $c['label'] ); ?>"
                            aria-label="<?php echo esc_attr( $c['label'] ); ?>">
                        <?php if ( ! $c['hex'] ) echo esc_html( $c['label'] ); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- ── SIZE ───────────────────────────────────── -->
            <?php if ( ! empty( $size_opts ) ) : ?>
            <div class="sr-section">
                <div class="sr-section__head">
                    <span class="sr-section__label">Size</span>
                    <?php if ( $size_guide_url ) : ?>
                    <a class="sr-section__guide" href="<?php echo esc_url( $size_guide_url ); ?>" target="_blank">Size Guide</a>
                    <?php endif; ?>
                </div>
                <div class="sr-sizes" id="srSizes">
                    <?php foreach ( $size_opts as $slug => $s ) : ?>
                    <button type="button"
                            class="sr-size-btn"
                            data-value="<?php echo esc_attr( $slug ); ?>"
                            aria-label="<?php echo esc_attr( $s['label'] ); ?>">
                        <?php echo esc_html( $s['label'] ); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                <p class="sr-size-err" id="srSizeErr" hidden>Please select a size</p>
            </div>
            <?php endif; ?>

            <?php endif; // end $is_variable ?>

            <!-- ── ADD TO CART ────────────────────────────── -->
            <?php if ( $is_variable ) : ?>

            <!-- Invisible real WC form — WC JS initialises on it -->
            <form class="variations_form cart sr-wc-form" method="post"
                  data-product_id="<?php echo esc_attr( $pid ); ?>"
                  data-product_variations="<?php echo htmlspecialchars( wp_json_encode( $product->get_available_variations() ) ); ?>">
                <?php foreach ( $product->get_variation_attributes() as $attr_name => $options ) :
                    $slug = sanitize_title( $attr_name );
                ?>
                <select name="attribute_<?php echo esc_attr( $slug ); ?>"
                        data-attribute_name="attribute_<?php echo esc_attr( $slug ); ?>"
                        class="sr-hidden-select">
                    <option value=""><?php echo esc_html( wc_attribute_label( $attr_name ) ); ?></option>
                    <?php foreach ( $options as $opt ) : ?>
                    <option value="<?php echo esc_attr( $opt ); ?>"><?php echo esc_html( $opt ); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php endforeach; ?>
                <input type="hidden" name="variation_id" id="srVarId" value="">
                <input type="hidden" name="product_id" value="<?php echo esc_attr( $pid ); ?>">
                <input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $pid ); ?>">
                <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
            </form>

            <button type="button" class="sr-atc" id="srAtc">
                <span class="sr-atc__label">Add to Cart</span>
                <span class="sr-atc__spin"></span>
            </button>

            <?php else : ?>
            <!-- Simple product -->
            <div class="sr-simple-atc">
                <?php woocommerce_template_single_add_to_cart(); ?>
            </div>
            <?php endif; ?>

            <!-- ── ACCORDIONS ─────────────────────────────── -->
            <div class="sr-accordion">

                <div class="sr-acc-item">
                    <button class="sr-acc-trigger" aria-expanded="false" type="button">
                        Description <span class="sr-acc-icon"></span>
                    </button>
                    <div class="sr-acc-body">
                        <div class="sr-acc-inner">
                            <?php
                            $d = $product->get_description() ?: $product->get_short_description();
                            echo wp_kses_post( $d ?: '<p>No description available.</p>' );
                            ?>
                        </div>
                    </div>
                </div>

                <?php if ( $size_guide_url ) : ?>
                <div class="sr-acc-item">
                    <button class="sr-acc-trigger" aria-expanded="false" type="button">
                        Size Guide <span class="sr-acc-icon"></span>
                    </button>
                    <div class="sr-acc-body">
                        <div class="sr-acc-inner">
                            <a href="<?php echo esc_url( $size_guide_url ); ?>" target="_blank">View full size guide &rarr;</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="sr-acc-item">
                    <button class="sr-acc-trigger" aria-expanded="false" type="button">
                        Shipping <span class="sr-acc-icon"></span>
                    </button>
                    <div class="sr-acc-body">
                        <div class="sr-acc-inner">
                            <?php
                            $t = function_exists( 'get_field' ) ? get_field( 'shipping_info', $pid ) : '';
                            echo wp_kses_post( $t ?: '<p>Standard shipping 3–5 business days. Express available at checkout.</p>' );
                            ?>
                        </div>
                    </div>
                </div>

                <div class="sr-acc-item">
                    <button class="sr-acc-trigger" aria-expanded="false" type="button">
                        Returns &amp; Exchanges <span class="sr-acc-icon"></span>
                    </button>
                    <div class="sr-acc-body">
                        <div class="sr-acc-inner">
                            <?php
                            $t = function_exists( 'get_field' ) ? get_field( 'returns_info', $pid ) : '';
                            echo wp_kses_post( $t ?: '<p>Returns accepted within 30 days. Items must be unworn with tags attached.</p>' );
                            ?>
                        </div>
                    </div>
                </div>

                <?php if ( comments_open() ) :
                    $rc  = $product->get_rating_count();
                    $avg = $product->get_average_rating();
                ?>
                <div class="sr-acc-item">
                    <button class="sr-acc-trigger" aria-expanded="false" type="button">
                        Reviews<?php if ( $rc ) echo ' <span class="sr-rc">(' . $rc . ')</span>'; ?>
                        <span class="sr-acc-icon"></span>
                    </button>
                    <div class="sr-acc-body">
                        <div class="sr-acc-inner">
                            <?php if ( $rc ) : ?>
                            <div class="sr-rating">
                                <?php echo wc_get_rating_html( $avg, $rc ); ?>
                                <span><?php echo $rc; ?> reviews</span>
                            </div>
                            <?php endif; ?>
                            <?php comments_template(); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div><!-- .sr-accordion -->

        </aside>
    </div><!-- .sr-layout -->

    <!-- Related -->
    <?php
    $related_ids = wc_get_related_products( $pid, 4 );
    if ( ! empty( $related_ids ) ) :
        $related = array_filter( array_map( 'wc_get_product', $related_ids ) );
    ?>
    <div class="sr-related">
        <h2 class="sr-related__title">You May Also Like</h2>
        <div class="sr-related__grid">
            <?php foreach ( $related as $rel ) :
                $thumb = wp_get_attachment_image_url( $rel->get_image_id(), 'woocommerce_thumbnail' );
            ?>
            <a href="<?php echo esc_url( $rel->get_permalink() ); ?>" class="sr-related__card">
                <div class="sr-related__img">
                    <img src="<?php echo esc_url( $thumb ?: wc_placeholder_img_src() ); ?>"
                         alt="<?php echo esc_attr( $rel->get_name() ); ?>" loading="lazy">
                </div>
                <p class="sr-related__name"><?php echo esc_html( $rel->get_name() ); ?></p>
                <p class="sr-related__price"><?php echo $rel->get_price_html(); ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div><!-- .sr-product -->

<!-- JS data payload -->
<?php if ( $is_variable ) : ?>
<script>
var SR_DATA = {
    pid:       <?php echo $pid; ?>,
    ajaxUrl:   '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>',
    nonce:     '<?php echo wp_create_nonce( 'woocommerce-add-to-cart' ); ?>',
    colorAttr: '<?php echo esc_js( $color_attr ? 'attribute_' . sanitize_title( $color_attr ) : '' ); ?>',
    sizeAttr:  '<?php echo esc_js( $size_attr  ? 'attribute_' . sanitize_title( $size_attr )  : '' ); ?>',
    lookup:    <?php echo wp_json_encode( $var_lookup ); ?>,
    allVars:   <?php echo wp_json_encode( $product->get_available_variations() ); ?>
};
</script>
<?php endif; ?>

<style>
/* ================================================================
   SINGLE PRODUCT — Stan Ray Style
================================================================ */
.sr-product {
    --f:   'Barlow','Helvetica Neue',Helvetica,sans-serif;
    --k:   #1a1a1a;
    --w:   #fff;
    --bg:  #edeae4;
    --br:  #e2e2e2;
    --mu:  #888;
    --out: #ccc;
    font-family: var(--f);
    font-size: 13px;
    color: var(--k);
    -webkit-font-smoothing: antialiased;
    line-height: 1.5;
}

/* BREADCRUMB */
.sr-breadcrumb {
    padding: 10px 20px;
    font-size: 10.5px;
    color: var(--mu);
    letter-spacing: .06em;
    border-bottom: 1px solid var(--br);
}
.sr-breadcrumb a { color: var(--mu); }
.sr-breadcrumb a:hover { color: var(--k); }
.sr-bc-sep { margin: 0 6px; }

/* LAYOUT */
.sr-layout {
    display: grid;
    grid-template-columns: 57% 43%;
    align-items: start;
}

/* LEFT IMAGES */
.sr-images { background: var(--bg); }
.sr-img-item {
    position: relative;
    line-height: 0;
    border-bottom: 3px solid var(--w);
    overflow: hidden;
}
.sr-img-item:last-child { border-bottom: none; }
.sr-img-item img {
    width: 100%; height: auto; display: block;
    transition: transform .55s ease;
}
.sr-img-item:hover img { transform: scale(1.02); }

/* ZOOM DOT */
.sr-zoom {
    position: absolute; right: 13px; top: 50%;
    transform: translateY(-50%);
    width: 22px; height: 22px;
    background: rgba(108,99,255,.18);
    border-radius: 50%; border: none; cursor: zoom-in;
    display: flex; align-items: center; justify-content: center;
    z-index: 2; transition: background .2s;
}
.sr-zoom:hover { background: rgba(108,99,255,.4); }
.sr-zoom svg { width: 11px; height: 11px; stroke: #6c63ff; }

/* RIGHT PANEL */
.sr-panel {
    position: sticky;
    top: 60px; /* ← set to your header height */
    height: calc(100vh - 60px);
    overflow-y: auto;
    padding: 22px 26px 60px;
    border-left: 1px solid var(--br);
    scrollbar-width: thin;
    scrollbar-color: #ddd transparent;
}
.sr-panel::-webkit-scrollbar       { width: 3px; }
.sr-panel::-webkit-scrollbar-thumb { background: #ddd; }

/* NAME */
.container-fluid {
        font-family: var(--font-body);
    font-size: 12px; font-weight: 600;
    letter-spacing: .12em; text-transform: uppercase;
    line-height: 1.3; margin-bottom: 4px;
}

/* PRICE */
.sr-price {
    font-size: 13px; color: var(--mu);
    margin-bottom: 22px; letter-spacing: .03em;
}
.sr-price .woocommerce-Price-amount { font-size: 13px; font-weight: 400; color: var(--mu); }
.sr-price ins { text-decoration: none; }
.sr-price del .woocommerce-Price-amount { color: var(--out); }

/* SECTIONS (colour / size share same layout) */
.sr-section { margin-bottom: 16px; }
.sr-section__head {
    display: flex; align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}
.sr-section__label {
    font-size: 10px; font-weight: 600;
    letter-spacing: .18em; text-transform: uppercase;
}
.sr-section__val {
    font-size: 10px; color: var(--mu); letter-spacing: .06em;
}
.sr-section__guide {
    font-size: 10px; color: var(--mu);
    text-decoration: underline; text-underline-offset: 2px;
}
.sr-section__guide:hover { color: var(--k); }

/* COLOUR SWATCHES */
.sr-swatches { display: flex; flex-wrap: wrap; gap: 7px; }
.sr-swatch {
    width: 26px; height: 26px;
    border-radius: 50%;
    border: 2px solid transparent;
    cursor: pointer; outline: none;
    transition: transform .14s, box-shadow .14s;
    background: #ccc;
    font-size: 0; /* hide label text unless text-swatch */
}
.sr-swatch:hover { transform: scale(1.12); }
.sr-swatch.active {
    box-shadow: 0 0 0 2px var(--w), 0 0 0 4px var(--k);
    border-color: transparent;
}
/* Text-based swatch when no hex colour */
.sr-swatch--text {
    border-radius: 2px;
    width: auto; min-width: 36px; height: 27px;
    padding: 0 8px;
    font-size: 11px; font-weight: 400;
    color: var(--k);
    background: var(--w);
    border: 1px solid var(--br);
    font-family: var(--f);
}
.sr-swatch--text:hover { border-color: var(--k); }
.sr-swatch--text.active {
    background: var(--k); color: var(--w);
    border-color: var(--k);
    box-shadow: none;
}

/* SIZE BUTTONS */
.sr-sizes { display: flex; flex-wrap: wrap; gap: 4px; }
.sr-size-btn {
    min-width: 34px; height: 27px; padding: 0 6px;
    display: flex; align-items: center; justify-content: center;
    border: 1px solid var(--br);
    background: var(--w);
    font-family: var(--f); font-size: 11px; font-weight: 400;
    color: var(--k); cursor: pointer;
    letter-spacing: .02em;
    transition: border-color .12s, background .12s, color .12s;
    position: relative; overflow: hidden;
}
.sr-size-btn:hover:not(.is-out):not(:disabled) { border-color: var(--k); }
.sr-size-btn.active { background: var(--k); color: var(--w); border-color: var(--k); }
.sr-size-btn.is-out {
    color: var(--out); border-color: #ebebeb; cursor: not-allowed;
}
.sr-size-btn.is-out::after {
    content: ''; position: absolute; inset: 0;
    background: repeating-linear-gradient(-50deg,transparent,transparent 4px,#f0f0f0 4px,#f0f0f0 5px);
}
.sr-size-err {
    font-size: 10.5px; color: #c0392b;
    margin-top: 6px; letter-spacing: .03em;
}
.sr-img-item:last-child {
    border-bottom: none;
    padding: 0px 0px 0px 20px;
}
/* HIDDEN WC FORM */
.sr-wc-form { position: absolute; left: -9999px; width: 1px; overflow: hidden; }
.sr-hidden-select { position: absolute; left: -9999px; }

/* ATC BUTTON */
.sr-atc {
    width: 100%; height: 40px;
    background: var(--k); color: var(--w);
    border: none; font-family: var(--f);
    font-size: 10.5px; font-weight: 600;
    letter-spacing: .22em; text-transform: uppercase;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: background .15s;
    margin-bottom: 22px;
}
.sr-atc:hover { background: #333; }
.sr-atc:disabled { opacity: .5; cursor: not-allowed; }
.sr-atc.is-loading { opacity: .65; pointer-events: none; }
.sr-atc.is-done { background: #2a6b2a; }
.sr-atc__spin {
    display: none;
    width: 12px; height: 12px;
    border: 1.5px solid rgba(255,255,255,.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: sr-spin .55s linear infinite;
}
.sr-atc.is-loading .sr-atc__spin { display: block; }
@keyframes sr-spin { to { transform: rotate(360deg); } }

/* Simple product native ATC */
.sr-simple-atc .single_add_to_cart_button {
    width: 100%; height: 40px;
    background: var(--k); color: var(--w);
    border: none; font-family: var(--f);
    font-size: 10.5px; font-weight: 600;
    letter-spacing: .22em; text-transform: uppercase;
    cursor: pointer; border-radius: 0; padding: 0;
    display: flex; align-items: center; justify-content: center;
    transition: background .15s; margin-bottom: 22px;
}
.sr-simple-atc .single_add_to_cart_button:hover { background: #333; }
.sr-simple-atc .quantity { display: none; }

/* ACCORDION */
.sr-accordion { border-top: 1px solid var(--br); }
.sr-acc-item { border-bottom: 1px solid var(--br); }
.sr-acc-trigger {
    width: 100%; display: flex; align-items: center;
    justify-content: space-between; padding: 11px 0;
    background: none; border: none;
    font-family: var(--f); font-size: 10px; font-weight: 600;
    letter-spacing: .18em; text-transform: uppercase;
    color: var(--k); cursor: pointer; text-align: left;
}
.sr-acc-trigger:hover { color: #555; }
.sr-rc { font-weight: 400; color: var(--mu); }
.sr-acc-icon {
    flex-shrink: 0; width: 12px; height: 12px;
    position: relative; margin-left: 8px;
}
.sr-acc-icon::before,
.sr-acc-icon::after {
    content: ''; position: absolute; background: currentColor;
    border-radius: 1px; transition: opacity .2s, transform .2s;
}
.sr-acc-icon::before { width: 12px; height: 1px; top: 50%; left: 0; transform: translateY(-50%); }
.sr-acc-icon::after  { width: 1px; height: 12px; top: 0; left: 50%; transform: translateX(-50%); }
.sr-acc-trigger[aria-expanded="true"] .sr-acc-icon::after {
    transform: translateX(-50%) rotate(90deg); opacity: 0;
}
.sr-acc-body { overflow: hidden; max-height: 0; transition: max-height .28s ease; }
.sr-acc-body.open { max-height: 600px; }
.sr-acc-inner {
    padding: 0 0 14px;
    font-size: 11.5px; color: #5a5a5a; line-height: 1.78;
}
.sr-acc-inner p + p { margin-top: .5em; }
.sr-acc-inner a { color: var(--k); text-decoration: underline; text-underline-offset: 2px; }
.sr-acc-inner ul { padding-left: 16px; list-style: disc; }
.sr-acc-inner ul li { margin-bottom: 2px; }
.sr-rating { display: flex; align-items: center; gap: 8px; font-size: 11.5px; margin-bottom: 10px; }

/* RELATED */
.sr-related { padding: 40px 26px 60px; border-top: 1px solid var(--br); }
.sr-related__title {
    font-size: 10px; font-weight: 600;
    letter-spacing: .18em; text-transform: uppercase; margin-bottom: 18px;
}
.sr-related__grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 3px; }
.sr-related__card { display: block; color: var(--k); }
.sr-related__img { background: var(--bg); overflow: hidden; aspect-ratio: 3/4; }
.sr-related__img img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; display: block; }
.sr-related__card:hover .sr-related__img img { transform: scale(1.04); }
.sr-related__name { font-size: 11px; font-weight: 400; letter-spacing: .05em; text-transform: uppercase; margin-top: 6px; }
.sr-related__price { font-size: 11px; color: var(--mu); }
.sr-related__price .woocommerce-Price-amount { color: var(--mu); font-size: 11px; }

/* LIGHTBOX */
.sr-lightbox {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,.92);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; pointer-events: none; transition: opacity .22s;
}
.sr-lightbox.open { opacity: 1; pointer-events: all; }
.sr-lightbox img { max-width: 90vw; max-height: 90vh; object-fit: contain; }
.sr-lightbox__close {
    position: absolute; top: 18px; right: 22px;
    background: none; border: none;
    color: #fff; font-size: 28px; cursor: pointer; opacity: .7; line-height: 1;
}
.sr-lightbox__close:hover { opacity: 1; }

/* SHAKE */
@keyframes sr-shake {
    0%,100% { transform: translateX(0); }
    20%      { transform: translateX(-6px); }
    40%      { transform: translateX(6px); }
    60%      { transform: translateX(-4px); }
    80%      { transform: translateX(4px); }
}

/* RESPONSIVE */
@media (max-width: 900px) {
    .sr-layout { grid-template-columns: 1fr; }
    .sr-panel { position: static; height: auto; border-left: none; border-top: 1px solid var(--br); }
    .sr-related__grid { grid-template-columns: repeat(2,1fr); }
}
</style>

<script>
jQuery(function ($) {
    'use strict';

    /* ── Accordion ──────────────────────────────────────────── */
    $('.sr-acc-trigger').on('click', function () {
        var $b  = $(this).next('.sr-acc-body');
        var was = $(this).attr('aria-expanded') === 'true';
        $('.sr-acc-trigger').attr('aria-expanded', 'false');
        $('.sr-acc-body').removeClass('open');
        if (!was) { $(this).attr('aria-expanded', 'true'); $b.addClass('open'); }
    });

    /* ── Lightbox ───────────────────────────────────────────── */
    var $lb  = $('#srLightbox');
    var $lbi = $('#srLightboxImg');
    $(document).on('click', '.sr-zoom', function () {
        $lbi.attr('src', $(this).data('src'));
        $lb.addClass('open');
        $('body').css('overflow', 'hidden');
    });
    function closeLb() {
        $lb.removeClass('open'); $('body').css('overflow', '');
        setTimeout(function () { $lbi.attr('src', ''); }, 300);
    }
    $('#srLightboxClose').on('click', closeLb);
    $lb.on('click', function (e) { if ($(e.target).is($lb)) closeLb(); });
    $(document).on('keydown', function (e) { if (e.key === 'Escape') closeLb(); });

    /* ── Variable product only ──────────────────────────────── */
    if (typeof SR_DATA === 'undefined') return;

    var D         = SR_DATA;
    var selColor  = '';
    var selSize   = '';
    var resolvedId = 0;

    /* Resolve variation_id from current selections */
    function resolve() {
        resolvedId = 0;
        var c = selColor.toLowerCase();
        var s = selSize.toLowerCase();

        if (D.colorAttr && D.sizeAttr) {
            /* both colour + size */
            if (D.lookup[c] && D.lookup[c][s]) resolvedId = D.lookup[c][s].id;
        } else if (D.sizeAttr) {
            /* size only */
            if (D.lookup[''] && D.lookup[''][s]) resolvedId = D.lookup[''][s].id;
            /* some setups use '' or first key */
            if (!resolvedId) {
                var firstKey = Object.keys(D.lookup)[0];
                if (D.lookup[firstKey] && D.lookup[firstKey][s]) resolvedId = D.lookup[firstKey][s].id;
            }
        } else if (D.colorAttr) {
            /* colour only */
            if (D.lookup[c]) {
                var sk = Object.keys(D.lookup[c])[0];
                if (sk !== undefined) resolvedId = D.lookup[c][sk].id;
            }
        }
    }

    /* Update size stock when colour changes */
    function refreshSizeStock() {
        if (!D.colorAttr || !D.sizeAttr) return;
        var c = selColor.toLowerCase();
        $('#srSizes .sr-size-btn').each(function () {
            var s  = $(this).data('value').toLowerCase();
            var ok = D.lookup[c] && D.lookup[c][s] && D.lookup[c][s].in_stock;
            $(this).toggleClass('is-out', !ok).prop('disabled', !ok);
            if (!ok && $(this).hasClass('active')) {
                $(this).removeClass('active');
                selSize = '';
            }
        });
    }

    /* Update displayed price for resolved variation */
    function refreshPrice() {
        if (!resolvedId) return;
        var v = D.allVars.find(function (x) { return x.variation_id === resolvedId; });
        if (v && v.price_html) $('#srPrice').html(v.price_html);
    }

    /* ── Colour click ───────────────────────────────────────── */
    $('#srColorSwatches').on('click', '.sr-swatch', function () {
        $('#srColorSwatches .sr-swatch').removeClass('active');
        $(this).addClass('active');
        selColor = $(this).data('value');
        $('#srColorVal').text($(this).data('label'));
        /* Sync hidden select */
        if (D.colorAttr) {
            $('[data-attribute_name="' + D.colorAttr + '"]').val(selColor).trigger('change');
        }
        refreshSizeStock();
        resolve();
        refreshPrice();
    });

    /* ── Size click ─────────────────────────────────────────── */
    $('#srSizes').on('click', '.sr-size-btn', function () {
        if ($(this).hasClass('is-out') || $(this).prop('disabled')) return;
        $('#srSizes .sr-size-btn').removeClass('active');
        $(this).addClass('active');
        selSize = $(this).data('value');
        $('#srSizeErr').attr('hidden', true);
        /* Sync hidden select */
        if (D.sizeAttr) {
            $('[data-attribute_name="' + D.sizeAttr + '"]').val(selSize).trigger('change');
        }
        resolve();
        refreshPrice();
    });

    /* ── Add to Cart ────────────────────────────────────────── */
    $('#srAtc').on('click', function () {
        var $btn = $(this);

        /* Validate: need size if size attr exists */
        if (D.sizeAttr && !selSize) {
            $('#srSizeErr').removeAttr('hidden');
            var $sz = $('#srSizes');
            $sz.css('animation', 'none');
            $sz[0].offsetWidth;
            $sz.css('animation', 'sr-shake .35s ease');
            return;
        }

        /* Validate: must have resolved variation */
        if (!resolvedId) {
            $btn.find('.sr-atc__label').text('Select options first');
            setTimeout(function () { $btn.find('.sr-atc__label').text('Add to Cart'); }, 1800);
            return;
        }

        /* Set state */
        $btn.addClass('is-loading').prop('disabled', true);
        $btn.find('.sr-atc__label').text('Adding…');

        /* ── Direct WC AJAX add to cart ──────────────────── */
        var postData = {
            action:       'woocommerce_add_variation_to_cart',
            product_id:   D.pid,
            variation_id: resolvedId,
            quantity:     1,
            security:     D.nonce
        };
        if (D.colorAttr) postData[D.colorAttr] = selColor;
        if (D.sizeAttr)  postData[D.sizeAttr]  = selSize;

        $.post(wc_add_to_cart_params ? wc_add_to_cart_params.ajax_url : D.ajaxUrl, postData)
            .done(function (res) {
                if (res && res.error) {
                    /* WC returned an error message */
                    $btn.removeClass('is-loading').prop('disabled', false);
                    $btn.find('.sr-atc__label').text('Add to Cart');
                    alert(res.error);
                    return;
                }

                /* Success — refresh WC fragments (mini-cart etc.) */
                if (res.fragments) {
                    $.each(res.fragments, function (key, val) { $(key).replaceWith(val); });
                }
                $(document.body).trigger('added_to_cart', [res.fragments, res.cart_hash]);

                $btn.removeClass('is-loading').addClass('is-done').prop('disabled', false);
                $btn.find('.sr-atc__label').text('Added ✓');
                setTimeout(function () {
                    $btn.removeClass('is-done');
                    $btn.find('.sr-atc__label').text('Add to Cart');
                }, 2500);
            })
            .fail(function () {
                /* Network/server error — fallback: standard form POST */
                var $f = $('<form method="post" action="' + window.location.href + '">' +
                    '<input name="add-to-cart"  value="' + D.pid + '">' +
                    '<input name="variation_id" value="' + resolvedId + '">' +
                    '<input name="quantity"      value="1">' +
                    (D.colorAttr ? '<input name="' + D.colorAttr + '" value="' + selColor + '">' : '') +
                    (D.sizeAttr  ? '<input name="' + D.sizeAttr  + '" value="' + selSize  + '">' : '') +
                    '</form>');
                $('body').append($f);
                $f.submit();
            });
    });

    /* ── WC wc_add_to_cart_params fallback ──────────────────── */
    /* If wc_add_to_cart_params is not defined, create a minimal shim */
    if (typeof wc_add_to_cart_params === 'undefined') {
        window.wc_add_to_cart_params = { ajax_url: D.ajaxUrl };
    }
});
</script>

<?php
endwhile;
get_footer();
?>