<?php
/**
 * Review Comments Template — Stanray PDP redesign
 * Avatar, name + verified badge + time-ago, review title, body, stars.
 *
 * Overrides woocommerce/templates/single-product/review.php
 * Closing </li> intentionally omitted — WordPress' comment walker adds it.
 */

defined( 'ABSPATH' ) || exit;

$rating   = (int) get_comment_meta( $comment->comment_ID, 'rating', true );
$title    = get_comment_meta( $comment->comment_ID, 'review_title', true );
$verified = wc_review_is_from_verified_owner( $comment->comment_ID );
$awaiting = '0' === $comment->comment_approved;
?>
<li <?php comment_class( 'pdp-review' ); ?> id="li-comment-<?php comment_ID(); ?>">

	<div id="comment-<?php comment_ID(); ?>" class="comment_container pdp-review__container">

		<div class="pdp-review__avatar"><?php echo get_avatar( $comment, 48 ); ?></div>

		<div class="comment-text pdp-review__body">

			<?php if ( $awaiting ) : ?>

				<p class="meta">
					<em class="woocommerce-review__awaiting-approval"><?php esc_html_e( 'Your review is awaiting approval', 'woocommerce' ); ?></em>
				</p>

			<?php else : ?>

				<div class="pdp-review__head">
					<span class="pdp-review__author">
						<?php comment_author(); ?>
						<?php if ( 'yes' === get_option( 'woocommerce_review_rating_verification_label' ) && $verified ) : ?>
							<em class="woocommerce-review__verified verified pdp-review__verified"><?php esc_html_e( '(Verified)', 'woocommerce' ); ?></em>
						<?php endif; ?>
					</span>
					<time class="pdp-review__time" datetime="<?php echo esc_attr( get_comment_date( 'c' ) ); ?>">
						<?php
						/* translators: %s: human-readable time difference */
						printf( esc_html__( '%s ago', 'stanray-custom' ), human_time_diff( get_comment_time( 'U' ), current_time( 'timestamp' ) ) );
						?>
					</time>
				</div>

				<?php if ( $title ) : ?>
					<p class="pdp-review__title"><?php echo esc_html( $title ); ?></p>
				<?php endif; ?>

				<div class="description pdp-review__text">
					<?php comment_text(); ?>
				</div>

				<?php if ( $rating && wc_review_ratings_enabled() ) : ?>
					<div class="pdp-review__stars"><?php echo wc_get_rating_html( $rating ); ?></div>
				<?php endif; ?>

			<?php endif; ?>

		</div>
	</div>
