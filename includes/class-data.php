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
		$items  = get_comments( array( 'user_id' => absint( $user_id ) ) );

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
		$items  = get_comments( array( 'user_id' => absint( $user_id ), 'post_type' => 'product' ) );

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

	// End our class.
}

// Call our class.
new LW_Woo_GDPR_Data();

