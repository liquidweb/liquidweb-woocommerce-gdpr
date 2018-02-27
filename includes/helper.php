<?php
/**
 * Our helper file.
 *
 * Various functions that are used in the plugin.
 *
 * @package LiquidWeb_Woo_GDPR
 */

/**
 * Check an code and (usually an error) return the appropriate text.
 *
 * @param  string $code  The code provided.
 *
 * @return string
 */
function lw_woo_gdpr_notice_text( $code = '' ) {

	// Return if we don't have an error code.
	if ( empty( $code ) ) {
		return __( 'There was an error with your request.', 'liquidweb-woocommerce-gdpr' );
	}

	// Handle my different error codes.
	switch ( esc_attr( strtolower( $code ) ) ) {

		case 'success-deleteme' :
			return __( 'Your data deletion request has been registered.', 'liquidweb-woocommerce-gdpr' );
			break;

		case 'success-delete' :
			return __( 'The requested export file has been deleted.', 'liquidweb-woocommerce-gdpr' );
			break;

		case 'success-export' :
			return __( 'The requested data export completed and is ready to download.', 'liquidweb-woocommerce-gdpr' );
			break;

		case 'success-removed' :
			return __( 'The requested field has been removed.', 'liquidweb-woocommerce-gdpr' );
			break;

		case 'success-changeopts' :
			return __( 'Your opt-in choices have been updated.', 'liquidweb-woocommerce-gdpr' );
			break;

		case 'success-general' :
		case 'success' :
			return __( 'Your request has been completed.', 'liquidweb-woocommerce-gdpr' );
			break;

		case 'no_option' :
			return __( 'Please select a type of data you would like to export.', 'liquidweb-woocommerce-gdpr' );
			break;

		case 'no_user' :
			return __( 'My eventual error message.', 'liquidweb-woocommerce-gdpr' );
			break;

		case 'no_export_files' :
			return __( 'There was no available data to be exported.', 'liquidweb-woocommerce-gdpr' );
			break;

		case 'no_export_type_file' :
			return __( 'My eventual error message.', 'liquidweb-woocommerce-gdpr' );
			break;

		case 'invalid_action_request' :
			return __( 'My eventual error message.', 'liquidweb-woocommerce-gdpr' );
			break;

		case 'no_datatype' :
			return __( 'My eventual error message.', 'liquidweb-woocommerce-gdpr' );
			break;

		case 'invalid_datatype' :
			return __( 'My eventual error message.', 'liquidweb-woocommerce-gdpr' );
			break;

		case 'no-users' :
			return __( 'No users were selected.', 'liquidweb-woocommerce-gdpr' );
			break;

		case 'missing-nonce' :
			return __( 'The required nonce was missing.', 'liquidweb-woocommerce-gdpr' );
			break;

		case 'bad-nonce' :
			return __( 'The required nonce was invalid.', 'liquidweb-woocommerce-gdpr' );
			break;

		case 'invalid-nonce' :
			return __( 'The required nonce was missing or invalid.', 'liquidweb-woocommerce-gdpr' );
			break;

		case 'missing-title' :
			return __( 'The title field is required.', 'liquidweb-woocommerce-gdpr' );
			break;

		case 'missing-label' :
			return __( 'The label field is required.', 'liquidweb-woocommerce-gdpr' );
			break;

		case 'missing-user-id' :
			return __( 'The user ID could not be determined.', 'liquidweb-woocommerce-gdpr' );
			break;

		case 'bad-field-id' :
			return __( 'The ID of the field could not be determined.', 'liquidweb-woocommerce-gdpr' );
			break;

		case 'unknown' :
		case 'unknown_error' :
			return __( 'There was an unknown error with your request.', 'liquidweb-woocommerce-gdpr' );
			break;

		default :
			return __( 'There was an error with your request.', 'liquidweb-woocommerce-gdpr' );

		// End all case breaks.
	}
}

/**
 * Our default values, which will seed the settings tab.
 *
 * @return array
 */
function lw_woo_gdpr_optin_defaults() {

	// Set an array of what we know we need.
	$fields = array(

		// Our general contact list item.
		'general-contact' => array(
			'type'      => 'checkbox',
			'id'        => 'general-contact',
			'action'    => 'lw_woo_gdpr_general_contact_optin',
			'title'     => __( 'General Contact', 'liquidweb-woocommerce-gdpr' ),
			'label'     => __( 'You may contact me regarding my order or account.', 'liquidweb-woocommerce-gdpr' ),
			'required'  => false,
		),

		// Our mailing list item.
		'mailing-list' => array(
			'type'      => 'checkbox',
			'id'        => 'mailing-list',
			'action'    => 'lw_woo_gdpr_mailing_list_optin',
			'title'     => __( 'Mailing List', 'liquidweb-woocommerce-gdpr' ),
			'label'     => __( 'You may include me on your mailing list.', 'liquidweb-woocommerce-gdpr' ),
			'required'  => false,
		),
	);

	// Do our check for the "terms and conditions" setting.
	$tc = get_option( 'woocommerce_terms_page_id', 0 );

	// Include one if we haven't turned it on.
	if ( empty( $tc ) ) {

		// Add our terms and conditions. But people should have their own.
		$fields['terms-conditions'] = array(
			'type'      => 'checkbox',
			'id'        => 'terms-conditions',
			'action'    => 'lw_woo_gdpr_terms_conditions_optin',
			'title'     => __( 'Terms and Conditions', 'liquidweb-woocommerce-gdpr' ),
			'label'     => __( 'I have read and understand the terms and conditions.', 'liquidweb-woocommerce-gdpr' ),
			'required'  => true,
		);
	}

	// Set the fields with a filter.
	$fields = apply_filters( 'lw_woo_gdpr_optin_field_defaults', $fields );

	// Bail if we have no fields.
	return ! empty( $fields ) ? $fields : false;
}

/**
 * Get the array of each kind of opt-in box we have.
 *
 * @param  boolean $keys  Whether we want array keys or all of it.
 *
 * @return array
 */
function lw_woo_gdpr_optin_fields( $keys = false ) {

	// Fetch our fields.
	$fields = get_option( 'lw_woo_gdpr_optin_fields', lw_woo_gdpr_optin_defaults() );

	// Bail if we have no fields.
	if ( empty( $fields ) ) {
		return false;
	}

	// Return the entire thing or just the keys.
	return ! empty( $keys ) ? array_keys( $fields ) : $fields;
}

/**
 * A simple function to return an array of the excluded Woo types.
 *
 * @param  string $post_type  If we want to compare a single type against it.
 *
 * @return array
 */
function lw_woo_gdpr_excluded_post_types( $post_type = '' ) {

	// Set an array of what we know we need.
	$types  = array(
		'product',
		'product_variation',
		'product_visibility',
		'shop_order',
		'shop_coupon',
		'shop_webhook',
	);

	// Set the types with a filter.
	$types  = apply_filters( 'lw_woo_gdpr_excluded_post_types', $types );

	// Return the entire array if we didn't pass a type.
	if ( empty( $post_type ) ) {
		return $types;
	}

	// Return whether it's in the type or not.
	return in_array( $post_type, $types ) ? true : false;
}

/**
 * Get the array of each kind of export types we have.
 *
 * @param  boolean $keys  Whether we want array keys or all of it.
 *
 * @return array
 */
function lw_woo_gdpr_export_types( $keys = false ) {

	// Set an array of what we know we need.
	$types  = array(
		'orders'    => __( 'Orders', 'liquidweb-woocommerce-gdpr' ),
		'comments'  => __( 'Comments', 'liquidweb-woocommerce-gdpr' ),
		'reviews'   => __( 'Reviews', 'liquidweb-woocommerce-gdpr' ),
	);

	// Set the types with a filter.
	$types  = apply_filters( 'lw_woo_gdpr_export_types', $types );

	// Bail if we have no types.
	if ( empty( $types ) ) {
		return false;
	}

	// Return the entire thing or just the keys.
	return ! empty( $keys ) ? array_keys( $types ) : $types;
}

/**
 * Get the array of each kind of export types we have.
 *
 * @param  string $type  Which type we want (or all of them).
 *
 * @return array
 */
function lw_woo_gdpr_export_headers( $type = '' ) {

	// Set an array of what we know we need.
	$items  = array(

		// Set our headers for orders.
		'orders'    => array(
			__( 'Order Number', 'liquidweb-woocommerce-gdpr' ),
			__( 'Order Date', 'liquidweb-woocommerce-gdpr' ),
			__( 'Order Time', 'liquidweb-woocommerce-gdpr' ),
			__( 'Order Total', 'liquidweb-woocommerce-gdpr' ),
			__( 'Payment Method', 'liquidweb-woocommerce-gdpr' ),
			__( 'Total Items', 'liquidweb-woocommerce-gdpr' ),
			__( 'Purchased Items', 'liquidweb-woocommerce-gdpr' ),
		),

		// Set our headers for comments.
		'comments'  => array(
			__( 'Comment Date', 'liquidweb-woocommerce-gdpr' ),
			__( 'Comment Time', 'liquidweb-woocommerce-gdpr' ),
			__( 'Original Source Title', 'liquidweb-woocommerce-gdpr' ),
			__( 'Original Source URL', 'liquidweb-woocommerce-gdpr' ),
			__( 'Author Name', 'liquidweb-woocommerce-gdpr' ),
			__( 'Author Email', 'liquidweb-woocommerce-gdpr' ),
			__( 'User IP Address', 'liquidweb-woocommerce-gdpr' ),
			__( 'Comment Content', 'liquidweb-woocommerce-gdpr' ),
		),

		// Set our headers for reviews.
		'reviews'   => array(
			__( 'Review Date', 'liquidweb-woocommerce-gdpr' ),
			__( 'Review Time', 'liquidweb-woocommerce-gdpr' ),
			__( 'Product Source Title', 'liquidweb-woocommerce-gdpr' ),
			__( 'Product Source URL', 'liquidweb-woocommerce-gdpr' ),
			__( 'Author Name', 'liquidweb-woocommerce-gdpr' ),
			__( 'Author Email', 'liquidweb-woocommerce-gdpr' ),
			__( 'User IP Address', 'liquidweb-woocommerce-gdpr' ),
			__( 'Review Rating', 'liquidweb-woocommerce-gdpr' ),
			__( 'Review Content', 'liquidweb-woocommerce-gdpr' ),
		),
	);

	// Set the items with a filter.
	$items  = apply_filters( 'lw_woo_gdpr_export_headers', $items );

	// Bail if we have no items or don't have the particular one.
	if ( empty( $items ) || ! empty( $type ) && ! isset( $items[ $type ] ) ) {
		return false;
	}

	// Return the entire thing or a piece.
	return ! empty( $type ) ? $items[ $type ] : $items;
}

/**
 * Remove one of the data types from the download array.
 *
 * @param  string  $userfile  The specific file we are checking.
 * @param  integer $filetime  The file creation time.
 *
 * @return boolean
 */
function lw_woo_gdpr_check_export_file( $userfile = '', $filetime = 0 ) {

	// Bail if this isn't expired.
	if ( empty( $userfile ) || ! is_file( $userfile ) ) {
		return false;
	}

	// Make sure we have a file time to compare against.
	$filetime   = ! empty( $filetime ) ? $filetime : filemtime( $userfile );

	// Now set the expiration rate.
	$expirerate = apply_filters( 'lw_woo_gdpr_file_expire', ( WEEK_IN_SECONDS * 2 ) );

	// See how long we have on the file expiration.
	$expiretime = current_time( 'timestamp', 1 ) - absint( $filetime );

	// Return our boolean.
	return absint( $expiretime ) > absint( $expirerate ) ? true : false;
}

/**
 * Create the file links for a specific type.
 *
 * @param  string  $single    The file that should be there.
 * @param  string  $datatype  Which data type this is for.
 * @param  string  $baselink  The base URL for the link.
 * @param  integer $user_id   The user ID we are making.
 *
 * @return void
 */
function lw_woo_gdpr_create_file_links( $single = '', $datatype = '', $baselink = '', $user_id = 0 ) {

	// Bail without the required parts.
	if ( empty( $datatype ) || empty( $baselink ) || empty( $user_id ) ) {
		return;
	}

	// Return some basic text if we don't have the file.
	if ( empty( $single ) ) {

		// Trim off the S at the end.
		$notplural  = rtrim( $datatype, 's' );

		// And return the text.
		return '<em>' . sprintf( __( 'There is no %s data available to export.', 'liquidweb-woocommerce-gdpr' ), esc_attr( $notplural ) ) . '</em>';
	}

	// Make my download and delete links.
	$downlink   = add_query_arg( array( 'data-type' => $datatype, 'gdpr-action' => 'download' ), $baselink );
	$deltlink   = add_query_arg( array( 'data-type' => $datatype, 'gdpr-action' => 'delete' ), $baselink );

	// Set my link titles.
	$downtitle  = sprintf( __( 'Click here to download the %s file', 'liquidweb-woocommerce-gdpr' ), esc_attr( $datatype ) );
	$delttitle  = sprintf( __( 'Click here to delete the %s file', 'liquidweb-woocommerce-gdpr' ), esc_attr( $datatype ) );

	// Set an empty.
	$setup  = '';

	// Build the links themselves.
	$setup .= '<a class="lw-woo-gdpr-option-link lw-woo-gdpr-download-link" data-user-id="' . absint( $user_id ) . '" data-type="' . esc_attr( $datatype ) . '" title="' . esc_attr( $downtitle ) . '" href="' . esc_url( $downlink ) . '">' . esc_html__( 'Download', 'liquidweb-woocommerce-gdpr' ) . '</a>';
	$setup .= '&nbsp;|&nbsp;';
	$setup .= '<a class="lw-woo-gdpr-option-link lw-woo-gdpr-delete-link" data-user-id="' . absint( $user_id ) . '" data-type="' . esc_attr( $datatype ) . '" data-nonce="' . wp_create_nonce( 'lw_woo_gdpr_delete_file' ) . '" title="' . esc_attr( $delttitle ) . '" href="' . esc_url( $deltlink ) . '">' . esc_html__( 'Delete', 'liquidweb-woocommerce-gdpr' ) . '</a>';

	// Return the build setup.
	return $setup;
}

/**
 * Create a label for our user delete request.
 *
 * @param  object $user          The user object we are looking at.
 * @param  array  $request_data  The data contained in the request.
 *
 * @return void
 */
function lw_woo_gdpr_create_delete_label( $user, $request_data = array() ) {

	// Bail without our things.
	if ( empty( $user ) || ! is_object( $user ) ) {
		return;
	}

	// Create my empty.
	$label  = '';

	// First wrap it up.
	$label .= '<label for="delete-option-' . absint( $user->ID ) . '">';

		// First start with the actual display name.
		$label .= '<span class="delete-option-name">' . esc_attr( $user->display_name ) . '</span>';

		// Add an edit link.
		$label .= '<a target="_blank" href="' . get_edit_user_link( $user->ID ) . '">' . __( 'View Profile', 'liquidweb-woocommerce-gdpr' ) . '</a>';

		// Check for request data.
		if ( ! empty( $request_data['datatypes'] ) ) {

			// Sanitize all the types we have.
			$types  = array_map( 'sanitize_text_field', $request_data['datatypes'] );

			// Now add the list of what they requested.
			$label .= '&nbsp;|&nbsp;<em>' . __( 'Requests', 'liquidweb-woocommerce-gdpr' ) . ':&nbsp;' . implode( ', ', $types ) . '</em>';
		}

	// Close the label wrap.
	$label .= '</label>';

	// Return my entire thing.
	return $label;
}

/**
 * Run a check to see if there are pending requests.
 *
 * @param  string $return  How to return it. Either the data or a boolean.
 *
 * @return mixed
 */
function lw_woo_gdpr_maybe_requests_exist( $return = 'data' ) {

	// First grab my requests.
	$requests   = get_option( 'lw_woo_gdrp_delete_requests', array() );

	// Return false if we have none.
	if ( empty( $requests ) ) {
		return false;
	}

	// Return one or the other.
	return 'boolean' === sanitize_text_field( $return ) ? true : $requests;
}
