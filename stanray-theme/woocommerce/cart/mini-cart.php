<?php
defined('ABSPATH') || exit;

do_action('woocommerce_before_mini_cart');
?>

<div class="custom-mini-cart">

    <div class="cart-header">
        <span>Cart (<?php echo WC()->cart->get_cart_contents_count(); ?>)</span>
    </div>

    <div class="cart-body">
        <?php if ( WC()->cart && ! WC()->cart->is_empty() ) : ?>

            <?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :

                $_product = $cart_item['data'];

                if ( ! $_product || ! $_product->exists() ) {
                    continue;
                }
            ?>

           <div class="cart-item" data-key="<?php echo $cart_item_key; ?>">
                 <a href="<?php echo wc_get_cart_remove_url($cart_item_key); ?>" 
       class="remove-item"
       aria-label="Remove product">
       
       <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
<path d="M3 6h18" stroke="black" stroke-width="2"/>
<path d="M8 6V4h8v2" stroke="black" stroke-width="2"/>
<path d="M19 6l-1 14H6L5 6" stroke="black" stroke-width="2"/>
</svg>
    </a>
    <div class="cart-img">
        <?php echo $_product->get_image(); ?>
    </div>

    <div class="cart-info">
        <h4><?php echo $_product->get_name(); ?></h4>
        <span class="price"><?php echo wc_price($_product->get_price()); ?></span>

        <!-- Quantity -->
        <div class="qty-box">
            <button class="qty-minus">-</button>

            <input type="number" 
                   class="qty-input" 
                   value="<?php echo $cart_item['quantity']; ?>" 
                   min="1">

            <button class="qty-plus">+</button>
        </div>

    </div>
    

</div>

            <?php endforeach; ?>

        <?php else : ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </div>

    <div class="cart-footer">
        <strong>Subtotal:</strong>
        <?php wc_cart_totals_subtotal_html(); ?>

        <br><br>

        <a href="<?php echo wc_get_checkout_url(); ?>" class="checkout-btn">
            Checkout
        </a>
    </div>

</div>

<?php do_action('woocommerce_after_mini_cart'); ?>