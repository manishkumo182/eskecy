<?php
/**
 * Extends how long password-reset / "set your password" account links stay
 * valid. WordPress core defaults this to 24 hours (DAY_IN_SECONDS) for both
 * the "lost password" flow and the "new account" welcome email link.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_filter( 'password_reset_expiration', 'stanray_password_reset_expiration' );
function stanray_password_reset_expiration() {
    return 3 * DAY_IN_SECONDS;
}
