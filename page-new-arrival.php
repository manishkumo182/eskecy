<?php
/*
Template Name: New Arrival
*/
get_header();
?>

<section class="new-arrival-page">
    <div class="container">

        <?php
        // ✅ Get sorting value from URL
        $orderby = isset($_GET['orderby']) ? wc_clean($_GET['orderby']) : 'date';

        // ✅ Map WooCommerce orderby to WP_Query
        switch ($orderby) {
            case 'price':
                $meta_key = '_price';
                $order = 'ASC';
                $orderby_query = 'meta_value_num';
                break;

            case 'price-desc':
                $meta_key = '_price';
                $order = 'DESC';
                $orderby_query = 'meta_value_num';
                break;

            case 'title':
                $meta_key = '';
                $order = 'ASC';
                $orderby_query = 'title';
                break;

            case 'title-desc':
                $meta_key = '';
                $order = 'DESC';
                $orderby_query = 'title';
                break;

            default:
                $meta_key = '';
                $order = 'DESC';
                $orderby_query = 'date';
        }

        // ✅ Pagination
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

        // ✅ Query
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => 9,
            'paged'          => $paged,
            'orderby'        => $orderby_query,
            'order'          => $order,
        );

        if (!empty($meta_key)) {
            $args['meta_key'] = $meta_key;
        }

        $loop = new WP_Query($args);
        ?>

        <!-- ✅ HEADER -->
        <div class="shop-header">
       
       
        </div>

        <!-- ✅ PRODUCT GRID -->
        <div class="product-grid" id="new-arrival-grid">
            <?php
            if ($loop->have_posts()) {
                while ($loop->have_posts()) : $loop->the_post();

                    $product = wc_get_product(get_the_ID());
                    if (!$product) continue;
                    ?>

                    <?php
                    $in_wish = in_array( get_the_ID(), eskecy_get_wishlist() );
                    ?>
                    <div class="product-card">
                        <a href="<?php the_permalink(); ?>">

                            <?php echo woocommerce_get_product_thumbnail('medium'); ?>

                            <h3 class="product-card__title"><?php the_title(); ?></h3>

                            <span class="price">
                                <?php echo $product->get_price_html(); ?>
                            </span>

                        </a>

                        <button class="product-card__wish<?php echo $in_wish ? ' is-wished' : ''; ?>"
                            data-product-id="<?php echo esc_attr( get_the_ID() ); ?>"
                            data-nonce="<?php echo esc_attr( wp_create_nonce('eskecy_wishlist') ); ?>"
                            aria-label="<?php echo $in_wish ? 'Remove from Wishlist' : 'Save to Wishlist'; ?>"
                            title="<?php echo $in_wish ? 'Remove from Wishlist' : 'Save to Wishlist'; ?>"
                        ><?php echo $in_wish ? '&#x2665;' : '&#x2661;'; ?></button>

                        <!-- ✅ Add to cart -->
                        <div class="add-to-cart-btn">
                            <?php woocommerce_template_loop_add_to_cart(); ?>
                        </div>

                        <?php
                        // ✅ Variants
                        if ($product->is_type('variable')) {

                            $attributes = $product->get_attributes();

                            echo '<div class="product-variants">';

                            // COLOR
                            foreach ($attributes as $key => $attr) {
                                if (strpos(strtolower($key), 'color') !== false) {
                                    $terms = wc_get_product_terms($product->get_id(), $key);

                                    echo '<div class="variant-color">';
                                    foreach ($terms as $term) {
                                        $hex = get_term_meta($term->term_id, 'color', true);

                                        echo '<span class="color-item"
                                                title="'.esc_attr($term->name).'"
                                                style="background:'.esc_attr($hex ?: '#ccc').'">
                                              </span>';
                                    }
                                    echo '</div>';
                                }
                            }

                            // SIZE
                            foreach ($attributes as $key => $attr) {
                                if (strpos(strtolower($key), 'size') !== false) {
                                    $terms = wc_get_product_terms($product->get_id(), $key);

                                    echo '<div class="variant-size">';
                                    foreach ($terms as $term) {
                                        echo '<span class="size-item">'.$term->name.'</span>';
                                    }
                                    echo '</div>';
                                }
                            }

                            echo '</div>';
                        }
                        ?>
                    </div>

                    <?php
                endwhile;
            } else {
                echo '<p>No products found</p>';
            }
            ?>
        </div>

        <?php if ( $loop->max_num_pages > 1 ) : ?>
        <div class="pagination new-arrival-page__pagination" data-scroll-anchor="new-arrival-grid">
            <?php
            echo paginate_links([
                'base'      => add_query_arg( 'paged', '%#%' ),
                'format'    => '',
                'current'   => $paged,
                'total'     => $loop->max_num_pages,
                'prev_text' => '←',
                'next_text' => '→',
            ]);
            ?>
        </div>
        <?php endif; ?>

        <?php wp_reset_postdata(); ?>

    </div>
</section>

<?php get_footer(); ?>