

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
	var optsForm   = 'form.lw-woo-gdpr-changeopt-form';
	var optsInputs = 'ul.lw-woo-gdpr-optin-list input:checked';
	var optsSubmit = '.lw-woo-gdpr-optin-list-submit';

	var optsUserID = 0;
	var optsNonce  = '';
	var optsUpdate;

	/**
	 * Look for click actions on the opt-ins list.
	 */
	$( optsForm ).divExists( function() {

		/**
		 * Check for the actual saving.
		 */
		$( optsForm ).on( 'click', optsSubmit, function( event ) {

			// Stop the actual click.
			event.preventDefault();

			// Fetch the nonce.
			optsNonce   = $( 'input#lw_woo_gdpr_changeopt_nonce' ).val();

			// Bail real quick without a nonce.
			if ( '' === optsNonce || undefined === optsNonce ) {
				return false;
			}

			// Get my user ID.
			optsUserID  = $( 'input#lw_woo_gdpr_data_changeopt_user' ).val();
			optsUpdate  = $( optsInputs ).map( function() { return this.id; }).get();

			console.log( optsUpdate );

			// Build the data structure for the call.
			var data = {
				action: 'lw_woo_update_user_optins',
				user_id: optsUserID,
				optins: optsUpdate,
				nonce: optsNonce
			};
			// console.log( data );
			/*
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
			*/
		});


	});

//********************************************************
// you're still here? it's over. go home.
//********************************************************
});
