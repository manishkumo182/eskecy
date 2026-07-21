<?php
/* Archive: Video Gallery — "In Concert" style hero + tour dates */
get_header();

global $wp_query;

$tour_page = isset( $_GET['tour_page'] ) ? max( 1, absint( $_GET['tour_page'] ) ) : 1;

$tour_dates = new WP_Query([
    'post_type'      => 'tour_date',
    'posts_per_page' => 5,
    'paged'          => $tour_page,
    'meta_key'       => '_event_date',
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
    'meta_query'     => [
        [
            'key'     => '_event_date',
            'value'   => '',
            'compare' => '!=',
        ],
    ],
]);
?>

<div class="video-gallery-page">

    <!-- HERO -->
    <?php
    $eh_video_id = get_option( 'stanray_eh_video_id', 0 );
    $eh_video    = $eh_video_id ? wp_get_attachment_url( $eh_video_id ) : get_template_directory_uri() . '/assets/videos/SKC-Live-Web-Banner-.mp4';
    $eh_eyebrow  = get_option( 'stanray_eh_eyebrow', 'Live · Events' );
    $eh_headline = get_option( 'stanray_eh_headline', 'In Concert' );
    $eh_subtext  = get_option( 'stanray_eh_subtext', 'Catch every show, every stage, every moment — live and on video.' );
    $eh_cta_text = get_option( 'stanray_eh_cta_text', 'New Dates Added' );
    $eh_cta_link = get_option( 'stanray_eh_cta_link', '#tour-dates' );
    ?>
    <section class="hero" aria-label="<?php echo esc_attr( $eh_headline ); ?>">
        <div class="hero__media">
            <video class="hero__video" autoplay muted loop playsinline>
                <source src="<?php echo esc_url( $eh_video ); ?>" type="video/mp4">
            </video>
            <div class="hero__overlay"></div>
        </div>
        <div class="hero__content">
            <p class="hero__eyebrow eyebrow"><?php echo esc_html( $eh_eyebrow ); ?></p>
            <h1 class="hero__headline"><?php echo esc_html( $eh_headline ); ?></h1>
            <p class="hero__subtext"><?php echo esc_html( $eh_subtext ); ?></p>
            <a href="<?php echo esc_url( $eh_cta_link ); ?>" class="btn btn--outline-white hero__cta"><?php echo esc_html( $eh_cta_text ); ?></a>
        </div>
    </section>

    <div class="container">

        <!-- TOUR DATES -->
        <?php if ( $tour_dates->have_posts() ) : ?>
        <section id="tour-dates" class="tour-dates section" aria-label="Tour Dates">
            <h2 class="tour-dates__title">Tour Dates</h2>
            <div class="tour-dates__list">
                <?php while ( $tour_dates->have_posts() ) : $tour_dates->the_post();
                    $event_date    = get_post_meta( get_the_ID(), '_event_date', true );
                    $event_venue   = get_post_meta( get_the_ID(), '_event_venue', true );
                    $event_country = get_post_meta( get_the_ID(), '_event_country', true );
                    $ticket_url   = get_post_meta( get_the_ID(), '_ticket_url', true );
                    $linked_video = get_post_meta( get_the_ID(), '_linked_video_id', true );
                    $video_url    = $linked_video ? get_permalink( $linked_video ) : '';
                    $timestamp    = $event_date ? strtotime( $event_date ) : false;
                ?>
                    <div class="tour-dates__item">
                        <div class="tour-dates__date">
                            <?php if ( $timestamp ) : ?>
                                <span class="tour-dates__month"><?php echo esc_html( date_i18n( 'M', $timestamp ) ); ?></span>
                                <span class="tour-dates__day"><?php echo esc_html( date_i18n( 'd', $timestamp ) ); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="tour-dates__info">
                            <span class="tour-dates__name"><?php the_title(); ?></span>
                            <?php if ( $event_venue ) : ?>
                                <span class="tour-dates__venue">
                                    <?php if ( $event_country ) : ?>
                                        <span class="tour-dates__flag"><?php echo esc_html( stanray_country_flag_emoji( $event_country ) ); ?></span>
                                    <?php endif; ?>
                                    <?php echo esc_html( $event_venue ); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if ( $ticket_url ) : ?>
                            <a href="<?php echo esc_url( $ticket_url ); ?>" class="btn btn--outline-white tour-dates__btn" target="_blank" rel="noopener">Get Tickets</a>
                        <?php elseif ( $video_url ) : ?>
                            <a href="<?php echo esc_url( $video_url ); ?>" class="btn btn--outline-white tour-dates__btn">Watch Video</a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>

            <?php if ( $tour_dates->max_num_pages > 1 ) : ?>
                <div class="pagination tour-dates__pagination" data-scroll-anchor="tour-dates">
                    <?php
                    echo paginate_links([
                        'base'      => add_query_arg( 'tour_page', '%#%' ),
                        'format'    => '',
                        'current'   => $tour_page,
                        'total'     => $tour_dates->max_num_pages,
                        'prev_text' => '←',
                        'next_text' => '→',
                    ]);
                    ?>
                </div>
            <?php endif; ?>
        </section>
        <?php endif; ?>

        <!-- VIDEO GRID -->
        <section id="video-archive" class="video-gallery section" aria-label="Video Archive">
            <h2 class="video-grid_title">The Sushant KC Archive</h2>
            <div class="video-grid">

                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                    <div class="video-card">
                        <a href="<?php the_permalink(); ?>">

                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('large'); ?>
                            <?php else: ?>
                                <img src="https://via.placeholder.com/600x400">
                            <?php endif; ?>

                            <div class="overlay">▶</div>

                        </a>
                    </div>

                <?php endwhile; endif; ?>

            </div>

            <?php if ( $wp_query->max_num_pages > 1 ) : ?>
                <div class="pagination video-grid__pagination" data-scroll-anchor="video-archive">
                    <?php
                    echo paginate_links([
                        'prev_text' => '←',
                        'next_text' => '→',
                    ]);
                    ?>
                </div>
            <?php endif; ?>
        </section>

    </div>
</div>

<?php get_footer(); ?>
