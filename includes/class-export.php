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
		add_action( 'init',                                             array( $this, 'check_data_export_request'   )           );
		add_action( 'init',                                             array( $this, 'check_data_action_request'   )           );
		add_action( 'init',                                             array( $this, 'check_data_cancel_request'   )           );
		add_action( 'init',                                             array( $this, 'check_data_delete_request'   )           );
	}

	/**
	 * Look for our data export function.
	 *
	 * @return void
	 */
	public function check_data_export_request() {

		// Make sure we have the action we want.
		if ( empty( $_POST['action'] ) || 'lw_woo_gdpr_data_export' !== esc_attr( $_POST['action'] ) ) {
			return;
		}

		// The nonce check. ALWAYS A NONCE CHECK.
		if ( ! isset( $_POST['lw_woo_gdpr_export_nonce'] ) || ! wp_verify_nonce( $_POST['lw_woo_gdpr_export_nonce'], 'lw_woo_gdpr_export_action' ) ) {
			return;
		}

		// Make sure we selected something to export.
		if ( empty( $_POST['lw_woo_gdpr_export_option'] ) ) {
			self::redirect_export_error( 'no-option' );
		}

		// Make sure we have a user of some kind.
		if ( empty( $_POST['lw_woo_gdpr_data_export_user'] ) ) {
			self::redirect_export_error( 'no-user' );
		}

		// Set my user ID.
		$user_id    = absint( $_POST['lw_woo_gdpr_data_export_user'] );

		// Fetch any existing download files.
		$existing   = get_user_meta( $user_id, 'woo_gdpr_export_files', true );
		$downloads  = ! empty( $existing ) ? $existing : array();

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
		$filelist   = array_filter( $downloads );

		// Bail if we have no exports to provide.
		if ( empty( $filelist ) ) {
			self::redirect_export_error( 'no-export-files' );
		}

		// Update the user meta so we can show it.
		update_user_meta( $user_id, 'woo_gdpr_export_files', $downloads );

		// Now set my redirect link.
		$link   = lw_woo_gdpr()->get_account_page_link( array( 'gdpr-result' => 1, 'success' => 1, 'action' => 'export' ) );

		// Do the redirect.
		wp_redirect( $link );
		exit;
	}

	/**
	 * Look for our data download / delete functions.
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
			self::redirect_export_error( 'invalid-action-request' );
		}

		// Set my action.
		$action = esc_attr( $_GET['gdpr-action'] );

		// Make sure we have a user of some kind.
		if ( empty( $_GET['user'] ) ) {
			self::redirect_export_error( 'no-user' );
		}

		// Make sure we selected something to deal with.
		if ( empty( $_GET['data-type'] ) ) {
			self::redirect_export_error( 'no-datatype' );
		}

		// Set my user ID and datatype.
		$user_id    = absint( $_GET['user'] );
		$datatype   = esc_attr( $_GET['data-type'] );

		// Make sure we selected something to export.
		if ( ! in_array( $datatype, array( 'orders', 'comments', 'reviews' ) ) ) {
			self::redirect_export_error( 'invalid-datatype' );
		}

		// Get my download files.
		$downloads  = get_user_meta( $user_id, 'woo_gdpr_export_files', true );

		// Make sure we have any download files at all.
		if ( empty( $downloads ) ) {
			self::redirect_export_error( 'no-export-files' );
		}

		// Make sure we have the specific download file.
		if ( empty( $downloads[ $datatype ] ) ) {
			self::redirect_export_error( 'no-export-type-file' );
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
	 * Look for our data delete cancel function.
	 *
	 * @return void
	 */
	public function check_data_cancel_request() {

		// Make sure we have an action.
		if ( empty( $_GET['gdpr-action'] ) ) {
			return;
		}

		// The nonce check. ALWAYS A NONCE CHECK.
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'lw_woo_gdpr_cancel' ) ) {
			return;
		}

		// Make sure it's a valid action.
		if ( 'cancel' !== esc_attr( $_GET['gdpr-action'] ) ) {
			self::redirect_export_error( 'invalid-action-request' );
		}

		// Make sure we have a user of some kind.
		if ( empty( $_GET['user'] ) ) {
			self::redirect_export_error( 'no-user' );
		}

		// Make sure we selected something to deal with.
		if ( empty( $_GET['data-type'] ) ) {
			self::redirect_export_error( 'no-datatype' );
		}

		// Set my user ID and datatype.
		$user_id    = absint( $_GET['user'] );
		$datatype   = esc_attr( $_GET['data-type'] );

		// Make sure we selected something to export.
		if ( ! in_array( $datatype, array( 'orders', 'comments', 'reviews' ) ) ) {
			self::redirect_export_error( 'invalid-datatype' );
		}

		// Get my delete request.
		$existing   = get_user_meta( $user_id, 'woo_gdpr_deleteme_request', true );

		// Make sure we have any requets files at all.
		if ( empty( $existing ) ) {
			self::redirect_export_error( 'no-existing-requests' );
		}

		// Make sure we have the specific request.
		if ( ! in_array( $datatype, $existing ) ) {
			self::redirect_export_error( 'no-request-type' );
		}

		// Handle removing the request.
		lw_woo_gdpr()->update_user_delete_requests( $user_id, $datatype, 'cancel' );

		// Now set my redirect link.
		$link   = lw_woo_gdpr()->get_account_page_link( array( 'gdpr-result' => 1, 'success' => 1, 'action' => 'cancel' ) );

		// Do the redirect.
		wp_redirect( $link );
		exit;
	}

	/**
	 * Look for our data delete function.
	 *
	 * @return void
	 */
	public function check_data_delete_request() {

		// Make sure we have the action we want.
		if ( empty( $_POST['action'] ) || 'lw_woo_gdpr_data_delete' !== esc_attr( $_POST['action'] ) ) {
			return;
		}

		// The nonce check. ALWAYS A NONCE CHECK.
		if ( ! isset( $_POST['lw_woo_gdpr_delete_nonce'] ) || ! wp_verify_nonce( $_POST['lw_woo_gdpr_delete_nonce'], 'lw_woo_gdpr_delete_action' ) ) {
			return;
		}

		// Make sure we selected something to delete.
		if ( empty( $_POST['lw_woo_gdpr_delete_option'] ) ) {
			self::redirect_export_error( 'no-option' );
		}

		// Make sure we have a user of some kind.
		if ( empty( $_POST['lw_woo_gdpr_data_delete_user'] ) ) {
			self::redirect_export_error( 'no-user' );
		}

		// Set my user ID.
		$user_id    = absint( $_POST['lw_woo_gdpr_data_delete_user'] );

		// Sanitize all the types we requested.
		$datatypes  = array_map( 'sanitize_text_field', $_POST['lw_woo_gdpr_delete_option'] );

		// And update our data accordingly.
		lw_woo_gdpr()->update_user_delete_requests( $user_id, $datatypes );

		// Now set my redirect link.
		$link   = lw_woo_gdpr()->get_account_page_link( array( 'gdpr-result' => 1, 'success' => 1, 'action' => 'deleteme' ) );

		// Do the redirect.
		wp_redirect( $link );
		exit;
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
		if ( false === $orders = LW_Woo_GDPR_Data::get_orders_for_user( $user_id ) ) {
			return null; // self::redirect_export_error( 'NO_ORDERS' );
		}

		// And call my file generator.
		if ( false === $export = self::generate_export_file( $orders, 'orders', $user_id ) ) {
			self::redirect_export_error( 'no-export-files' );
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
		if ( false === $comments = LW_Woo_GDPR_Data::get_comments_for_user( $user_id ) ) {
			return null; // self::redirect_export_error( 'NO_COMMENTS' );
		}

		// And call my file generator.
		if ( false === $export = self::generate_export_file( $comments, 'comments', $user_id ) ) {
			self::redirect_export_error( 'no-export-files' );
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
		if ( false === $reviews = LW_Woo_GDPR_Data::get_reviews_for_user( $user_id ) ) {
			return null; // self::redirect_export_error( 'NO_REVIEWS' );
		}

		// And call my file generator.
		if ( false === $export = self::generate_export_file( $reviews, 'reviews', $user_id ) ) {
			self::redirect_export_error( 'no-export-files' );
		}

		// Return the export.
		return $export;
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
		do_action( 'lw_woo_gdpr_before_export', $type, $data );

		// Fetch the filebase setup.
		$setup  = lw_woo_gdpr()->set_export_filebase( $type, $user_id );

		// Attempt to chmod the file.
		if ( is_file( $setup['file'] ) ) {
			@chmod( $setup['file'], 0644 );
		}

		// Do our check to make sure we can write to it.
		if ( false === $export = fopen( $setup['file'], 'w' ) ) {
			return false;
		}

		// Set the column headers.
		if ( false !== $headers = lw_woo_gdpr_export_headers( $type ) ) {
			fputcsv( $export, $headers, ',', '"' );
		}

		// Save each row of the data.
		foreach ( $data as $row ) {

			// Clean our data.
			array_walk( $row, array( 'LW_Woo_GDPR_Formatting', 'clean_export' ) );

			// And output the row.
			fputcsv( $export, $row, ',', '"' );
		}

		// Close the item.
		fclose( $export );

		// Handle our after action.
		do_action( 'lw_woo_gdpr_after_export', $type, $data, $setup );

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
			'gdpr-result' => 1,
			'success'     => 0,
			'errcode'     => esc_attr( $errcode ),
		);

		// Now set my redirect link.
		$link   = lw_woo_gdpr()->get_account_page_link( $args );

		// Do the redirect.
		wp_redirect( $link );
		exit;
	}

	// End our class.
}

// Call our class.
$LW_Woo_GDPR_Export = new LW_Woo_GDPR_Export();
$LW_Woo_GDPR_Export->init();
