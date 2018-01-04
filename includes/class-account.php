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
		add_filter( 'the_title',                                        array( $this, 'add_endpoint_title'          )           );
		add_filter( 'woocommerce_account_menu_items',                   array( $this, 'add_endpoint_menu_item'      )           );
		add_action( 'woocommerce_account_privacy-data_endpoint',        array( $this, 'add_endpoint_content'        )           );
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
			$title = __( 'My Privacy Data', 'liquidweb-woocommerce-gdpr' );

			// Remove the filter so we don't loop endlessly.
			remove_filter( 'the_title', array( $this, 'add_endpoint_title' ) );
		}

		// Return the title.
		return $title;
	}

	/**
	 * Merge in our new enpoint into the existing "My Account" menu.
	 *
	 * @param array $items  The existing menu items.
	 */
	public function add_endpoint_menu_item( $items ) {
		return wp_parse_args( array( LW_WOO_GDPR_FRONT_VAR => __( 'Privacy Data', 'liquidweb-woocommerce-gdpr' ) ), $items );
	}

	/**
	 * Add the content for our endpoint to display.
	 */
	public function add_endpoint_content() {

		// Get my current customer.
		$user_id    = get_current_user_id();
		//$customer   = new WC_Customer( $user_id );

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
			$build .= '<h3>' . esc_html__( 'Your Data Opt-In', 'liquidweb-woocommerce-gdpr' ) . '</h3>';

			// Open up our list.
			$build .= '<ul>';

			// Loop my fields to display.
			foreach ( $fields as $key => $field ) {

				// Check the status.
				$status = get_user_meta( $user_id, 'woo_gdrp_' . $key, true );

				// Open up our list item.
				$build .= '<li>';

				// Set the text accordingly.
				if ( ! empty( $status ) ) {
					$build .= sprintf( __( 'You have opted in to %s', 'liquidweb-woocommerce-gdpr' ), esc_attr( $field['title'] ) );
				} else {
					$build .= sprintf( __( 'You have not opted in to %s', 'liquidweb-woocommerce-gdpr' ), esc_attr( $field['title'] ) );
				}

				// Close the list item.
				$build .= '</li>';
			}

			// Close the list.
			$build .= '</ul>';

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
			$build .= '<h3>' . esc_html__( 'Export Your Data', 'liquidweb-woocommerce-gdpr' ) . '</h3>';

			// Describe what to do.
			$build .= '<p>' . esc_html__( 'Select the type(s) of data you would like to export. Please note that any request will replace an existing file for that type.', 'liquidweb-woocommerce-gdpr' ) . '</p>';

			// Set the form.
			$build .= '<form class="lw-woo-gdpr-export-form" action="" method="post">';

				// Set a paragraph around the checkboxes.
				$build .= '<p class="lw-woo-gdpr-data-options lw-woo-gdpr-export-options">';

				// Now loop my types.
				foreach ( $datatypes as $type => $label ) {

					// Open up the span.
					$build .= '<span class="lw-woo-gdpr-data-option lw-woo-gdpr-export-option">';

						// The input field.
						$build .= '<input name="lw_woo_gdpr_export_option[]" id="export-option-' . esc_attr( $type ) . '" type="checkbox" value="' . esc_attr( $type ) . '" >';

						// The label field.
						$build .= '<label for="export-option-' . esc_attr( $type ) . '">' . esc_html( $label ) . '</label>';

					// Close the span.
					$build .= '</span>';
				}

				// Close the paragraph.
				$build .= '</p>';

				// Open the paragraph for the submit button.
				$build .= '<p class="lw-woo-gdpr-export-submit">';

					// Handle the nonce.
					$build .= wp_nonce_field( 'lw_woo_gdpr_action', 'lw_woo_gdpr_nonce', false, false );

					// The button / action combo.
					$build .= '<input class="woocommerce-Button button" name="lw_woo_gdpr_data_export" value="' . __( 'Export Requested Data', 'liquidweb-woocommerce-gdpr' ) . '" type="submit">';
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
	 * Display the export options.
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
			$build .= '<h3>' . esc_html__( 'Manage Your Data', 'liquidweb-woocommerce-gdpr' ) . '</h3>';

			// Display the options if we have saved files.
			if ( ! empty( $files ) ) {

				// Set my base URL for download links.
				$base   = add_query_arg( array( 'user' => $user_id, '_wpnonce' => wp_create_nonce( 'lw_woo_gdpr_files' ) ), home_url( '/account/privacy-data/' ) );

				// Describe what to do.
				$build .= '<p>' . esc_html__( 'Select which export file you would like to download or delete.', 'liquidweb-woocommerce-gdpr' ) . '</p>';

				// Set a paragraph around the checkboxes.
				$build .= '<p class="lw-woo-gdpr-data-options lw-woo-gdpr-download-files">';

				// Now loop my types.
				foreach ( $datatypes as $type => $label ) {

					// If we have no file for this export type, skip it.
					if ( empty( $files[ $type ] ) ) {
						continue;
					}

					// Make my download and delete links.
					$download   = add_query_arg( array( 'data-type' => $type, 'gdpr-action' => 'download' ), $base );
					$delete     = add_query_arg( array( 'data-type' => $type, 'gdpr-action' => 'delete' ), $base );

					// Open up the span.
					$build .= '<span class="lw-woo-gdpr-data-option lw-woo-gdpr-download-option">';

						// The link fields.
						$build .= '<span class="data-option-label">' . esc_html( $label ) . '</span>';
						$build .= '<a href="' . esc_url( $download ) . '">' . esc_html__( 'Download' ) . '</a>';
						$build .= '&nbsp;|&nbsp;';
						$build .= '<a href="' . esc_url( $delete ) . '">' . esc_html__( 'Delete' ) . '</a>';

					// Close the span.
					$build .= '</span>';
				}

				// Close the paragraph.
				$build .= '</p>';

			} else { // Show a message saying no files exist.
				$build .= '<p>' . esc_html__( 'You have not generated any export files.', 'liquidweb-woocommerce-gdpr' ) . '</p>';
			}

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
