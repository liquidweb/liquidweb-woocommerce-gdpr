<?php

/**
 * Delete various options when deactivating the plugin.
 *
 * @return void
 */
function lw_woo_gdpr_deactivate() {

	// Get the time setting.
	$check  = wp_next_scheduled( 'lw_woo_gdpr_file_cleanup' );

	// Remove the cron job.
	wp_unschedule_event( $check, 'lw_woo_gdpr_file_cleanup', array() );

	// Include our action so that we may add to this later.
	do_action( 'lw_woo_gdpr_deactivate_process' );

	// And flush our rewrite rules.
	flush_rewrite_rules();
}
register_deactivation_hook( LW_WOO_GDPR_FILE, 'lw_woo_gdpr_deactivate' );

