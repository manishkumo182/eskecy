<?php
/**
 * Account-required login modal.
 *
 * Reused in two places that both now require an account:
 *  - Wishlist: the heart icon is visible to everyone, but saving is
 *    account-only (see eskecy_toggle_wishlist_handler in inc/woocommerce.php).
 *    JS opens this modal on click.
 *  - Checkout: guest checkout is disabled (woocommerce_enable_guest_checkout
 *    = no). This modal auto-opens on page load instead of showing WooCommerce's
 *    default dead-end "you must be logged in" text.
 *
 * Both reuse the theme's existing Login/Register template
 * (woocommerce/myaccount/form-login.php) verbatim — same fields, same
 * WooCommerce processing, same notices — just wrapped in a dialog instead of
 * living on the My Account page. After a successful login/register,
 * WooCommerce's own redirect-to-referer behavior lands the user back on
 * whichever page (wishlist trigger or checkout) they started from.
 *
 * Skipped on the My Account page itself, since that page already renders
 * the same template inline; rendering it twice would duplicate element IDs.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_footer', 'stanray_render_wishlist_login_modal' );
function stanray_render_wishlist_login_modal() {
    if ( is_admin() || is_user_logged_in() || is_account_page() ) return;

    $on_checkout = function_exists( 'is_checkout' ) && is_checkout();
    $intro       = $on_checkout
        ? 'Log in or create an account to complete your order.'
        : 'Log in or create an account to save items to your wishlist.';
    ?>
    <div class="wishlist-login-modal<?php echo $on_checkout ? ' wishlist-login-modal--autoopen' : ''; ?>" id="wishlist-login-modal" role="dialog" aria-modal="true" aria-label="Log in to continue">
        <div class="wishlist-login-modal__overlay" id="wishlist-login-overlay"></div>
        <div class="wishlist-login-modal__box">
            <button type="button" class="wishlist-login-modal__close" id="wishlist-login-close" aria-label="Close">&times;</button>
            <p class="wishlist-login-modal__intro"><?php echo esc_html( $intro ); ?></p>
            <?php wc_print_notices(); ?>
            <?php wc_get_template( 'myaccount/form-login.php' ); ?>
        </div>
    </div>
    <?php
}

// Guest checkout is disabled, so WooCommerce's own form-checkout.php template
// bails out early with a plain-text "you must be logged in" message. Swap it
// for a link that reopens our modal, so a guest who dismisses the auto-opened
// modal isn't left looking at a dead end.
add_filter( 'woocommerce_checkout_must_be_logged_in_message', function() {
    return 'Please <a href="#" class="wishlist-login-trigger">log in or create an account</a> to complete your order.';
} );
