<?php
/* Template Name: Explore Page */
get_header();

// Entrance image is editable from wp-admin: Homepage Sections → Explore Page.
// Falls back to the theme's bundled default when no image has been chosen yet.
$entrance_image_id = absint( get_option( 'stanray_explore_entrance_image', 0 ) );
$entrance_image    = $entrance_image_id ? wp_get_attachment_image_url( $entrance_image_id, 'large' ) : '';
if ( ! $entrance_image ) {
    $entrance_image = get_template_directory_uri() . '/assets/images/explore-entrance.jpg';
}

// The studio room lives on its own page — the door leads there.
$studio_page = get_page_by_path( 'studio' );
$studio_url  = $studio_page ? get_permalink( $studio_page ) : home_url( '/studio/' );
?>

<div class="explore" id="explore-root">

    <section class="explore__scene explore__scene--entrance" id="explore-entrance" aria-label="Explore — entrance">

        <img src="<?php echo esc_url( $entrance_image ); ?>" alt="Eskecy — dusk entrance" class="explore__bg">
        <div class="explore__vignette" aria-hidden="true"></div>

        <div class="explore__badge" aria-hidden="true">
            <span class="explore__badge-dot"></span>
            EX_ESKECY_V1.0
        </div>

        <a href="<?php echo esc_url( $studio_url ); ?>" class="explore__door" id="explore-door" aria-label="Enter the studio">
            <span class="explore__door-ring" aria-hidden="true"></span>
        </a>

        <a href="<?php echo esc_url( $studio_url ); ?>" class="explore__prompt" id="explore-enter" aria-label="Enter the studio">
            <span class="explore__prompt-label">Enter Studio</span>
            <span class="explore__prompt-arrow" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" class="explore__prompt-chevron" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 19V4M6 10l6-6 6 6"/>
                </svg>
            </span>
        </a>

    </section>

</div>

<?php get_footer(); ?>
