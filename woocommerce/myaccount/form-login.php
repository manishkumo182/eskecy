<?php
/**
 * PART 1 — Custom Login & Register Form
 *
 * FILE LOCATION: /woocommerce/myaccount/form-login.php
 * (inside your theme folder, this overrides WooCommerce default)
 *
 * Custom fields added to registration:
 *  - First Name, Last Name (required)
 *  - Phone Number (optional)
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_customer_login_form' );
?>

<?php $stanray_auth_active_tab = function_exists( 'stanray_auth_active_tab' ) ? stanray_auth_active_tab() : 'login'; ?>
<div class="stanray-auth">

    <!-- ── Left: Login ─────────────────────────────────────── -->
    <div class="stanray-auth__panel stanray-auth__panel--login<?php echo 'login' === $stanray_auth_active_tab ? ' is-active-tab' : ''; ?>" data-tab-panel="login">
        <div class="stanray-auth__panel-inner">

            <h2 class="stanray-auth__title">Sign In</h2>
            <p class="stanray-auth__sub">Welcome back</p>

            <form class="woocommerce-form woocommerce-form-login stanray-form" method="post">

                <?php do_action( 'woocommerce_login_form_start' ); ?>

                <div class="stanray-field">
                    <label for="username"><?php esc_html_e( 'Email Address', 'woocommerce' ); ?></label>
                    <input
                        type="text"
                        class="stanray-input"
                        name="username"
                        id="username"
                        autocomplete="username"
                        value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>"
                        placeholder="your@email.com"
                    >
                </div>

                <div class="stanray-field">
                    <label for="password"><?php esc_html_e( 'Password', 'woocommerce' ); ?></label>
                    <div class="stanray-input-wrap">
                        <input
                            type="password"
                            class="stanray-input"
                            name="password"
                            id="password"
                            autocomplete="current-password"
                            placeholder="••••••••"
                        >
                        <button type="button" class="stanray-toggle-password" data-target="password" aria-label="<?php esc_attr_e( 'Show password', 'woocommerce' ); ?>" aria-pressed="false">
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

                <div class="stanray-field stanray-field--row">
                    <label class="stanray-checkbox">
                        <input name="rememberme" type="checkbox" id="rememberme" value="forever">
                        <span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
                    </label>
                    <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="stanray-auth__forgot">
                        <?php esc_html_e( 'Forgot password?', 'woocommerce' ); ?>
                    </a>
                </div>

                <?php do_action( 'woocommerce_login_form' ); ?>

                <input type="hidden" name="redirect" value="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">
                <?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>

                <button type="submit" class="stanray-btn stanray-btn--full" name="login" value="<?php esc_attr_e( 'Sign in', 'woocommerce' ); ?>">
                    <?php esc_html_e( 'Sign In', 'woocommerce' ); ?>
                </button>

                <?php do_action( 'woocommerce_login_form_end' ); ?>

            </form>
        </div>
    </div>

    <!-- ── Divider ─────────────────────────────────────────── -->
    <div class="stanray-auth__divider"><span>or</span></div>

    <!-- ── Right: Register ─────────────────────────────────── -->
    <?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>
    <div class="stanray-auth__panel stanray-auth__panel--register<?php echo 'register' === $stanray_auth_active_tab ? ' is-active-tab' : ''; ?>" data-tab-panel="register">
        <div class="stanray-auth__panel-inner">

            <h2 class="stanray-auth__title">Create Account</h2>
            <p class="stanray-auth__sub">Join us</p>

            <form method="post" class="woocommerce-form woocommerce-form-register stanray-form">

                <?php do_action( 'woocommerce_register_form_start' ); ?>

                <!-- First + Last Name row -->
                <div class="stanray-field-row">
                    <div class="stanray-field">
                        <label for="reg_first_name">First Name <span class="required">*</span></label>
                        <input
                            type="text"
                            class="stanray-input"
                            name="billing_first_name"
                            id="reg_first_name"
                            value="<?php echo ( ! empty( $_POST['billing_first_name'] ) ) ? esc_attr( wp_unslash( $_POST['billing_first_name'] ) ) : ''; ?>"
                            placeholder="First"
                            required
                        >
                    </div>
                    <div class="stanray-field">
                        <label for="reg_last_name">Last Name <span class="required">*</span></label>
                        <input
                            type="text"
                            class="stanray-input"
                            name="billing_last_name"
                            id="reg_last_name"
                            value="<?php echo ( ! empty( $_POST['billing_last_name'] ) ) ? esc_attr( wp_unslash( $_POST['billing_last_name'] ) ) : ''; ?>"
                            placeholder="Last"
                            required
                        >
                    </div>
                </div>

                <?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
                <div class="stanray-field">
                    <label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?> <span class="required">*</span></label>
                    <input
                        type="text"
                        class="stanray-input"
                        name="username"
                        id="reg_username"
                        value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>"
                        placeholder="username"
                    >
                </div>
                <?php endif; ?>

                <div class="stanray-field">
                    <label for="reg_email"><?php esc_html_e( 'Email Address', 'woocommerce' ); ?> <span class="required">*</span></label>
                    <input
                        type="email"
                        class="stanray-input"
                        name="email"
                        id="reg_email"
                        autocomplete="email"
                        value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>"
                        placeholder="your@email.com"
                    >
                </div>

                <!-- Custom field: Phone -->
                <div class="stanray-field">
                    <label for="reg_phone">Phone Number <span class="stanray-optional">(optional)</span></label>
                    <input
                        type="tel"
                        class="stanray-input"
                        name="billing_phone"
                        id="reg_phone"
                        value="<?php echo ( ! empty( $_POST['billing_phone'] ) ) ? esc_attr( wp_unslash( $_POST['billing_phone'] ) ) : ''; ?>"
                        placeholder="+1 234 567 8900"
                    >
                </div>

                <?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
                <div class="stanray-field">
                    <label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?> <span class="required">*</span></label>
                    <input
                        type="password"
                        class="stanray-input"
                        name="password"
                        id="reg_password"
                        autocomplete="new-password"
                        placeholder="min. 8 characters"
                    >
                </div>
                <?php else : ?>
                    <p class="stanray-auth__note">A password will be sent to your email address.</p>
                <?php endif; ?>

                <?php do_action( 'woocommerce_register_form' ); ?>

                <?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>

                <button type="submit" class="stanray-btn stanray-btn--full stanray-btn--outline" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>">
                    <?php esc_html_e( 'Create Account', 'woocommerce' ); ?>
                </button>

                <?php do_action( 'woocommerce_register_form_end' ); ?>

            </form>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>