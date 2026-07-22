<?php
/**
 * Template Name: Eskecy Point (Coming Soon)
 */
get_header();
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
        <h1 class="coming-soon-page__title">Coming Soon</h1>
        <p class="coming-soon-page__note">
            We're putting the finishing touches on this. Check back soon.
        </p>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="coming-soon-page__link">&larr; Back to home</a>
    </div>
</main>

<?php get_footer(); ?>
