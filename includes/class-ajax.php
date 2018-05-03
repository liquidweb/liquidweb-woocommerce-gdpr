<?php
/**
 * Our specific Ajax calls.
 *
 * @package LiquidWeb_Woo_GDPR
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Start our engines.
 */
class LW_Woo_GDPR_Ajax {

	/**
	 * Call our hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp_ajax_lw_woo_update_user_optins',    array( $this, 'update_user_optins'          )           );
		add_action( 'wp_ajax_lw_woo_request_user_exports',  array( $this, 'request_user_exports'        )           );
		add_action( 'wp_ajax_lw_woo_delete_export_file',    array( $this, 'delete_export_file'          )           );
		add_action( 'wp_ajax_lw_woo_request_user_deletion', array( $this, 'request_user_deletion'       )           );
		add_action( 'wp_ajax_lw_woo_cancel_delete_request', array( $this, 'cancel_delete_request'       )           );

		add_action( 'wp_ajax_lw_woo_add_new_optin_row',     array( $this, 'add_new_optin_row'           )           );
		add_action( 'wp_ajax_lw_woo_delete_single_row',     array( $this, 'delete_single_row'           )           );
		add_action( 'wp_ajax_lw_woo_update_sorted_rows',    array( $this, 'update_sorted_rows'          )           );

		add_action( 'wp_ajax_lw_woo_process_user_delete',   array( $this, 'process_user_delete'         )           );
		add_action( 'wp_ajax_lw_woo_process_bulk_delete',   array( $this, 'process_bulk_delete'         )           );
	}

	/**
	 * Update our user opt-in values.
	 *
	 * @return mixed
	 */
	public function update_user_optins() {

		// Check our various constants.
		if ( false === $constants = self::check_ajax_constants() ) {
			return;
		}

		// Check for the specific action.
		if ( empty( $_POST['action'] ) || 'lw_woo_update_user_optins' !== sanitize_text_field( $_POST['action'] ) ) {
			return false;
		}

		// Check to see if our nonce was provided.
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'lw_woo_gdpr_changeopt_action' ) ) {
			self::send_error( 'invalid-nonce' );
		}

		// Check for the user ID field.
		if ( empty( $_POST['user_id'] ) ) {
			self::send_error( 'missing-user-id' );
		}

		// Determine if we have opt-in choices.
		$items  = ! empty( $_POST['optins'] ) ? array_filter( (array) $_POST['optins'], 'sanitize_text_field' ) : array();

		// Run through the update.
		if ( false !== $update = lw_woo_gdpr()->update_user_optin_fields( absint( $_POST['user_id'] ), null, $items, false ) ) {

			// Grab our fields.
			$fields = lw_woo_gdpr_optin_fields();

			// Build our return.
			$return = array(
				'errcode' => null,
				'markup'  => LW_Woo_GDPR_Fields::get_optin_status_list( $fields, absint( $_POST['user_id'] ) ),
				'message' => lw_woo_gdpr_notice_text( 'success-changeopts' ),
			);

			// And handle my JSON return.
			wp_send_json_success( $return );
		}

		// Made it to the end without knowing what to do.
		self::send_error( 'unknown' );
	}

	/**
	 * Handle the user requesting their exports.
	 *
	 * @return mixed
	 */
	public function request_user_exports() {

		// Check our various constants.
		if ( false === $constants = self::check_ajax_constants() ) {
			return;
		}

		// Check for the specific action.
		if ( empty( $_POST['action'] ) || 'lw_woo_request_user_exports' !== sanitize_text_field( $_POST['action'] ) ) {
			return false;
		}

		// Check to see if our nonce was provided.
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'lw_woo_gdpr_export_action' ) ) {
			self::send_error( 'invalid-nonce' );
		}

		// Check for the user ID field.
		if ( empty( $_POST['user_id'] ) ) {
			self::send_error( 'missing-user-id' );
		}

		// Check for the export types field.
		if ( empty( $_POST['exports'] ) ) {
			self::send_error( 'no-option' );
		}

		// Set my user ID.
		$user_id    = absint( $_POST['user_id'] );

		// Clean up the requested export types.
		$datatypes  = array_filter( (array) $_POST['exports'], 'sanitize_text_field' );

		// Fetch any existing download files.
		$existing   = get_user_meta( $user_id, 'woo_gdpr_export_files', true );
		$downloads  = ! empty( $existing ) ? $existing : array();

		// Loop my types.
		foreach ( $datatypes as $type ) {

			// Now switch between my export types.
			switch ( $type ) {

				// Fetch orders.
				case 'orders':
					$downloads['orders'] = LW_Woo_GDPR_Export::process_orders_export( $user_id );
					break;

				// Fetch comments.
				case 'comments':
					$downloads['comments'] = LW_Woo_GDPR_Export::process_comments_export( $user_id );
					break;

				// Fetch reviews
				case 'reviews':
					$downloads['reviews'] = LW_Woo_GDPR_Export::process_reviews_export( $user_id );
					break;
			}
		}

		// Make sure it's not got empties.
		$filelist   = array_filter( $downloads );

		// Run through the update.
		if ( ! empty( $filelist ) ) {

			// Update the user meta so we can show it.
			update_user_meta( $user_id, 'woo_gdpr_export_files', $downloads );

			// Build our return.
			$return = array(
				'errcode' => null,
				'markup'  => LW_Woo_GDPR_Account::display_export_downloads( $user_id ),
				'message' => lw_woo_gdpr_notice_text( 'success-export' ),
			);

			// And handle my JSON return.
			wp_send_json_success( $return );
		}

		// Made it to the end without knowing what to do.
		self::send_error( 'unknown' );
	}

	/**
	 * Handle the user requesting a file deleted.
	 *
	 * @return mixed
	 */
	public function delete_export_file() {

		// Check our various constants.
		if ( false === $constants = self::check_ajax_constants() ) {
			return;
		}

		// Check for the specific action.
		if ( empty( $_POST['action'] ) || 'lw_woo_delete_export_file' !== sanitize_text_field( $_POST['action'] ) ) {
			return false;
		}

		// The nonce check. ALWAYS A NONCE CHECK.
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'lw_woo_gdpr_delete_file' ) ) {
			self::send_error( 'invalid-nonce' );
		}

		// Check for the user ID field.
		if ( empty( $_POST['user_id'] ) ) {
			self::send_error( 'missing-user-id' );
		}

		// Make sure we selected something to deal with.
		if ( empty( $_POST['datatype'] ) ) {
			self::send_error( 'no-datatype' );
		}

		// Set my user ID and datatype.
		$user_id    = absint( $_POST['user_id'] );
		$datatype   = esc_attr( $_POST['datatype'] );

		// Make sure we selected something to export.
		if ( ! in_array( $datatype, array( 'orders', 'comments', 'reviews' ) ) ) {
			self::send_error( 'invalid-datatype' );
		}

		// Get my download files.
		$downloads  = get_user_meta( $user_id, 'woo_gdpr_export_files', true );

		// Make sure we have any download files at all.
		if ( empty( $downloads ) ) {
			self::send_error( 'no-export-files' );
		}

		// Make sure we have the specific download file.
		if ( empty( $downloads[ $datatype ] ) ) {
			self::send_error( 'no-export-type-file' );
		}

		// Set my file URL.
		$file_url   = esc_url( $downloads[ $datatype ] );

		// Delete the actual item.
		if ( false !== $delete = lw_woo_gdpr()->delete_file( $file_url, $user_id, $datatype, $downloads, false ) ) {

			// Build our return.
			$return = array(
				'errcode' => null,
				'message' => lw_woo_gdpr_notice_text( 'success-delete' ),
				'markup'  => 'li.lw-woo-gdpr-data-option-' . $datatype,
			);

			// And handle my JSON return.
			wp_send_json_success( $return );
		}

		// Made it to the end without knowing what to do.
		self::send_error( 'unknown' );
	}

	/**
	 * Handle the user requesting to be deleted.
	 *
	 * @return mixed
	 */
	public function request_user_deletion() {

		// Check our various constants.
		if ( false === $constants = self::check_ajax_constants() ) {
			return;
		}

		// Check for the specific action.
		if ( empty( $_POST['action'] ) || 'lw_woo_request_user_deletion' !== sanitize_text_field( $_POST['action'] ) ) {
			return false;
		}

		// Check to see if our nonce was provided.
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'lw_woo_gdpr_delete_action' ) ) {
			self::send_error( 'invalid-nonce' );
		}

		// Check for the user ID field.
		if ( empty( $_POST['user_id'] ) ) {
			self::send_error( 'missing-user-id' );
		}

		// Check for the delete request types field.
		if ( empty( $_POST['deletes'] ) ) {
			self::send_error( 'no-option' );
		}

		// Set my user ID.
		$user_id    = absint( $_POST['user_id'] );

		// Clean up the requested delete types.
		$datatypes  = array_filter( (array) $_POST['deletes'], 'sanitize_text_field' );

		// And update our data accordingly.
		if ( false !== $updates = lw_woo_gdpr()->update_user_delete_requests( $user_id, $datatypes ) ) {

			// Check for the existing delete requests.
			$exists = get_user_meta( $user_id, 'woo_gdpr_deleteme_request', true );

			// Create the submits.
			$remain = count( $exists ) >= 3 ? false : true;

			// Build our return.
			$return = array(
				'errcode' => null,
				'markup'  => array(
					'requests'  => LW_Woo_GDPR_Fields::get_delete_request_list( array(), $exists, $user_id ),
					'remaining' => $remain,
				),
				'message' => lw_woo_gdpr_notice_text( 'success-deleteme' ),
			);

			// And handle my JSON return.
			wp_send_json_success( $return );
		}

		// Made it to the end without knowing what to do.
		self::send_error( 'unknown' );
	}

	/**
	 * Handle the user cancelling their request.
	 *
	 * @return mixed
	 */
	public function cancel_delete_request() {

		// Check our various constants.
		if ( false === $constants = self::check_ajax_constants() ) {
			return;
		}

		// Check for the specific action.
		if ( empty( $_POST['action'] ) || 'lw_woo_cancel_delete_request' !== sanitize_text_field( $_POST['action'] ) ) {
			return false;
		}

		// Check to see if our nonce was provided.
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'lw_woo_gdpr_cancel_request' ) ) {
			self::send_error( 'invalid-nonce' );
		}

		// Check for the user ID field.
		if ( empty( $_POST['user_id'] ) ) {
			self::send_error( 'missing-user-id' );
		}

		// Check for the delete request types field.
		if ( empty( $_POST['datatype'] ) ) {
			self::send_error( 'no-datatype' );
		}

		// Set my user ID.
		$user_id    = absint( $_POST['user_id'] );

		// Clean up the requested delete types.
		$datatype   = sanitize_text_field( $_POST['datatype'] );

		// Run the data update accordingly.
		$updates    = lw_woo_gdpr()->update_user_delete_requests( $user_id, $datatype, 'cancel' );

		// And update our data accordingly.
		if ( ! is_wp_error( $updates ) ) {

			// Check for the existing delete requests.
			$exists = get_user_meta( $user_id, 'woo_gdpr_deleteme_request', true );

			// Create the submits.
			$remain = count( $exists ) >= 3 ? false : true;

			// Build our return.
			$return = array(
				'errcode' => null,
				'markup'  => array(
					'requests'  => LW_Woo_GDPR_Fields::get_delete_request_list( array(), $exists, $user_id ),
					'remaining' => $remain,
				),
				'message' => lw_woo_gdpr_notice_text( 'success-cancelled' ),
			);

			// And handle my JSON return.
			wp_send_json_success( $return );
		}

		// Made it to the end without knowing what to do.
		self::send_error( 'unknown' );
	}

	/**
	 * Add a new row from the field.
	 *
	 * @return mixed
	 */
	public function add_new_optin_row() {

		// Only run this on the admin side.
		if ( ! is_admin() ) {
			die();
		}

		// Check our various constants.
		if ( false === $constants = self::check_ajax_constants() ) {
			return;
		}

		// Check for the specific action.
		if ( empty( $_POST['action'] ) || 'lw_woo_add_new_optin_row' !== sanitize_text_field( $_POST['action'] ) ) {
			return false;
		}

		// Check to see if our nonce was provided.
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'lw_woo_gdpr_new_action' ) ) {
			self::send_error( 'invalid-nonce' );
		}

		// Check for the title field.
		if ( empty( $_POST['title'] ) ) {
			self::send_error( 'missing-title' );
		}

		// Check for the label field.
		if ( empty( $_POST['label'] ) ) {
			self::send_error( 'missing-label' );
		}

		// Set my ID, since we use it a few places.
		$id = sanitize_title_with_dashes( $_POST['title'], '', 'save' );

		// Create the data array needed to make the field.
		$setup  = array(
			'id'        => $id,
			'title'     => sanitize_text_field( $_POST['title'] ),
			'label'     => sanitize_text_field( $_POST['label'] ),
			'required'  => 'true' === sanitize_text_field( $_POST['required'] ) ? 1 : 0,
		);

		// Grab my field based on the setup.
		if ( false === $fields = LW_Woo_GDPR_Formatting::format_new_optin_field( $setup, true ) ) {
			self::send_error( 'no-field' );
		}

		// Update our option. // no idea how to use woocommerce_update_options();
		lw_woo_gdpr()->update_saved_optin_fields( $fields, null );

		// Grab my table row markup based on the setup.
		if ( false !== $markup = LW_Woo_GDPR_Fields::table_row( $fields[ $id ] ) ) {

			// Build our return.
			$return = array(
				'errcode' => null,
				'markup'  => $markup,
				'message' => lw_woo_gdpr_notice_text( 'success' ),
			);

			// And handle my JSON return.
			wp_send_json_success( $return );
		}

		// Made it to the end without knowing what to do.
		self::send_error( 'unknown' );
	}

	/**
	 * Handle deleting a single row from the data array.
	 *
	 * @return mixed
	 */
	public function delete_single_row() {

		// Only run this on the admin side.
		if ( ! is_admin() ) {
			die();
		}

		// Check our various constants.
		if ( false === $constants = self::check_ajax_constants() ) {
			return;
		}

		// Check for the specific action.
		if ( empty( $_POST['action'] ) || 'lw_woo_delete_single_row' !== sanitize_text_field( $_POST['action'] ) ) {
			return false;
		}

		// Check to see if our nonce was provided.
		if ( empty( $_POST['nonce'] ) ) {
			self::send_error( 'missing-nonce' );
		}

		// Check to see if our field ID was provided.
		if ( empty( $_POST['field_id'] ) ) {
			self::send_error( 'bad-field-id' );
		}

		// Set my field ID and nonce key.
		$field_id   = esc_attr( $_POST['field_id'] );
		$noncekey   = 'lw_woo_optin_single_' . esc_attr( $_POST['field_id'] );

		// Check to see if our nonce failed.
		if ( ! wp_verify_nonce( $_POST['nonce'], $noncekey ) ) {
			self::send_error( 'bad-nonce' );
		}

		// Go ahead and remove it.
		if ( false !== $remove = lw_woo_gdpr()->update_saved_optin_fields( 0, $field_id ) ) {

			// Build our return.
			$return = array(
				'errcode' => null,
				'message' => lw_woo_gdpr_notice_text( 'success-removed' ),
			);

			// And handle my JSON return.
			wp_send_json_success( $return );
		}

		// Made it to the end without knowing what to do.
		self::send_error( 'unknown' );
	}

	/**
	 * Update our stored away with the new sort.
	 *
	 * @return mixed
	 */
	public function update_sorted_rows() {

		// Only run this on the admin side.
		if ( ! is_admin() ) {
			die();
		}

		// Check our various constants.
		if ( false === $constants = self::check_ajax_constants() ) {
			return;
		}

		// Check for the specific action.
		if ( empty( $_POST['action'] ) || 'lw_woo_update_sorted_rows' !== sanitize_text_field( $_POST['action'] ) ) {
			return;
		}

		// Check to see if our sorted data was provided.
		if ( empty( $_POST['sorted'] ) ) {
			return;
		}

		// Fetch my existing fields.
		if ( false === $current = lw_woo_gdpr_optin_fields() ) {
			return;
		}

		// Filter my fields.
		$fields = array_filter( $_POST['sorted'], 'sanitize_text_field' );

		// Set my new array variable.
		$update = array();

		// Loop my field IDs to reconstruct the order.
		foreach ( $fields as $field_id ) {
			$update[ $field_id ] = $current[ $field_id ];
		}

		// Update our option. // no idea how to use woocommerce_update_options();
		lw_woo_gdpr()->update_saved_optin_fields( $update, null );

		// Build our return.
		$return = array(
			'errcode' => null,
			'message' => lw_woo_gdpr_notice_text( 'success' ),
		);

		// And handle my JSON return.
		wp_send_json_success( $return );
	}

	/**
	 * Handle the actual user delete process.
	 *
	 * @return mixed
	 */
	public function process_user_delete() {

		// Only run this on the admin side.
		if ( ! is_admin() ) {
			die();
		}

		// Check our various constants.
		if ( false === $constants = self::check_ajax_constants() ) {
			return;
		}

		// Check for the specific action.
		if ( empty( $_POST['action'] ) || 'lw_woo_process_user_delete' !== sanitize_text_field( $_POST['action'] ) ) {
			return false;
		}

		// Check to see if our nonce was provided.
		if ( empty( $_POST['nonce'] ) ) {
			self::send_error( 'missing-nonce' );
		}

		// Check to see if our user ID was provided.
		if ( empty( $_POST['user_id'] ) ) {
			self::send_error( 'missing-user-id' );
		}

		// Set my user ID.
		$user_id    = absint( $_POST['user_id'] );

		// Make sure it isn't the current user.
		if ( $user_id === get_current_user_id() ) {
			self::send_error( 'current-user' );
		}

		// Check to see if our nonce failed.
		if ( ! wp_verify_nonce( $_POST['nonce'], 'lw_woo_delete_single_' . $user_id ) ) {
			self::send_error( 'bad-nonce' );
		}

		// Run the update procedure.
		self::process_single_user_request( $user_id );

		// Get any existing requests.
		$leftov = get_option( 'lw_woo_gdrp_delete_requests', array() );

		// Now count how many requests remain.
		$count  = count( $leftov );
		$remain = ! empty( $count ) && absint( $count ) > 0 ? true : false;

		// Write the text for the item count.
		$ctext  = ! empty( $count ) ? sprintf( _n( '%s item', '%s items', absint( $count ), 'liquidweb-woocommerce-gdpr' ), number_format_i18n( absint( $count ) ) ) : '';

		// Build our return.
		$return = array(
			'errcode' => null,
			'message' => lw_woo_gdpr_notice_text( 'success-userdelete' ),
			'remain'  => $remain,
			'ctext'   => $ctext
		);

		// And handle my JSON return.
		wp_send_json_success( $return );
	}

	/**
	 * Handle the bulk user delete process.
	 *
	 * @return mixed
	 */
	public function process_bulk_delete() {

		// Only run this on the admin side.
		if ( ! is_admin() ) {
			die();
		}

		// Check our various constants.
		if ( false === $constants = self::check_ajax_constants() ) {
			return;
		}

		// Check for the specific action.
		if ( empty( $_POST['action'] ) || 'lw_woo_process_bulk_delete' !== sanitize_text_field( $_POST['action'] ) ) {
			return false;
		}

		// Check to see if our nonce was provided.
		if ( empty( $_POST['nonce'] ) ) {
			self::send_error( 'missing-nonce' );
		}

		// Check to see if our user IDs were provided.
		if ( empty( $_POST['user_ids'] ) ) {
			self::send_error( 'missing-user-ids' );
		}

		// Check to see if our nonce failed.
		if ( ! wp_verify_nonce( $_POST['nonce'], 'lw_woo_gdpr_bulk_delete_action' ) ) {
			self::send_error( 'bad-nonce' );
		}

		// Sanitize my user IDs and filter dupes.
		$user_ids   = array_map( 'absint', (array) $_POST['user_ids'] );
		$user_ids   = array_unique( $user_ids );

		// Double check we have user IDs.
		if ( empty( $user_ids ) || ! is_array( $user_ids ) ) {
			self::send_error( 'invalid-user-ids' );
		}

		// Now loop all the users and run the process.
		foreach ( $user_ids as $user_id ) {
			self::process_single_user_request( $user_id );
		}

		// Get any existing requests.
		$leftov = get_option( 'lw_woo_gdrp_delete_requests', array() );

		// Now count how many requests remain.
		$count  = count( $leftov );
		$remain = ! empty( $count ) && absint( $count ) > 0 ? true : false;

		// Write the text for the item count.
		$ctext  = ! empty( $count ) ? sprintf( _n( '%s item', '%s items', absint( $count ), 'liquidweb-woocommerce-gdpr' ), number_format_i18n( absint( $count ) ) ) : '';

		// Build our return.
		$return = array(
			'errcode' => null,
			'message' => lw_woo_gdpr_notice_text( 'success-bulkdelete' ),
			'remain'  => $remain,
			'ctext'   => $ctext
		);

		// And handle my JSON return.
		wp_send_json_success( $return );
	}

	/**
	 * Run the update procedure on a single user ID.
	 *
	 * @param  integer $user_id  The user ID we are doing the things to.
	 *
	 * @return boolean
	 */
	public static function process_single_user_request( $user_id = 0 ) {

		// Bail with no user ID.
		if ( empty( $user_id ) ) {
			return false;
		}

		// Fetch my data types and downloads.
		$datatypes  = get_user_meta( $user_id, 'woo_gdpr_deleteme_request', true );

		// Handle my datatypes if we have them.
		if ( ! empty( $datatypes ) ) {

			// Loop my types.
			foreach ( $datatypes as $datatype ) {

				// Remove any files from the meta we have for this data type.
				lw_woo_gdpr()->remove_file_from_meta( $user_id, $datatype );

				// Delete the datatype.
				lw_woo_gdpr()->delete_userdata( $user_id, $datatype );
			}
		}

		// Remove any data files we may have.
		lw_woo_gdpr()->delete_user_files( $user_id );

		// Remove this user from our overall data array with the count.
		lw_woo_gdpr()->update_user_delete_requests( $user_id, null, 'remove' );

		// And just return true.
		return true;
	}

	/**
	 * Check our various constants on an Ajax call.
	 *
	 * @return boolean
	 */
	public static function check_ajax_constants() {

		// Check for a REST API request.
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return false;
		}

		// Check for running an autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		// Check for running a cron, unless we've skipped that.
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return false;
		}

		// We hit none of the checks, so proceed.
		return true;
	}

	/**
	 * Build and process our Ajax error handler.
	 *
	 * @param  string $errcode  The error code in question.
	 *
	 * @return void
	 */
	public static function send_error( $errcode = '' ) {

		// Build our return.
		$return = array(
			'errcode' => $errcode,
			'message' => lw_woo_gdpr_notice_text( $errcode ),
		);

		// And handle my JSON return.
		wp_send_json_error( $return );
	}

	// End our class.
}

// Call our class.
$LW_Woo_GDPR_Ajax = new LW_Woo_GDPR_Ajax();
$LW_Woo_GDPR_Ajax->init();
