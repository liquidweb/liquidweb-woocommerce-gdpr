<?php
/**
 * Our general admin.
 *
 * Create the WP Admin setup.
 *
 * @package LiquidWeb_Woo_GDPR
 */

/**
 * Start our engines.
 */
class LW_Woo_GDPR_Admin {

	/**
	 * Call our hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_init',                           array( $this, 'process_delete_request'      )           );
		add_action( 'admin_enqueue_scripts',                array( $this, 'load_admin_assets'           ),  10      );
		add_action( 'admin_menu',                           array( $this, 'load_settings_menu'          ),  99      );
	}

	/**
	 * Check for an incoming delete request from the admin.
	 *
	 * @return void
	 */
	public function process_delete_request() {

		// Make sure we have the action we want.
		if ( empty( $_POST['gdpr-admin-request'] ) || empty( $_POST['action'] ) || 'lw_woo_gdpr_admin_delete' !== esc_attr( $_POST['action'] ) ) {
			return;
		}

		// The nonce check. ALWAYS A NONCE CHECK.
		if ( ! isset( $_POST['lw_woo_gdpr_admin_nonce'] ) || ! wp_verify_nonce( $_POST['lw_woo_gdpr_admin_nonce'], 'lw_woo_gdpr_admin_action' ) ) {
			return;
		}

		// Make sure we have users to process.
		if ( empty( $_POST['lw_woo_gdpr_delete_option'] ) ) {
			return false; // @@todo  add some error handling.
		}

		// Sanitize all the users included.
		$users  = array_map( 'absint', $_POST['lw_woo_gdpr_delete_option'] );

		// Make sure we have users to process, still.
		if ( empty( $users ) ) {
			return false; // @@todo  add some error handling.
		}

		// Set a total (blank) for now.
		$total  = array();

		// Now loop my users and process accordingly.
		foreach ( $users as $user_id ) {

			// Fetch my data types and downloads.
			$datatypes  = get_user_meta( $user_id, 'woo_gdpr_deleteme_request', true );

			// Handle my datatypes if we have them.
			if ( ! empty( $datatypes ) ) {

				// Loop my types.
				foreach ( $datatypes as $datatype ) {

					// Remove any files from the meta we have for this data type.
					lw_woo_gdpr()->remove_file_from_meta( $user_id, $datatype );

					// Delete the datatype and add it to the count.
					if ( false !== $items = lw_woo_gdpr()->delete_userdata( $user_id, $datatype ) ) {
						$total[] = $items;
					}
				}
			}

			// Remove any data files we may have.
			lw_woo_gdpr()->delete_user_files( $user_id );

			// Remove this user from our overall data array.
			lw_woo_gdpr()->update_user_delete_requests( $user_id, null, 'remove' );
		}

		// Remove any empties.
		$total  = array_filter( $total );

		// Set a count total.
		$count  = ! empty( $total ) ? array_sum( $total ) : 0;

		// Now set my redirect link.
		$link   = add_query_arg( array( 'success' => 1, 'action' => 'purged', 'count' => absint( $count ) ), menu_page_url( 'pending-gdpr-requests', 0 ) );

		// Do the redirect.
		wp_redirect( $link );
		exit;
	}

	/**
	 * Load our admin side JS and CSS.
	 *
	 * @todo add conditional loading for the assets.
	 *
	 * @return void
	 */
	public function load_admin_assets( $hook ) {

		// Set a file suffix structure based on whether or not we want a minified version.
		$file   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? 'liquidweb-woo-gdpr-admin' : 'liquidweb-woo-gdpr-admin.min';

		// Set a version for whether or not we're debugging.
		$vers   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : LW_WOO_GDPR_VER;

		// Load our CSS file.
		wp_enqueue_style( 'liquidweb-woo-gdpr-admin', LW_WOO_GDPR_ASSETS_URL . '/css/' . $file . '.css', false, $vers, 'all' );
	}

	/**
	 * Load our menu item.
	 *
	 * @return void
	 */
	public function load_settings_menu() {

		// Add our submenu page.
		add_submenu_page(
			'woocommerce',
			__( 'Pending GDPR Requests', 'liquidweb-woocommerce-gdpr' ),
			__( 'GDPR Requests', 'liquidweb-woocommerce-gdpr' ),
			'manage_options',
			'pending-gdpr-requests',
			array( __class__, 'settings_page_view' )
		);
	}

	/**
	 * Our actual settings page for things.
	 *
	 * @return mixed
	 */
	public static function settings_page_view() {

		// Get any pending requests.
		$requests   = get_option( 'lw_woo_gdrp_delete_requests', array() );
		$totalcount = ! empty( $requests ) ? count( $requests ) : 0;

		// Wrap the entire thing.
		echo '<div class="wrap lw-woo-gdpr-requests-admin-wrap">';

			// Handle the title.
			echo '<h1 class="lw-woo-gdpr-requests-admin-title">' . get_admin_page_title() . '</h1>';

			// Output some context.
			echo '<p>' . sprintf( _n( 'You currently have %d pending GDPR "Delete Me" request.', 'You currently have %d pending GDPR "Delete Me" requests.', absint( $totalcount ), 'liquidweb-woocommerce-gdpr' ), absint( $totalcount ) );

			// Handle our export form, but only if we have some.
			if ( ! empty( $totalcount ) ) {

				// Wrap it all in a form.
				echo '<form class="lw-woo-gdpr-requests-admin-form" action="" method="post">';

					// Set a paragraph around the checkboxes.
					echo '<p class="lw-woo-gdpr-admin-data-options lw-woo-gdpr-requests-admin-data-options">';

					// Now loop my types.
					foreach ( $requests as $user_id => $datatypes ) {

						// Get my user object.
						$user   = get_user_by( 'id', $user_id );

						// Open up the span.
						echo '<span class="lw-woo-gdpr-admin-data-option lw-woo-gdpr-requests-admin-data-option">';

							// The input field.
							echo '<input name="lw_woo_gdpr_delete_option[]" id="delete-option-' . absint( $user->ID ) . '" type="checkbox" value="' . absint( $user->ID ) . '" >';

							// The label field.
							echo lw_woo_gdpr_create_delete_label( $user, $datatypes );

						// Close the span.
						echo '</span>';
					}

					// Close the paragraph.
					echo '</p>';

					// Handle the nonce.
					echo wp_nonce_field( 'lw_woo_gdpr_admin_action', 'lw_woo_gdpr_admin_nonce', false, false );

					// And the submit button.
					echo get_submit_button( __( 'Delete Requested Data', 'liquidweb-woocommerce-gdpr' ), 'primary', 'gdpr-admin-request', true, array( 'id' => 'gdpr-admin-request' ) );

					// And our hidden field.
					echo '<input name="action" value="lw_woo_gdpr_admin_delete" type="hidden">';

				// Close the form.
				echo '</form>';
			}

		// Close the entire thing.
		echo '</div>';
	}

	// End our class.
}

// Call our class.
$LW_Woo_GDPR_Admin = new LW_Woo_GDPR_Admin();
$LW_Woo_GDPR_Admin->init();
