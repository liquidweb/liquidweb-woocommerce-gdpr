<?php
/**
 * Our settings tab in WooCommerce for handling opt-ins.
 *
 * @package LiquidWeb_Woo_GDPR
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Start our engines.
 */
class LW_Woo_GDPR_SettingsTab {

	/**
	 * Call our hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'woocommerce_settings_tabs_array',                  array( $this, 'add_settings_tab'            ),  50      );
		add_action( 'woocommerce_settings_tabs_gdpr_optins',            array( $this, 'settings_tab'                )           );
		add_action( 'woocommerce_update_options_gdpr_optins',           array( $this, 'update_settings'             )           );
		add_action( 'woocommerce_admin_field_repeating_group',          array( $this, 'output_repeating_group'      ),  10, 1   );
	}

	/**
	 * Output our custom repeating field.
	 *
	 * @param  array $args  The field args we set up.
	 *
	 * @return HTML
	 */
	public function output_repeating_group( $args ){

		// Output the initial description context.
		echo '<p>' . $args['desc'] . '</p>';

		echo '';
	}

	/**
	 * Add a new settings tab to the WooCommerce settings tabs array.
	 *
	 * @param  array $tabs  The current array of WooCommerce setting tabs.
	 *
	 * @return array $tabs  The modified array of WooCommerce setting tabs.
	 */
	public function add_settings_tab( $tabs ) {

		// Confirm we don't already have the tab.
		if ( ! isset( $tabs['gdpr_optins'] ) ) {
			$tabs['gdpr_optins'] = __( 'GDPR Settings', 'liquidweb-woocommerce-gdpr' );
		}

		// And return the entire array.
		return $tabs;
	}

	/**
	 * Uses the WooCommerce admin fields API to output settings.
	 *
	 * @see  woocommerce_admin_fields() function.
	 *
	 * @uses woocommerce_admin_fields()
	 * @uses self::get_settings()
	 */
	public function settings_tab() {
		woocommerce_admin_fields( self::get_settings() );
	}

	/**
	 * Uses the WooCommerce options API to save settings
	 *
	 * @see  woocommerce_update_options() function.
	 *
	 * @uses woocommerce_update_options()
	 * @uses self::get_settings()
	 */
	public function update_settings() {
		woocommerce_update_options( self::get_settings() );
	}

	/**
	 * Create the array of opt-ins we are going to display.
	 *
	 * @return array $settings  The array of settings data.
	 */
	public static function get_settings() {

		// Set up our array, including default Woo items.
		$setup  = array(

			// Include the header portion.
			'header'    => array( 'name' => __( 'Opt-In Fields', 'liquidweb-woocommerce-gdpr' ), 'type' => 'title', 'id' => 'lw_woo_gdpr_header' ),

			// Now our opt-in fields, which is just one repeating field.
			'optins' => array(
				'type' => 'repeating_group',
				'desc' => __( 'This will likely have some context we wanna display.', 'liquidweb-woocommerce-gdpr' ),
				'id'   => ''
			),

			// Include my section end.
			'section_end' => array( 'type' => 'sectionend', 'id' => 'lw_woo_gdpr_section_end' ),
		);

		// Return our set of fields with a filter.
		return apply_filters( 'lw_woo_gdpr_optin_settings_array', $setup );
	}

	// End our class.
}

// Call our class.
$LW_Woo_GDPR_SettingsTab = new LW_Woo_GDPR_SettingsTab();
$LW_Woo_GDPR_SettingsTab->init();
