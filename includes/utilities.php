<?php
/**
 * Our utilities file.
 *
 * Our various utilities used in the plugin.
 *
 * @package LiquidWeb_Woo_GDPR
 */

if ( ! function_exists( 'lw_woo_gdpr_is_dir_empty' ) ) {
	/**
	 * Look inside a directory and say if it has files or not.
	 *
	 * @param  string $directory  The name of the directory.
	 *
	 * @return boolean
	 */
	function lw_woo_gdpr_is_dir_empty( $directory = '' ) {

		// Bail if we never got a directory.
		if ( empty( $directory ) ) {
			return false;
		}

		// Loop my directory for fileinfo.
		foreach ( new DirectoryIterator( $directory ) as $fileinfo ) {

			// Check for the dot.
			if ( $fileinfo->isDot() ) {
				continue;
			}

			// We have false, since it didn't dot.
			return false;
		}

		// Return true, it passed.
		return true;
	}
	// End the function exists checks.
}

if ( ! function_exists( 'lw_woo_gdpr_check_admin_screen' ) ) {
	/**
	 * Do the whole 'check current screen' progressions.
	 *
	 * @param  string $check    The type of comparison we want to do.
	 * @param  string $compare  What we want to compare against on the screen.
	 * @param  string $action   If we want to return the value or compare it against something.
	 *
	 * @return boolean          Whether or not we are.
	 */
	function lw_woo_gdpr_check_admin_screen( $check = 'post_type', $compare = '', $action = 'compare' ) {

		// Bail if not on admin or our function doesnt exist.
		if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		// Get my current screen.
		$screen = get_current_screen();

		// Bail without.
		if ( empty( $screen ) || ! is_object( $screen ) ) {
			return false;
		}

		// If the check is false, return the entire screen object.
		if ( empty( $check ) || ! empty( $action ) && 'object' === sanitize_key( $action ) ) {
			return $screen;
		}

		// Do the post type check.
		if ( 'post_type' === $check ) {

			// If we have no post type, it's false right off the bat.
			if ( empty( $screen->post_type ) ) {
				return false;
			}

			// Handle my different action types.
			switch ( $action ) {

				case 'compare' :
					return ! empty( $compare ) && sanitize_key( $compare ) === $screen->post_type ? true : false;
					break;

				case 'return' :
					return $screen->post_type;
					break;
			}
		}

		// Do the base check.
		if ( 'base' === $check ) {

			// If we have no base, it's false right off the bat.
			if ( empty( $screen->base ) ) {
				return false;
			}

			// Handle my different action types.
			switch ( $action ) {

				case 'compare' :
					return ! empty( $compare ) && sanitize_key( $compare ) === $screen->base ? true : false;
					break;

				case 'return' :
					return $screen->base;
					break;
			}
		}

		// Do the ID check.
		if ( in_array( $check, array( 'id', 'ID' ) ) ) {

			// If we have no ID, it's false right off the bat.
			if ( empty( $screen->id ) ) {
				return false;
			}

			// Handle my different action types.
			switch ( $action ) {

				case 'compare' :
					return ! empty( $compare ) && sanitize_key( $compare ) === $screen->id ? true : false;
					break;

				case 'return' :
					return $screen->id;
					break;
			}
		}

		// Nothing left. bail.
		return false;
	}

	// End the function exists checks.
}


if ( ! function_exists( 'lw_woo_gdpr_gmt_to_local' ) ) {
	/**
	 * Take a GMT timestamp and convert it to the local.
	 *
	 * @param  integer $timestamp  The timestamp in GMT.
	 * @param  string  $format     What date format we want to return. False for the timestamp.
	 *
	 * @return integer $timestamp  The timestamp in GMT.
	 */
	function lw_woo_gdpr_gmt_to_local( $timestamp = 0, $format = 'Y/m/d g:i:s' ) {

		// Bail if we don't have a timestamp to check.
		if ( empty( $timestamp ) ) {
			return;
		}

		// Fetch our timezone.
		$savedzone  = get_option( 'timezone_string', 'GMT' );

		// Pull my stored time with the UTC code on it.
		$date_gmt   = new DateTime( date( 'Y-m-d H:i:s', $timestamp ), new DateTimeZone( 'GMT' ) );

		// Now set the timezone to return the date.
		$date_gmt->setTimezone( new DateTimeZone( $savedzone ) );

		// Return it formatted, or the timestamp.
		return ! empty( $format ) ? $date_gmt->format( $format ) : $date_gmt->format( 'U' );
	}

	// End the function exists checks.
}


if ( ! function_exists( 'lw_woo_gdpr_make_action_key' ) ) {
	/**
	 * Take a string and make it all cleaned up for using in an action.
	 *
	 * @param  string $key     What date format we want to return. False for the timestamp.
	 * @param  string $prefix  What to begin the name with. Optional.
	 * @param  string $suffix  What to end the name with. Optional.
	 *
	 * @return string          The name used in the action.
	 */
	function lw_woo_gdpr_make_action_key( $key = '', $prefix = 'lw_woo_gdpr_', $suffix = '_optin' ) {

		// Bail if we don't have a key to check.
		if ( empty( $key ) ) {
			return;
		}

		// Run the main cleanup.
		$clean  = sanitize_key( $key );

		// Now swap the dashes for underscores.
		$strip  = str_replace( array( '-', ' ' ), '_', $clean );

		// Return it.
		return esc_attr( $prefix ) . esc_attr( $strip ) . esc_attr( $suffix );
	}

	// End the function exists checks.
}


if ( ! function_exists( 'lw_woo_gdpr_remain_count_class' ) ) {
	/**
	 * Check the amount of days remaining on a request and return the class.
	 *
	 * @param  integer $remain  The amount of days remaining.
	 *
	 * @return string
	 */
	function lw_woo_gdpr_remain_count_class( $remain = 0 ) {

		// Set my base class.
		$class  = 'lw-woo-gdpr-days-remain-count';

		// Run a basic switch to see the amount of days remaining.
		switch ( true ) {

			// Over or expired.
			case absint( $remain ) < 1:
				return $class . ' lw-woo-gdpr-days-remain-red lw-woo-gdpr-days-remain-expired';
				break;

			// Almost there.
			case absint( $remain ) < 5:
				return $class . ' lw-woo-gdpr-days-remain-red';
				break;

			// Begin a warning class.
			case absint( $remain ) < 15:
				return $class . ' lw-woo-gdpr-days-remain-orange';
				break;

			// Our general OK.
			case absint( $remain ) <= 30:
				return $class . ' lw-woo-gdpr-days-remain-green';
				break;

			// Our default class.
			default :
				return $class;
				break;
		}
	}

	// End the function exists checks.
}
