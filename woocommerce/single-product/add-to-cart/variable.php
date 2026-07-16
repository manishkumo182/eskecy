<?php
/**
 * Variable product add to cart — Stanray PDP redesign
 * Pill-style attribute selector (e.g. Size/Volume) instead of the default
 * <select> dropdown. The real <select> elements stay in the DOM (visually
 * hidden) so WooCommerce's own add-to-cart-variation.js keeps working
 * unmodified — pills just sync their value + fire 'change'.
 *
 * Overrides woocommerce/templates/single-product/add-to-cart/variable.php
 */

defined( 'ABSPATH' ) || exit;

global $product;

$attribute_keys  = array_keys( $attributes );
$variations_json = wp_json_encode( $available_variations );
$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );

do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<form class="variations_form cart pdp-atc-form" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype="multipart/form-data" data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo $variations_attr; // phpcs:ignore ?>">
	<?php do_action( 'woocommerce_before_variations_form' ); ?>

	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>

		<p class="stock out-of-stock"><?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'This product is currently out of stock and unavailable.', 'woocommerce' ) ) ); ?></p>

	<?php else : ?>

		<div class="variations pdp-variations">
			<?php foreach ( $attributes as $attribute_name => $options ) :
				$attr_id       = sanitize_title( $attribute_name );
				$attr_label    = wc_attribute_label( $attribute_name );
				$product_attrs = $product->get_attributes();
				$is_taxonomy   = isset( $product_attrs[ $attribute_name ] ) && $product_attrs[ $attribute_name ]->is_taxonomy();
				$terms_by_slug = [];

				if ( $is_taxonomy ) {
					$terms = wc_get_product_terms( $product->get_id(), $attribute_name, [ 'fields' => 'all' ] );
					foreach ( $terms as $term ) {
						$terms_by_slug[ $term->slug ] = $term->name;
					}
				}
			?>
			<div class="pdp-variation" data-attribute="attribute_<?php echo esc_attr( $attr_id ); ?>">
				<div class="pdp-variation__head">
					<span class="pdp-variation__label"><?php echo esc_html( $attr_label ); ?></span>
				</div>

				<div class="pdp-variation__pills" role="group" aria-label="<?php echo esc_attr( $attr_label ); ?>">
					<?php foreach ( $options as $option ) :
						$label = $is_taxonomy
							? ( $terms_by_slug[ $option ] ?? $option )
							: apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute_name, $product );
					?>
					<button type="button" class="pdp-pill" data-value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $label ); ?></button>
					<?php endforeach; ?>
				</div>

				<select
					name="attribute_<?php echo esc_attr( $attr_id ); ?>"
					data-attribute_name="attribute_<?php echo esc_attr( $attr_id ); ?>"
					class="pdp-variation__select">
					<option value=""><?php esc_html_e( 'Choose an option', 'woocommerce' ); ?></option>
					<?php foreach ( $options as $option ) :
						$label = $is_taxonomy ? ( $terms_by_slug[ $option ] ?? $option ) : $option;
					?>
					<option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php endforeach; ?>

			<a class="reset_variations pdp-variation__reset" href="#" aria-label="<?php esc_attr_e( 'Clear options', 'woocommerce' ); ?>"><?php esc_html_e( 'Clear', 'woocommerce' ); ?></a>
		</div>

		<div class="reset_variations_alert screen-reader-text" role="alert" aria-live="polite" aria-relevant="all"></div>
		<?php do_action( 'woocommerce_after_variations_table' ); ?>

		<div class="single_variation_wrap">
			<?php
			do_action( 'woocommerce_before_single_variation' );
			do_action( 'woocommerce_single_variation' );
			do_action( 'woocommerce_after_single_variation' );
			?>
		</div>

	<?php endif; ?>

	<?php do_action( 'woocommerce_after_variations_form' ); ?>
</form>

<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>
