<?php
/**
 * Shared layout for legal/policy pages: eyebrow + title header,
 * sticky auto-built TOC from the content's h2s, and styled body.
 * Used by page-privacy-policy-2.php, page-shipping-policy.php,
 * page-refund-policy.php, page-terms-of-service.php.
 */

$toc = [];

// Closure over $toc by reference instead of `global` — get_template_part()
// runs this file inside load_template(), so top-level vars here are local
// to that call, not true PHP globals; `global $toc` would bind to an
// unrelated, always-empty $GLOBALS['toc'] instead.
$stanray_toc_heading = function ( $matches ) use ( &$toc ) {
    $label = wp_strip_all_tags( $matches[1] );
    $slug  = sanitize_title( $label );
    $base  = $slug;
    $i     = 2;
    while ( in_array( $slug, wp_list_pluck( $toc, 'slug' ), true ) ) {
        $slug = $base . '-' . $i++;
    }
    $toc[] = [ 'slug' => $slug, 'label' => $label ];
    return '<h2 id="' . esc_attr( $slug ) . '" class="wp-block-heading">' . $matches[1] . '</h2>';
};

// Some policy pages (shipping, refund, terms) don't use real <h2> blocks —
// their sections are numbered bold paragraphs like <p><strong>1. Order
// Processing</strong></p>. Promote those to real headings too, so the TOC
// and section styling work the same regardless of which pattern a page uses.
// Deliberately restricted to a leading "N. " so closing remarks like
// "<p><strong>Thank you for supporting Eskecy.</strong></p>" are left alone.
$stanray_toc_pseudo_heading = function ( $matches ) use ( &$toc ) {
    $label = wp_strip_all_tags( $matches[1] );
    $slug  = sanitize_title( $label );
    $base  = $slug;
    $i     = 2;
    while ( in_array( $slug, wp_list_pluck( $toc, 'slug' ), true ) ) {
        $slug = $base . '-' . $i++;
    }
    $toc[] = [ 'slug' => $slug, 'label' => $label ];
    return '<h2 id="' . esc_attr( $slug ) . '" class="wp-block-heading">' . $matches[1] . '</h2>';
};
?>

<main id="main" class="policy-page" role="main">
    <div class="container policy-page__header">
        <p class="eyebrow">Legal</p>
        <h3 class="policy-page__title"><?php the_title(); ?></h3>
        <p class="policy-page__updated">Last updated <?php echo esc_html( get_the_modified_date( 'F j, Y' ) ); ?></p>
    </div>

    <?php
    $raw     = preg_replace_callback( '/<h2[^>]*class="wp-block-heading"[^>]*>(.*?)<\/h2>/s', $stanray_toc_heading, get_the_content() );
    $raw     = preg_replace_callback( '/<p><strong>(\d+\.\s+[^<]+)<\/strong><\/p>/', $stanray_toc_pseudo_heading, $raw );
    $content = apply_filters( 'the_content', $raw );
    $has_toc = count( $toc ) > 1; // a single-entry TOC isn't useful — just show the body full width.
    ?>
    <div class="container policy-page__layout<?php echo $has_toc ? '' : ' policy-page__layout--no-toc'; ?>">
        <?php if ( $has_toc ) : ?>
        <nav class="policy-toc" aria-label="Sections">
            <p class="policy-toc__label">On this page</p>
            <ol>
                <?php foreach ( $toc as $item ) : ?>
                    <li><a href="#<?php echo esc_attr( $item['slug'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a></li>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php endif; ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class( 'policy-body' . ( $has_toc ? '' : ' policy-body--full' ) ); ?>>
            <?php echo $content; ?>
            <a href="#main" class="policy-body__top">Back to top ↑</a>
        </article>
    </div>
</main>

<style>
.policy-page__header {
    padding-top: calc(var(--header-height) + var(--space-lg));
    padding-bottom: var(--space-lg);
    
}
.policy-page__header > * {
    max-width: 720px;
}
.policy-page__title {
    margin-top: var(--space-sm);
    padding-bottom: 0;
}
.policy-page__updated {
    color: var(--color-gray-600);
    font-size: var(--font-size-sm);
    margin-top: var(--space-sm);
}

.policy-page__layout {
    display: grid;
    grid-template-columns: 240px minmax(0, 1fr);
    gap: var(--space-2xl);
    align-items: start;
    padding-bottom: var(--space-2xl);
}
.policy-page__layout--no-toc {
    grid-template-columns: minmax(0, 1fr);
}

.policy-toc {
    position: sticky;
    top: calc(var(--header-height) + var(--space-lg));
    border-left: 2px solid var(--color-gray-200);
    padding-left: var(--space-lg);
}
.policy-toc__label {
    font-size: var(--font-size-xs);
    font-weight: 500;
    letter-spacing: var(--letter-spacing);
    text-transform: uppercase;
    color: var(--color-gray-600);
    margin-bottom: var(--space-md);
}
.policy-toc ol { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: var(--space-12); }
.policy-toc a {
    font-size: var(--font-size-sm);
    color: var(--color-gray-600);
    text-decoration: none;
    line-height: 1.4;
    transition: color var(--transition-fast);
}
.policy-toc a:hover { color: var(--color-black); }
.policy-toc a.is-active { color: var(--color-black); font-weight: 500; }

.policy-body {
    max-width: 68ch;
    font-size: var(--font-size-base);
    line-height: 1.8;
    color: var(--color-gray-800);
}
.policy-body h2 {
    font-family: var(--font-body);
    font-weight: 400;
    font-size: var(--font-size-md);
    margin-top: var(--space-md);
    padding-top: var(--space-lg);
    padding-bottom: 0;
    border-top: 1px solid var(--color-gray-200);
}
.policy-body > *:first-child,
.policy-body h2:first-of-type { margin-top: 0; padding-top: 0; border-top: none; }
.policy-body p { margin-bottom: var(--space-md); }
.policy-body ul, .policy-body ol { margin: 0 0 var(--space-md) var(--space-lg); }
.policy-body li { margin-bottom: var(--space-sm); }
.policy-body a { color: var(--color-black); text-decoration: underline; text-underline-offset: 3px; }
.policy-body table { width: 100%; border-collapse: collapse; margin-bottom: var(--space-lg); }
.policy-body th, .policy-body td { text-align: left; padding: var(--space-sm) var(--space-md); border-bottom: 1px solid var(--color-gray-200); }

.policy-body .privacy-policy-tutorial {
    display: inline-block;
    font-family: var(--font-body);
    font-weight: 500;
    font-size: var(--font-size-xs);
    letter-spacing: var(--letter-spacing);
    text-transform: uppercase;
    color: var(--color-accent);
    background: rgba(200, 169, 110, 0.12);
    padding: 0.15em 0.6em;
    border-radius: 3px;
    margin-right: var(--space-sm);
}

.policy-body__top {
    display: inline-block;
    margin-top: var(--space-xl);
    font-size: var(--font-size-sm);
    color: var(--color-gray-600);
    text-decoration: none;
}
.policy-body__top:hover { color: var(--color-black); }

@media (max-width: 900px) {
    .policy-page__layout { grid-template-columns: 1fr; gap: var(--space-lg); }
    .policy-toc { position: static; border-left: none; border-bottom: 1px solid var(--color-gray-200); padding-left: 0; padding-bottom: var(--space-lg); }
    .policy-toc ol { flex-direction: row; flex-wrap: wrap; gap: var(--space-md); }
}
</style>

<script>
(function () {
    var links = document.querySelectorAll( '.policy-toc a' );
    if ( ! links.length ) return;
    var sections = Array.prototype.map.call( links, function ( a ) {
        return document.getElementById( a.getAttribute( 'href' ).slice( 1 ) );
    } ).filter( Boolean );

    var observer = new IntersectionObserver( function ( entries ) {
        entries.forEach( function ( entry ) {
            var link = document.querySelector( '.policy-toc a[href="#' + entry.target.id + '"]' );
            if ( ! link ) return;
            if ( entry.isIntersecting ) {
                links.forEach( function ( l ) { l.classList.remove( 'is-active' ); } );
                link.classList.add( 'is-active' );
            }
        } );
    }, { rootMargin: '-20% 0px -70% 0px' } );

    sections.forEach( function ( el ) { observer.observe( el ); } );
})();
</script>
