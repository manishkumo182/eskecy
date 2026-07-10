<?php get_header(); ?>

<main id="main" class="site-main" role="main">
    <div class="container">
        <div class="error-404">
            <p class="eyebrow">404</p>
            <h1 class="error-404__title">Page Not Found</h1>
            <p class="error-404__text">The page you're looking for has been moved or doesn't exist.</p>
            <div class="error-404__actions">
                <a href="<?php echo esc_url( home_url('/') ); ?>" class="btn btn--primary">Go Home</a>
                <a href="<?php echo esc_url( wc_get_page_permalink('shop') ); ?>" class="btn btn--outline">Shop All</a>
            </div>
        </div>
    </div>
</main>

<style>
.error-404 { text-align: center; padding: var(--space-2xl) 0; }
.error-404__title { font-size: clamp(3rem, 8vw, 6rem); margin-block: var(--space-md); }
.error-404__text { font-size: var(--font-size-md); color: var(--color-gray-600); margin-bottom: var(--space-xl); }
.error-404__actions { display: flex; justify-content: center; gap: var(--space-md); flex-wrap: wrap; }
</style>

<?php get_footer(); ?>
