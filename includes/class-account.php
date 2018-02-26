<?php
/**
 * Our functions related to the "my account" page.
 *
 * Set up the actions that happen inside the admin area.
 *
 * @package LiquidWeb_Woo_GDPR
 */

/**
 * Start our engines.
 */
class LW_Woo_GDPR_Account {

	/**
	 * Call our hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init',                                             array( $this, 'check_user_optin_changes'    )           );
		add_action( 'woocommerce_before_account_navigation',            array( $this, 'add_endpoint_notices'        )           );
		add_filter( 'the_title',                                        array( $this, 'add_endpoint_title'          )           );
		add_filter( 'woocommerce_account_menu_items',                   array( $this, 'add_endpoint_menu_item'      )           );
		add_action( 'woocommerce_account_privacy-data_endpoint',        array( $this, 'add_endpoint_content'        )           );
	}

	/**
	 * Look for our users changing their opt-in statuses.
	 *
	 * @return void
	 */
	public function check_user_optin_changes() {

		//preprint( $_POST, true );

		// Make sure we have the action we want.
		if ( empty( $_POST['action'] ) || 'lw_woo_gdpr_changeopt' !== esc_attr( $_POST['action'] ) ) {
			return;
		}

		// The nonce check. ALWAYS A NONCE CHECK.
		if ( ! isset( $_POST['lw_woo_gdpr_changeopt_nonce'] ) || ! wp_verify_nonce( $_POST['lw_woo_gdpr_changeopt_nonce'], 'lw_woo_gdpr_changeopt_action' ) ) {
			return;
		}

		// Make sure we have a user of some kind.
		if ( empty( $_POST['lw_woo_gdpr_data_changeopt_user'] ) ) {
		//	self::redirect_export_error( 'NO_USER' );
		}

		// Set my user ID.
		$user_id    = absint( $_POST['lw_woo_gdpr_data_changeopt_user'] );

		// Filter my field args getting passed.
		$field_args = empty( $_POST['lw_woo_gdpr_changeopt_items'] ) ? array() : array_filter( $_POST['lw_woo_gdpr_changeopt_items'], 'sanitize_text_field' );

		// Update my fields.
		if ( false !== $update = lw_woo_gdpr()->update_user_optin_fields( $user_id, null, $field_args ) ) {

			// Now set my redirect link.
			$link   = lw_woo_gdpr()->get_account_page_link( array( 'gdpr-result' => 1, 'success' => 1, 'action' => 'changeopts' ) );

			// Do the redirect.
			wp_redirect( $link );
			exit;
		}

		// And do the return / redirect / etc for the error.
	}

	/**
	 * Add the notices above the "my account" area.
	 *
	 * @return HTML
	 */
	public function add_endpoint_notices() {

		// Bail without our result flag.
		if ( empty( $_GET['gdpr-result'] ) ) {
			return;
		}

		// Set our base class.
		$class  = 'lw-woo-gdpr-notice';
		$code   = '';

		// We have an error, so handle that.
		if ( empty( $_GET['success'] ) ) {

			// Add to the class.
			$class .= ' lw-woo-gdpr-notice-error';

			// Confirm our error code.
			$code   = ! empty( $_GET['errcode'] ) ? esc_attr( $_GET['errcode'] ) : 'unknown';
		}

		// We have success, so handle that.
		if ( ! empty( $_GET['success'] ) ) {

			// Add to the class.
			$class .= ' lw-woo-gdpr-notice-success';

			// Figure out a code based on what action we took.
			$code  = ! empty( $_GET['action'] ) && in_array( esc_attr( $_GET['action'] ), array( 'export', 'delete', 'deleteme', 'changeopts' ) ) ? 'success-' . esc_attr( $_GET['action'] ) : 'success-general';
		}

		// Bail if we have no message text.
		if ( empty( $code ) ) {
			return;
		}

		// Get my text for the notice.
		$msgtxt = lw_woo_gdpr_notice_text( $code );

		// And output the actual message.
		echo '<div class="' . esc_attr( $class ) . '">';
			echo '<p>' . wp_kses_post( $msgtxt ) . '</p>';
		echo '</div>';
	}

	/**
	 * Set a title for the individual endpoint we just made.
	 *
	 * @param  string $title  The existing page title.
	 *
	 * @return string
	 */
	public function add_endpoint_title( $title ) {

		// Bail if we aren't on the right general place.
		if ( is_admin() || ! is_main_query() || ! in_the_loop() || ! is_account_page() ) {
			return $title;
		}

		// Call the global query object.
		global $wp_query;

		// We are here, check some other stuff, then output.
		if ( isset( $wp_query->query_vars[ LW_WOO_GDPR_FRONT_VAR ] ) ) {

			// New page title.
			$title = apply_filters( 'lw_woo_gdpr_endpoint_title', __( 'My Privacy Data', 'liquidweb-woocommerce-gdpr' ) );

			// Remove the filter so we don't loop endlessly.
			remove_filter( 'the_title', array( $this, 'add_endpoint_title' ) );
		}

		// Return the title.
		return $title;
	}

	/**
	 * Merge in our new enpoint into the existing "My Account" menu.
	 *
	 * @param  array $items  The existing menu items.
	 *
	 * @return array
	 */
	public function add_endpoint_menu_item( $items ) {

		// Set up our menu item title.
		$title  = apply_filters( 'lw_woo_gdpr_endpoint_menu_title', __( 'Privacy Data', 'liquidweb-woocommerce-gdpr' ) );

		// Add it to the array.
		$items  = wp_parse_args( array( LW_WOO_GDPR_FRONT_VAR => esc_attr( $title ) ), $items );

		// If we don't have the logout link, just tack ours on the end.
		if ( ! isset( $items['customer-logout'] ) ) {
			return $items;
		}

		// Set our logout link.
		$logout = $items['customer-logout'];

		// Remove the logout.
		unset( $items['customer-logout'] );

		// Now add it back in.
		$items['customer-logout'] = $logout;

		// And return the whole thing.
		return $items;
	}

	/**
	 * Add the content for our endpoint to display.
	 *
	 * @return HTML
	 */
	public function add_endpoint_content() {

		// Get my current customer.
		$user_id    = get_current_user_id();

		// Get my export types.
		$datatypes  = lw_woo_gdpr_export_types();

		// Handle the opt-in statuses section.
		echo self::display_optin_statuses( $user_id );

		// Show the data options only if we've enabled it.
		if ( ! empty( $datatypes ) ) {

			// Handle the export section.
			echo self::display_export_options( $user_id, $datatypes );

			// Handle the downloads section.
			echo self::display_export_downloads( $user_id, $datatypes );

			// Handle the delete me section.
			echo self::display_data_delete( $user_id, $datatypes );
		}
	}

	/**
	 * Display the possible opt-in statuses.
	 *
	 * @param  integer $user_id    The user ID we are dealing with.
	 *
	 * @return HTML
	 */
	public static function display_optin_statuses( $user_id = 0 ) {

		// Get my fields.
		$fields = lw_woo_gdpr_optin_fields();

		// Bail without my fields.
		if ( empty( $fields ) ) {
			return;
		}

		// Set an empty.
		$build  = '';

		// Wrap it in a div.
		$build .= '<div class="lw-woo-gdpr-section lw-woo-gdpr-optin-section">';

			// Add some title stuff.
			$build .= '<h3 class="lw-woo-gdpr-section-title">' . esc_html__( 'Your Data Opt-Ins', 'liquidweb-woocommerce-gdpr' ) . '</h3>';

			// Describe what to do.
			$build .= '<p class="lw-woo-gdpr-section-subtitle">' . esc_html__( 'Below are the choices you have opted into. You can review and update them at any time.', 'liquidweb-woocommerce-gdpr' ) . '</p>';

			// Set the form.
			$build .= '<form class="lw-woo-gdpr-changeopt-form" action="" method="post">';

				// Open up our list.
				$build .= '<ul class="lw-woo-gdpr-account-item-list lw-woo-gdpr-optin-list">';

				// Loop my fields to display.
				foreach ( $fields as $key => $field ) {

					// Check the status.
					$status = get_user_meta( $user_id, 'woo_gdrp_' . $key, true );
					$check  = ! empty( $status ) ? true : false;

					// Set the text accordingly.
					$text   = ! empty( $status ) ? sprintf( __( 'You have opted in to %s', 'liquidweb-woocommerce-gdpr' ), esc_attr( $field['title'] ) ) : sprintf( __( 'You have not opted in to %s', 'liquidweb-woocommerce-gdpr' ), esc_attr( $field['title'] ) );

					// Set new field args.
					$new_field_args = array(
						'name'      => 'lw_woo_gdpr_changeopt_items[' . esc_attr( $field['id'] ) . ']',
						'label'     => wp_kses_post( $text ),
						'required'  => false,
						'checked'   => $check,
					);

					// Merge my new args.
					$field  = wp_parse_args( $new_field_args, $field );

					// Open up our list item.
					$build .= '<li class="lw-woo-gdpr-data-option lw-woo-gdpr-optin-list-item">';

						// Include the actual checkbox.
						$build .= LW_Woo_GDPR_Fields::checkbox_field( $field );

					// Close the list item.
					$build .= '</li>';
				}

				// Close the list.
				$build .= '</ul>';

				// Open the paragraph for the submit button.
				$build .= '<p class="lw-woo-gdpr-data-submit lw-woo-gdpr-export-submit">';

					// Handle the nonce.
					$build .= wp_nonce_field( 'lw_woo_gdpr_changeopt_action', 'lw_woo_gdpr_changeopt_nonce', false, false );

					// The button / action combo.
					$build .= '<input class="woocommerce-Button button lw-woo-gdpr-optin-list-submit" name="lw_woo_gdpr_changeopt" value="' . __( 'Update Your Opt-Ins', 'liquidweb-woocommerce-gdpr' ) . '" type="submit">';
					$build .= '<input name="action" value="lw_woo_gdpr_changeopt" type="hidden">';
					$build .= '<input name="lw_woo_gdpr_data_changeopt_user" value="' . absint( $user_id ) . '" type="hidden">';

				// Close the paragraph.
				$build .= '</p>';

			// Close the form.
			$build .= '</form>';

		// Close the div.
		$build .= '</div>';

		// Return our build.
		return $build;
	}

	/**
	 * Display the export options.
	 *
	 * @param  integer $user_id    The user ID we are dealing with.
	 * @param  array   $datatypes  Our possible data types.
	 *
	 * @return HTML
	 */
	public static function display_export_options( $user_id = 0, $datatypes = array() ) {

		// Bail without my user ID or data types.
		if ( empty( $user_id ) || empty( $datatypes ) ) {
			return;
		}

		// Set an empty.
		$build  = '';

		// Wrap it in a div.
		$build .= '<div class="lw-woo-gdpr-section lw-woo-gdpr-export-section">';

			// Add some title stuff.
			$build .= '<h3 class="lw-woo-gdpr-section-title">' . esc_html__( 'Export Your Data', 'liquidweb-woocommerce-gdpr' ) . '</h3>';

			// Describe what to do.
			$build .= '<p class="lw-woo-gdpr-section-subtitle">' . esc_html__( 'Select the type(s) of data you would like to export. Please note that any request will replace an existing file for that type.', 'liquidweb-woocommerce-gdpr' ) . '</p>';

			// Set the form.
			$build .= '<form class="lw-woo-gdpr-export-form" action="" method="post">';

				// Set a unordered list around the checkboxes.
				$build .= '<ul class="lw-woo-gdpr-account-item-list lw-woo-gdpr-data-options lw-woo-gdpr-export-options">';

				// Now loop my types.
				foreach ( $datatypes as $type => $label ) {

					// Open up the list item.
					$build .= '<li class="lw-woo-gdpr-data-option lw-woo-gdpr-export-option">';

						// The input field.
						$build .= '<input name="lw_woo_gdpr_export_option[]" id="export-option-' . esc_attr( $type ) . '" type="checkbox" value="' . esc_attr( $type ) . '" >';

						// The label field.
						$build .= '<label for="export-option-' . esc_attr( $type ) . '">' . esc_html( $label ) . '</label>';

					// Close the item.
					$build .= '</li>';
				}

				// Close the unordered list.
				$build .= '</ul>';

				// Open the paragraph for the submit button.
				$build .= '<p class="lw-woo-gdpr-data-submit lw-woo-gdpr-export-submit">';

					// Handle the nonce.
					$build .= wp_nonce_field( 'lw_woo_gdpr_export_action', 'lw_woo_gdpr_export_nonce', false, false );

					// The button / action combo.
					$build .= '<input class="woocommerce-Button button" name="lw_woo_gdpr_data_export" value="' . __( 'Request Export Data', 'liquidweb-woocommerce-gdpr' ) . '" type="submit">';
					$build .= '<input name="action" value="lw_woo_gdpr_data_export" type="hidden">';
					$build .= '<input name="lw_woo_gdpr_data_export_user" value="' . absint( $user_id ) . '" type="hidden">';

				// Close the paragraph.
				$build .= '</p>';

			// Close the form.
			$build .= '</form>';

		// Close the div.
		$build .= '</div>';

		// Return our build.
		return $build;
	}

	/**
	 * Display the download options.
	 *
	 * @param  integer $user_id    The user ID we are dealing with.
	 * @param  array   $datatypes  Our possible data types.
	 *
	 * @return HTML
	 */
	public static function display_export_downloads( $user_id = 0, $datatypes = array() ) {

		// Bail without my user ID or data types.
		if ( empty( $user_id ) || empty( $datatypes ) ) {
			return;
		}

		// Check for the export files.
		$files  = get_user_meta( $user_id, 'woo_gdpr_export_files', true );

		// Set an empty.
		$build  = '';

		// Wrap it in a div.
		$build .= '<div class="lw-woo-gdpr-section lw-woo-gdpr-download-section">';

			// Add some title stuff.
			$build .= '<h3 class="lw-woo-gdpr-section-title">' . esc_html__( 'Manage Your Data', 'liquidweb-woocommerce-gdpr' ) . '</h3>';

			// Display the options if we have saved files.
			if ( ! empty( $files ) ) {

				// Set my base URL for download links.
				$base   = add_query_arg( array( 'user' => $user_id, '_wpnonce' => wp_create_nonce( 'lw_woo_gdpr_files' ) ), home_url( '/account/privacy-data/' ) );

				// Describe what to do.
				$build .= '<p class="lw-woo-gdpr-section-subtitle">' . esc_html__( 'Select which export file you would like to download or delete.', 'liquidweb-woocommerce-gdpr' ) . '</p>';

				// Set a unordered list around the checkboxes.
				$build .= '<ul class="lw-woo-gdpr-account-item-list lw-woo-gdpr-data-options lw-woo-gdpr-download-files">';

				// Now loop my types.
				foreach ( $datatypes as $datatype => $label ) {

					// Figure out if we have files of this type.
					$single = ! empty( $files[ $datatype ] ) ? $files[ $datatype ] : '';

					// Open up the list item.
					$build .= '<li class="lw-woo-gdpr-data-option lw-woo-gdpr-download-option">';

						// Our label for the data type.
						$build .= '<span class="data-option-label">' . esc_html( $label ) . '</span>';

						// And our download / delete links.
						$build .= lw_woo_gdpr_create_file_links( $single, $datatype, $base );

					// Close the list item.
					$build .= '</li>';
				}

				// Close the unordered list.
				$build .= '</ul>';

			} else { // Show a message saying no files exist.
				$build .= '<p class="lw-woo-gdpr-section-description">' . esc_html__( 'You have not generated any export files.', 'liquidweb-woocommerce-gdpr' ) . '</p>';
			}

		// Close the div.
		$build .= '</div>';

		// Return our build.
		return $build;
	}

	/**
	 * Display the "delete me" options.
	 *
	 * @param  integer $user_id    The user ID we are dealing with.
	 * @param  array   $datatypes  Our possible data types.
	 *
	 * @return HTML
	 */
	public static function display_data_delete( $user_id = 0, $datatypes = array() ) {

		// Bail without my user ID or data types.
		if ( empty( $user_id ) || empty( $datatypes ) ) {
			return;
		}

		// Check for the existing delete requests.
		$requests   = get_user_meta( $user_id, 'woo_gdpr_deleteme_request', true );

		// Set an empty.
		$build  = '';

		// Wrap it in a div.
		$build .= '<div class="lw-woo-gdpr-section lw-woo-gdpr-data-delete-section">';

			// Add some title stuff.
			$build .= '<h3 class="lw-woo-gdpr-section-title">' . esc_html__( 'Delete Me', 'liquidweb-woocommerce-gdpr' ) . '</h3>';

			// Describe what to do.
			$build .= '<p class="lw-woo-gdpr-section-subtitle">' . esc_html__( 'Select the type(s) of data you would like to remove.', 'liquidweb-woocommerce-gdpr' ) . '</p>';

			// Set the form.
			$build .= '<form class="lw-woo-gdpr-delete-me-form" action="" method="post">';

				// Set a unordered list around the checkboxes.
				$build .= '<ul class="lw-woo-gdpr-account-item-list lw-woo-gdpr-data-options lw-woo-gdpr-delete-options">';

				// Now loop my types.
				foreach ( $datatypes as $datatype => $label ) {

					// Determine if it's pending or now.
					$didask = in_array( $datatype, (array) $requests ) ? true : false;

					// Set my class.
					$class  = 'lw-woo-gdpr-data-option lw-woo-gdpr-delete-option';

					// Add a disabled flag to orders (for now).
					//$class .= 'orders' === esc_attr( $datatype ) ? ' lw-woo-gdpr-data-option-disabled' : '';
					$class .= ! empty( $didask ) ? ' lw-woo-gdpr-data-option-pending' : '';

					// Open up the list item.
					$build .= '<li class="' . esc_attr( $class ) . '">';

						// The input field or icon.
						if ( ! empty( $didask ) ) {

							// Trim off the S at the end.
							$notplural  = rtrim( $datatype, 's' );

							// Our link title.
							$asktitle   = sprintf( __( 'Your %s data request is pending', 'liquidweb-woocommerce-gdpr' ), esc_attr( $notplural ) );

							// Our icon field.
							$build .= '<i class="lw-woo-gdpr-data-option-icon dashicons dashicons-lock"></i>' . esc_html( $label );

						} else {

							// The input field.
							// $build .= '<input name="lw_woo_gdpr_delete_option[]" id="delete-option-' . esc_attr( $datatype ) . '" type="checkbox" value="' . esc_attr( $datatype ) . '" ' . disabled( $datatype, 'orders' , false ) . ' >';

							$build .= '<input name="lw_woo_gdpr_delete_option[]" id="delete-option-' . esc_attr( $datatype ) . '" type="checkbox" value="' . esc_attr( $datatype ) . '">';

							// The label field.
							$build .= '<label for="delete-option-' . esc_attr( $datatype ) . '">' . esc_html( $label ) . '</label>';
						}

					// Close the list item.
					$build .= '</li>';
				}

				// Close the unordered list.
				$build .= '</ul>';

				// Open the paragraph for the submit button.
				$build .= '<p class="lw-woo-gdpr-data-submit lw-woo-gdpr-delete-submit">';

					// Check how many requests we have, if all three are there don't show the button.
					if ( count( $requests ) === 3 ) {

						// Just a simple statement abouit what's pending.
						$build .= '<em>' . esc_html__( 'Your requests are pending.', 'liquidweb-woocommerce-gdpr' ) . '</em>';

					} else {

						// Handle the nonce.
						$build .= wp_nonce_field( 'lw_woo_gdpr_delete_action', 'lw_woo_gdpr_delete_nonce', false, false );

						// The button / action combo.
						$build .= '<input class="woocommerce-Button button" name="lw_woo_gdpr_data_delete" value="' . __( 'Request Data Deletion', 'liquidweb-woocommerce-gdpr' ) . '" type="submit">';
						$build .= '<input name="action" value="lw_woo_gdpr_data_delete" type="hidden">';
						$build .= '<input name="lw_woo_gdpr_data_delete_user" value="' . absint( $user_id ) . '" type="hidden">';
					}

				// Close the paragraph.
				$build .= '</p>';

			// Close the form.
			$build .= '</form>';

		// Close the div.
		$build .= '</div>';

		// Return our build.
		return $build;
	}

	// End our class.
}

// Call our class.
$LW_Woo_GDPR_Account = new LW_Woo_GDPR_Account();
$LW_Woo_GDPR_Account->init();
