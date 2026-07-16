<?php get_header(); ?>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<?php
$youtube = get_post_meta(get_the_ID(), '_youtube_url', true);

// extract video id safely
$video_id = '';
if ($youtube) {
    preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&]+)/', $youtube, $matches);
    $video_id = $matches[1] ?? '';
}
?>

<div class="video-detail">

    <a href="<?php echo home_url('/events'); ?>" class="btn-back">← Back</a>

    <div class="video-layout">

        <div class="video-player">
            <?php if ($video_id): ?>
                <iframe 
                    width="100%" 
                    height="500"
                    src="https://www.youtube.com/embed/<?php echo esc_attr($video_id); ?>?autoplay=1"
                    frameborder="0"
                    allowfullscreen>
                </iframe>
            <?php else: ?>
                <p>No video URL added</p>
            <?php endif; ?>
        </div>

        <div class="video-info">
            <h2><?php the_title(); ?></h2>
            <p><?php the_content(); ?></p>
        </div>

    </div>

</div>

<?php endwhile; endif; ?>

<?php get_footer(); ?>