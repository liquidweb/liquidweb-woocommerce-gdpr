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
		add_action( 'wp_ajax_lw_woo_add_new_optin_row',     array( $this, 'add_new_optin_row'           )           );
		add_action( 'wp_ajax_lw_woo_delete_single_row',     array( $this, 'delete_single_row'           )           );
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

		// Bail if we are doing a REST API request.
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}

		// Bail out if running an autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Bail out if running a cron, unless we've skipped that.
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
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

		// Bail if we are doing a REST API request.
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}

		// Bail out if running an autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Bail out if running a cron, unless we've skipped that.
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
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
