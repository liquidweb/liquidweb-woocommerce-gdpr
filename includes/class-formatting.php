<?php
/**
 * Our formatting class to handle encoding, escaping, etc.
 *
 * @package LiquidWeb_Woo_GDPR
 */

/**
 * Start our engines.
 */
class LW_Woo_GDPR_Formatting {

	/**
	 * Run our individual strings through some clean up.
	 *
	 * @param  string $string  The data we wanna clean up.
	 *
	 * @return string
	 */
	public static function clean_export( $string ) {

		// Original PHP code by Chirp Internet: www.chirp.com.au
		// Please acknowledge use of this code by including this header.

		// Handle my different string checks.
		switch ( $string ) {

			case 't':
				$string = 'TRUE';
				break;

			case 'f':
				$string = 'FALSE';
				break;

			case preg_match( "/^0/", $string ):
			case preg_match( "/^\+?\d{8,}$/", $string ):
			case preg_match( "/^\d{4}.\d{1,2}.\d{1,2}/", $string ):
				$string = "'$string";
				break;

			case strstr( $string, '"' ):
				$string = '"' . str_replace( '"', '""', $string ) . '"';
				break;

			default:
				$string = mb_convert_encoding( $string, 'UTF-16LE', 'UTF-8' );

			// End all case breaks.
		}
	}

	/**
	 * Format the orders to our exportable array.
	 *
	 * @param  array  $orders  The array of order data.
	 *
	 * @return array
	 */
	public static function orders_export( $orders = array() ) {

		// Set my empty.
		$data  = array();

		// Loop my orders.
		foreach ( $orders as $order_id ) {

			// Grab the order.
			$order  = wc_get_order( $order_id );

			// Fetch the items inside the order.
			$items  = $order->get_items();

			// Loop my items.
			if ( ! empty( $items ) ) {

				// Set up our initial data set.
				$setup  = array(
					$order->get_order_number(),
					date( 'Y-m-d', strtotime( $order->get_date_created() ) ),
					date( 'H:i:s', strtotime( $order->get_date_created() ) ),
					$order->get_total(),
					$order->get_payment_method_title(),
					count( $items ),
				);

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
	public static function comments_export( $comments = array() ) {

		// Set my empty.
		$data  = array();

		// Loop my orders.
		foreach ( $comments as $comment ) {

			// Make sure we have some text.
			$text   = ! empty( $comment->comment_content ) ? $comment->comment_content : __( 'no content provided', 'liquidweb-woocommerce-gdpr' );

			// Set my data array up.
			$data[] = array(
				date( 'Y-m-d', strtotime( $comment->comment_date ) ),
				date( 'H:i:s', strtotime( $comment->comment_date ) ),
				get_the_title( $comment->comment_post_ID ),
				get_the_permalink( $comment->comment_post_ID ),
				$comment->comment_author,
				$comment->comment_author_email,
				$comment->comment_author_IP,
				htmlspecialchars_decode( $text, ENT_NOQUOTES ),
				// esc_attr( $text ),
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
	public static function reviews_export( $reviews = array() ) {

		// Set my empty.
		$data  = array();

		// Loop my orders.
		foreach ( $reviews as $review ) {

			// Make sure we have some text.
			$text   = ! empty( $review->comment_content ) ? $review->comment_content : __( 'no content provided', 'liquidweb-woocommerce-gdpr' );

			// Set my data array up.
			$data[] = array(
				date( 'Y-m-d', strtotime( $review->comment_date ) ),
				date( 'H:i:s', strtotime( $review->comment_date ) ),
				get_the_title( $review->comment_post_ID ),
				get_the_permalink( $review->comment_post_ID ),
				$review->comment_author,
				$review->comment_author_email,
				$review->comment_author_IP,
				get_comment_meta( $review->comment_ID, 'rating', true ),
				htmlspecialchars_decode( $text, ENT_NOQUOTES ),
				// esc_attr( $text ),
			);
		}

		// Return my export data.
		return apply_filters( 'lw_woo_gdpr_format_reviews_export', $data, $reviews );
	}

	/**
	 * Take the existing opt-in fields and create our full data array.
	 *
	 * @param  array $existing  The existing args saved by the user.
	 *
	 * @return array $data      The new data array.
	 */
	public static function format_current_optin_fields( $current = array() ) {

		// Make sure we have stuff.
		if ( empty( $current ) ) {
			return false; // @@todo some error checking
		}

		// Set my empty.
		$data   = array();

		// Loop my existing items and reconstruct the array.
		foreach ( $current as $id => $args ) {

			// Sanitize the args in one fell swoop.
			$setup  = array_filter( $args, 'sanitize_text_field' );

			// Make sure we have an action.
			$req    = ! empty( $args['required'] ) ? true : false;
			$action = ! empty( $args['action'] ) ? esc_attr( $args['action'] ) : lw_woo_gdpr_make_action_key( $id );

			// Build the data array.
			$build  = array(
				'id'        => $id,
				'action'    => $action,
				'title'     => esc_attr( $setup['title'] ),
				'label'     => esc_attr( $setup['label'] ),
				'required'  => $req,
			);

			// And add it to our data.
			$data[ $id ] = $build;
		}

		// Return the whole thing, or cleaned up.
		return ! empty( $data ) ? $data : false;
	}

	/**
	 * Take a new opt-in field and create our full data array.
	 *
	 * @param  array   $args   The args saved by the user.
	 * @param  boolean $merge  Whether to merge the existing fields.
	 *
	 * @return array         The new data array.
	 */
	public static function format_new_optin_field( $new_args = array(), $merge = false ) {

		// Check the required title and label.
		if ( empty( $new_args['title'] ) || empty( $new_args['label'] ) ) {
			return false; // @@todo some error checking
		}

		// Sanitize the args in one fell swoop.
		$filter = array_filter( $new_args, 'sanitize_text_field' );

		// Parse out the rest.
		$id     = sanitize_title_with_dashes( $filter['title'], '', 'save' );
		$action = lw_woo_gdpr_make_action_key( $id );
		$req    = ! empty( $filter['required'] ) ? true : false;

		// Build the constructed data array.
		$setup  = array(
			'id'        => $id,
			'action'    => $action,
			'title'     => esc_attr( $filter['title'] ),
			'label'     => esc_attr( $filter['label'] ),
			'required'  => $req,
		);

		// If we didn't request the merge, return our setup.
		if ( empty( $merge ) ) {
			return $setup;
		}

		// Set up the new item.
		$update = array( $id => $setup );

		// Pull our saved items.
		$saved  = get_option( 'lw_woo_gdpr_optin_fields', array() );

		// And return the merged array.
		return wp_parse_args( $update, $saved );
	}

	// End our class.
}

// Call our class.
new LW_Woo_GDPR_Formatting();
