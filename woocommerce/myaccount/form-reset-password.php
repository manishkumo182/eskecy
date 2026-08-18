<?php
/**
 * Set-new-password form (reached via the emailed reset link) — styled to
 * match form-login.php's auth card, with the same show/hide password toggle.
 *
 * FILE LOCATION: /woocommerce/myaccount/form-reset-password.php
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_reset_password_form' );
?>

<div class="stanray-auth stanray-auth--single">
    <div class="stanray-auth__panel">
        <div class="stanray-auth__panel-inner">

            <h2 class="stanray-auth__title"><?php esc_html_e( 'Set a New Password', 'woocommerce' ); ?></h2>
            <p class="stanray-auth__sub"><?php echo apply_filters( 'woocommerce_reset_password_message', esc_html__( 'Enter a new password below.', 'woocommerce' ) ); ?></p>

            <form method="post" class="woocommerce-ResetPassword lost_reset_password stanray-form">

                <div class="stanray-field">
                    <label for="password_1"><?php esc_html_e( 'New password', 'woocommerce' ); ?> <span class="required">*</span></label>
                    <?php // "password-input" tells WC's own password-strength-meter.js to insert the
                    // strength/hint text after this wrapper instead of inside it — otherwise the
                    // absolutely-positioned show/hide icon re-centers on the now-taller wrapper. ?>
                    <div class="stanray-input-wrap password-input">
                        <input
                            type="password"
                            class="stanray-input"
                            name="password_1"
                            id="password_1"
                            autocomplete="new-password"
                            placeholder="min. 14 characters"
                            required
                            aria-required="true"
                        >
                        <button type="button" class="stanray-toggle-password" data-target="password_1" aria-label="<?php esc_attr_e( 'Show password', 'woocommerce' ); ?>" aria-pressed="false">
                            <svg class="stanray-toggle-password__icon-eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="stanray-toggle-password__icon-eye-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden>
                                <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a20.4 20.4 0 0 1 5.06-6.06M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a20.4 20.4 0 0 1-3.29 4.55M14.12 14.12a3 3 0 1 1-4.24-4.24"></path>
                                <path d="M1 1l22 22"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="stanray-field">
                    <label for="password_2"><?php esc_html_e( 'Re-enter new password', 'woocommerce' ); ?> <span class="required">*</span></label>
                    <div class="stanray-input-wrap password-input">
                        <input
                            type="password"
                            class="stanray-input"
                            name="password_2"
                            id="password_2"
                            autocomplete="new-password"
                            placeholder="min. 14 characters"
                            required
                            aria-required="true"
                        >
                        <button type="button" class="stanray-toggle-password" data-target="password_2" aria-label="<?php esc_attr_e( 'Show password', 'woocommerce' ); ?>" aria-pressed="false">
                            <svg class="stanray-toggle-password__icon-eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="stanray-toggle-password__icon-eye-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden>
                                <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a20.4 20.4 0 0 1 5.06-6.06M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a20.4 20.4 0 0 1-3.29 4.55M14.12 14.12a3 3 0 1 1-4.24-4.24"></path>
                                <path d="M1 1l22 22"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <input type="hidden" name="reset_key" value="<?php echo esc_attr( $args['key'] ); ?>">
                <input type="hidden" name="reset_login" value="<?php echo esc_attr( $args['login'] ); ?>">

                <?php do_action( 'woocommerce_resetpassword_form' ); ?>

                <input type="hidden" name="wc_reset_password" value="true">
                <?php wp_nonce_field( 'reset_password', 'woocommerce-reset-password-nonce' ); ?>

                <button type="submit" class="stanray-btn stanray-btn--full">
                    <?php esc_html_e( 'Save New Password', 'woocommerce' ); ?>
                </button>

            </form>
        </div>
    </div>
</div>

<?php do_action( 'woocommerce_after_reset_password_form' ); ?>
