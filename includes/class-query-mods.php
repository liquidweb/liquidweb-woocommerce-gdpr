<?php
/**
 * Functions that alter or otherwise modify a query.
 *
 * New enpoints, etc.
 *
 * @package LiquidWeb_Woo_GDPR
 */

/**
 * Start our engines.
 */
class LW_Woo_GDPR_QueryMods {

	/**
	 * Call our hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init',                                             array( $this, 'add_rewrite_endpoint'        )           );
		add_filter( 'query_vars',                                       array( $this, 'add_endpoint_vars'           ),  0       );
	}

	/**
	 * Register new endpoint to use inside My Account page.
	 *
	 * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
	 */
	public function add_rewrite_endpoint() {
		add_rewrite_endpoint( LW_WOO_GDPR_FRONT_VAR, EP_ROOT | EP_PAGES );
	}

	/**
	 * Add new query var for the GDPR endpoint.
	 *
	 * @param  array $vars  The existing query vars.
	 *
	 * @return array
	 */
	public function add_endpoint_vars( $vars ) {

		// Add our new endpoint var if we don't already have it.
		if ( ! in_array( LW_WOO_GDPR_FRONT_VAR, $vars ) ) {
			$vars[] = LW_WOO_GDPR_FRONT_VAR;
		}

		// And return it.
		return $vars;
	}

	// End our class.
}

// Call our class.
$LW_Woo_GDPR_QueryMods = new LW_Woo_GDPR_QueryMods();
$LW_Woo_GDPR_QueryMods->init();
