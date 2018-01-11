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

		// And our JS.
		wp_enqueue_script( 'liquidweb-woo-gdpr-admin', LW_WOO_GDPR_ASSETS_URL . '/js/' . $file . '.js', array( 'jquery' ), $vers, true );
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

		// Do a check for the counts.
		$req_count  = ! empty( $requests ) ? count( $requests ) : 0;

		// Wrap the entire thing.
		echo '<div class="wrap lw-woo-gdpr-requests-admin-wrap">';

			// Handle the title.
			echo '<h1 class="lw-woo-gdpr-requests-admin-title">' . get_admin_page_title() . '</h1>';

			// Output some context.
			echo '<p>' . sprintf( _n( 'You currently have %d pending GDPR "Delete Me" request.', 'You currently have %d pending GDPR "Delete Me" requests.', absint( $req_count ), 'liquidweb-woocommerce-gdpr' ), absint( $req_count ) );

			// Handle our export form, but only if we have some.
			echo self::delete_request_form( $requests );

		// Close the entire thing.
		echo '</div>';
	}

	/**
	 * Create and return the user list table of delete requests.
	 *
	 * @param  array  $requests  The existing requests.
	 *
	 * @return HTML
	 */
	public static function delete_request_form( $requests = array() ) {

		// Bail if we don't have any.
		if ( empty( $requests ) ) {
			return;
		}

		// Set our empty.
		$build  = '';

		// Wrap it all in a form.
		$build .= '<form class="lw-woo-gdpr-requests-admin-form" action="" method="post">';

			// Handle our hidden action field and the nonce.
			$build .= '<input name="action" value="lw_woo_gdpr_admin_delete" type="hidden">';
			$build .= wp_nonce_field( 'lw_woo_gdpr_admin_action', 'lw_woo_gdpr_admin_nonce', false, false );

			// Set up the table itself.
			$build .= '<table class="wp-list-table widefat fixed striped lw-woo-gdpr-requests-admin-table">';

			// Our table header.
			$build .= '<thead>';

				// Open up our row.
				$build .= '<tr>';

					// Our checkbox "check all" field.
					$build .= '<td class="checkbox-column">';
						$build .= '<label class="screen-reader-text" for="cb-select-all-1">' . esc_html__( 'Select All', 'liquidweb-woocommerce-gdpr' ) . '</label>';
						$build .= '<input class="lw-woo-gdpr-requests-select-all" id="cb-select-all-1" type="checkbox">';
					$build .= '</td>';

					// The rest of our table headers.
					$build .= '<th>' . esc_html__( 'User Name', 'liquidweb-woocommerce-gdpr' ) . '</th>';
					$build .= '<td>' . esc_html__( 'User Email', 'liquidweb-woocommerce-gdpr' ) . '</td>';
					$build .= '<td>' . esc_html__( 'Request Date', 'liquidweb-woocommerce-gdpr' ) . '</td>';
					$build .= '<td>' . esc_html__( 'Data Types', 'liquidweb-woocommerce-gdpr' ) . '</td>';

				// Close up our row.
				$build .= '</tr>';

			// Close out the table header.
			$build .= '</thead>';

			// Now the table body.
			$build .= '<tbody>';

			// Now loop my types.
			foreach ( $requests as $user_id => $request_data ) {

				// Skip any that have no types requested.
				if ( empty( $request_data['datatypes'] ) ) {
					continue;
				}

				// Get my date info.
				$date   = ! empty( $request_data['timestamp'] ) ? date( 'Y/m/d \a\t g:ia', $request_data['timestamp'] ) : __( 'unknown', 'liquidweb-woocommerce-gdpr' );

				// Sanitize all the types we have.
				$types  = array_map( 'sanitize_text_field', $request_data['datatypes'] );

				// Get my user object.
				$user   = get_user_by( 'id', $user_id );

				// And set up the row.
				$build .= '<tr>';

					// Our checkbox for the single user.
					$build .= '<td class="checkbox-column">';
						$build .= '<label class="screen-reader-text" for="cb-select-' . absint( $user_id ) . '">' . esc_html__( 'Select All', 'liquidweb-woocommerce-gdpr' ) . '</label>';
						$build .= '<input class="lw-woo-gdpr-requests-select-single" id="cb-select-' . absint( $user_id ) . '" name="lw_woo_gdpr_delete_option[]" value="' . absint( $user_id ) . '" type="checkbox">';
					$build .= '</td>';

					// Now the rest of the fields.
					$build .= '<th><a title="' . __( 'View profile', 'liquidweb-woocommerce-gdpr' ) . '" href="' . get_edit_user_link( $user_id ) . '">' . esc_html( $user->display_name ) . '</a></th>';
					$build .= '<td><a title="' . __( 'Email user', 'liquidweb-woocommerce-gdpr' ) . '" href="mailto:' . esc_url( antispambot( $user->user_email ) ) . '">' . esc_html( antispambot( $user->user_email ) ) . '</a></td>';
					$build .= '<td>' . esc_html( $date ) . '</td>';
					$build .= '<td><em>' . implode( ', ', $types ) . '</em></td>';

				// And close up the row.
				$build .= '</tr>';
			}

			// Close up the table body.
			$build .= '</tbody>';

			// Our table footer.
			$build .= '<tfoot>';

				// Open up our row.
				$build .= '<tr>';
					$build .= '<td class="gutter-row" colspan="5">&nbsp;</td>';
				$build .= '</tr>';

			// Close out the table tfoot.
			$build .= '</tfoot>';

			// And now close the entire table.
			$build .= '</table>';

			// And the submit button.
			$build .= get_submit_button( __( 'Delete Requested Data', 'liquidweb-woocommerce-gdpr' ), 'primary', 'gdpr-admin-request', true, array( 'id' => 'gdpr-admin-request' ) );

		// Close the form.
		$build .= '</form>';

		// Return our entire build.
		return $build;
	}

	// End our class.
}

// Call our class.
$LW_Woo_GDPR_Admin = new LW_Woo_GDPR_Admin();
$LW_Woo_GDPR_Admin->init();
