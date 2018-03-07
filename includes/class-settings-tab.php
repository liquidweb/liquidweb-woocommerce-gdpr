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
		add_action( 'admin_init',                                       array( $this, 'remove_single_field'         )           );
		add_action( 'admin_notices',                                    array( $this, 'process_remove_notices'      )           );
		add_filter( 'woocommerce_settings_tabs_array',                  array( $this, 'add_settings_tab'            ),  50      );
		add_action( 'woocommerce_settings_tabs_gdpr_optins',            array( $this, 'settings_tab'                )           );
		add_action( 'woocommerce_update_options_gdpr_optins',           array( $this, 'update_settings'             )           );
		add_action( 'woocommerce_admin_field_repeating_setup',          array( $this, 'output_repeating_setup'      ),  10, 1   );
		add_action( 'woocommerce_admin_field_repeating_group',          array( $this, 'output_repeating_group'      ),  10, 1   );
	}

	/**
	 * Handle removing a single field.
	 *
	 */
	public function remove_single_field() {

		// First check for the single delete.
		if ( empty( $_GET['gdpr-single-delete'] ) || empty( $_GET['gdpr-field-id'] ) ) {
			return;
		}

		// @@todo  do the nonce check.

		// Go ahead and remove it.
		lw_woo_gdpr()->update_saved_optin_fields( 0, esc_attr( $_GET['gdpr-field-id'] ) );

		// Now set my redirect link.
		$link   = add_query_arg( array( 'gdpr-success' => 1, 'result' => 'gdpr-single-delete' ), lw_woo_gdpr()->get_settings_tab_link() );

		// Do our return redirect.
		wp_redirect( $link );
		exit;
	}

	/**
	 * Set up the admin notices.
	 *
	 * @return mixed
	 */
	public function process_remove_notices() {

		// Make sure we have the page we want.
		if ( empty( $_GET['page'] ) || 'wc-settings' !== esc_attr( $_GET['page'] ) ) {
			return;
		}

		// Make sure we have the tab we want.
		if ( empty( $_GET['tab'] ) || LW_WOO_GDPR_TAB_BASE !== esc_attr( $_GET['tab'] ) ) {
			return;
		}

		// Handle the success notice first.
		if ( ! empty( $_GET['result'] ) && 'gdpr-single-delete' === esc_attr( $_GET['result'] ) ) {

			// Get my message text.
			$msgtxt = lw_woo_gdpr_notice_text( 'success-removed' );

			// Output the message along with the dismissable.
			echo '<div class="notice notice-success is-dismissible lw-woo-gdpr-message">';
				echo '<p>' . wp_kses_post( $msgtxt ) . '</p>';
			echo '</div>';

			// And be done.
			return;
		}

		// And be done.
		return;
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
		if ( ! isset( $tabs[ LW_WOO_GDPR_TAB_BASE ] ) ) {
			$tabs[ LW_WOO_GDPR_TAB_BASE ] = __( 'GDPR Opt-Ins', 'liquidweb-woocommerce-gdpr' );
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
		lw_woo_gdpr()->update_saved_optin_fields( $fields, null );

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

				// Open up the row.
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

				// Open the row.
				$block .= '<tr class="lw-woo-gdpr-new-fields-row">';

					// Add the required checkbox.
					$block .= '<td class="lw-woo-gdpr-new-field lw-woo-gdpr-new-field-checkbox-input lw-woo-gdpr-new-field-required">';
						$block .= '<input type="checkbox" id="lw-woo-gdpr-new-required" class="lw-woo-gdpr-field-input" name="gdpr-optin-new[required]" value="1">';
					$block .= '</td>';

					// Add the title field.
					$block .= '<td class="lw-woo-gdpr-new-field lw-woo-gdpr-new-field-text-input lw-woo-gdpr-new-field-title">';
						$block .= '<input type="text" id="lw-woo-gdpr-new-title" placeholder="' . __( 'Field Title', 'liquidweb-woocommerce-gdpr' ) . '" class="widefat lw-woo-gdpr-field-input" name="gdpr-optin-new[title]" value="">';
					$block .= '</td>';

					// Add the label field.
					$block .= '<td class="lw-woo-gdpr-new-field lw-woo-gdpr-new-field-text-input lw-woo-gdpr-new-field-label">';
						$block .= '<input type="text" id="lw-woo-gdpr-new-label" placeholder="' . __( 'Field Label', 'liquidweb-woocommerce-gdpr' ) . '" class="widefat lw-woo-gdpr-field-input" name="gdpr-optin-new[label]" value="">';
					$block .= '</td>';

					// Add the button setup itself.
					$block .= '<td class="lw-woo-gdpr-new-field lw-woo-gdpr-new-field-add-button">';
						$block .= '<button type="submit" class="button button-secondary button-small lw-woo-gdpr-admin-button lw-woo-gdpr-add-new">' . esc_html__( 'Add New Item', 'liquidweb-woocommerce-gdpr' ) . '</button>';

						// Include a nonce.
						$block .= wp_nonce_field( 'lw_woo_gdpr_new_action', 'lw_woo_gdpr_new_nonce', true, false );

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

			echo LW_Woo_GDPR_Fields::table_row( $field );

			/*
			// Create my name field and confirm the action name.
			$name   = 'gdpr-optin-current[' . esc_attr( $field['id'] ) . ']';
			$check  = ! empty( $field['required'] ) ? true : false;
			$action = ! empty( $field['action'] ) ? $field['action'] : lw_woo_gdpr_make_action_key( $field['id'] );

			// Set our delete link.
			$d_nonc = wp_create_nonce( 'lw_woo_optin_single_' . esc_attr( $field['id'] )  );
			$d_args = array( 'gdpr-single-delete' => 1, 'gdpr-field-id' => esc_attr( $field['id'] ), 'nonce' => $d_nonc );
			$delete = add_query_arg( $d_args, lw_woo_gdpr()->get_settings_tab_link() );

			// Set the data attributes for the Ajax call.
			$d_atrb = ' data-field-id="' . esc_attr( $field['id'] ) . '" data-nonce="' . $d_nonc . '"';

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
					echo '<a class="lw-woo-gdpr-field-trigger-item lw-woo-gdpr-field-trigger-trash" href="' . esc_url( $delete ) . '" ' . $d_atrb . ' >';
						echo '<i class="lw-woo-gdpr-trigger-icon dashicons dashicons-trash"></i>';
					echo '</a>';

					// Handle the sort trigger.
					echo '<a class="lw-woo-gdpr-field-trigger-item lw-woo-gdpr-field-trigger-sort hide-if-no-js" href="">';
						echo '<i class="lw-woo-gdpr-trigger-icon dashicons dashicons-sort"></i>';
					echo '</a>';

				echo '</td>';

			// Close the single div.
			echo '</tr>';
			*/
		}

		// Close the table body.
		echo '</tbody>';

		// Close my table.
		echo '</table>';
	}

	// End our class.
}

// Call our class.
$LW_Woo_GDPR_SettingsTab = new LW_Woo_GDPR_SettingsTab();
$LW_Woo_GDPR_SettingsTab->init();
