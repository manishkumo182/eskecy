<?php
/* Template Name: Gallery Page */
get_header();

// Get all images attached to this page
$images = get_attached_media('image', get_the_ID());
$total  = count($images);
?>

<div class="gallery-page">
    <div class="gallery-page__header">
        <span class="eyebrow">Visual Stories</span>
        <h1 class="page-title-gallery"><?php the_title(); ?></h1>
    </div>

    <div class="gallery-grid">

        <?php if ($images) : ?>
            <?php $i = 0; foreach ($images as $image) :
                $i++;
                $thumb_url = wp_get_attachment_image_url($image->ID, 'large');
                $full_url  = wp_get_attachment_image_url($image->ID, 'full');
            ?>

                <button type="button" class="gallery-item" data-full="<?php echo esc_url($full_url); ?>" aria-label="View image <?php echo esc_attr($i); ?> of <?php echo esc_attr($total); ?>">
                    <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr( get_the_title() . ' — gallery image ' . $i ); ?>" loading="lazy">
                </button>

            <?php endforeach; ?>
        <?php else: ?>
            <p class="gallery-empty">No images found in gallery.</p>
        <?php endif; ?>

    </div>
</div>

<?php get_footer(); ?>