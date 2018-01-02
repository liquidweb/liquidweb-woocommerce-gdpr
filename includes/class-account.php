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
		$customer   = new WC_Customer( get_current_user_id() );
		// preprint( $customer, true );
		?>

		<p>This will have the stuff for doing GDPR Compliance</p>


		<?php
	}

	// End our class.
}

// Call our class.
$LW_Woo_GDPR_Account = new LW_Woo_GDPR_Account();
$LW_Woo_GDPR_Account->init();
