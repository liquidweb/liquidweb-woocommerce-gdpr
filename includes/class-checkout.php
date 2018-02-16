<?php
/**
 * Our functions related to the checkout.
 *
 * Set up the actions that happen on checkout.
 *
 * @package LiquidWeb_Woo_GDPR
 */

/**
 * Start our engines.
 */
class LW_Woo_GDPR_Checkout {

	/**
	 * Call our hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'woocommerce_review_order_before_submit',           array( $this, 'display_optin_fields'        )           );
		add_filter( 'woocommerce_checkout_posted_data',                 array( $this, 'merge_optin_post_data'       )           );
		add_action( 'woocommerce_after_checkout_validation',            array( $this, 'validate_optin_fields'       ),  10, 2   );
		add_action( 'woocommerce_checkout_update_customer',             array( $this, 'update_customer_optin'       ),  10, 2   );
	}

	/**
	 * Add our new opt-in boxes.
	 *
	 * @return HTML
	 */
	public function display_optin_fields() {

		// Get my fields.
		$fields = lw_woo_gdpr_optin_fields();

		// Bail without my fields.
		if ( empty( $fields ) ) {
			return;
		}

		// Set an empty.
		$build  = '';

		// Loop my fields and set up each one.
		foreach ( $fields as $key => $field ) {

			// Wrap each one in a paragraph.
			$build .= '<p class="form-row wc-gdpr-' . esc_attr( $key ) . '-field">';

				// Handle our field output.
				$build .= LW_Woo_GDPR_Fields::checkbox_field( $field );

			// Close the single paragraph.
			$build .= '</p>';
		}

		// And echo it out.
		echo $build;
	}

	/**
	 * Merge in our posted field data.
	 *
	 * @param  array  $data  The post data that comes by default.
	 *
	 * @return array  $data  The possibly modified posted data.
	 */
	public function merge_optin_post_data( $data ) {

		// Bail if we have no posted data.
		if ( empty( $_POST['gdpr-optin'] ) ) {
			return $data;
		}

		// Loop the posted opt in values.
		foreach ( $_POST['gdpr-optin'] as $key => $value ) {
			$data[ $key ] = $value;
		}

		// And return the modified array.
		return $data;
	}

	/**
	 * Validate the opt-in fields.
	 *
	 * @param  array  $data    The post data that comes by default.
	 * @param  object $errors  The existing error object.
	 *
	 * @return mixed
	 */
	public function validate_optin_fields( $data, $errors ) {

		// Get my fields.
		$fields = lw_woo_gdpr_optin_fields();

		// Bail without my fields.
		if ( empty( $fields ) ) {
			return;
		}

		// Now loop my fields.
		foreach ( $fields as $key => $field ) {

			// If it isn't a required field, skip it.
			if ( empty( $field['required'] ) ) {
				continue;
			}

			// If we have the required opt-in, skip it.
			if ( in_array( $key, array_keys( $data ) ) ) {
				continue;
			}

			// Make sure I have a title.
			$title  = ! empty( $field['title'] ) ? $field['title'] : __( 'Opt In Field', 'liquidweb-woocommerce-gdpr' );

			// Set our error key and message.
			$error_code = 'missing-' . esc_attr( $key );
			$error_text = sprintf( __( 'You did not agree to %s', 'liquidweb-woocommerce-gdpr' ), esc_attr( $title ) );

			// And add my error.
			$errors->add( $error_code, $error_text );
		}

		// And just be done.
		return;
	}

	/**
	 * Validate the opt-in fields.
	 *
	 * @param  object $customer  The WooCommerce customer object.
	 * @param  array  $data      The post data from the order.
	 *
	 * @return void
	 */
	public function update_customer_optin( $customer, $data ) {

		// Bail without data or customer info.
		if ( empty( $customer ) || ! is_object( $customer ) || empty( $data ) || ! is_array( $data ) ) {
			return;
		}

		// Get my fields.
		$fields = lw_woo_gdpr_optin_fields();

		// Bail without my fields.
		if ( empty( $fields ) ) {
			return;
		}

		// Now loop my fields.
		foreach ( $fields as $id => $field ) {

			// Set the meta key using the field name.
			$meta_key   = apply_filters( 'lw_woo_gdpr_optin_meta_name', 'woo-gdrp-' . esc_attr( $id ), $field );

			// Set the value from the posted data, or null if it's missing.
			$meta_value = in_array( $id, array_keys( $data ) ) ? esc_attr( $data[ $id ] ) : null;

			// And add it to the customer object.
			$customer->update_meta_data( $meta_key, $meta_value );

			// Run an action for each individual opt-in.
			if ( ! empty( $field['action'] ) ) {

				// Sanitize the action name.
				$action = sanitize_text_field( $field['action'] );

				// And do the action.
				do_action( $action, $field );
			}
		}

		// And just be done.
		return;
	}

	// End our class.
}

// Call our class.
$LW_Woo_GDPR_Checkout = new LW_Woo_GDPR_Checkout();
$LW_Woo_GDPR_Checkout->init();
