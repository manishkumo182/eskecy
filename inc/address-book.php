<?php
/**
 * Address book — multiple saved billing/shipping addresses per customer,
 * each with a default, selectable at checkout.
 *
 * Storage: one `stanray_address` CPT per saved address (post_author = the
 * customer). Default address is a user-meta pointer per type
 * (_stanray_default_billing_address_id / _stanray_default_shipping_address_id),
 * not a per-post flag — a single write keeps it exclusive, and "no default" /
 * "default was deleted" are both cheap explicit checks.
 *
 * Checkout picking never touches WooCommerce's own order pipeline: the picker
 * only fills the same billing_ and shipping_ inputs core already renders, so
 * submission, validation, and order creation are exactly what they'd be
 * without this feature.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ─── FIELD SCHEMA ──────────────────────────────────────────────────────── */

/**
 * Field keys (without the billing_/shipping_ prefix) for a given type +
 * country, straight from WooCommerce's own locale-aware field definitions —
 * not a hand-maintained list, so country-specific fields (or their absence,
 * e.g. no `state` for some countries) are never silently dropped.
 */
function stanray_address_field_keys( $type, $country = '' ) {
    if ( ! $country ) {
        $country = WC()->countries->get_base_country();
    }
    $fields = WC()->countries->get_address_fields( $country, $type . '_' );
    return array_map( function( $key ) use ( $type ) {
        return substr( $key, strlen( $type ) + 1 );
    }, array_keys( $fields ) );
}

/* ─── CRUD HELPERS ──────────────────────────────────────────────────────── */

function stanray_get_user_addresses( $user_id, $type ) {
    $query = new WP_Query( [
        'post_type'      => 'stanray_address',
        'post_status'    => 'publish',
        'author'         => $user_id,
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_query'     => [ [ 'key' => '_address_type', 'value' => $type ] ],
    ] );
    return $query->posts;
}

function stanray_get_address_post( $post_id, $user_id, $type = null ) {
    $post = get_post( $post_id );
    if ( ! $post || 'stanray_address' !== $post->post_type ) return null;
    if ( (int) $post->post_author !== (int) $user_id ) return null;
    if ( $type && get_post_meta( $post_id, '_address_type', true ) !== $type ) return null;
    return $post;
}

function stanray_get_default_address_id( $user_id, $type ) {
    $id = (int) get_user_meta( $user_id, "_stanray_default_{$type}_address_id", true );
    if ( ! $id || ! stanray_get_address_post( $id, $user_id, $type ) ) return 0;
    return $id;
}

function stanray_set_default_address( $user_id, $type, $post_id ) {
    update_user_meta( $user_id, "_stanray_default_{$type}_address_id", $post_id );
}

/**
 * Address fields as a flat array (unprefixed keys), for
 * WC()->countries->get_formatted_address() or for filling checkout inputs.
 */
function stanray_get_address_fields_array( $post_id ) {
    $type = get_post_meta( $post_id, '_address_type', true );
    $data = [];
    foreach ( stanray_address_field_keys( $type, get_post_meta( $post_id, '_country', true ) ) as $key ) {
        $data[ $key ] = get_post_meta( $post_id, "_{$key}", true );
    }
    return $data;
}

function stanray_format_address_html( $post_id ) {
    return WC()->countries->get_formatted_address( stanray_get_address_fields_array( $post_id ) );
}

/**
 * Build a [ label => value ] list for a saved address-book entry — same
 * shape/labels as stanray_order_address_lines() in inc/woocommerce.php
 * (used for order Billing/Shipping details), just reading from the
 * stanray_address CPT instead of an order. Skips empty fields, e.g.
 * Address/Postcode/Province on entries created after checkout stopped
 * collecting them.
 */
function stanray_saved_address_lines( $post_id ) {
    $fields       = stanray_get_address_fields_array( $post_id );
    $country_code = $fields['country'] ?? '';
    $state_code   = $fields['state'] ?? '';

    $lines = [
        __( 'Name', 'stanray-custom' )     => trim( ( $fields['first_name'] ?? '' ) . ' ' . ( $fields['last_name'] ?? '' ) ),
        __( 'Company', 'stanray-custom' )  => $fields['company'] ?? '',
        __( 'Address', 'stanray-custom' )  => trim( ( $fields['address_1'] ?? '' ) . ' ' . ( $fields['address_2'] ?? '' ) ),
        __( 'City', 'stanray-custom' )     => $fields['city'] ?? '',
        __( 'Postcode', 'stanray-custom' ) => $fields['postcode'] ?? '',
        __( 'Province', 'stanray-custom' ) => $state_code ? ( WC()->countries->get_states( $country_code )[ $state_code ] ?? $state_code ) : '',
        __( 'Country', 'stanray-custom' )  => $country_code ? ( WC()->countries->countries[ $country_code ] ?? $country_code ) : '',
        __( 'Phone', 'stanray-custom' )    => $fields['phone'] ?? '',
    ];
    if ( 'billing' === get_post_meta( $post_id, '_address_type', true ) ) {
        $lines[ __( 'Email', 'stanray-custom' ) ] = $fields['email'] ?? '';
    }

    return array_filter( $lines, function( $value ) { return '' !== trim( $value ); } );
}

/**
 * Create or update a saved address from sanitized $fields (unprefixed keys,
 * e.g. 'address_1' not 'billing_address_1'). Returns the post ID.
 */
function stanray_save_address_from_fields( $user_id, $type, $fields, $post_id = 0, $label = '' ) {
    $is_new = ! $post_id;

    if ( ! $label ) {
        $label = trim( ( $fields['city'] ?? '' ) . ( ! empty( $fields['postcode'] ) ? ', ' . $fields['postcode'] : '' ) );
    }

    $postarr = [
        'post_type'   => 'stanray_address',
        'post_status' => 'publish',
        'post_title'  => $label ?: __( 'Address', 'stanray-custom' ),
        'post_author' => $user_id,
    ];

    if ( $is_new ) {
        $post_id = wp_insert_post( $postarr );
    } else {
        $postarr['ID'] = $post_id;
        wp_update_post( $postarr );
    }

    update_post_meta( $post_id, '_address_type', $type );
    foreach ( stanray_address_field_keys( $type, $fields['country'] ?? '' ) as $key ) {
        update_post_meta( $post_id, "_{$key}", $fields[ $key ] ?? '' );
    }

    // A user's first address of a type is always the default, regardless of
    // whether they checked "set as default" — otherwise the checkout picker
    // would have nothing to preselect.
    if ( $is_new && count( stanray_get_user_addresses( $user_id, $type ) ) === 1 ) {
        stanray_set_default_address( $user_id, $type, $post_id );
    }

    return $post_id;
}

function stanray_delete_address( $post_id, $user_id, $type ) {
    if ( ! stanray_get_address_post( $post_id, $user_id, $type ) ) return false;

    $was_default = ( stanray_get_default_address_id( $user_id, $type ) === (int) $post_id );
    wp_delete_post( $post_id, true );

    if ( $was_default ) {
        $remaining = stanray_get_user_addresses( $user_id, $type );
        stanray_set_default_address( $user_id, $type, $remaining ? $remaining[0]->ID : 0 );
    }
    return true;
}

/**
 * Dedupe check for the "save this address at checkout" flow — a payment
 * retry re-fires woocommerce_checkout_order_processed, so without this a
 * customer could rack up duplicate entries for the same address.
 */
function stanray_address_already_saved( $user_id, $type, $fields ) {
    foreach ( stanray_get_user_addresses( $user_id, $type ) as $post ) {
        $existing = stanray_get_address_fields_array( $post->ID );
        $match = true;
        foreach ( [ 'country', 'postcode', 'address_1', 'city' ] as $key ) {
            if ( strcasecmp( trim( $existing[ $key ] ?? '' ), trim( $fields[ $key ] ?? '' ) ) !== 0 ) {
                $match = false;
                break;
            }
        }
        if ( $match ) return true;
    }
    return false;
}

/* ─── MIGRATION OF THE LEGACY SINGLE ADDRESS ───────────────────────────── */

/**
 * Runs once per user (gated by a persistent sentinel, not a live "zero
 * addresses" check — zero is also the normal state after a legitimate
 * delete, and re-deriving from a live count would resurrect a deleted
 * address from stale legacy user meta).
 */
function stanray_maybe_migrate_legacy_addresses() {
    if ( ! is_user_logged_in() ) return;
    $user_id = get_current_user_id();
    if ( get_user_meta( $user_id, '_stanray_address_migrated', true ) ) return;

    foreach ( [ 'billing', 'shipping' ] as $type ) {
        $fields = [];
        foreach ( stanray_address_field_keys( $type ) as $key ) {
            $fields[ $key ] = get_user_meta( $user_id, "{$type}_{$key}", true );
        }
        // Only migrate if there's a real address (not just a stray phone/email).
        if ( ! empty( $fields['address_1'] ) ) {
            stanray_save_address_from_fields( $user_id, $type, $fields );
        }
    }

    update_user_meta( $user_id, '_stanray_address_migrated', 1 );
}

/**
 * The old single-address edit form (my-account/edit-address/billing|shipping)
 * still works and still writes straight to legacy user meta, which nothing
 * else reads after migration — redirect it to the new list instead of
 * leaving a second, disconnected editing path live.
 */
function stanray_handle_address_endpoints() {
    if ( ! is_wc_endpoint_url( 'edit-address' ) ) return;

    global $wp;
    $sub = $wp->query_vars['edit-address'] ?? '';

    if ( in_array( $sub, [ 'billing', 'shipping' ], true ) ) {
        wp_safe_redirect( wc_get_account_endpoint_url( 'edit-address' ) );
        exit;
    }

    stanray_maybe_migrate_legacy_addresses();
}
add_action( 'template_redirect', 'stanray_handle_address_endpoints' );

/**
 * Drop "Province" (state), "Street address" (address_1), and "Apartment,
 * suite, unit, etc." (address_2) from the address-book add/edit form AND
 * the checkout form — both read from this same
 * WC()->countries->get_address_fields() source, so unsetting the keys here
 * removes them from rendering, from required-field validation (both this
 * theme's own check in stanray_handle_save_address_form() and WooCommerce
 * core's own checkout validation), and from what actually gets stored,
 * everywhere consistently. Scoped to just these two contexts so other
 * consumers of get_address_fields() (admin order screens, REST API, order
 * emails rendering an already-placed order's stored address) are untouched.
 */
function stanray_trim_saved_address_fields( $fields ) {
    if ( ! is_wc_endpoint_url( 'saved-address' ) && ! is_checkout() ) return $fields;
    unset(
        $fields['billing_state'], $fields['shipping_state'],
        $fields['billing_address_1'], $fields['shipping_address_1'],
        $fields['billing_address_2'], $fields['shipping_address_2']
    );
    return $fields;
}
add_filter( 'woocommerce_billing_fields', 'stanray_trim_saved_address_fields' );
add_filter( 'woocommerce_shipping_fields', 'stanray_trim_saved_address_fields' );

/* ─── SAVED-ADDRESS ENDPOINT (add/edit one entry) ──────────────────────── */

add_filter( 'woocommerce_get_query_vars', function( $vars ) {
    $vars['saved-address'] = 'saved-address';
    return $vars;
} );

// Themes have no activation hook the way plugins do, so a code push needs an
// explicit one-time flush or the new endpoint 404s until permalinks are
// resaved by hand. Version-gated so it never runs on every request.
add_action( 'init', function() {
    if ( '1' !== get_option( 'stanray_address_book_rewrite_v' ) ) {
        flush_rewrite_rules();
        update_option( 'stanray_address_book_rewrite_v', '1' );
    }
}, 20 );

// The existing "Addresses" nav item already points at edit-address / my-address.php — no menu changes needed.
add_action( 'woocommerce_account_saved-address_endpoint', function() {
    wc_get_template( 'myaccount/form-saved-address.php' );
} );

/**
 * Save handler for the add/edit form. Deliberately its own nonce action,
 * nonce field name, and $_POST['action'] value — WC_Form_Handler::save_address()
 * is registered globally on template_redirect and self-triggers purely by
 * inspecting $_POST (nonce action 'woocommerce-edit_address' + action
 * 'edit_address'), with no endpoint scoping. Reusing any of those names would
 * make core's handler ALSO fire here and silently overwrite legacy
 * billing_* user meta (it defaults to billing since the edit-address query
 * var isn't set on this endpoint).
 */
function stanray_handle_save_address_form() {
    if ( ! is_wc_endpoint_url( 'saved-address' ) ) return;
    if ( empty( $_POST['action'] ) || 'stanray_save_address' !== $_POST['action'] ) return;
    if ( ! is_user_logged_in() ) return;

    if ( ! wp_verify_nonce( $_POST['stanray-save-address-nonce'] ?? '', 'stanray-save-address' ) ) {
        wc_add_notice( __( 'Your session expired, please try again.', 'stanray-custom' ), 'error' );
        wp_safe_redirect( wc_get_account_endpoint_url( 'edit-address' ) );
        exit;
    }

    $user_id = get_current_user_id();
    $type    = in_array( $_POST['address_type'] ?? '', [ 'billing', 'shipping' ], true ) ? $_POST['address_type'] : 'billing';
    $post_id = absint( $_POST['address_id'] ?? 0 );

    if ( $post_id && ! stanray_get_address_post( $post_id, $user_id, $type ) ) {
        wc_add_notice( __( 'Address not found.', 'stanray-custom' ), 'error' );
        wp_safe_redirect( wc_get_account_endpoint_url( 'edit-address' ) );
        exit;
    }

    $country = wc_clean( wp_unslash( $_POST['country'] ?? '' ) );

    // The form has its own handler (not WC_Form_Handler::save_address(), see
    // note above) and its own novalidate-free HTML5 required attributes —
    // but a bypassed/JS-disabled submit skips those, so mirror the same
    // required check server-side rather than trusting the browser alone.
    $field_defs = WC()->countries->get_address_fields( $country, $type . '_' );
    $missing    = [];
    foreach ( $field_defs as $key => $field ) {
        $unprefixed = substr( $key, strlen( $type ) + 1 );
        $posted     = trim( wc_clean( wp_unslash( $_POST[ $unprefixed ] ?? '' ) ) );
        if ( ! empty( $field['required'] ) && '' === $posted ) {
            $missing[] = $field['label'] ?? $unprefixed;
        }
    }

    if ( $missing ) {
        wc_add_notice(
            sprintf(
                /* translators: %s: comma-separated list of missing field labels */
                __( 'Please fill in the required fields: %s', 'stanray-custom' ),
                implode( ', ', $missing )
            ),
            'error'
        );
        $back_url = $post_id
            ? wc_get_endpoint_url( 'saved-address', $post_id, wc_get_page_permalink( 'myaccount' ) )
            : add_query_arg( 'type', $type, wc_get_endpoint_url( 'saved-address', '', wc_get_page_permalink( 'myaccount' ) ) );
        wp_safe_redirect( $back_url );
        exit;
    }

    $fields = [];
    foreach ( stanray_address_field_keys( $type, $country ) as $key ) {
        $fields[ $key ] = isset( $_POST[ $key ] ) ? wc_clean( wp_unslash( $_POST[ $key ] ) ) : '';
    }

    $label   = sanitize_text_field( wp_unslash( $_POST['label'] ?? '' ) );
    $post_id = stanray_save_address_from_fields( $user_id, $type, $fields, $post_id, $label );

    if ( ! empty( $_POST['is_default'] ) ) {
        stanray_set_default_address( $user_id, $type, $post_id );
    }

    wc_add_notice( __( 'Address saved.', 'stanray-custom' ) );
    wp_safe_redirect( wc_get_account_endpoint_url( 'edit-address' ) );
    exit;
}
add_action( 'template_redirect', 'stanray_handle_save_address_form' );

/* ─── DELETE / SET-DEFAULT QUICK ACTIONS ───────────────────────────────── */

function stanray_handle_address_quick_actions() {
    if ( ! is_wc_endpoint_url( 'edit-address' ) || ! is_user_logged_in() ) return;

    $user_id = get_current_user_id();

    if ( isset( $_GET['stanray_delete_address'], $_GET['_wpnonce'] )
        && wp_verify_nonce( $_GET['_wpnonce'], 'stanray-delete-address' ) ) {
        $post_id = absint( $_GET['stanray_delete_address'] );
        $type    = get_post_meta( $post_id, '_address_type', true );
        if ( stanray_delete_address( $post_id, $user_id, $type ) ) {
            wc_add_notice( __( 'Address removed.', 'stanray-custom' ) );
        }
        wp_safe_redirect( wc_get_account_endpoint_url( 'edit-address' ) );
        exit;
    }

    if ( isset( $_GET['stanray_set_default'], $_GET['_wpnonce'] )
        && wp_verify_nonce( $_GET['_wpnonce'], 'stanray-set-default' ) ) {
        $post_id = absint( $_GET['stanray_set_default'] );
        $type    = get_post_meta( $post_id, '_address_type', true );
        if ( stanray_get_address_post( $post_id, $user_id, $type ) ) {
            stanray_set_default_address( $user_id, $type, $post_id );
            wc_add_notice( __( 'Default address updated.', 'stanray-custom' ) );
        }
        wp_safe_redirect( wc_get_account_endpoint_url( 'edit-address' ) );
        exit;
    }
}
add_action( 'template_redirect', 'stanray_handle_address_quick_actions' );

/* ─── CHECKOUT PICKER ───────────────────────────────────────────────────── */

function stanray_render_checkout_address_picker( $type ) {
    if ( ! is_user_logged_in() ) return;

    $user_id   = get_current_user_id();
    $addresses = stanray_get_user_addresses( $user_id, $type );
    if ( ! $addresses ) return; // nothing to pick from — just show the normal fields.

    $default_id = stanray_get_default_address_id( $user_id, $type );
    ?>
    <div class="address-picker" data-type="<?php echo esc_attr( $type ); ?>">
        <?php foreach ( $addresses as $post ) :
            $fields    = stanray_get_address_fields_array( $post->ID );
            $is_default = ( (int) $post->ID === $default_id );
        ?>
            <label class="address-picker__card<?php echo $is_default ? ' is-selected' : ''; ?>">
                <input type="radio" name="<?php echo esc_attr( $type ); ?>_address_picker"
                       value="<?php echo esc_attr( $post->ID ); ?>"
                       <?php checked( $is_default ); ?>
                       <?php foreach ( $fields as $key => $value ) : ?>
                           data-<?php echo esc_attr( str_replace( '_', '-', $key ) ); ?>="<?php echo esc_attr( $value ); ?>"
                       <?php endforeach; ?>>
                <span class="address-picker__label">
                    <?php echo esc_html( get_the_title( $post ) ); ?>
                    <?php if ( $is_default ) : ?><em class="address-picker__default-tag"><?php esc_html_e( 'Default', 'stanray-custom' ); ?></em><?php endif; ?>
                </span>
                <span class="address-picker__preview"><?php echo wp_kses_post( stanray_format_address_html( $post->ID ) ); ?></span>
            </label>
        <?php endforeach; ?>
        <label class="address-picker__card address-picker__card--new">
            <input type="radio" name="<?php echo esc_attr( $type ); ?>_address_picker" value="new" <?php checked( ! $default_id ); ?>>
            <span class="address-picker__label">+ <?php esc_html_e( 'Enter a new address', 'stanray-custom' ); ?></span>
        </label>
        <label class="address-picker__save-toggle" style="display:none;">
            <input type="checkbox" name="save_<?php echo esc_attr( $type ); ?>_address" value="1">
            <?php esc_html_e( 'Save this address for next time', 'stanray-custom' ); ?>
        </label>
    </div>
    <?php
}
add_action( 'woocommerce_before_checkout_billing_form', function() { stanray_render_checkout_address_picker( 'billing' ); } );
add_action( 'woocommerce_before_checkout_shipping_form', function() { stanray_render_checkout_address_picker( 'shipping' ); } );

/**
 * "Save this address for next time" — reads the ORDER's own stored fields
 * (not raw $_POST) so it's guaranteed consistent with what actually saved.
 * Fires on every processed checkout attempt, including ones where payment
 * later fails, so a retry can call this more than once — dedupe before
 * inserting.
 */
function stanray_maybe_save_checkout_address( $order_id, $posted_data, $order ) {
    $user_id = $order->get_customer_id();
    if ( ! $user_id ) return;

    foreach ( [ 'billing', 'shipping' ] as $type ) {
        if ( empty( $_POST[ "save_{$type}_address" ] ) ) continue;

        $fields = [];
        foreach ( stanray_address_field_keys( $type, $order->{"get_{$type}_country"}() ) as $key ) {
            $getter = "get_{$type}_{$key}";
            $fields[ $key ] = method_exists( $order, $getter ) ? $order->$getter() : '';
        }
        if ( empty( $fields['address_1'] ) ) continue;
        if ( stanray_address_already_saved( $user_id, $type, $fields ) ) continue;

        stanray_save_address_from_fields( $user_id, $type, $fields );
    }
}
add_action( 'woocommerce_checkout_order_processed', 'stanray_maybe_save_checkout_address', 10, 3 );
