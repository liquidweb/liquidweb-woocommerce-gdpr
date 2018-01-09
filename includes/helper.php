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

		case 'success-general' :
			return __( 'Your request has been completed.', 'liquidweb-woocommerce-gdpr' );
			break;

		case 'no_option' :
			return __( 'Please select a type of data you would like to export.', 'liquidweb-woocommerce-gdpr' );
			break;

		case 'no_user' :
			return __( 'My eventual error message.', 'liquidweb-woocommerce-gdpr' );
			break;

		case 'no_export_files' :
			return __( 'My eventual error message.', 'liquidweb-woocommerce-gdpr' );
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
 * Get the array of each kind of opt-in box we have.
 *
 * @param  boolean $keys  Whether we want array keys or all of it.
 *
 * @return array
 */
function lw_woo_gdpr_optin_fields( $keys = false ) {

	// Set an array of what we know we need.
	$fields = array(

		// Our item.
		'optin-1'   => array(
			'type'      => 'checkbox',
			'id'        => 'gdpr-optin-1',
			'name'      => 'gdpr-optin[optin-1]',
			'title'     => __( 'Opt In Field 1', 'liquidweb-woocommerce-gdpr' ),
			'label'     => __( 'This is an opt in field', 'liquidweb-woocommerce-gdpr' ),
			'required'  => true,
		),

		// Our item.
		'optin-2'   => array(
			'type'      => 'checkbox',
			'id'        => 'gdpr-optin-2',
			'name'      => 'gdpr-optin[optin-2]',
			'title'     => __( 'Opt In Field 3', 'liquidweb-woocommerce-gdpr' ),
			'label'     => __( 'This is a different opt in field', 'liquidweb-woocommerce-gdpr' ),
			'required'  => false,
		),

		// Our item.
		'optin-3'   => array(
			'type'      => 'checkbox',
			'id'        => 'gdpr-optin-3',
			'name'      => 'gdpr-optin[optin-3]',
			'title'     => __( 'Opt In Field 3', 'liquidweb-woocommerce-gdpr' ),
			'label'     => __( 'This is yet another opt in field', 'liquidweb-woocommerce-gdpr' ),
			'required'  => true,
		),
	);

	// Set the fields with a filter.
	$fields = apply_filters( 'lw_woo_gdpr_optin_fields', $fields );

	// Bail if we have no fields.
	if ( empty( $fields ) ) {
		return false;
	}

	// Return the entire thing or just the keys.
	return ! empty( $keys ) ? array_keys( $fields ) : $fields;
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
			__( 'Author Name', 'liquidweb-woocommerce-gdpr' ),
			__( 'User IP Address', 'liquidweb-woocommerce-gdpr' ),
			__( 'Comment Content', 'liquidweb-woocommerce-gdpr' ),
		),

		// Set our headers for reviews.
		'reviews'   => array(
			__( 'Review Date', 'liquidweb-woocommerce-gdpr' ),
			__( 'Review Time', 'liquidweb-woocommerce-gdpr' ),
			__( 'Author Name', 'liquidweb-woocommerce-gdpr' ),
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
 * Format the orders to our exportable array.
 *
 * @param  array  $orders  The array of order data.
 *
 * @return array
 */
function lw_woo_gdpr_format_orders_export( $orders = array() ) {

	// Set my empty.
	$data  = array();

	// Loop my orders.
	foreach ( $orders as $order_id ) {

		// Grab the order.
		$order  = wc_get_order( $order_id );

		// preprint( $order, true );
		$items  = $order->get_items();
		// preprint( $items, true );

		// Loop my items.
		if ( ! empty( $items ) ) {
			// preprint( $items, true );

			// Set up our initial data set.
			$setup  = array(
				$order->get_order_number(),
				date( 'Y-m-d', strtotime( $order->get_date_created() ) ),
				date( 'H:i:s', strtotime( $order->get_date_created() ) ),
				$order->get_total(),
				$order->get_payment_method_title(),
				count( $items ),
			);
			// preprint( $setup );

			// Set an empty.
			$prod   = array();

			// Loop each one and add it to the end of the array.
			foreach ( $items as $item ) {
				$prod[] = esc_attr( $item->get_name() );
			}

			// And merge our arrays.
			$data[] = wp_parse_args( $prod, $setup );

		} else {

			// Set the data group with our text.
			$data[] = array(
				$order->get_order_number(),
				date( 'Y-m-d', strtotime( $order->get_date_created() ) ),
				date( 'H:i:s', strtotime( $order->get_date_created() ) ),
				$order->get_total(),
				$order->get_payment_method_title(),
				0,
				__( 'No products found', 'liquidweb-woocommerce-gdpr' ),
			);
		}
	}

	// Return my export data.
	return apply_filters( 'lw_woo_gdpr_format_orders_export', $data, $orders );
}

/**
 * Format the comments to our exportable array.
 *
 * @param  array  $comments  The array of comments data.
 *
 * @return array
 */
function lw_woo_gdpr_format_comments_export( $comments = array() ) {

	// Set my empty.
	$data  = array();

	// Loop my orders.
	foreach ( $comments as $comment ) {

		// Make sure we have some text.
		$text   = ! empty( $comment->comment_content ) ? $comment->comment_content : __( 'no content provided', 'liquidweb-woocommerce-gdpr' );
		$text   = str_replace( ',', '\,', $text );

		// Set my data array up.
		$data[] = array(
			date( 'Y-m-d', strtotime( $comment->comment_date ) ),
			date( 'H:i:s', strtotime( $comment->comment_date ) ),
			$comment->comment_author,
			$comment->comment_author_IP,
			esc_attr( $text ),
		);
	}

	// Return my export data.
	return apply_filters( 'lw_woo_gdpr_format_comments_export', $data, $comments );
}

/**
 * Format the reviews to our exportable array.
 *
 * @param  array $reviews  The array of reviews data.
 *
 * @return array
 */
function lw_woo_gdpr_format_reviews_export( $reviews = array() ) {

	// Set my empty.
	$data  = array();

	// Loop my orders.
	foreach ( $reviews as $review ) {

		// Make sure we have some text.
		$text   = ! empty( $review->comment_content ) ? esc_attr( $review->comment_content ) : __( 'no content provided', 'liquidweb-woocommerce-gdpr' );

		// Set my data array up.
		$data[] = array(
			date( 'Y-m-d', strtotime( $review->comment_date ) ),
			date( 'H:i:s', strtotime( $review->comment_date ) ),
			$review->comment_author,
			$review->comment_author_IP,
			get_comment_meta( $review->comment_ID, 'rating', true ),
			esc_attr( $text ),
		);
	}

	// Return my export data.
	return apply_filters( 'lw_woo_gdpr_format_reviews_export', $data, $reviews );
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
	$expiretime = current_time( 'timestamp' ) - absint( $filetime );

	// Return our boolean.
	return absint( $expiretime ) > absint( $expirerate ) ? true : false;
}

/**
 * Create the file links for a specific type.
 *
 * @param  string $single    The file that should be there.
 * @param  string $datatype  Which data type this is for.
 * @param  string $baselink  The base URL for the link.
 *
 * @return void
 */
function lw_woo_gdpr_create_file_links( $single = '', $datatype = '', $baselink = '' ) {

	// Bail without the required parts.
	if ( empty( $datatype ) || empty( $baselink ) ) {
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
	$setup .= '<a title="' . esc_attr( $downtitle ) . '" href="' . esc_url( $downlink ) . '">' . esc_html__( 'Download', 'liquidweb-woocommerce-gdpr' ) . '</a>';
	$setup .= '&nbsp;|&nbsp;';
	$setup .= '<a title="' . esc_attr( $delttitle ) . '" href="' . esc_url( $deltlink ) . '">' . esc_html__( 'Delete', 'liquidweb-woocommerce-gdpr' ) . '</a>';

	// Return the build setup.
	return $setup;
}

/**
 * Create a label.
 *
 * @param  object $user       The user object we are looking at.
 * @param  array  $datatypes  Which data types they have.
 *
 * @return void
 */
function lw_woo_gdpr_create_delete_label( $user, $datatypes = array() ) {

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

		// Check for datatypes.
		if ( ! empty( $datatypes ) ) {

			// Sanitize all the types we have.
			$types  = array_map( 'sanitize_text_field', $datatypes );

			// Now add the list of what they requested.
			$label .= '&nbsp;|&nbsp;<em>' . __( 'Requests', 'liquidweb-woocommerce-gdpr' ) . ':&nbsp;' . implode( ', ', $types ) . '</em>';
		}

	// Close the label wrap.
	$label .= '</label>';

	// Return my entire thing.
	return $label;
}
