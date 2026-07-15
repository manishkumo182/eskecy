<?php get_header(); ?>

<main id="main" class="site-main" role="main">
    <div class="container">
        <?php
        if ( have_posts() ) :
            while ( have_posts() ) :
                the_post();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( 'page-content' ); ?>>
                    <h1 class="page-title"><?php the_title(); ?></h1>
                    <div class="page-body">
                        <?php the_content(); ?>
                    </div>
                </article>
                <?php
            endwhile;
        endif;
        ?>
    </div>
</main>

<style>
.page-content { margin-inline: auto; padding-block: var(--space-xl); }
.page-title { font-size: clamp(1.75rem, 3vw, 3rem); margin-bottom: var(--space-xl); }
.page-body { font-size: var(--font-size-base); line-height: 1.8; color: var(--color-gray-800); }
.page-body p { margin-bottom: var(--space-md); }
.thankyou-actions {
margin-top:2rem;
    padding-right: 2rem;
    display: flex;
    gap: 2rem;

}
.page-body h2, .page-body h3 {  margin-bottom: var(--space-md); font-family: var(--font-body); font-weight: 500;margin-top:2rem; }
.page-body a { color: var(--color-black); text-decoration: underline; text-underline-offset: 3px; }
.page-body img { max-width: 100%; border-radius: 0; margin-block: var(--space-lg); }
</style>

<?php get_footer(); ?>
