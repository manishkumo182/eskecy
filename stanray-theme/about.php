<?php
/*
Template Name: About Page
*/
get_header();
?>

<main id="main" role="main">
    <section class="about-editorial">
        <div class="container">

            <!-- TOP HEADER -->
            <div class="editorial-header">
                <div class="editorial-left">
                    <span class="tag">RECAP</span>
                    <h1>ESKECY 2025–2026</h1>
                </div>

                <div class="editorial-right">
                    <p>
                        Explore our seasonal editorial featuring the latest trends and styles.
                    </p>
                </div>
            </div>

            <!-- IMAGE GRID -->
            <div class="editorial-grid">

                <!-- BIG IMAGE -->
                <div class="grid-big">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/su.jpg" alt="ESKECY seasonal editorial">
                </div>

                <!-- SMALL GRID -->
                <div class="grid-small">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/su.jpg" alt="ESKECY seasonal editorial">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/sh.jpg" alt="ESKECY seasonal editorial">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/sh.jpg" alt="ESKECY seasonal editorial">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/sh.jpg" alt="ESKECY seasonal editorial">
                </div>

            </div>

        </div>
    </section>
</main>


<?php get_footer(); ?>