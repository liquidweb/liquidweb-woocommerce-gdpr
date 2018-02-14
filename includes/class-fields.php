<?php
/**
 * Fields setup.
 *
 * Setting up all the various field types.
 *
 * @package LiquidWeb_Woo_GDPR
 */

/**
 * Start our engines.
 */
class LW_Woo_GDPR_Fields {

	/**
	 * A checkbox input.
	 *
	 * @param  array   $args   The field args I passed.
	 * @param  boolean $echo   Whether to echo the field or return it.
	 *
	 * @return  HTML
	 */
	public static function checkbox_field( $args = array(), $echo = false ) {

		// Remove our type.
		unset( $args['type'] );

		// Set my default args.
		$base   = array(
			'id'        => microtime(),
			'required'  => 0,
		);

		// Parse my args.
		$args   = wp_parse_args( $args, $base );

		// Make sure I have a value to enter.
		$value  = ! empty( $args['value'] ) ? $args['value'] : 1;

		// Add a required check for the markup.
		$reqrd  = ! empty( $args['required'] ) ? 'required="required"' : '';

		// Set an empty.
		$field  = '';

		// Start the label setup.
		$field .= '<label class="woocommerce-form__label woocommerce-form-' . esc_attr( $args['id'] ) . '__label woocommerce-form__label-for-checkbox checkbox">';

			// Set the input box.
			$field .= '<input class="woocommerce-form__input woocommerce-form-' . esc_attr( $args['id'] ) . '__input-checkbox woocommerce-form__input-checkbox input-checkbox" name="gdpr-optin[' . esc_attr( $args['id'] ) . ']" id="' . esc_attr( $args['id'] ) . '" type="checkbox" value="' . esc_attr( $value ) . '" ' . $reqrd . '>';

			// Add the label text if present.
			if ( ! empty( $args['label'] ) ) {
				$field .= '<span>' . esc_html( $args['label'] ) . '</span>';
			}

			// Add the required flag if present.
			if ( ! empty( $args['required'] ) ) {
				$field .= ' <span class="required">*</span>';
			}

		// And close the tag.
		$field .= '</label>';

		// Echo it if requested.
		if ( ! empty( $echo ) ) {
			echo $field;
		}

		// Just return it.
		return $field;
	}

	// End our class.
}

// Call our class.
new LW_Woo_GDPR_Fields();

