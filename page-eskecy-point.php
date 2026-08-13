<?php
/**
 * Template Name: Eskecy Point (Coming Soon)
 * Public "how it works" page for the Eskecy Points loyalty program. Falls
 * back to the original coming-soon message if the program is switched off
 * from wp-admin → Eskecy Points (inc/admin-points-settings.php).
 */

// Content branches on login state (CTA target differs) — same reasoning as
// page-wishlist.php: without this, server-level page caching would serve
// one visitor's CTA to everyone. Harmless no-op where no such cache is active.
do_action( 'litespeed_control_set_nocache', 'eskecy points page — CTA depends on login state' );
nocache_headers();

get_header();

$enabled = function_exists( 'stanray_points_enabled' ) && stanray_points_enabled();

if ( $enabled ) {
    $earn_divisor = (float) get_option( 'stanray_points_earn_divisor', 100 );
    $redeem_rate  = (float) get_option( 'stanray_points_redeem_rate', 0.5 );
    $min_redeem   = (int) get_option( 'stanray_points_min_redeem', 100 );

    $cta_url  = is_user_logged_in()
        ? wc_get_account_endpoint_url( 'eskecy-points' )
        : wc_get_account_endpoint_url( 'dashboard' );
    $cta_text = is_user_logged_in() ? __( 'View My Points', 'stanray-custom' ) : __( 'Log In / Sign Up', 'stanray-custom' );
}
?>

<main id="main" class="coming-soon-page" role="main">
    <div class="container coming-soon-page__inner">
        <div class="coming-soon-page__logo">
            <?php
            if ( has_custom_logo() ) {
                the_custom_logo();
            } else {
                echo '<span class="header-logo__text">' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
            }
            ?>
        </div>
        <span class="coming-soon-page__eyebrow">Eskecy Store</span>

        <?php if ( $enabled ) : ?>
            <h1 class="coming-soon-page__title"><?php esc_html_e( 'Eskecy Points', 'stanray-custom' ); ?></h1>
            <p class="coming-soon-page__note">
                <?php echo esc_html( sprintf(
                    /* translators: 1: amount spent per point, 2: value per point, 3: minimum points to redeem */
                    __( 'Earn 1 point for every %1$s you spend. Redeem your points at checkout for %2$s off per point, once you have at least %3$s points.', 'stanray-custom' ),
                    wp_strip_all_tags( wc_price( $earn_divisor ) ),
                    wp_strip_all_tags( wc_price( $redeem_rate ) ),
                    number_format_i18n( $min_redeem )
                ) ); ?>
            </p>
            <a href="<?php echo esc_url( $cta_url ); ?>" class="btn btn--primary coming-soon-page__cta"><?php echo esc_html( $cta_text ); ?></a>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="coming-soon-page__link">&larr; <?php esc_html_e( 'Back to home', 'stanray-custom' ); ?></a>
        <?php else : ?>
            <h1 class="coming-soon-page__title">Coming Soon</h1>
            <p class="coming-soon-page__note">
                We're putting the finishing touches on this. Check back soon.
            </p>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="coming-soon-page__link">&larr; Back to home</a>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
