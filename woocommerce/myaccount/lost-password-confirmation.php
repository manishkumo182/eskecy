<?php
/**
 * Lost password confirmation ("check your email") — styled to match the
 * auth card used by form-login.php / form-lost-password.php.
 *
 * FILE LOCATION: /woocommerce/myaccount/lost-password-confirmation.php
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="stanray-auth stanray-auth--single">
    <div class="stanray-auth__panel">
        <div class="stanray-auth__panel-inner stanray-auth__panel-inner--centered">

            <div class="stanray-auth__check" aria-hidden="true">
                <svg width="32" height="32" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="20" cy="20" r="19" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M12 20.5l5.5 5.5L28 14.5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <h2 class="stanray-auth__title"><?php esc_html_e( 'Check Your Email', 'woocommerce' ); ?></h2>
            <p class="stanray-auth__sub stanray-auth__sub--centered">
                <?php echo esc_html( apply_filters( 'woocommerce_lost_password_confirmation_message', esc_html__( 'A password reset link has been sent to the email address on file for your account. It may take a few minutes to arrive — please check your spam folder too.', 'woocommerce' ) ) ); ?>
            </p>

            <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="stanray-btn stanray-btn--outline stanray-btn--full">
                <?php esc_html_e( 'Back to Sign In', 'woocommerce' ); ?>
            </a>

        </div>
    </div>
</div>
