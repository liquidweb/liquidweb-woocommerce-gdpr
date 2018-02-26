<?php
/**
 * Our generic front-end setup.
 *
 * @package LiquidWeb_Woo_GDPR
 */

/**
 * Start our engines.
 */
class LW_Woo_GDPR_FrontEnd {

	/**
	 * Call our hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts',                   array( $this, 'load_front_assets'           ),  10      );
	}

	/**
	 * Load our front end JS and CSS.
	 *
	 * @todo add conditional loading for the assets.
	 *
	 * @return void
	 */
	public function load_front_assets( $hook ) {

		// Set a file suffix structure based on whether or not we want a minified version.
		$file   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? 'liquidweb-woo-gdpr-front' : 'liquidweb-woo-gdpr-front.min';

		// Set a version for whether or not we're debugging.
		$vers   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : LW_WOO_GDPR_VER;

		// Load our CSS file.
		wp_enqueue_style( 'liquidweb-woo-gdpr-front', LW_WOO_GDPR_ASSETS_URL . '/css/' . $file . '.css', array( 'dashicons' ), $vers, 'all' );

		// And our JS.
		wp_enqueue_script( 'liquidweb-woo-gdpr-front', LW_WOO_GDPR_ASSETS_URL . '/js/' . $file . '.js', array( 'jquery' ), $vers, true );
	}

	// End our class.
}

// Call our class.
$LW_Woo_GDPR_FrontEnd = new LW_Woo_GDPR_FrontEnd();
$LW_Woo_GDPR_FrontEnd->init();
