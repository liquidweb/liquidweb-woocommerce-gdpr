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
		add_action( 'admin_enqueue_scripts',                array( $this, 'load_admin_assets'           ),  10      );
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

	// End our class.
}

// Call our class.
$LW_Woo_GDPR_Admin = new LW_Woo_GDPR_Admin();
$LW_Woo_GDPR_Admin->init();
