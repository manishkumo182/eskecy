<?php
defined( 'ABSPATH' ) || exit;
?>

<?php wc_print_notices(); ?>
<?php do_action( 'woocommerce_before_my_account' ); ?>

<?php if ( is_user_logged_in() ) : ?>

<div class="stanray-account-layout">

    <aside class="stanray-account-nav">
        <p class="stanray-account-nav__heading">My Account</p>
        <?php do_action( 'woocommerce_account_navigation' ); ?>
    </aside>

    <div class="stanray-account-content">
        <?php do_action( 'woocommerce_account_content' ); ?>
    </div>

</div>

<?php else : ?>

<?php do_action( 'woocommerce_account_content' ); ?>

<?php endif; ?>

<?php do_action( 'woocommerce_after_my_account' ); ?>
