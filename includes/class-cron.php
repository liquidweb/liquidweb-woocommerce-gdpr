<?php
/**
 * Our cron section.
 *
 * Handle our various WP-Cron setups.
 *
 * @package LiquidWeb_Woo_GDPR
 */


add_action( 'lw_woo_gdpr_file_cleanup', 'lw_woo_gdpr_run_file_cleanup' );
/**
 * Set our function up to fetch and delete the old archives.
 *
 * @return void
 */
function lw_woo_gdpr_run_file_cleanup() {

	// Add the optional check for disabling the process.
	if ( false !== apply_filters( 'lw_woo_gdpr_disable_file_cleanup', false ) ) {
		return;
	}

	// Run our delete function.
	lw_woo_gdpr()->delete_expired_files();

	// And finish up.
	return;
}


add_action( 'wp', 'lw_woo_gdpr_activate_file_cleanup' );
/**
 * Add our scheduled event to run daily.
 *
 * @return void
 */
function lw_woo_gdpr_activate_file_cleanup() {

	// Only schedule my event if it isn't already.
    if ( ! wp_next_scheduled( 'lw_woo_gdpr_file_cleanup' ) ) {
        wp_schedule_event( current_time( 'timestamp' ), 'daily', 'lw_woo_gdpr_file_cleanup' );
    }
}
