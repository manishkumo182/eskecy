<?php
/**
 * Fallback template — used for blog index, search results, 404, etc.
 */
get_header();
?>

<main id="main" class="site-main" role="main">
    <div class="container" style="padding-block: var(--space-2xl);">
        <?php
        if ( have_posts() ) :
            if ( is_home() && ! is_front_page() ) :
                echo '<h1 class="page-title">Latest Posts</h1>';
            endif;

            while ( have_posts() ) :
                the_post();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('post-entry'); ?>>
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <div><?php the_excerpt(); ?></div>
                </article>
                <?php
            endwhile;

            stanray_pagination();

        else :
            echo '<p>Nothing found.</p>';
            echo '<a href="' . esc_url( home_url('/') ) . '" class="btn btn--outline">Go Home</a>';
        endif;
        ?>
    </div>
</main>

<?php get_footer(); ?>
