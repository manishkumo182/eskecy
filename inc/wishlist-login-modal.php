<?php
/**
 * Account-required login modal.
 *
 * Reused in two places:
 *  - Wishlist: the heart icon is visible to everyone, but saving is
 *    account-only (see eskecy_toggle_wishlist_handler in inc/woocommerce.php).
 *    JS opens this modal on click.
 *  - Checkout: only when WooCommerce's own settings actually require an
 *    account (woocommerce_enable_guest_checkout = no AND
 *    woocommerce_enable_signup_and_login_from_checkout = no — the same test
 *    core's form-checkout.php uses). When guest checkout is on, guests must
 *    NOT be interrupted here — this modal doesn't render on checkout at all
 *    in that case, so they land straight on the billing form and can buy
 *    without an account. When it does render, it auto-opens on page load
 *    instead of showing WooCommerce's default dead-end "you must be logged
 *    in" text.
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

/**
 * Which tab (login/register) the modal — and form-login.php's panels, via
 * the matching check there — should show as active. Defaults to login;
 * flips to register only when a register submission on this same request
 * failed and redisplayed the form (WooCommerce doesn't redirect on
 * registration errors, so $_POST['register'] is still set at that point).
 */
function stanray_auth_active_tab() {
    return ! empty( $_POST['register'] ) ? 'register' : 'login';
}

add_action( 'wp_footer', 'stanray_render_wishlist_login_modal' );
function stanray_render_wishlist_login_modal() {
    if ( is_admin() || is_user_logged_in() || is_account_page() ) return;

    $on_checkout = function_exists( 'is_checkout' ) && is_checkout();

    // Same check core's checkout/form-checkout.php uses: an account is only
    // actually required when BOTH guest checkout and checkout registration
    // are turned off. Guest checkout is on by default on this store, so this
    // is normally false and the modal simply doesn't render on checkout —
    // guests go straight to the billing form.
    $checkout_requires_login = $on_checkout && function_exists( 'WC' ) && WC()->checkout()
        ? ( ! WC()->checkout()->is_registration_enabled() && WC()->checkout()->is_registration_required() )
        : false;

    if ( $on_checkout && ! $checkout_requires_login ) return;

    $intro         = $on_checkout
        ? 'Log in or create an account to complete your order.'
        : 'Log in or create an account to save items to your wishlist.';
    $active_tab    = stanray_auth_active_tab();
    $show_register = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );
    ?>
    <div class="wishlist-login-modal<?php echo $checkout_requires_login ? ' wishlist-login-modal--autoopen' : ''; ?>" id="wishlist-login-modal" role="dialog" aria-modal="true" aria-label="Log in to continue">
        <div class="wishlist-login-modal__overlay" id="wishlist-login-overlay"></div>
        <div class="wishlist-login-modal__box">
            <button type="button" class="wishlist-login-modal__close" id="wishlist-login-close" aria-label="Close">&times;</button>
            <p class="wishlist-login-modal__intro"><?php echo esc_html( $intro ); ?></p>
            <?php if ( $show_register ) : ?>
            <div class="wishlist-login-modal__tabs" role="tablist">
                <button type="button" class="wishlist-login-modal__tab<?php echo 'login' === $active_tab ? ' is-active' : ''; ?>" data-tab="login" role="tab" aria-selected="<?php echo 'login' === $active_tab ? 'true' : 'false'; ?>"><?php esc_html_e( 'Sign In', 'stanray-custom' ); ?></button>
                <button type="button" class="wishlist-login-modal__tab<?php echo 'register' === $active_tab ? ' is-active' : ''; ?>" data-tab="register" role="tab" aria-selected="<?php echo 'register' === $active_tab ? 'true' : 'false'; ?>"><?php esc_html_e( 'Create Account', 'stanray-custom' ); ?></button>
            </div>
            <?php endif; ?>
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
