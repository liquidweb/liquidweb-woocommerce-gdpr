
/**
 * Set our account page notification.
 */
function setAccountNotification( noticeType, noticeText ) {

	// Set an empty var.
	var msgMarkup = '';

	// Build my new list item.
	msgMarkup += '<div class="lw-woo-gdpr-notice lw-woo-gdpr-notice-' + noticeType + '">';
		msgMarkup += '<p>' + noticeText + '</p>';
	msgMarkup += '</div>';

	// Add the message.
	jQuery( '.lw-woo-account-notices' ).html( msgMarkup );

	// Scroll up to it.
	scrollToMessage();

	// And now clear it after 4 seconds.
	jQuery( '.lw-woo-account-notices' ).delay( 4000 ).fadeOut( 'slow', function() {
		jQuery( this ).remove();
	});
}

/**
 * Scroll up to our message text.
 */
function scrollToMessage() {

	jQuery( 'html,body' ).animate({
		scrollTop: jQuery( '.lw-woo-gdpr-notice' ).offset().top - 60
	}, 500 );

	return false;
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
	var optsForm   = 'form.lw-woo-gdpr-changeopt-form';
	var optsList   = 'ul.lw-woo-gdpr-optin-list';
	var optsInputs = 'ul.lw-woo-gdpr-optin-list input:checked';
	var optsSubmit = '.lw-woo-gdpr-optin-list-submit';

	var exportForm   = 'form.lw-woo-gdpr-export-form';
	var exportList   = 'ul.lw-woo-gdpr-export-options';
	var exportInputs = 'ul.lw-woo-gdpr-export-options input:checked';
	var exportSubmit = '.lw-woo-gdpr-optin-export-submit';
	var exportUpdate;

	var filesBlock   = 'div.lw-woo-gdpr-download-section';
	var filesType    = '';

	/**
	 * Look for click actions on the opt-ins list.
	 */
	$( 'div.lw-woo-gdpr-section' ).divExists( function() {

		/**
		 * Check for the user saving opt-in actions.
		 */
		$( optsForm ).on( 'click', optsSubmit, function( event ) {

			// Stop the actual click.
			event.preventDefault();

			// Fetch the nonce.
			var optsNonce   = $( 'input#lw_woo_gdpr_changeopt_nonce' ).val();

			// Bail real quick without a nonce.
			if ( '' === optsNonce || undefined === optsNonce ) {
				return false;
			}

			// Build the data structure for the call.
			var data = {
				action: 'lw_woo_update_user_optins',
				user_id: $( 'input#lw_woo_gdpr_data_changeopt_user' ).val(),
				optins: $( optsInputs ).map( function() { return this.id; }).get(),
				nonce: optsNonce
			};
			// console.log( data );

			// Send out the ajax call itself.
			jQuery.post( ajaxurl, data, function( response ) {
				// console.log( response );

				// Handle the failure.
				if ( response.success !== true ) {
					return false;
				}

				// We got table row markup, so show it.
				if ( response.data.markup !== '' ) {

					// Show our message.
					setAccountNotification( 'success', response.data.message );

					// Clear out the existing list and add ours.
					$( optsList ).empty().append( response.data.markup );
				}
			});
		});

		/**
		 * Check for the user export request actions.
		 */
		$( exportForm ).on( 'click', exportSubmit, function( event ) {

			// Stop the actual click.
			event.preventDefault();

			// Fetch the nonce.
			var exportNonce = $( 'input#lw_woo_gdpr_export_nonce' ).val();

			// Bail real quick without a nonce.
			if ( '' === exportNonce || undefined === exportNonce ) {
				return false;
			}

			// Build the data structure for the call.
			var data = {
				action: 'lw_woo_request_user_exports',
				user_id: $( 'input#lw_woo_gdpr_data_export_user' ).val(),
				exports: $( exportInputs ).map( function() { return this.value; }).get(),
				nonce: exportNonce
			};
			// console.log( data );

			// Send out the ajax call itself.
			jQuery.post( ajaxurl, data, function( response ) {
				// console.log( response );

				// Handle the failure.
				if ( response.success !== true ) {
					return false;
				}

				// We got table row markup, so show it.
				if ( response.data.markup !== '' ) {

					// Show our message.
					setAccountNotification( 'success', response.data.message );

					// Clear out the existing list and add ours.
					$( filesBlock ).replaceWith( response.data.markup );
				}
			});
		});

		/**
		 * Check for the user file delete request actions.
		 */
		$( filesBlock ).on( 'click', 'a.lw-woo-gdpr-delete-link', function( event ) {

			// Stop the actual click.
			event.preventDefault();

			// Get the nonce.
			filesNonce  = $( this ).data( 'nonce' );

			// Bail real quick without a nonce.
			if ( '' === filesNonce || undefined === filesNonce ) {
				return false;
			}

			// Build the data structure for the call.
			var data = {
				action: 'lw_woo_delete_export_file',
				user_id: $( this ).data( 'user-id' ),
				datatype: $( this ).data( 'type' ),
				nonce: filesNonce
			};
			// console.log( data );

			// Send out the ajax call itself.
			jQuery.post( ajaxurl, data, function( response ) {
				// console.log( response );

				// Handle the failure.
				if ( response.success !== true ) {
					return false;
				}

				// Remove our individual row.
				if ( response.data.message !== '' ) {

					// Show our message.
					setAccountNotification( 'success', response.data.message );

					// Remove the single item.
					$( filesBlock ).find( response.data.markup ).remove();
				}
			});
		});

	});

//********************************************************
// you're still here? it's over. go home.
//********************************************************
});
