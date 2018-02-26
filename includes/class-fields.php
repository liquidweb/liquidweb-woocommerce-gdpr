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
			'checked'   => false
		);

		// Parse my args.
		$args   = wp_parse_args( $args, $base );

		// Make sure I have a value to enter.
		$value  = ! empty( $args['value'] ) ? $args['value'] : 1;

		// Set my field name if none was passed.
		$name   = ! empty( $args['name'] ) ? $args['name'] : 'gdpr-optin[' . esc_attr( $args['id'] ) . ']';

		// Add a required check for the markup.
		$reqrd  = ! empty( $args['required'] ) ? 'required="required"' : '';

		// Set an empty.
		$field  = '';

		// Start the label setup.
		$field .= '<label class="woocommerce-form__label woocommerce-form-' . esc_attr( $args['id'] ) . '__label woocommerce-form__label-for-checkbox checkbox">';

			// Set the input box.
			$field .= '<input class="woocommerce-form__input woocommerce-form-' . esc_attr( $args['id'] ) . '__input-checkbox woocommerce-form__input-checkbox input-checkbox" name="' . esc_attr( $name ) . '" id="' . esc_attr( $args['id'] ) . '" type="checkbox" value="' . esc_attr( $value ) . '" ' . checked( $args['checked'], $value, false ) . ' ' . $reqrd . '>';

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

	/**
	 * The individual table rows.
	 *
	 * @param  array   $args   The field args I passed.
	 * @param  boolean $echo   Whether to echo the field or return it.
	 *
	 * @return  HTML
	 */
	public static function table_row( $args = array(), $echo = false ) {

		// Create my name field and confirm the action name.
		$name   = 'gdpr-optin-current[' . esc_attr( $args['id'] ) . ']';
		$check  = ! empty( $args['required'] ) ? true : false;
		$action = ! empty( $args['action'] ) ? $args['action'] : lw_woo_gdpr_make_action_key( $args['id'] );

		// Set our delete link.
		$d_nonc = wp_create_nonce( 'lw_woo_optin_single_' . esc_attr( $args['id'] )  );
		$d_args = array( 'gdpr-single-delete' => 1, 'gdpr-field-id' => esc_attr( $args['id'] ), 'nonce' => $d_nonc );
		$delete = add_query_arg( $d_args, lw_woo_gdpr()->get_settings_tab_link() );

		// Set the data attributes for the Ajax call.
		$d_atrb = ' data-field-id="' . esc_attr( $args['id'] ) . '" data-nonce="' . $d_nonc . '"';

		// Set an empty.
		$field  = '';

		// Set up the single div.
		$field .= '<tr data-key="' . esc_attr( $args['id'] ) . '" id="lw-woo-gdpr-field-' . esc_attr( $args['id'] ) . '" class="lw-woo-gdpr-field-single">';

			// Output the required checkbox.
			$field .= '<td class="lw-woo-gdpr-field-item lw-woo-gdpr-field-required">';
				$field .= '<input type="checkbox" class="lw-woo-gdpr-field-input" name="' . esc_attr( $name ) . '[required]" value="1" ' . checked( $check, 1, false ) . '>';
			$field .= '</td>';

			// Output the title field.
			$field .= '<td class="lw-woo-gdpr-field-item lw-woo-gdpr-field-title">';
				$field .= '<input type="text" class="widefat lw-woo-gdpr-field-input" name="' . esc_attr( $name ) . '[title]" value="' . esc_attr( $args['title'] ) . '">';
			$field .= '</td>';

			// Output the label field.
			$field .= '<td class="lw-woo-gdpr-field-item lw-woo-gdpr-field-label">';
				$field .= '<input type="text" class="widefat lw-woo-gdpr-field-input" name="' . esc_attr( $name ) . '[label]" value="' . esc_attr( $args['label'] ) . '">';
			$field .= '</td>';

			// Output the hook name field.
			$field .= '<td class="lw-woo-gdpr-field-item lw-woo-gdpr-field-hook">';
				$field .= '<input type="text" class="widefat code lw-woo-gdpr-field-input" readonly="readonly" value="' . esc_attr( $action ) . '">';
				$field .= '<input type="hidden" name="' . esc_attr( $name ) . '[action]" value="' . esc_attr( $action ) . '">';
			$field .= '</td>';

			// Output the trigger field.
			$field .= '<td class="lw-woo-gdpr-field-item lw-woo-gdpr-field-trigger">';

				// Handle the trash trigger.
				$field .= '<a class="lw-woo-gdpr-field-trigger-item lw-woo-gdpr-field-trigger-trash" href="' . esc_url( $delete ) . '" ' . $d_atrb . ' >';
					$field .= '<i class="lw-woo-gdpr-trigger-icon dashicons dashicons-trash"></i>';
				$field .= '</a>';

				// Handle the sort trigger.
				$field .= '<a class="lw-woo-gdpr-field-trigger-item lw-woo-gdpr-field-trigger-sort hide-if-no-js" href="">';
					$field .= '<i class="lw-woo-gdpr-trigger-icon dashicons dashicons-sort"></i>';
				$field .= '</a>';

			$field .= '</td>';

		// Close the single div.
		$field .= '</tr>';

		// Echo it if requested.
		if ( ! empty( $echo ) ) {
			echo $field;
		}

		// Just return it.
		return $field;
	}

	/**
	 * Get the actual list markup of the statuses.
	 *
	 * @param  array   $fields   The field data we have to render.
	 * @param  integer $user_id  The user ID that is being viewed.
	 * @param  boolean $echo     Whether to echo or return it.
	 *
	 * @return HTML
	 */
	public static function get_optin_status_list( $fields = array(), $user_id = 0, $echo = false ) {

		// Bail without fields or a user ID.
		if ( empty( $fields ) || empty( $user_id ) ) {
			return;
		}

		// Set our empty.
		$build  = '';

		// Loop my fields to display.
		foreach ( $fields as $key => $field ) {

			// Check the status.
			$status = get_user_meta( $user_id, 'woo_gdrp_' . $key, true );
			$check  = ! empty( $status ) ? true : false;

			// Set the text accordingly.
			$text   = ! empty( $status ) ? sprintf( __( 'You have opted in to %s', 'liquidweb-woocommerce-gdpr' ), esc_attr( $field['title'] ) ) : sprintf( __( 'You have not opted in to %s', 'liquidweb-woocommerce-gdpr' ), esc_attr( $field['title'] ) );

			// Set new field args.
			$new_field_args = array(
				'name'      => 'lw_woo_gdpr_changeopt_items[' . esc_attr( $field['id'] ) . ']',
				'label'     => wp_kses_post( $text ),
				'required'  => false,
				'checked'   => $check,
			);

			// Merge my new args.
			$field  = wp_parse_args( $new_field_args, $field );

			// Open up our list item.
			$build .= '<li class="lw-woo-gdpr-data-option lw-woo-gdpr-optin-list-item">';

				// Include the actual checkbox.
				$build .= self::checkbox_field( $field );

			// Close the list item.
			$build .= '</li>';
		}

		// Echo if requested.
		if ( ! empty( $echo ) ) {
			echo $build;
		}

		// Return our build.
		return $build;
	}

	// End our class.
}

// Call our class.
new LW_Woo_GDPR_Fields();

