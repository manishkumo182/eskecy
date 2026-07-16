<?php
/**
 * Display single product reviews (comments) — Stanray PDP redesign
 * Adds a rating breakdown summary + "Review List" header with sort control
 * on top of WooCommerce's default review list/form.
 *
 * Overrides woocommerce/templates/single-product-reviews.php
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! comments_open() ) {
	return;
}

$rating_count = $product->get_rating_count();
$review_count = $product->get_review_count();
$average      = $product->get_average_rating();
$sort         = isset( $_GET['review_sort'] ) ? sanitize_key( $_GET['review_sort'] ) : 'newest';
$sort_labels  = [
	'newest'  => __( 'Newest', 'stanray-custom' ),
	'oldest'  => __( 'Oldest', 'stanray-custom' ),
	'highest' => __( 'Highest Rated', 'stanray-custom' ),
	'lowest'  => __( 'Lowest Rated', 'stanray-custom' ),
];
?>
<div id="reviews" class="woocommerce-Reviews pdp-reviews">

	<?php if ( $rating_count > 0 && wc_review_ratings_enabled() ) :
		$data = stanray_get_rating_breakdown( $product->get_id() );
	?>
	<div class="pdp-reviews__summary">
		<div class="pdp-reviews__avg">
			<span class="pdp-reviews__avg-num"><?php echo esc_html( number_format( (float) $average, 1 ) ); ?></span>
			<span class="pdp-reviews__avg-of"><?php esc_html_e( 'out of 5', 'stanray-custom' ); ?></span>
			<?php echo wc_get_rating_html( $average, $rating_count ); ?>
			<span class="pdp-reviews__avg-count">(<?php echo esc_html( $review_count ); ?> <?php esc_html_e( 'Review', 'stanray-custom' ); ?>)</span>
		</div>

		<div class="pdp-reviews__bars">
			<?php foreach ( $data['breakdown'] as $star => $row ) : ?>
			<div class="pdp-reviews__bar-row">
				<span class="pdp-reviews__bar-label"><?php echo esc_html( $star ); ?> Star</span>
				<span class="pdp-reviews__bar-track">
					<span class="pdp-reviews__bar-fill" style="width: <?php echo esc_attr( $row['percent'] ); ?>%"></span>
				</span>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>

	<div id="comments">

		<?php if ( have_comments() ) :
			$total_shown = get_comment_pages_count() > 1 && get_option( 'page_comments' )
				? min( (int) get_option( 'comments_per_page' ), $review_count )
				: $review_count;
		?>
		<div class="pdp-reviews__list-head">
			<h3 class="pdp-reviews__list-title"><?php esc_html_e( 'Review List', 'stanray-custom' ); ?></h3>
			<p class="pdp-reviews__showing">
				<?php
				printf(
					/* translators: 1: number shown 2: total review count */
					esc_html__( 'Showing 1-%1$d of %2$d results', 'stanray-custom' ),
					(int) $total_shown,
					(int) $review_count
				);
				?>
			</p>
			<form class="pdp-reviews__sort" method="get">
				<label for="review_sort"><?php esc_html_e( 'Sort by:', 'stanray-custom' ); ?></label>
				<select name="review_sort" id="review_sort" onchange="this.form.submit()">
					<?php foreach ( $sort_labels as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $sort, $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<?php // preserve current URL (minus review_sort) so sorting doesn't drop other params ?>
				<?php foreach ( $_GET as $k => $v ) : if ( 'review_sort' === $k || ! is_scalar( $v ) ) continue; ?>
				<input type="hidden" name="<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $v ); ?>">
				<?php endforeach; ?>
			</form>
		</div>

			<ol class="commentlist pdp-review-list">
				<?php wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', [ 'callback' => 'woocommerce_comments' ] ) ); ?>
			</ol>

			<?php
			if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
				echo '<nav class="woocommerce-pagination">';
				paginate_comments_links(
					apply_filters(
						'woocommerce_comment_pagination_args',
						[
							'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
							'next_text' => is_rtl() ? '&larr;' : '&rarr;',
							'type'      => 'list',
						]
					)
				);
				echo '</nav>';
			endif;
			?>
		<?php else : ?>
			<p class="woocommerce-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'woocommerce' ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>
		<div id="review_form_wrapper">
			<div id="review_form">
				<?php
				$commenter    = wp_get_current_commenter();
				$comment_form = [
					/* translators: %s is product title */
					'title_reply'         => have_comments() ? esc_html__( 'Add a review', 'woocommerce' ) : sprintf( esc_html__( 'Be the first to review &ldquo;%s&rdquo;', 'woocommerce' ), get_the_title() ),
					/* translators: %s is product title */
					'title_reply_to'      => esc_html__( 'Leave a Reply to %s', 'woocommerce' ),
					'title_reply_before'  => '<span id="reply-title" class="comment-reply-title" role="heading" aria-level="3">',
					'title_reply_after'   => '</span>',
					'comment_notes_after' => '',
					'label_submit'        => esc_html__( 'Submit', 'woocommerce' ),
					'logged_in_as'        => '',
					'comment_field'       => '',
				];

				$name_email_required = (bool) get_option( 'require_name_email', 1 );
				$fields              = [
					'author' => [
						'label'        => __( 'Name', 'woocommerce' ),
						'type'         => 'text',
						'value'        => $commenter['comment_author'],
						'required'     => $name_email_required,
						'autocomplete' => 'name',
					],
					'email'  => [
						'label'        => __( 'Email', 'woocommerce' ),
						'type'         => 'email',
						'value'        => $commenter['comment_author_email'],
						'required'     => $name_email_required,
						'autocomplete' => 'email',
					],
				];

				$comment_form['fields'] = [];

				foreach ( $fields as $key => $field ) {
					$field_html  = '<p class="comment-form-' . esc_attr( $key ) . '">';
					$field_html .= '<label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] );

					if ( $field['required'] ) {
						$field_html .= '&nbsp;<span class="required">*</span>';
					}

					$field_html .= '</label><input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="' . esc_attr( $field['type'] ) . '" autocomplete="' . esc_attr( $field['autocomplete'] ) . '" value="' . esc_attr( $field['value'] ) . '" size="30" ' . ( $field['required'] ? 'required' : '' ) . ' /></p>';

					$comment_form['fields'][ $key ] = $field_html;
				}

				$account_page_url = wc_get_page_permalink( 'myaccount' );
				if ( $account_page_url ) {
					/* translators: %s opening and closing link tags respectively */
					$comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf( esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'woocommerce' ), '<a href="' . esc_url( $account_page_url ) . '">', '</a>' ) . '</p>';
				}

				if ( wc_review_ratings_enabled() ) {
					$comment_form['comment_field'] = '<div class="comment-form-rating"><label for="rating" id="comment-form-rating-label">' . esc_html__( 'Your rating', 'woocommerce' ) . ( wc_review_ratings_required() ? '&nbsp;<span class="required">*</span>' : '' ) . '</label><select name="rating" id="rating" required>
						<option value="">' . esc_html__( 'Rate&hellip;', 'woocommerce' ) . '</option>
						<option value="5">' . esc_html__( 'Perfect', 'woocommerce' ) . '</option>
						<option value="4">' . esc_html__( 'Good', 'woocommerce' ) . '</option>
						<option value="3">' . esc_html__( 'Average', 'woocommerce' ) . '</option>
						<option value="2">' . esc_html__( 'Not that bad', 'woocommerce' ) . '</option>
						<option value="1">' . esc_html__( 'Very poor', 'woocommerce' ) . '</option>
					</select></div>';
				}

				$comment_form['comment_field'] .= '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Your review', 'woocommerce' ) . '&nbsp;<span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="8" required></textarea></p>';

				comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
				?>
			</div>
		</div>
	<?php else : ?>
		<p class="woocommerce-verification-required"><?php esc_html_e( 'Only logged in customers who have purchased this product may leave a review.', 'woocommerce' ); ?></p>
	<?php endif; ?>

	<div class="clear"></div>
</div>
