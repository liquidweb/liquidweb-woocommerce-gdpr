<?php
/**
 * Our inital setup function when activated.
 *
 * @return void
 */
function lw_woo_gdpr_install() {

	// Include our action so that we may add to this later.
	do_action( 'lw_woo_gdpr_install_process' );
}
register_activation_hook( LW_WOO_GDPR_FILE, 'lw_woo_gdpr_install' );
