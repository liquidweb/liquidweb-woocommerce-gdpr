<?php
/**
 * Our helper file.
 *
 * Various functions that are used in the plugin.
 *
 * @package LiquidWeb_Woo_GDPR
 */

/**
 * Get the array of each kind of opt-in box we have.
 *
 * @return array
 */
function lw_woo_gdpr_optin_fields( $keys = false ) {

	// Set an array of what we know we need.
	$fields = array(

		// Our item.
		'optin-1'   => array(
			'type'      => 'checkbox',
			'id'        => 'gdpr-optin-1',
			'name'      => 'gdpr-optin[optin-1]',
			'title'     => __( 'Opt In Field 1', 'liquidweb-woocommerce-gdpr' ),
			'label'     => __( 'This is an opt in field', 'liquidweb-woocommerce-gdpr' ),
			'required'  => true,
		),

		// Our item.
		'optin-2'   => array(
			'type'      => 'checkbox',
			'id'        => 'gdpr-optin-2',
			'name'      => 'gdpr-optin[optin-2]',
			'title'     => __( 'Opt In Field 3', 'liquidweb-woocommerce-gdpr' ),
			'label'     => __( 'This is a different opt in field', 'liquidweb-woocommerce-gdpr' ),
			'required'  => false,
		),

		// Our item.
		'optin-3'   => array(
			'type'      => 'checkbox',
			'id'        => 'gdpr-optin-3',
			'name'      => 'gdpr-optin[optin-3]',
			'title'     => __( 'Opt In Field 3', 'liquidweb-woocommerce-gdpr' ),
			'label'     => __( 'This is yet another opt in field', 'liquidweb-woocommerce-gdpr' ),
			'required'  => true,
		),
	);

	// Set the fields with a filter.
	$fields = apply_filters( 'lw_woo_gdpr_optin_fields', $fields );

	// Return the entire thing or just the keys.
	return ! empty( $keys ) ? array_keys( $fields ) : $fields;
}
