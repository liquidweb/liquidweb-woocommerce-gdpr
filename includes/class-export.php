<?php
/**
 * Functions that handle the data exporting.
 *
 * @package LiquidWeb_Woo_GDPR
 */

/**
 * Start our engines.
 */
class LW_Woo_GDPR_Export {

	/**
	 * Call our hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init',                                             array( $this, 'check_export_request'        )           );
		add_action( 'init',                                             array( $this, 'check_data_action_request'   )           );
	}

	/**
	 * Look for our data export function.
	 *
	 * @return void
	 */
	public function check_export_request() {

		// Make sure we have the action we want.
		if ( empty( $_POST['action'] ) || 'lw_woo_gdpr_data_export' !== esc_attr( $_POST['action'] ) ) {
			return;
		}

		// The nonce check. ALWAYS A NONCE CHECK.
		if ( ! isset( $_POST['lw_woo_gdpr_nonce'] ) || ! wp_verify_nonce( $_POST['lw_woo_gdpr_nonce'], 'lw_woo_gdpr_action' ) ) {
			return;
		}

		// Make sure we selected something to export.
		if ( empty( $_POST['lw_woo_gdpr_export_option'] ) ) {
			self::redirect_export_error( 'NO_OPTION' );
		}

		// Make sure we have a user of some kind.
		if ( empty( $_POST['lw_woo_gdpr_data_export_user'] ) ) {
			self::redirect_export_error( 'NO_USER' );
		}

		// Set my user ID.
		$user_id    = absint( $_POST['lw_woo_gdpr_data_export_user'] );

		// Fetch any existing download files.
		$downloads  = get_user_meta( $user_id, 'woo_gdpr_export_files', true );

		// Sanitize all the types we requested.
		$datatypes  = array_map( 'sanitize_text_field', $_POST['lw_woo_gdpr_export_option'] );

		// Loop my types.
		foreach ( $datatypes as $type ) {

			// Now switch between my export types.
			switch ( $type ) {

				// Fetch orders.
				case 'orders':
					$downloads['orders'] = self::process_orders_export( $user_id );
					break;

				// Fetch comments.
				case 'comments':
					$downloads['comments'] = self::process_comments_export( $user_id );
					break;

				// Fetch reviews
				case 'reviews':
					$downloads['reviews'] = self::process_reviews_export( $user_id );
					break;
			}
		}

		// Make sure it's not got empties.
		$downloads  = array_filter( $downloads );

		// Bail if we have no exports to provide.
		if ( empty( $downloads ) ) {
			self::redirect_export_error( 'NO_EXPORT_FILES' );
		}

		// Update the user meta so we can show it.
		update_user_meta( $user_id, 'woo_gdpr_export_files', $downloads );

		// Now set my redirect link.
		$link   = add_query_arg( array( 'success' => 1, 'action' => 'export' ), home_url( '/account/privacy-data/' ) );

		// Do the redirect.
		wp_redirect( $link );
		exit;
	}

	/**
	 * Look for our data download function.
	 *
	 * @return void
	 */
	public function check_data_action_request() {

		// Make sure we have an action.
		if ( empty( $_GET['gdpr-action'] ) ) {
			return;
		}

		// The nonce check. ALWAYS A NONCE CHECK.
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'lw_woo_gdpr_files' ) ) {
			return;
		}

		// Make sure it's a valid action.
		if ( ! in_array( esc_attr( $_GET['gdpr-action'] ), array( 'download', 'delete' ) ) ) {
			self::redirect_export_error( 'INVALID_ACTION_REQUEST' );
		}

		// Set my action.
		$action = esc_attr( $_GET['gdpr-action'] );

		// Make sure we have a user of some kind.
		if ( empty( $_GET['user'] ) ) {
			self::redirect_export_error( 'NO_USER' );
		}

		// Make sure we selected something to deal with.
		if ( empty( $_GET['data-type'] ) ) {
			self::redirect_export_error( 'NO_DATA_TYPE' );
		}

		// Set my user ID and datatype.
		$user_id    = absint( $_GET['user'] );
		$datatype   = esc_attr( $_GET['data-type'] );

		// Make sure we selected something to export.
		if ( ! in_array( $datatype, array( 'orders', 'comments', 'reviews' ) ) ) {
			self::redirect_export_error( 'INVALID_DATA_TYPE' );
		}

		// Get my download files.
		$downloads  = get_user_meta( $user_id, 'woo_gdpr_export_files', true );

		// Make sure we have any download files at all.
		if ( empty( $downloads ) ) {
			self::redirect_export_error( 'NO_EXPORT_FILES' );
		}

		// Make sure we have the specific download file.
		if ( empty( $downloads[ $datatype ] ) ) {
			self::redirect_export_error( 'NO_EXPORT_TYPE_FILE' );
		}

		// Set my file URL.
		$file_url   = esc_url( $downloads[ $datatype ] );

		// Handle my download action request.
		if ( 'download' === $action ) {
			lw_woo_gdpr()->download_file( $file_url );
		}

		// Handle my delete action request.
		if ( 'delete' === $action ) {
			lw_woo_gdpr()->delete_file( $file_url, $user_id, $datatype, $downloads );
		}
	}

	/**
	 * Set up and run the order export
	 *
	 * @param  integer $user_id  The user ID we want to check.
	 *
	 * @return array
	 */
	public static function process_orders_export( $user_id = 0 ) {

		// First try to get my orders.
		if ( false === $orders = self::get_orders_for_user( $user_id ) ) {
			self::redirect_export_error( 'NO_ORDERS' );
		}

		// And call my file generator.
		if ( false === $export = self::generate_export_file( $orders, 'orders', $user_id ) ) {
			self::redirect_export_error( 'NO_EXPORT_FILE' );
		}

		// Return the export.
		return $export;
	}

	/**
	 * Set up and run the comment export.
	 *
	 * @param  integer $user_id  The user ID we want to check.
	 *
	 * @return array
	 */
	public static function process_comments_export( $user_id = 0 ) {

		// First try to get my comments.
		if ( false === $comments = self::get_comments_for_user( $user_id ) ) {
			self::redirect_export_error( 'NO_COMMENTS' );
		}

		// And call my file generator.
		if ( false === $export = self::generate_export_file( $comments, 'comments', $user_id ) ) {
			self::redirect_export_error( 'NO_EXPORT_FILE' );
		}

		// Return the export.
		return $export;
	}

	/**
	 * Set up and run the reviews export.
	 *
	 * @param  integer $user_id  The user ID we want to check.
	 *
	 * @return array
	 */
	public static function process_reviews_export( $user_id = 0 ) {

		// First try to get my reviews.
		if ( false === $reviews = self::get_reviews_for_user( $user_id ) ) {
			self::redirect_export_error( 'NO_REVIEWS' );
		}

		// And call my file generator.
		if ( false === $export = self::generate_export_file( $reviews, 'reviews', $user_id ) ) {
			self::redirect_export_error( 'NO_EXPORT_FILE' );
		}

		// Return the export.
		return $export;
	}

	/**
	 * Get all the orders for a particular user.
	 *
	 * @param  integer $user_id  The user ID we want to check.
	 *
	 * @return array
	 */
	public static function get_orders_for_user( $user_id = 0 ) {

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
		return lw_woo_gdpr_format_orders_export( $orders );
	}

	/**
	 * Get all the comments for a particular user.
	 *
	 * @param  integer $user_id  The user ID we want to check.
	 *
	 * @return array
	 */
	public static function get_comments_for_user( $user_id = 0 ) {

		// Bail without a user ID.
		if ( empty( $user_id ) ) {
			return;
		}

		// Set my args for the query.
		$args   = array(
			'user_id'   => absint( $user_id ),
		);

		// Now fetch my comments.
		$items  = get_comments( $args );

		// Bail without comments.
		if ( empty( $items ) || is_wp_error( $items ) ) {
			return false;
		}

		// Loop to unset the reviews.
		foreach ( $items as $id => $comment ) {

			// Skip my product reviews.
			if ( 'product' !== get_post_type( $comment->comment_post_ID ) ) {
				continue;
			}

			// Unset this comment from the array.
			unset( $items[ $id ] );
		}

		// Bail if there are no actual comments left.
		if ( empty( $items ) ) {
			return false;
		}

		// Format my orders and return them.
		return lw_woo_gdpr_format_comments_export( $items );
	}

	/**
	 * Get all the reviews for a particular user.
	 *
	 * @param  integer $user_id  The user ID we want to check.
	 *
	 * @return array
	 */
	public static function get_reviews_for_user( $user_id = 0 ) {

		// Bail without a user ID.
		if ( empty( $user_id ) ) {
			return;
		}

		// Set my args for the query.
		$args   = array(
			'user_id'   => absint( $user_id ),
			'post_type' => 'product',
		);

		// Now fetch my reviews.
		$items  = get_comments( $args );

		// Bail without orders.
		if ( empty( $items ) || is_wp_error( $items ) ) {
			return false;
		}

		// Format my orders and return them.
		return lw_woo_gdpr_format_reviews_export( $items );
	}

	/**
	 * Generate our CSV file.
	 *
	 * @param  array   $data     The actual data we are exporting.
	 * @param  string  $type     What the export type is.
	 * @param  integer $user_id  The user ID we want to check.
	 *
	 * @return bool
	 */
	public static function generate_export_file( $data = array(), $type = '', $user_id = 0 ) {

		// Handle our before action.
		do_action( 'lw_woo_gdpr_before_export' );

		// Fetch the filebase setup.
		$setup  = lw_woo_gdpr()->set_export_filebase( $type, $user_id );

		// Attempt to chmod the file.
		if ( ! empty( $setup['dir'] ) ) {
			@chmod( $setup['dir'], 0644 );
		}

		// Do our check to make sure we can write to it.
		$export = fopen( $setup['dir'], 'wb' );

		// Bail if we can't write.
		if ( $export === false ) {
			return false;
		}

		// Set the column headers.
		fputcsv( $export, lw_woo_gdpr_export_headers( $type ), '|' );

		// Save each row of the data.
		foreach ( $data as $row ) {
			fputcsv( $export, $row, '|' );
		}

		// Close the item.
		fclose( $export );

		// Handle our after action.
		do_action( 'lw_woo_gdpr_after_export' );

		// And return.
		return $setup['url'];
	}

	/**
	 * Handle an export redirect request.
	 *
	 * @param  string  $errcode  What (if any) error code.
	 * @param  boolean $success  Whether it was a successful attempt.
	 *
	 * @return void
	 */
	public static function redirect_export_error( $errcode = '' ) {

		// Bail with no error code.
		if ( empty( $errcode ) ) {
			return;
		}

		// Set my args.
		$args   = array(
			'error'     => 1,
			'errcode'   => esc_attr( $errcode ),
		);

		// Now set my redirect link.
		$link   = add_query_arg( $args, home_url( '/account/privacy-data/' ) );

		// Do the redirect.
		wp_redirect( $link );
		exit;
	}

	// End our class.
}

// Call our class.
$LW_Woo_GDPR_Export = new LW_Woo_GDPR_Export();
$LW_Woo_GDPR_Export->init();
