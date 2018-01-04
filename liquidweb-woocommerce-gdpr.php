<?php
/**
 * Plugin Name: Liquid Web WooCommerce GDPR
 * Plugin URI:  https://liquidweb.com/
 * Description: Be compliant.
 * Version:     0.0.1
 * Author:      Liquid Web
 * Author URI:  https://www.liquidweb.com
 * Text Domain: liquidweb-woocommerce-gdpr
 * Domain Path: /languages
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 *
 * @package LiquidWeb_Woo_GDPR
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Call our class.
 */
final class LW_Woo_GDPR {

	/**
	 * LW_Woo_GDPR instance.
	 *
	 * @access private
	 * @since  1.0
	 * @var    LW_Woo_GDPR The one true LW_Woo_GDPR
	 */
	private static $instance;

	/**
	 * The version number of LW_Woo_GDPR.
	 *
	 * @access private
	 * @since  1.0
	 * @var    string
	 */
	private $version = '0.0.1';

	/**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * retuns it.
	 *
	 * @return $instance
	 */
	public static function instance() {

		// Run the check to see if we have the instance yet.
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof LW_Woo_GDPR ) ) {

			// Set our instance.
			self::$instance = new LW_Woo_GDPR;

			// Set my plugin constants.
			self::$instance->setup_constants();

			// Run our version compare.
			if ( version_compare( PHP_VERSION, '5.6', '<' ) ) {

				// Deactivate the plugin.
				deactivate_plugins( LW_WOO_GDPR_BASE );

				// And display the notice.
				wp_die( sprintf( __( 'Your current version of PHP is below the minimum version required by the Liquid Web WooCommerce GDPR. Please contact your host and request that your version be upgraded to 5.6 or later. <a href="%s">Click here</a> to return to the plugins page.', 'liquidweb-woocommerce-gdpr' ), admin_url( '/plugins.php' ) ) );
			}

			// Set my file includes.
			self::$instance->includes();

			// Load our textdomain.
			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
		}

		// And return the instance.
		return self::$instance;
	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 1.0
	 * @access protected
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'liquidweb-woocommerce-gdpr' ), '1.0' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @since 1.0
	 * @access protected
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'liquidweb-woocommerce-gdpr' ), '1.0' );
	}

	/**
	 * Setup plugin constants
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function setup_constants() {

		// Define our file base.
		if ( ! defined( 'LW_WOO_GDPR_BASE' ) ) {
			define( 'LW_WOO_GDPR_BASE', plugin_basename( __FILE__ ) );
		}

		// Set our base directory constant.
		if ( ! defined( 'LW_WOO_GDPR_DIR' ) ) {
			define( 'LW_WOO_GDPR_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL.
		if ( ! defined( 'LW_WOO_GDPR_URL' ) ) {
			define( 'LW_WOO_GDPR_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin root file.
		if( ! defined( 'LW_WOO_GDPR_FILE' ) ) {
			define( 'LW_WOO_GDPR_FILE', __FILE__ );
		}

		// Set our includes directory constant.
		if ( ! defined( 'LW_WOO_GDPR_INCLS' ) ) {
			define( 'LW_WOO_GDPR_INCLS', __DIR__ . '/includes' );
		}

		// Set our assets directory constant.
		if ( ! defined( 'LW_WOO_GDPR_ASSETS' ) ) {
			define( 'LW_WOO_GDPR_ASSETS', __DIR__ . '/assets' );
		}

		// Set our assets directory constant.
		if ( ! defined( 'LW_WOO_GDPR_ASSETS_URL' ) ) {
			define( 'LW_WOO_GDPR_ASSETS_URL', LW_WOO_GDPR_URL . 'assets' );
		}

		// Set our front menu endpoint constant.
		if ( ! defined( 'LW_WOO_GDPR_FRONT_VAR' ) ) {
			define( 'LW_WOO_GDPR_FRONT_VAR', 'privacy-data' );
		}

		// Set our version constant.
		if ( ! defined( 'LW_WOO_GDPR_VER' ) ) {
			define( 'LW_WOO_GDPR_VER', $this->version );
		}
	}

	/**
	 * Load our actual files in the places they belong.
	 *
	 * @return void
	 */
	public function includes() {

		// Load our helper and utility setup.
		require_once LW_WOO_GDPR_INCLS . '/helper.php';
		require_once LW_WOO_GDPR_INCLS . '/utilities.php';

		// Load our various classes.
		require_once LW_WOO_GDPR_INCLS . '/class-fields.php';
		require_once LW_WOO_GDPR_INCLS . '/class-query-mods.php';
		require_once LW_WOO_GDPR_INCLS . '/class-export.php';
		require_once LW_WOO_GDPR_INCLS . '/class-cron.php';

		// Load the classes that are only accessible via admin.
		if ( is_admin() ) {
			require_once LW_WOO_GDPR_INCLS . '/class-admin.php';
		}

		// Load the classes that are only accessible via the front end.
		if ( ! is_admin() ) {
			require_once LW_WOO_GDPR_INCLS . '/class-front-end.php';
			require_once LW_WOO_GDPR_INCLS . '/class-checkout.php';
			require_once LW_WOO_GDPR_INCLS . '/class-account.php';
		}

		// Load our install, deactivate, and uninstall items.
		require_once LW_WOO_GDPR_INCLS . '/install.php';
		require_once LW_WOO_GDPR_INCLS . '/deactivate.php';
		require_once LW_WOO_GDPR_INCLS . '/uninstall.php';
	}

	/**
	 * Create our root level folder for holding exports.
	 *
	 * @param  string  $key  An optional key to return part of the data array.
	 *
	 * @return void
	 */
	public function create_export_folder( $key = '' ) {

		// Fetch the uploads folder.
		$uploads    = wp_get_upload_dir();

		// Set our raw base.
		$toplevel   = array(
			'dir'   => $uploads['basedir'] . '/woo-gdpr-exports/',
			'url'   => $uploads['baseurl'] . '/woo-gdpr-exports/',
		);

		// Create our folder (will return if already exists).
		wp_mkdir_p( $toplevel['dir'] );

		// And set it as an option.
		update_option( 'lw_woo_gdrp_export_folder', $toplevel, 'no' );

		// And return the folder and paths
		return ! empty( $toplevel[ $key ] ) ? $toplevel[ $key ] : $toplevel;
	}

	/**
	 * Get our root level folder for holding exports.
	 *
	 * @param  string  $key  An optional key to return part of the data array.
	 *
	 * @return void
	 */
	public function get_export_folder( $key = '' ) {

		// Check for the root base folder.
		$toplevel   = get_option( 'lw_woo_gdrp_export_folder', false );
		$toplevel   = ! empty( $toplevel ) ? $toplevel : $this->create_export_folder();

		// And return the folder and paths
		return ! empty( $toplevel[ $key ] ) ? $toplevel[ $key ] : $toplevel;
	}

	/**
	 * Set filename and create folder if need be for reuse.
	 *
	 * @param  string  $datatype  A flag for what time of export file it is.
	 * @param  integer $user_id   What user ID this is for.
	 * @param  string  $key       An optional key to return part of the data array.
	 *
	 * @return string/array Either the specific item (if key provided) or the entire array.
	 */
	public function set_export_filebase( $datatype = '', $user_id = 0, $key = '' ) {

		// Make sure we have everything required.
		if ( empty( $datatype ) || empty( $user_id ) ) {
			return false;
		}

		// Get my top level folder.
		$toplevel   = $this->get_export_folder();

		// Set our two base items.
		$basedir    = $toplevel . absint( $user_id ) . '/';
		$baseurl    = $toplevel . absint( $user_id ) . '/';

		// Create our folder (will return if already exists).
		wp_mkdir_p( $basedir );

		// Open the csv file, or generate if one does not exist.
		$filename   = 'woo-gdpr-export-' . esc_attr( $datatype ) . '.csv';

		// Set up our two file types.
		$dirfile    = apply_filters( 'lw_woo_gdpr_dirfile', $basedir . $filename, $datatype, $user_id );
		$urlfile    = apply_filters( 'lw_woo_gdpr_urlfile', $baseurl . $filename, $datatype, $user_id );

		// Set our data array.
		$filedata   = array(
			'root'  => trim( $basedir ),
			'base'  => trim( $baseurl ),
			'dir'   => trim( $dirfile ),
			'url'   => trim( $urlfile ),
		);

		// Filter it.
		$filedata   = apply_filters( 'lw_woo_gdpr_filebase_settings', $filedata );

		// If we somehow cleared out the filebase settings, return false.
		if ( empty( $filedata ) ) {
			return false;
		}

		// If we requested a single key, check for that.
		if ( ! empty( $key ) ) {
			return isset( $filedata[ $key ] ) ? $filedata[ $key ] : false;
		}

		// Send back the entire data array.
		return $filedata;
	}

	/**
	 * Fetch all of the export files available and make an array.
	 *
	 * @return array
	 */
	public function get_all_export_files() {

		// Get my top level folder.
		$toplevel   = $this->get_export_folder();

		// Filter out the non-numeric folders.
		$user_ids   = array_filter( scandir( $toplevel['dir'] ), 'absint' );

		// Bail if no user ID folders exist.
		if ( empty( $user_ids ) ) {
			return false;
		}

		// Set my empty data.
		$data   = null;

		// Now loop my user IDs and build an array.
		foreach ( $user_ids as $user_id ) {

			// Make my user folder.
			$user_dir   = $toplevel['dir'] . $user_id . '/';
			$user_url   = $toplevel['url'] . $user_id . '/';

			// If my directory is empty, skip.
			if ( lw_woo_gdpr_is_dir_empty( $user_dir ) ) {
				continue;
			}

			// Now loop and set up each file into an array.
			foreach ( glob( $user_dir . '*.csv' ) as $userfile ) {

				// Not sure how it could be empty, but...
				if ( empty( $userfile ) ) {
					continue;
				}

				// Set my variables.
				$filename   = str_replace( $user_dir, '', $userfile );
				$filetime   = filemtime( $userfile );

				// Set my expirey data.
				$expirerate = apply_filters( 'lw_woo_gdpr_file_expire', ( WEEK_IN_SECONDS * 2 ) );
				$expiretime = current_time( 'timestamp' ) - absint( $filetime );
				$is_expired = absint( $expiretime ) > absint( $expirerate ) ? true : false;

				// Now set up my data array.
				$data[ $user_id ][] = array(
					'filename'  => esc_attr( $filename ),
					'filelink'  => esc_url( $user_url . $filename ),
					'fileroot'  => esc_attr( $userfile ),
					'filetime'  => $filetime,
					'expired'   => $is_expired,
				);
			}
		}

		// Return the entire thing.
		return ! empty( $data ) ? $data : false;
	}

	/**
	 * Delete any expired files we have.
	 *
	 * @return void
	 */
	public function delete_expired_files() {

		// Our before action.
		do_action( 'lw_woo_gdpr_before_expired_delete' );

		// Get all my file data, bail without them.
		if ( false === $filedata = $this->get_all_export_files() ) {
			return;
		}

		// Now loop my file data and break it out by user.
		foreach ( $filedata as $user_id => $files ) {

			// Our after action.
			do_action( 'lw_woo_gdpr_before_expired_delete_user', $user_id );

			// Skip it if we have no files.
			if ( empty( $files ) ) {
				continue;
			}

			// Grab the existing meta.
			$downloads  = get_user_meta( $user_id, 'woo_gdpr_export_files', true );

			// Now loop the actual files in the array.
			foreach ( $files as $file ) {

				// If it isn't expired, skip it.
				if ( empty( $file['expired'] ) ) {
					continue;
				}

				// Now remove the actual file.
				@unlink( $file['fileroot'] );

				// If the file is in the meta, remove it.
				if ( ( $key = array_search( $file['fileroot'], (array) $downloads ) ) !== false ) {
					unset( $downloads[ $key ] );
				}
			}

			// Filter my remaining.
			$downloads  = array_filter( $downloads );

			// Either update the user meta, or delete it completely.
			if ( ! empty( $downloads ) ) {
				update_user_meta( $user_id, 'woo_gdpr_export_files', $downloads );
			} else {
				delete_user_meta( $user_id, 'woo_gdpr_export_files' );
			}

			// Our after action.
			do_action( 'lw_woo_gdpr_after_expired_delete_user', $user_id );
		}

		// Our after action.
		do_action( 'lw_woo_gdpr_after_expired_delete' );

		// Just return that we're good.
		return true;
	}
	/**
	 * Handle the actual file download from an export.
	 *
	 * @param  string $file_url  The URL of the file.
	 *
	 * @return void
	 */
	public function download_file( $file_url = '' ) {

		// First, create my filename.
		$filename   = pathinfo( $file_url, PATHINFO_BASENAME );

		// Output headers so that the file is downloaded rather than displayed.
		header( 'Content-type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . esc_attr( $filename ) . '"' );

		// Do not cache the file.
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// Create a file pointer connected to the output stream.
		$point  = fopen( 'php://output', 'w' );

		// Handle the readfile.
		readfile( esc_url( $file_url ) );

		// And exit.
		exit();
	}

	/**
	 * Handle the actual file download from an export.
	 *
	 * @param  string  $file_url   The URL of the file.
	 * @param  integer $user_id    What user this is tied to.
	 * @param  string  $datatype   What data type the file was.
	 * @param  array   $downloads  The name of the file.
	 *
	 * @return void
	 */
	public function delete_file( $file_url = '', $user_id = 0, $datatype = '', $downloads = array() ) {

		// Make sure we have everything required.
		if ( empty( $file_url ) || empty( $user_id ) || empty( $datatype ) || empty( $downloads ) ) {
			return false;
		}

		// First delete the file.
		wp_delete_file( esc_url( $file_url ) );

		// Now remove it from the existing.
		unset( $downloads[ $datatype ] );

		// Filter my remaining.
		$downloads  = array_filter( $downloads );

		// Either update the user meta, or delete it completely.
		if ( ! empty( $downloads ) ) {
			update_user_meta( $user_id, 'woo_gdpr_export_files', $downloads );
		} else {
			delete_user_meta( $user_id, 'woo_gdpr_export_files' );
		}

		// Now set my redirect link.
		$link   = add_query_arg( array( 'success' => 1, 'action' => 'delete' ), home_url( '/account/privacy-data/' ) );

		// Do the redirect.
		wp_redirect( $link );
		exit;
	}

	/**
	 * Loads the plugin language files
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function load_textdomain() {

		// Set filter for plugin's languages directory.
		$lang_dir = dirname( plugin_basename( LW_WOO_GDPR_FILE ) ) . '/languages/';

		/**
		 * Filters the languages directory path to use for LW_Woo_GDPR.
		 *
		 * @param string $lang_dir The languages directory path.
		 */
		$lang_dir = apply_filters( 'lw_woo_gdpr_languages_dir', $lang_dir );

		// Traditional WordPress plugin locale filter.

		global $wp_version;

		$get_locale = get_locale();

		if ( $wp_version >= 4.7 ) {
			$get_locale = get_user_locale();
		}

		/**
		 * Defines the plugin language locale used in LW_Woo_GDPR.
		 *
		 * @var $get_locale The locale to use. Uses get_user_locale()` in WordPress 4.7 or greater,
		 *                  otherwise uses `get_locale()`.
		 */
		$locale = apply_filters( 'plugin_locale', $get_locale, 'liquidweb-woocommerce-gdpr' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'liquidweb-woocommerce-gdpr', $locale );

		// Setup paths to current locale file.
		$mofile_local  = $lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/liquidweb-woocommerce-gdpr/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/liquidweb-woocommerce-gdpr/ folder
			load_textdomain( 'liquidweb-woocommerce-gdpr', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/liquidweb-woocommerce-gdpr/languages/ folder
			load_textdomain( 'liquidweb-woocommerce-gdpr', $mofile_local );
		} else {
			// Load the default language files.
			load_plugin_textdomain( 'liquidweb-woocommerce-gdpr', false, $lang_dir );
		}
	}

	// End our class.
}

/**
 * The main function responsible for returning the one true LW_Woo_GDPR
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $lw_woo_gdpr = lw_woo_gdpr(); ?>
 *
 * @since 1.0
 * @return LW_Woo_GDPR The one true LW_Woo_GDPR Instance
 */
function lw_woo_gdpr() {
	return LW_Woo_GDPR::instance();
}
lw_woo_gdpr();
