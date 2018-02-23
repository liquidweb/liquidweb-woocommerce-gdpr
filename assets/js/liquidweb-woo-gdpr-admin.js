
/**
 * Clear the new field inputs.
 */
function clearNewFieldInputs() {
	jQuery( '.lw-woo-gdpr-new-fields-row #lw-woo-gdpr-new-title' ).val( '' );
	jQuery( '.lw-woo-gdpr-new-fields-row #lw-woo-gdpr-new-label' ).val( '' );
	jQuery( '.lw-woo-gdpr-new-fields-row #lw-woo-gdpr-new-required' ).prop( 'checked', false );
}

/**
 * Now let's get started.
 */
jQuery(document).ready( function($) {

	/**
	 * Quick helper to check for an existance of an element.
	 */
	$.fn.divExists = function(callback) {

		// Slice some args.
		var args = [].slice.call( arguments, 1 );

		// Check for length.
		if ( this.length ) {
			callback.call( this, args );
		}
		// Return it.
		return this;
	};

	/**
	 * Set some vars for later
	 */
	var saveTable = 'table.lw-woo-gdpr-add-new-table-wrap';
	var sortTable = 'table.lw-woo-gdpr-saved-table-wrap';
	var sortBody = 'table.lw-woo-gdpr-saved-table-wrap tbody';
	var sortUpdate;
	var fieldBlock;
	var fieldID;
	var fieldNonce;

	var newRequired;
	var newTitle;
	var newLabel;
	var newNonce;

	/**
	 * Set up the sortable table rows.
	 */
	$( sortTable ).divExists( function() {

		// Make our table sortable.
		$( this ).find( 'tbody' ).sortable({
			handle: '.lw-woo-gdpr-trigger-icon',
			update: function( event, ui ) {

				// Fetch the updated sort order.
				sortUpdate = $( sortBody ).sortable( 'toArray', { attribute: 'data-key' } );

				// Build the data structure for the call.
				var data = {
					action: 'lw_woo_update_sorted_rows',
					sorted: sortUpdate
				};

				// Send the post request, we don't actually care about the response.
				jQuery.post( ajaxurl, data );
			}
		});

	});

	/**
	 * Add a new item into the table.
	 */
	$( saveTable ).on( 'click', 'input.lw-woo-gdpr-add-new', function( event ) {

		// Stop the actual click.
		event.preventDefault();

		// Fetch the nonce.
		newNonce = $( 'input#lw_woo_gdpr_new_nonce' ).val();

		// Bail real quick without a nonce.
		if ( '' === newNonce || undefined === newNonce ) {
			return false;
		}

		// Pull all three items.
		newRequired  = $( 'input#lw-woo-gdpr-new-required' ).is( ':checked' );
		newTitle     = $( 'input#lw-woo-gdpr-new-title' ).val();
		newLabel     = $( 'input#lw-woo-gdpr-new-label' ).val();

		// Build the data structure for the call.
		var data = {
			action: 'lw_woo_add_new_optin_row',
			required: newRequired,
			title: newTitle,
			label: newLabel,
			nonce: newNonce
		};
		// console.log( data );
		jQuery.post( ajaxurl, data, function( response ) {

			// console.log( response );

			// Handle the failure.
			if ( response.success !== true ) {
				return false;
			}

			// We got table row markup, so show it.
			if ( response.data.markup !== '' ) {

				// Clear the new field inputs.
				clearNewFieldInputs();

				// Add the row itself.
				$( 'table#lw-woo-gdpr-fields-table tr:last' ).after( response.data.markup );

				// Refresh the sortable table.
				$( sortTable ).sortable( 'refreshPositions' );
			}
		});
	});

	/**
	 * Handle the individual item deletion.
	 */
	$( sortTable ).on( 'click', 'a.lw-woo-gdpr-field-trigger-trash', function( event ) {

		// Stop the actual click.
		event.preventDefault();

		// Set my field block.
		fieldBlock  = $( this ).parents( 'tr.lw-woo-gdpr-field-single' );

		// Fetch my field ID and nonce.
		fieldID     = $( this ).data( 'field-id' );
		fieldNonce  = $( this ).data( 'nonce' );

		// console.log( fieldID );
		// console.log( fieldNonce );

		// Bail real quick without a nonce.
		if ( '' === fieldNonce || undefined === fieldNonce ) {
			return false;
		}

		// Handle the missing field ID.
		if ( '' === fieldID || undefined === fieldID ) {
			return false; // @@todo need a better return.
		}

		// Build the data structure for the call.
		var data = {
			action: 'lw_woo_delete_single_row',
			field_id: fieldID,
			nonce: fieldNonce
		};

		// console.log( data );

		jQuery.post( ajaxurl, data, function( response ) {

			// console.log( response );

			// Handle the failure.
			if ( response.success !== true ) {
				return false;
			}

			// No error, so remove the field
			if ( response.success === true || response.success === 'true' ) {

				// Remove the field.
				$( sortTable ).find( fieldBlock ).fadeOut().remove();

				// Refresh the sortable table.
				$( sortBody ).sortable( 'refreshPositions' );
			}
		});
	});

//********************************************************
// you're still here? it's over. go home.
//********************************************************
});
