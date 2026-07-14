<?php
/**
 * PART 4 — Custom Edit Account / Profile Page
 *
 * FILE LOCATION: /woocommerce/myaccount/form-edit-account.php
 * Overrides WooCommerce default edit account form.
 * Includes custom Phone field saved to billing meta.
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_edit_account_form' );

$user         = wp_get_current_user();
$phone        = get_user_meta( $user->ID, 'billing_phone', true );
?>

<div class="stanray-account-page">

    <div class="stanray-page-header">
        <h2 class="stanray-page-title">Edit Profile</h2>
    </div>

    <form class="woocommerce-EditAccountForm stanray-form stanray-account-form" action="" method="post"
        <?php do_action( 'woocommerce_edit_account_form_tag' ); ?>>

        <?php do_action( 'woocommerce_edit_account_form_start' ); ?>

        <!-- ── Personal Info ─────────────────────────────── -->
        <div class="stanray-form-section">
            <h3 class="stanray-form-section__title">Personal Information</h3>

            <div class="stanray-field-row">
                <div class="stanray-field">
                    <label for="account_first_name">First Name <span class="required">*</span></label>
                    <input
                        type="text"
                        class="stanray-input"
                        name="account_first_name"
                        id="account_first_name"
                        autocomplete="given-name"
                        value="<?php echo esc_attr( $user->first_name ); ?>"
                    >
                </div>
                <div class="stanray-field">
                    <label for="account_last_name">Last Name <span class="required">*</span></label>
                    <input
                        type="text"
                        class="stanray-input"
                        name="account_last_name"
                        id="account_last_name"
                        autocomplete="family-name"
                        value="<?php echo esc_attr( $user->last_name ); ?>"
                    >
                </div>
            </div>

            <div class="stanray-field">
                <label for="account_display_name">Display Name <span class="required">*</span></label>
                <input
                    type="text"
                    class="stanray-input"
                    name="account_display_name"
                    id="account_display_name"
                    value="<?php echo esc_attr( $user->display_name ); ?>"
                >
                <span class="stanray-field__hint">Shown publicly if you leave a review.</span>
            </div>

            <div class="stanray-field">
                <label for="account_email">Email Address <span class="required">*</span></label>
                <input
                    type="email"
                    class="stanray-input"
                    name="account_email"
                    id="account_email"
                    autocomplete="email"
                    value="<?php echo esc_attr( $user->user_email ); ?>"
                >
            </div>

            <!-- Custom: Phone -->
            <div class="stanray-field">
                <label for="account_phone">Phone Number</label>
                <input
                    type="tel"
                    class="stanray-input"
                    name="billing_phone"
                    id="account_phone"
                    autocomplete="tel"
                    value="<?php echo esc_attr( $phone ); ?>"
                    placeholder="+1 234 567 8900"
                >
            </div>
        </div>

        <!-- ── Change Password ───────────────────────────── -->
        <div class="stanray-form-section">
            <h3 class="stanray-form-section__title">Change Password</h3>
            <p class="stanray-form-section__note">Leave blank to keep your current password.</p>

            <div class="stanray-field">
                <label for="password_current">Current Password</label>
                <input
                    type="password"
                    class="stanray-input"
                    name="password_current"
                    id="password_current"
                    autocomplete="off"
                    placeholder="••••••••"
                >
            </div>

            <div class="stanray-field-row">
                <div class="stanray-field">
                    <label for="password_1">New Password</label>
                    <input
                        type="password"
                        class="stanray-input"
                        name="password_1"
                        id="password_1"
                        autocomplete="new-password"
                        placeholder="••••••••"
                    >
                </div>
                <div class="stanray-field">
                    <label for="password_2">Confirm New Password</label>
                    <input
                        type="password"
                        class="stanray-input"
                        name="password_2"
                        id="password_2"
                        autocomplete="new-password"
                        placeholder="••••••••"
                    >
                </div>
            </div>
        </div>

        <?php do_action( 'woocommerce_edit_account_form' ); ?>

        <div class="stanray-form-actions">
            <?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
            <input type="hidden" name="action" value="save_account_details">
            <button type="submit" class="stanray-btn" name="save_account_details" value="Save changes">
                Save Changes
            </button>
            <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="stanray-btn stanray-btn--ghost">
                Cancel
            </a>
        </div>

        <?php do_action( 'woocommerce_edit_account_form_end' ); ?>

    </form>
</div>

<?php do_action( 'woocommerce_after_edit_account_form' ); ?>