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
	 * The slugs being used for the menus.
	 */
	public static $menu_slug = LW_WOO_GDPR_MENU_BASE;
	public static $hook_slug = 'woocommerce_page_' . LW_WOO_GDPR_MENU_BASE;

	/**
	 * Call our hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_enqueue_scripts',                array( $this, 'load_admin_assets'           ),  10      );
		add_action( 'admin_notices',                        array( $this, 'process_request_notices'     )           );
		add_action( 'admin_menu',                           array( $this, 'load_settings_menu'          ),  99      );
	}

	/**
	 * Load our admin side JS and CSS.
	 *
	 * @todo add conditional loading for the assets.
	 *
	 * @return void
	 */
	public function load_admin_assets( $hook ) {

		// Check my hook before moving forward.
		if ( self::$hook_slug !== esc_attr( $hook ) ) {
			return;
		}

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
	 * Set up the admin notices.
	 *
	 * @return mixed
	 */
	public function process_request_notices() {

		// Make sure we have the page we want.
		if ( empty( $_GET['page'] ) || LW_WOO_GDPR_MENU_BASE !== esc_attr( $_GET['page'] ) ) {
			return;
		}

		// Do our check for the "my account" setting.
		if ( false === get_option( 'woocommerce_myaccount_page_id', 0 ) ) {

			// Handle the notice.
			echo '<div class="notice notice-warning lw-woo-gdpr-message">';
				echo '<p><strong>' . esc_html__( 'NOTICE:', 'liquidweb-woocommerce-gdpr' ) . '</strong> ' . esc_html__( 'You must set the "My Account" page option to use this plugin.', 'liquidweb-woocommerce-gdpr' ) . '</p>';
			echo '</div>';
		}

		// Now check to make sure we have a request response.
		if ( empty( $_GET['gdpr-request-response'] ) ) {
			return;
		}

		// Handle the success notice first.
		if ( ! empty( $_GET['success'] ) ) {

			// Output the message along with the dismissable.
			echo '<div class="notice notice-success is-dismissible lw-woo-gdpr-message">';
				echo '<p>' . esc_html__( 'Success! The requested data has been deleted.', 'liquidweb-woocommerce-gdpr' ) . '</p>';
			echo '</div>';

			// And be done.
			return;
		}

		// Figure out my error code.
		$error_code = ! empty( $_GET['errcode'] ) ? esc_attr( $_GET['errcode'] ) : 'unknown';

		// Determine my error text.
		$error_text = lw_woo_gdpr_notice_text( $error_code );

		// Output the message along with the dismissable.
		echo '<div class="notice notice-error is-dismissible lw-woo-gdpr-message">';
			echo '<p>' . wp_kses_post( $error_text ) . '</p>';
		echo '</div>';

		// And be done.
		return;
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
			self::$menu_slug,
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
		$requests   = lw_woo_gdpr_maybe_requests_exist();

		// Wrap the entire thing.
		echo '<div class="wrap lw-woo-gdpr-requests-admin-wrap">';

			// Handle the title.
			echo '<h1 class="lw-woo-gdpr-requests-admin-title">' . get_admin_page_title() . '</h1>';

			// Output some context.
			echo ! empty( $requests ) ? '<p>' . sprintf( _n( 'You currently have %d pending GDPR "Delete Me" request.', 'You currently have %d pending GDPR "Delete Me" requests.', count( $requests ), 'liquidweb-woocommerce-gdpr' ), count( $requests ) ) : '';

			// Handle our table, but only if we have some.
			echo self::pending_requests_table( $requests );

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
	public static function pending_requests_table( $requests = array() ) {

		// Bail if we don't have any.
		if ( empty( $requests ) ) {

			// Echo out the message.
			echo '<p>' . esc_html__( 'There are no pending requests.', 'liquidweb-woocommerce-gdpr' ) . '</p>';

			// And be done.
			return;
		}

		// Fetch the action link.
		$action = lw_woo_gdpr()->get_admin_menu_link();

		// Call our table class.
		$table  = new UserDeleteRequests_Table();

		// And output the table.
		$table->prepare_items();

		// And handle the display
		echo '<form class="lw-woo-gdpr-admin-form" id="lw-woo-gdpr-requests-admin-form" action="' . esc_url( $action ) . '" method="post">';

		// The actual table itself.
		$table->display();

		// And close it up.
		echo '</form>';
	}

	/**
	 * Handle our redirect within the admin settings page.
	 *
	 * @param  array $args  The query args to include in the redirect.
	 *
	 * @return void
	 */
	public static function admin_page_redirect( $args = array(), $response = true ) {

		// Don't redirect if we didn't pass any args.
		if ( empty( $args ) ) {
			return;
		}

		// Set my base link.
		$base   = lw_woo_gdpr()->get_admin_menu_link();

		// Add the default args we need in the return.
		$args   = ! empty( $response ) ? wp_parse_args( array( 'gdpr-request-response' => 1 ), $args ) : $args;

		// Now set my redirect link.
		$link   = add_query_arg( $args, $base );

		// Do the redirect.
		wp_redirect( $link );
		exit;
	}

	// End our class.
}

// Call our class.
$LW_Woo_GDPR_Admin = new LW_Woo_GDPR_Admin();
$LW_Woo_GDPR_Admin->init();
