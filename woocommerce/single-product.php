<?php
/**
 * The Template for displaying all single products — stock WooCommerce content.
 *
 * This is intentionally the plain WooCommerce default. It exists here (rather
 * than relying on the plugin's own copy) because WooCommerce's template
 * loader always checks the theme first, so this file takes priority no
 * matter what state wp-content/plugins/woocommerce/templates/single-product.php
 * is in on any given environment — it can't be silently overridden by a
 * hand-edited core file again, and it's immune to that file getting wiped or
 * left stale by a WooCommerce update.
 *
 * All of the actual page content/layout is controlled by the theme's
 * woocommerce/single-product/*.php overrides and the woocommerce_* hooks in
 * inc/woocommerce.php — this file just provides the page wrapper.
 *
 * @package WooCommerce\Templates
 * @version 1.6.4
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' ); ?>

	<?php
	/**
	 * Hook: woocommerce_before_main_content.
	 */
	do_action( 'woocommerce_before_main_content' );
	?>

		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>

			<?php wc_get_template_part( 'content', 'single-product' ); ?>

		<?php endwhile; // end of the loop. ?>

	<?php
	/**
	 * Hook: woocommerce_after_main_content.
	 */
	do_action( 'woocommerce_after_main_content' );
	?>

	<?php
	/**
	 * Hook: woocommerce_sidebar.
	 */
	do_action( 'woocommerce_sidebar' );
	?>

<?php
get_footer( 'shop' );
