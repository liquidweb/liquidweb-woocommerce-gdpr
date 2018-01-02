<?php
/**
 * Delete various options when uninstalling the plugin.
 *
 * @return void
 */
function lw_woo_gdpr_uninstall() {

	// Include our action so that we may add to this later.
	do_action( 'lw_woo_gdpr_uninstall_process' );

	// And flush our rewrite rules.
	flush_rewrite_rules();
}
register_uninstall_hook( LW_WOO_GDPR_FILE, 'lw_woo_gdpr_uninstall' );

