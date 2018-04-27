
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
	jQuery( '.lw-woo-gdpr-notice' ).delay( 5000 ).fadeOut( 'slow', function() {
		jQuery( this ).remove();
	});
}

/**
 * Scroll up to our message text.
 */
function scrollToMessage() {

	jQuery( 'html,body' ).animate({
		scrollTop: jQuery( '.lw-woo-account-notices' ).offset().top - 60
	}, 500 );

	// And just return false.
	return false;
}

/**
 * Clear all the checkboxes in a list.
 */
function clearAllCheckboxes( listBlock ) {

	jQuery( listBlock ).each( function() {
		jQuery( this ).find( 'input:checkbox' ).prop( 'checked', false );
	});
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
	var ajaxurl = frontLWWooGDPR.ajaxurl;
	var messageType;
	var filesBlock   = 'div.lw-woo-gdpr-download-section';
	var deleteBlock  = 'div.lw-woo-gdpr-data-delete-section';

	/**
	 * Look for click actions on the opt-ins list.
	 */
	$( 'div.lw-woo-gdpr-section' ).divExists( function() {

		/**
		 * Check for the user saving opt-in actions.
		 */
		$( 'form.lw-woo-gdpr-changeopt-form' ).submit( function( event ) {

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
				optins: $( 'ul.lw-woo-gdpr-optin-list input:checked' ).map( function() { return this.id; }).get(),
				nonce: optsNonce
			};

			// Send out the ajax call itself.
			jQuery.post( ajaxurl, data, function( response ) {

				// We got message markup, so show it.
				if ( response.data.message !== '' ) {

					// Determine the message type.
					messageType = response.success !== true ? 'error' : 'success';

					// Show our message.
					setAccountNotification( messageType, response.data.message );
				}

				// Handle the failure.
				if ( response.success !== true ) {
					return false;
				}

				// We got table row markup, so show it.
				if ( response.data.markup !== '' ) {
					$( 'ul.lw-woo-gdpr-optin-list' ).empty().append( response.data.markup );
				}
			}, 'json' );
		});

		/**
		 * Check for the user export request actions.
		 */
		$( 'form.lw-woo-gdpr-export-form' ).submit( function( event ) {

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
				exports: $( 'ul.lw-woo-gdpr-export-options input:checked' ).map( function() { return this.value; }).get(),
				nonce: exportNonce
			};

			// Send out the ajax call itself.
			jQuery.post( ajaxurl, data, function( response ) {

				// We got message markup, so show it.
				if ( response.data.message !== '' ) {

					// Determine the message type.
					messageType = response.success !== true ? 'error' : 'success';

					// Show our message.
					setAccountNotification( messageType, response.data.message );
				}

				// Handle the failure.
				if ( response.success !== true ) {
					return false;
				}

				// We got table row markup, so show it.
				if ( response.data.markup !== '' ) {

					// Clear our checkboxes.
					clearAllCheckboxes( 'li.lw-woo-gdpr-export-option' );

					// Clear out the existing list and add ours.
					$( filesBlock ).replaceWith( response.data.markup );
				}
			}, 'json' );
		});

		/**
		 * Check for the user file delete request actions.
		 */
		$( filesBlock ).on( 'click', 'a.lw-woo-gdpr-delete-link', function( event ) {

			// Stop the actual click.
			event.preventDefault();

			// Get the nonce.
			var filesNonce  = $( this ).data( 'nonce' );

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

			// Send out the ajax call itself.
			jQuery.post( ajaxurl, data, function( response ) {

				// We got message markup, so show it.
				if ( response.data.message !== '' ) {

					// Determine the message type.
					messageType = response.success !== true ? 'error' : 'success';

					// Show our message.
					setAccountNotification( messageType, response.data.message );
				}

				// Handle the failure.
				if ( response.success !== true ) {
					return false;
				}

				// Remove our individual row.
				if ( response.data.markup !== '' ) {
					$( filesBlock ).find( response.data.markup ).remove();
				}
			}, 'json' );
		});

		/**
		 * Check for the user cancel delete request actions.
		 */
		$( deleteBlock ).on( 'click', 'a.lw-woo-gdpr-cancel-request-link', function( event ) {

			// Stop the actual click.
			event.preventDefault();

			// Get the nonce.
			var deleteNonce  = $( this ).data( 'nonce' );

			// Bail real quick without a nonce.
			if ( '' === deleteNonce || undefined === deleteNonce ) {
				return false;
			}

			// Build the data structure for the call.
			var data = {
				action: 'lw_woo_cancel_delete_request',
				user_id: $( this ).data( 'user-id' ),
				datatype: $( this ).data( 'type' ),
				nonce: deleteNonce
			};

			// Send out the ajax call itself.
			jQuery.post( ajaxurl, data, function( response ) {
console.log( response );
				// We got message markup, so show it.
				if ( response.data.message !== '' ) {

					// Determine the message type.
					messageType = response.success !== true ? 'error' : 'success';

					// Show our message.
					setAccountNotification( messageType, response.data.message );
				}

				// Handle the failure.
				if ( response.success !== true ) {
					return false;
				}

				// Remove our individual row.
				if ( response.data.markup !== '' ) {
					$( 'ul.lw-woo-gdpr-delete-options' ).empty().append( response.data.markup.requests );
				}

			}, 'json' );
		});

		/**
		 * Check for the user delete request actions.
		 */
		$( 'form.lw-woo-gdpr-delete-me-form' ).submit( function( event ) {

			// Stop the actual click.
			event.preventDefault();

			// Fetch the nonce.
			var deleteNonce = $( 'input#lw_woo_gdpr_delete_nonce' ).val();

			// Bail real quick without a nonce.
			if ( '' === deleteNonce || undefined === deleteNonce ) {
				return false;
			}

			// Build the data structure for the call.
			var data = {
				action: 'lw_woo_request_user_deletion',
				user_id: $( 'input#lw_woo_gdpr_data_delete_user' ).val(),
				deletes: $( 'ul.lw-woo-gdpr-delete-options input:checked' ).map( function() { return this.value; }).get(),
				nonce: deleteNonce
			};

			// Send out the ajax call itself.
			jQuery.post( ajaxurl, data, function( response ) {

				// We got message markup, so show it.
				if ( response.data.message !== '' ) {

					// Determine the message type.
					messageType = response.success !== true ? 'error' : 'success';

					// Show our message.
					setAccountNotification( messageType, response.data.message );
				}

				// Handle the failure.
				if ( response.success !== true ) {
					return false;
				}

				// We got table row markup, so show it.
				if ( response.data.markup !== '' ) {

					// Clear our checkboxes.
					clearAllCheckboxes( 'li.lw-woo-gdpr-delete-option' );

					// Clear out the existing list and add ours.
					$( 'ul.lw-woo-gdpr-delete-options' ).empty().append( response.data.markup.requests );
				}

				// If we have a submits replace, do that.
				if ( response.data.markup.remaining !== true ) {
					$( 'p.lw-woo-gdpr-delete-submit' ).empty().append( '<em>' + frontLWWooGDPR.remain_text + '</em>' );
				}

			}, 'json' );
		});

		// End the whole 'divexists' wrapper.
	});

//********************************************************
// you're still here? it's over. go home.
//********************************************************
});
