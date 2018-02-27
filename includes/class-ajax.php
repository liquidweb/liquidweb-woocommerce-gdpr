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

		add_action( 'wp_ajax_lw_woo_add_new_optin_row',     array( $this, 'add_new_optin_row'           )           );
		add_action( 'wp_ajax_lw_woo_delete_single_row',     array( $this, 'delete_single_row'           )           );
		add_action( 'wp_ajax_lw_woo_update_sorted_rows',    array( $this, 'update_sorted_rows'          )           );
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
			self::send_error( 'NO_OPTION' );
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
			self::send_error( 'NO_DATATYPE' );
		}

		// Set my user ID and datatype.
		$user_id    = absint( $_POST['user_id'] );
		$datatype   = esc_attr( $_POST['datatype'] );

		// Make sure we selected something to export.
		if ( ! in_array( $datatype, array( 'orders', 'comments', 'reviews' ) ) ) {
			self::send_error( 'INVALID_DATATYPE' );
		}

		// Get my download files.
		$downloads  = get_user_meta( $user_id, 'woo_gdpr_export_files', true );

		// Make sure we have any download files at all.
		if ( empty( $downloads ) ) {
			self::send_error( 'NO_EXPORT_FILES' );
		}

		// Make sure we have the specific download file.
		if ( empty( $downloads[ $datatype ] ) ) {
			self::send_error( 'NO_EXPORT_TYPE_FILE' );
		}

		// Set my file URL.
		$file_url   = esc_url( $downloads[ $datatype ] );

		// Delete the actual item.
		if ( false !== $delete = lw_woo_gdpr()->delete_file( $file_url, $user_id, $datatype, $downloads, false ) ) {

			// Build our return.
			$return = array(
				'errcode' => null,
				'message' => lw_woo_gdpr_notice_text( 'success-delete' ),
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
