<?php
/**
 * Our helper file.
 *
 * Various functions that are used in the plugin.
 *
 * @package LiquidWeb_Woo_GDPR
 */

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
			__( 'Order Total', 'liquidweb-woocommerce-gdpr' ),
			__( 'Payment Method', 'liquidweb-woocommerce-gdpr' ),
			__( 'Purchased Items', 'liquidweb-woocommerce-gdpr' ),
		),

		// Set our headers for comments.
		'comments'  => array(
			__( 'Comment Date', 'liquidweb-woocommerce-gdpr' ),
			__( 'Comment Content', 'liquidweb-woocommerce-gdpr' ),
			__( 'Author Name', 'liquidweb-woocommerce-gdpr' ),
			__( 'User IP Address', 'liquidweb-woocommerce-gdpr' ),
		),

		// Set our headers for reviews.
		'reviews'   => array(
			__( 'Review Date', 'liquidweb-woocommerce-gdpr' ),
			__( 'Review Content', 'liquidweb-woocommerce-gdpr' ),
			__( 'Review Rating', 'liquidweb-woocommerce-gdpr' ),
			__( 'Author Name', 'liquidweb-woocommerce-gdpr' ),
			__( 'User IP Address', 'liquidweb-woocommerce-gdpr' ),
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

		// Set my empty.
		$prods  = array();

		// Loop my items.
		if ( ! empty( $items ) ) {

			// And loop each one.
			foreach ( $items as $item ) {
				$prods[] = $item->get_name();
			}
		}

		// Now explode it or set some empty stuff.
		$prods  = ! empty( $prods ) ? implode( '     ', $prods ) : __( 'No products found', 'liquidweb-woocommerce-gdpr' );

		// Set my data array up.
		$data[] = array(
			$order->get_order_number(),
			date( 'Y-m-d H:i:s', strtotime( $order->get_date_created() ) ),
			$order->get_total(),
			$order->get_payment_method_title(),
			$prods,
		);
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
		$text   = ! empty( $comment->comment_content ) ? esc_attr( $comment->comment_content ) : __( 'no content provided', 'liquidweb-woocommerce-gdpr' );

		// Set my data array up.
		$data[] = array(
			$comment->comment_date,
			$text,
			$comment->comment_author,
			$comment->comment_author_IP,
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
			$review->comment_date,
			$text,
			get_comment_meta( $review->comment_ID, 'rating', true ),
			$review->comment_author,
			$review->comment_author_IP,
		);
	}

	// Return my export data.
	return apply_filters( 'lw_woo_gdpr_format_reviews_export', $data, $reviews );
}

/**
 * Remove one of the data types from the download array.
 *
 * @param  integer $user_id   The user ID we are looking at.
 * @param  string  $datatype  Which of the types we want.
 *
 * @return void
 */
function lw_woo_gdpr_remove_export_file( $user_id = 0, $datatype = '' ) {

	// Bail without our things.
	if ( empty( $user_id ) || empty( $datatype ) ) {
		return;
	}

	// Check for the export files.
	$downloads  = get_user_meta( $user_id, 'woo_gdpr_export_files', true );

	// Return if we have none.
	if ( empty( $downloads ) ) {
		return;
	}

	// Remove it from the array.
	unset( $downloads[ $datatype ] );

	// Make sure it's not got empties.
	$downloads  = array_filter( $downloads );

	// Either update the user meta, or delete it completely.
	if ( ! empty( $downloads ) ) {
		update_user_meta( $user_id, 'woo_gdpr_export_files', $downloads );
	} else {
		delete_user_meta( $user_id, 'woo_gdpr_export_files' );
	}

}
