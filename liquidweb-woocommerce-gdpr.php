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

		// Set our front menu endpoint constant.
		if ( ! defined( 'LW_WOO_GDPR_FRONT_VAR' ) ) {
			define( 'LW_WOO_GDPR_FRONT_VAR', 'gdpr-actions' );
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
		require_once LW_WOO_GDPR_INCLS . '/class-query-mods.php';

		// Load the classes that are only accessible via admin.
		if ( is_admin() ) {
		}

		// Load the classes that are only accessible via the front end.
		if ( ! is_admin() ) {
			require_once LW_WOO_GDPR_INCLS . '/class-account.php';
		}

		// Load our installer and uninstaller.
		require_once LW_WOO_GDPR_INCLS . '/install.php';
		require_once LW_WOO_GDPR_INCLS . '/uninstall.php';
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
		$lang_dir = apply_filters( 'cs_message_languages_dir', $lang_dir );

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
		$mofile_global = WP_LANG_DIR . '/cloudsites-messenger/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/cloudsites-messenger/ folder
			load_textdomain( 'liquidweb-woocommerce-gdpr', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/cloudsites-messenger/languages/ folder
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
