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
		add_action( 'woocommerce_admin_field_repeating_setup',          array( $this, 'output_repeating_setup'      ),  10, 1   );
		add_action( 'woocommerce_admin_field_repeating_group',          array( $this, 'output_repeating_group'      ),  10, 1   );
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
			$tabs['gdpr_optins'] = __( 'GDPR Opt-Ins', 'liquidweb-woocommerce-gdpr' );
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

		// Bail if we have neither.
		if ( empty( $_POST['gdpr-optin-new'] ) && empty( $_POST['gdpr-optin-current'] ) ) {
			return;
		}

		// Set my empty data array.
		$fields = array();

		// Check for the existing fields, and if it's there format the data.
		if ( ! empty( $_POST['gdpr-optin-current'] ) && false !== $current = LW_Woo_GDPR_Formatting::format_current_optin_fields( $_POST['gdpr-optin-current'] ) ) {

			// Add the item to our data array.
			$fields = wp_parse_args( $fields, $current );
		}

		// Check for the new field, and if it's there format the data.
		if ( ! empty( $_POST['gdpr-optin-new'] ) && false !== $new_field = LW_Woo_GDPR_Formatting::format_new_optin_field( $_POST['gdpr-optin-new'] ) ) {

			// Get the ID out of the returned data.
			$id = $new_field['id'];

			// And add it.
			$fields = wp_parse_args( array( $id => $new_field ), $fields );
		}

		// Update our option. // no idea how to use woocommerce_update_options();
		update_option( 'lw_woo_gdpr_optin_fields', $fields );

		// And be done.
		return;
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
			'header'   => array(
				'name' => __( 'Opt-In Fields', 'liquidweb-woocommerce-gdpr' ),
				'type' => 'repeating_setup',
				'text' => __( 'Below is each checkbox the user will need to opt-in to.', 'liquidweb-woocommerce-gdpr' ),
			),

			// Now our opt-in fields, which is just one repeating field.
			'optins'   => array(
				'type' => 'repeating_group',
			),

			// Include my section end.
			'section_end' => array( 'type' => 'sectionend', 'id' => 'lw_woo_gdpr_section_end' ),
		);

		// Return our set of fields with a filter.
		return apply_filters( 'lw_woo_gdpr_optin_settings_array', $setup );
	}

	/**
	 * Output our custom repeating field.
	 *
	 * @param  array $args  The field args we set up.
	 *
	 * @return HTML
	 */
	public function output_repeating_group( $args ){

		// Fetch my existing fields.
		if ( false === $fields = lw_woo_gdpr_optin_fields() ) {
			return;
		}

		// Wrap my entire table.
		echo '<table id="lw-woo-gdpr-fields-table" class="lw-woo-gdpr-fields-table-wrap lw-woo-gdpr-saved-table-wrap wp-list-table widefat fixed striped">';

		// Set up the table header.
		echo '<thead>';
			echo '<tr>';
				echo '<th class="lw-woo-gdpr-field-header lw-woo-gdpr-field-required lw-woo-gdpr-field-header-required" scope="col">';
					echo '<i title="' . __( 'Required', 'liquidweb-woocommerce-gdpr' ) . '" class="dashicons dashicons-post-status"></i>';
					echo '<span class="screen-reader-text">' . __( 'Required', 'liquidweb-woocommerce-gdpr' ) . '</span>';
				echo '</th>';
				echo '<th class="lw-woo-gdpr-field-header lw-woo-gdpr-field-title lw-woo-gdpr-field-header-title" scope="col">' . __( 'Title', 'liquidweb-woocommerce-gdpr' ) . '</th>';
				echo '<th class="lw-woo-gdpr-field-header lw-woo-gdpr-field-label lw-woo-gdpr-field-header-label" scope="col">' . __( 'Label', 'liquidweb-woocommerce-gdpr' ) . '</th>';
				echo '<th class="lw-woo-gdpr-field-header lw-woo-gdpr-field-hook lw-woo-gdpr-field-header-hook" scope="col">' . __( 'Hook', 'liquidweb-woocommerce-gdpr' ) . '</th>';
				echo '<th class="lw-woo-gdpr-field-header lw-woo-gdpr-field-trigger lw-woo-gdpr-field-header-trigger" scope="col">&nbsp;</th>';
			echo '</tr>';
		echo '</thead>';

		// Set the table body.
		echo '<tbody>';

		// Loop my fields and make a block of each one.
		foreach ( $fields as $field ) {

			// Create my name field and confirm the action name.
			$name   = 'gdpr-optin-current[' . esc_attr( $field['id'] ) . ']';
			$check  = ! empty( $field['required'] ) ? true : false;
			$action = ! empty( $field['action'] ) ? $field['action'] : lw_woo_gdpr_make_action_key( $field['id'] );

			// Set our delete link.
			$delete = '';

			// Set up the single div.
			echo '<tr id="lw-woo-gdpr-field-' . esc_attr( $field['id'] ) . '" class="lw-woo-gdpr-field-single">';

				// Output the required checkbox.
				echo '<td class="lw-woo-gdpr-field-item lw-woo-gdpr-field-required">';
					echo '<input type="checkbox" class="lw-woo-gdpr-field-input" name="' . esc_attr( $name ) . '[required]" value="1" ' . checked( $check, 1, false ) . '>';
				echo '</td>';

				// Output the title field.
				echo '<td class="lw-woo-gdpr-field-item lw-woo-gdpr-field-title">';
					echo '<input type="text" class="widefat lw-woo-gdpr-field-input" name="' . esc_attr( $name ) . '[title]" value="' . esc_attr( $field['title'] ) . '">';
				echo '</td>';

				// Output the label field.
				echo '<td class="lw-woo-gdpr-field-item lw-woo-gdpr-field-label">';
					echo '<input type="text" class="widefat lw-woo-gdpr-field-input" name="' . esc_attr( $name ) . '[label]" value="' . esc_attr( $field['label'] ) . '">';
				echo '</td>';

				// Output the hook name field.
				echo '<td class="lw-woo-gdpr-field-item lw-woo-gdpr-field-hook">';
					echo '<input type="text" class="widefat code lw-woo-gdpr-field-input" readonly="readonly" value="' . esc_attr( $action ) . '">';
					echo '<input type="hidden" name="' . esc_attr( $name ) . '[action]" value="' . esc_attr( $action ) . '">';
				echo '</td>';

				// Output the trigger field.
				echo '<td class="lw-woo-gdpr-field-item lw-woo-gdpr-field-trigger">';

					// Handle the trash trigger.
					echo '<a class="lw-woo-gdpr-field-trigger-item lw-woo-gdpr-field-trigger-trash" href="">';
						echo '<i class="lw-woo-gdpr-trigger-icon dashicons dashicons-trash"></i>';
					echo '</a>';

					// Handle the sort trigger.
					echo '<a class="lw-woo-gdpr-field-trigger-item lw-woo-gdpr-field-trigger-sort hide-if-no-js" href="">';
						echo '<i class="lw-woo-gdpr-trigger-icon dashicons dashicons-sort"></i>';
					echo '</a>';

				echo '</td>';

			// Close the single div.
			echo '</tr>';
		}

		// Close the table body.
		echo '</tbody>';

		// Close my table.
		echo '</table>';
	}

	/**
	 * Output our custom repeating field.
	 *
	 * @param  array $args  The field args we set up.
	 *
	 * @return HTML
	 */
	public function output_repeating_setup( $args ){

		// Handle the name output.
		if ( ! empty( $args['name'] ) ) {
			echo '<h2>' . esc_html( $args['title'] ) . '</h2>';
		}

		// Handle the text output.
		if ( ! empty( $args['text'] ) ) {
			echo wp_kses_post( wpautop( wptexturize( $args['text'] ) ) );
		}

		// Echo out the block.
		echo self::add_new_entry_block();
	}

	/**
	 * Add the field setup for a new item.
	 */
	public static function add_new_entry_block( $echo = false ) {

		// Set an empty.
		$block  = '';

		// Wrap the new in a table.
		$block .= '<table id="lw-woo-gdpr-fields-add-new" class="lw-woo-gdpr-fields-table-wrap lw-woo-gdpr-add-new-table-wrap wp-list-table widefat fixed striped">';

			// And a header.
			$block .= '<thead>';
				$block .= '<tr>';

					// Add the required checkbox.
					$block .= '<th class="lw-woo-gdpr-field-header lw-woo-gdpr-new-field lw-woo-gdpr-new-field-required">';
						$block .= '<i title="' . __( 'Required', 'liquidweb-woocommerce-gdpr' ) . '" class="dashicons dashicons-post-status"></i>';
					$block .= '</th>';

					// Add the title field.
					$block .= '<th class="lw-woo-gdpr-field-header lw-woo-gdpr-new-field lw-woo-gdpr-new-field-title">';
						$block .= esc_html__( 'Title', 'liquidweb-woocommerce-gdpr' );
					$block .= '</th>';

					// Add the label field.
					$block .= '<th class="lw-woo-gdpr-field-header lw-woo-gdpr-new-field lw-woo-gdpr-new-field-label">';
						$block .= esc_html__( 'Label', 'liquidweb-woocommerce-gdpr' );
					$block .= '</th>';

					// Add the button setup itself.
					$block .= '<th class="lw-woo-gdpr-field-header lw-woo-gdpr-new-field lw-woo-gdpr-new-field-add-button">&nbsp;</th>';

				// Close the row.
				$block .= '</tr>';

			// Close the header.
			$block .= '</thead>';

			// And a body.
			$block .= '<tbody>';
				$block .= '<tr>';

					// Add the required checkbox.
					$block .= '<td class="lw-woo-gdpr-new-field lw-woo-gdpr-new-field-required">';
						$block .= '<input type="checkbox" class="lw-woo-gdpr-field-input" name="gdpr-optin-new[required]" value="1">';
					$block .= '</td>';

					// Add the title field.
					$block .= '<td class="lw-woo-gdpr-new-field lw-woo-gdpr-new-field-title">';
						$block .= '<input type="text" placeholder="' . __( 'Field Title', 'liquidweb-woocommerce-gdpr' ) . '" class="widefat lw-woo-gdpr-field-input" name="gdpr-optin-new[title]" value="">';
					$block .= '</td>';

					// Add the label field.
					$block .= '<td class="lw-woo-gdpr-new-field lw-woo-gdpr-new-field-label">';
						$block .= '<input type="text" placeholder="' . __( 'Field Label', 'liquidweb-woocommerce-gdpr' ) . '" class="widefat lw-woo-gdpr-field-input" name="gdpr-optin-new[label]" value="">';
					$block .= '</td>';

					// Add the button setup itself.
					$block .= '<td class="lw-woo-gdpr-new-field lw-woo-gdpr-new-field-add-button">';
						$block .= '<input type="submit" class="button button-secondary button-small lw-woo-gdpr-add-new" value="' . esc_html__( 'Add New Item', 'liquidweb-woocommerce-gdpr' ) . '">';
					$block .= '</td>';

				// Close the row.
				$block .= '</tr>';

			// Close the body.
			$block .= '</tbody>';

		// Close the table.
		$block .= '</table>';

		// Echo if requested.
		if ( ! empty( $echo ) ) {
			echo $block;
		}

		// Return it.
		return $block;
	}

	// End our class.
}

// Call our class.
$LW_Woo_GDPR_SettingsTab = new LW_Woo_GDPR_SettingsTab();
$LW_Woo_GDPR_SettingsTab->init();
