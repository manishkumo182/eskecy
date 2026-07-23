<?php
/**
 * Lost password form — styled to match form-login.php's auth card.
 *
 * FILE LOCATION: /woocommerce/myaccount/form-lost-password.php
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_lost_password_form' );
?>

<div class="stanray-auth stanray-auth--single">
    <div class="stanray-auth__panel">
        <div class="stanray-auth__panel-inner">

            <h2 class="stanray-auth__title"><?php esc_html_e( 'Reset Password', 'woocommerce' ); ?></h2>
            <p class="stanray-auth__sub"><?php echo apply_filters( 'woocommerce_lost_password_message', esc_html__( 'Enter your username or email address and we\'ll send you a link to create a new password.', 'woocommerce' ) ); ?></p>

            <form method="post" class="woocommerce-ResetPassword lost_reset_password stanray-form">

                <div class="stanray-field">
                    <label for="user_login"><?php esc_html_e( 'Username or email', 'woocommerce' ); ?> <span class="required">*</span></label>
                    <input
                        class="stanray-input"
                        type="text"
                        name="user_login"
                        id="user_login"
                        autocomplete="username"
                        placeholder="your@email.com"
                        required
                        aria-required="true"
                    >
                </div>

                <?php do_action( 'woocommerce_lostpassword_form' ); ?>

                <input type="hidden" name="wc_reset_password" value="true">
                <?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>

                <button type="submit" class="stanray-btn stanray-btn--full">
                    <?php esc_html_e( 'Reset', 'woocommerce' ); ?>
                </button>

                <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="stanray-auth__back">
                    &larr; <?php esc_html_e( 'Back to Sign In', 'woocommerce' ); ?>
                </a>

            </form>
        </div>
    </div>
</div>

<?php do_action( 'woocommerce_after_lost_password_form' ); ?>
