<?php
/*
Template Name: About Page
*/
get_header();

// Editable from wp-admin → Homepage Sections → About Page. Falls back to the
// theme's default images/copy so the page never renders blank if unset.
$about_tag   = get_option( 'stanray_about_tag', 'RECAP' );
$about_title = get_option( 'stanray_about_title', 'ESKECY 2025–2026' );
$about_desc  = get_option( 'stanray_about_desc', 'Explore our seasonal editorial featuring the latest trends and styles.' );

$default_img = get_template_directory_uri() . '/assets/images/su.jpg';
$default_sm  = get_template_directory_uri() . '/assets/images/sh.jpg';

$img_big = wp_get_attachment_image_url( get_option( 'stanray_about_img_big', 0 ), 'large' ) ?: $default_img;
$img_1   = wp_get_attachment_image_url( get_option( 'stanray_about_img_1', 0 ), 'medium' ) ?: $default_img;
$img_2   = wp_get_attachment_image_url( get_option( 'stanray_about_img_2', 0 ), 'medium' ) ?: $default_sm;
$img_3   = wp_get_attachment_image_url( get_option( 'stanray_about_img_3', 0 ), 'medium' ) ?: $default_sm;
$img_4   = wp_get_attachment_image_url( get_option( 'stanray_about_img_4', 0 ), 'medium' ) ?: $default_sm;
?>

<main id="main" role="main">
    <section class="about-editorial">
        <div class="container">

            <!-- TOP HEADER -->
            <div class="editorial-header">
                <div class="editorial-left">
                    <span class="tag"><?php echo esc_html( $about_tag ); ?></span>
                    <h1><?php echo esc_html( $about_title ); ?></h1>
                </div>

                <div class="editorial-right">
                    <p>
                        <?php echo esc_html( $about_desc ); ?>
                    </p>
                </div>
            </div>

            <!-- IMAGE GRID -->
            <div class="editorial-grid">

                <!-- BIG IMAGE -->
                <div class="grid-big">
                    <img src="<?php echo esc_url( $img_big ); ?>" alt="<?php echo esc_attr( $about_title ); ?>">
                </div>

                <!-- SMALL GRID -->
                <div class="grid-small">
                    <img src="<?php echo esc_url( $img_1 ); ?>" alt="<?php echo esc_attr( $about_title ); ?>">
                    <img src="<?php echo esc_url( $img_2 ); ?>" alt="<?php echo esc_attr( $about_title ); ?>">
                    <img src="<?php echo esc_url( $img_3 ); ?>" alt="<?php echo esc_attr( $about_title ); ?>">
                    <img src="<?php echo esc_url( $img_4 ); ?>" alt="<?php echo esc_attr( $about_title ); ?>">
                </div>

            </div>

        </div>
    </section>
</main>


<?php get_footer(); ?>