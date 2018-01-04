<?php
/**
 * Our utilities file.
 *
 * Our various utilities used in the plugin.
 *
 * @package LiquidWeb_Woo_GDPR
 */

if ( ! function_exists( 'preprint' ) ) {
	/**
	 * Display array results in a readable fashion.
	 *
	 * @param  mixed   $display  The output we want to display.
	 * @param  boolean $die      Whether or not to die as soon as output is generated.
	 * @param  boolean $return   Whether to return the output or show it.
	 *
	 * @return mixed             Our printed (or returned) output.
	 */
	function preprint( $display, $die = false, $return = false ) {

		// Add some CSS to make it a bit more readable.
		$style  = 'background-color: #fff; color: #000; font-size: 16px; line-height: 22px; padding: 5px; white-space: pre-wrap; white-space: -moz-pre-wrap; white-space: -pre-wrap; white-space: -o-pre-wrap; word-wrap: break-word;';

		// Filter the style.
		$style  = apply_filters( 'preprint_style', $style );

		// Set up the code itself.
		$code   = print_r( $display, 1 );

		// Generate the actual output.
		$output = wp_doing_ajax() ? $code : '<pre style="' . $style . '">' . $code . '</pre>';

		// Return if requested.
		if ( $return ) {
			return $output;
		}

		// Print if requested (the default).
		if ( ! $return ) {
			print $output;
		}

		// Die if you want to die.
		if ( $die ) {
			die();
		}
	}

	// End the function exists checks.
}

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
