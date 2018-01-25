<?php
/**
 * Data setup.
 *
 * Setting up all the various data queries.
 *
 * @package LiquidWeb_Woo_GDPR
 */

/**
 * Start our engines.
 */
class LW_Woo_GDPR_Data {

	/**
	 * Get all the orders for a particular user.
	 *
	 * @param  integer $user_id  The user ID we want to check.
	 * @param  boolean $format   Whether to format the results or not.
	 *
	 * @return array
	 */
	public static function get_orders_for_user( $user_id = 0, $format = true ) {

		// Bail without a user ID.
		if ( empty( $user_id ) ) {
			return;
		}

		// Set my args for the query.
		$args   = array(
			'fields'      => 'ids',
			'nopaging'    => true,
			'post_type'   => wc_get_order_types(),
			'post_status' => array_keys( wc_get_order_statuses() ),
			'meta_key'    => '_customer_user',
			'meta_value'  => absint( $user_id ),
			'order'       => 'ASC',
			'orderby'     => 'meta_value_num',
			'meta_key'    => '_date_completed',
			'meta_query'  => array(
				array(
					'key'     => '_date_completed',
					'compare' => 'EXISTS',
					'type'    => 'numeric',
				),
			),
		);

		// Now fetch my orders.
		$orders = get_posts( $args );

		// Bail without orders.
		if ( empty( $orders ) || is_wp_error( $orders ) ) {
			return false;
		}

		// Format my orders and return them.
		return ! empty( $format ) ? LW_Woo_GDPR_Formatting::orders_export( $orders ) : $orders;
	}

	/**
	 * Get all the comments for a particular user.
	 *
	 * @param  integer $user_id  The user ID we want to check.
	 * @param  boolean $format   Whether to format the results or not.
	 * @param  boolean $ids      Whether we just want IDs or not.
	 *
	 * @return array
	 */
	public static function get_comments_for_user( $user_id = 0, $format = true, $ids = false ) {

		// Bail without a user ID.
		if ( empty( $user_id ) ) {
			return;
		}

		// Now fetch my comments.
		$items  = get_comments( array( 'user_id' => absint( $user_id ), 'order' => 'ASC' ) );

		// Bail without comments.
		if ( empty( $items ) || is_wp_error( $items ) ) {
			return false;
		}

		// Loop to unset the reviews.
		foreach ( $items as $id => $comment ) {

			// Skip anything specific to WooCommerce.
			if ( false === $check = lw_woo_gdpr_excluded_post_types( get_post_type( $comment->comment_post_ID ) ) ) {
				continue;
			}

			// Unset this comment from the array.
			unset( $items[ $id ] );
		}

		// Bail if there are no actual comments left.
		if ( empty( $items ) ) {
			return false;
		}

		// If we requested IDs only, return that since we don't format anything.
		if ( ! empty( $ids ) ) {
			return wp_list_pluck( $items, 'comment_ID' );
		}

		// Format my comments and return them.
		return ! empty( $format ) ? LW_Woo_GDPR_Formatting::comments_export( $items ) : $items;
	}

	/**
	 * Get all the reviews for a particular user.
	 *
	 * @param  integer $user_id  The user ID we want to check.
	 * @param  boolean $format   Whether to format the results or not.
	 * @param  boolean $ids      Whether we just want IDs or not.
	 *
	 * @return array
	 */
	public static function get_reviews_for_user( $user_id = 0, $format = true, $ids = false ) {

		// Bail without a user ID.
		if ( empty( $user_id ) ) {
			return;
		}

		// Now fetch my reviews.
		$items  = get_comments( array( 'user_id' => absint( $user_id ), 'order' => 'ASC', 'post_type' => 'product' ) );

		// Bail without reviews.
		if ( empty( $items ) || is_wp_error( $items ) ) {
			return false;
		}

		// If we requested IDs only, return that since we don't format anything.
		if ( ! empty( $ids ) ) {
			return wp_list_pluck( $items, 'comment_ID' );
		}

		// Format my reviews and return them.
		return ! empty( $format ) ? LW_Woo_GDPR_Formatting::reviews_export( $items ) : $items;
	}

	/**
	 * Build a dataset for the randomized user.
	 *
	 * @param  integer $user_id  The user ID we want to check.
	 * @param  string  $key      Optional single part of the array data.
	 *
	 * @return array
	 */
	public static function get_random_userdata( $user_id = 0, $key = '' ) {

		// Allow other plugins or themes to totally bypass how that array is built.
		do_action( 'lw_woo_gdpr_get_random_userdata', $user_id, $key );

		// Create my email address.
		$first  = self::get_random_from_file( 'first-names' );
		$last   = self::get_random_from_file( 'last-names' );
		$email  = wp_generate_password( 13, false, false ) . '@example.com';
		$street = rand( 12, 9999 ) . ' ' . self::get_random_from_file( 'street-names' );

		// Build our data array.
		$data   = array(
			'first'   => $first,
			'last'    => $last,
			'email'   => $email,
			'street'  => $street,
		);

		// Pass it through our filter.
		$data   = apply_filters( 'lw_woo_gdpr_random_userdata', $data, $user_id );

		// Bail without data.
		if ( empty( $data ) ) {
			return false;
		}

		// Return the entire set if no key was requested.
		if ( empty( $key ) ) {
			return $data;
		}

		// Return a single key if requested and it exists.
		return ! empty( $key ) && isset( $data[ $key ] ) ? $data[ $key ] : false;
	}

	/**
	 * Read one of our files and return a random entry.
	 *
	 * @param  string $type  Which file type we wanna read.
	 *
	 * @return string
	 */
	public static function get_random_from_file( $type = '' ) {

		// Bail without being passed a type.
		if ( empty( $type ) ) {
			return false;
		}

		// Set my source file.
		$source = LW_WOO_GDPR_ASSETS . '/txt/' . esc_attr( $type ) . '.txt';

		// Filter our available source file.
		$source = apply_filters( 'lw_woo_gdpr_random_srcfile', $source, $type );

		// Bail without a source.
		if ( empty( $source ) || ! is_file( $source ) ) {
			return false;
		}

		// Handle the file read.
		$setup  = file( $source );

		// Fetch a random one.
		$single = array_rand( array_flip( $setup ), 1 );

		// Return it trimmed and cleaned.
		return trim( wp_strip_all_tags( $single, true ) );
	}

	// End our class.
}

// Call our class.
new LW_Woo_GDPR_Data();

