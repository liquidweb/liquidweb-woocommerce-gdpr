
/**
 * Clear the new field inputs.
 */
function clearNewFieldInputs() {
	jQuery( '.lw-woo-gdpr-new-fields-row #lw-woo-gdpr-new-title' ).val( '' );
	jQuery( '.lw-woo-gdpr-new-fields-row #lw-woo-gdpr-new-label' ).val( '' );
	jQuery( '.lw-woo-gdpr-new-fields-row #lw-woo-gdpr-new-required' ).prop( 'checked', false );
}

/**
 * Reset the item count in the table.
 */
function resetTableCount( tableText ) {
	jQuery( 'div.tablenav' ).each( function() {
		jQuery( this ).find( 'span.displaying-num' ).text( tableText );
	});
}

/**
 * Set our account page notification.
 */
function setAdminNotification( noticeText, noticeType, noticeWrap ) {

	// Set my notice type.
	noticeType = noticeType || 'updated';

	// Set the div wrapper we want to add the notice to.
	noticeWrap = noticeWrap || '.lw-woo-gdpr-requests-admin-wrap';

	// Set an empty var.
	var msgMarkup = '';

	// Build my new list item.
	msgMarkup += '<div id="message" class="' + noticeType + ' settings-error notice is-dismissible lw-woo-request-notice">';
		msgMarkup += '<p>' + noticeText + '</p>';
 		msgMarkup += '<button type="button" class="notice-dismiss"><span class="screen-reader-text">' + adminLWWooGDPR.dismiss_text + '</span></button>';
	msgMarkup += '</div>';

	// Add the message.
	jQuery( noticeWrap + ' h1:first' ).after( msgMarkup );
}

/**
 * Clear out user rows and checkboxes from bulk actions.
 */
function clearBulkChecks() {

	// Map out all the checked items and clear them out.
	jQuery( 'input.lw-woo-user-checkbox:checked' ).map( function() {
		jQuery( this ).parents( 'tr' ).fadeOut().remove();
	});

	// And uncheck the header and footer buttons.
	jQuery( 'table.userdeletionrequests td.check-column' ).find( 'input[type=checkbox]' ).prop( 'checked', false );

	// And clear the two select dropdowns.
	jQuery( '#bulk-action-selector-top' ).val( '-1' );
	jQuery( '#bulk-action-selector-bottom' ).val( '-1' );
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
	var saveForm = 'form#mainform';
	var saveSubmit;

	var sortTable = 'table.lw-woo-gdpr-saved-table-wrap';
	var sortBody = 'table.lw-woo-gdpr-saved-table-wrap tbody';

	var requestForm = 'form#lw-woo-gdpr-requests-admin-form';
	var requestTable = 'table.userdeletionrequests';

	var bulkChoice = '';
	var bulkUsers = [];

	var noticeType = '';

	/**
	 * Set up the sortable table rows.
	 */
	$( sortTable ).divExists( function() {

		// Make our table sortable.
		$( sortBody ).sortable({
			handle: '.lw-woo-gdpr-trigger-icon',
			update: function( event, ui ) {

				// Build the data structure for the call with the updated sort order.
				var data = {
					action: 'lw_woo_update_sorted_rows',
					sorted: $( sortBody ).sortable( 'toArray', { attribute: 'data-key' } )
				};

				// Send the post request, we don't actually care about the response.
				jQuery.post( ajaxurl, data );
			}
		});
	});

	/**
	 * Set the button variable to handle the two submits.
	 */
	$( saveForm ).on( 'click', 'button', function() {
		saveSubmit = $( this ).hasClass( 'lw-woo-gdpr-add-new' ) ? true : false;
	});

	/**
	 * Add a new item into the table.
	 */
	$( saveForm ).submit( function( event ) {

		// Bail on the actual save button.
		if ( saveSubmit !== true ) {
			return;
		}

		// Stop the actual submit.
		event.preventDefault();

		// We call this a sledgehammer because Woo doesn't register
		// the callback until the user has clicked one of the tabs.
		$( '.woo-nav-tab-wrapper a' ).off();

		// Fetch the nonce.
		newNonce = $( 'input#lw_woo_gdpr_new_nonce' ).val();

		// Bail real quick without a nonce.
		if ( '' === newNonce || undefined === newNonce ) {
			return false;
		}

		// Build the data structure for the call.
		var data = {
			action: 'lw_woo_add_new_optin_row',
			required: $( 'input#lw-woo-gdpr-new-required' ).is( ':checked' ),
			title: $( 'input#lw-woo-gdpr-new-title' ).val(),
			label: $( 'input#lw-woo-gdpr-new-label' ).val(),
			nonce: newNonce
		};

		// Send out the ajax call itself.
		jQuery.post( ajaxurl, data, function( response ) {

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
				$( sortBody ).sortable( 'refreshPositions' );
			}
		}, 'json' );
	});

	/**
	 * Handle the individual item deletion.
	 */
	$( sortTable ).on( 'click', 'a.lw-woo-gdpr-field-trigger-trash', function( event ) {

		// Stop the actual click.
		event.preventDefault();

		// Set my field block.
		var fieldBlock  = $( this ).parents( 'tr.lw-woo-gdpr-field-single' );

		// Fetch my field ID and nonce.
		var fieldID     = $( this ).data( 'field-id' );
		var fieldNonce  = $( this ).data( 'nonce' );

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

		// Send out the ajax call itself.
		jQuery.post( ajaxurl, data, function( response ) {

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
		}, 'json' );
	});

	/**
	 * Check for our pending requests form.
	 */
	$( requestForm ).divExists( function() {

		// Look for a single delete.
		$( requestTable ).on( 'click', 'a.lw-woo-action-delete', function( event ) {

			// Stop the actual click.
			event.preventDefault();

			// Set my user block.
			var userBlock   = $( this ).parents( 'tr' );

			// Fetch my user ID and nonce.
			var userID      = $( this ).data( 'user-id' );
			var userNonce   = $( this ).data( 'nonce' );

			// Bail real quick without a nonce.
			if ( '' === userNonce || undefined === userNonce ) {
				return false;
			}

			// Handle the missing user ID.
			if ( '' === userID || undefined === userID ) {
				return false; // @@todo need a better return.
			}

			// Build the data structure for the call.
			var data = {
				action: 'lw_woo_process_user_delete',
				user_id: userID,
				nonce: userNonce
			};

			// Send out the ajax call itself.
			jQuery.post( ajaxurl, data, function( response ) {


				// Handle the failure.
				if ( response.success !== true ) {
					return false;
				}

				// No error, so remove the row.
				if ( response.success === true || response.success === 'true' ) {
					$( requestTable ).find( userBlock ).fadeOut().remove();
				}

				// If we have the message text, show it.
				if ( response.data.message !== '' ) {
					setAdminNotification( response.data.message );
				}

				// If we have the text, swap it.
				if ( response.data.ctext !== '' ) {
					resetTableCount( response.data.ctext );
				}

				// If none remain, refresh.
				if ( response.data.remain !== true ) {
					window.location.reload( true );
				}

			}, 'json' );
		});

		// End the whole 'divexists' wrapper.
	});

	/**
	 * Look for a bulk action.
	 */
	$( 'div.bulkactions' ).on( 'click', 'input.action', function( event ) {

		// Stop the actual click.
		event.preventDefault();

		// Grab my top and bottom values.
		var selectTop = $( '#bulk-action-selector-top' ).val();
		var selectBot = $( '#bulk-action-selector-bottom' ).val();

		// If both are empty, bail.
		if ( selectTop === '-1' && selectBot === '-1' ) {
			return;
		}

		// Check for which one has an option.
		bulkChoice = selectTop !== '-1' ? selectTop : selectBot;

		// First check for the nonce.
		var bulkNonce = $( '#lw_woo_gdpr_bulk_delete_nonce' ).val();

		// Bail real quick without a nonce.
		if ( '' === bulkNonce || undefined === bulkNonce ) {
			return false;
		}

		// Grab all my users.
		bulkUsers  = $( 'input.lw-woo-user-checkbox:checked' ).map( function() {
			return $( this ).val();
		}).get();

		// Check for an empty array of users.
		if ( jQuery.isEmptyObject( bulkUsers ) ) {
			return;
		}

		// Build the data structure for the call.
		var data = {
			action: 'lw_woo_process_bulk_delete',
			user_ids: bulkUsers,
			nonce: bulkNonce
		};

		// Send out the ajax call itself.
		jQuery.post( ajaxurl, data, function( response ) {

			// Set my type class for the notice.
			noticeType  = response.success !== true ? 'error' : 'updated';

			// If we have the message text, show it.
			if ( response.data.message !== '' ) {
				setAdminNotification( response.data.message, noticeType );
			}

			// Handle the failure.
			if ( response.success !== true ) {
				return false;
			}

			// No error, so remove the row.
			if ( response.success === true || response.success === 'true' ) {
				clearBulkChecks();
			}

			// If we have the text, swap it.
			if ( response.data.ctext !== '' ) {
				resetTableCount( response.data.ctext );
			}

			// If none remain, refresh.
			if ( response.data.remain !== true ) {
				window.location.reload( true );
			}

		}, 'json' );

	});

	/**
	 * Handle the notice dismissal.
	 */
	$( '.lw-woo-gdpr-requests-admin-wrap' ).on( 'click', '.notice-dismiss', function() {
		$( '.lw-woo-gdpr-requests-admin-wrap' ).find( '.lw-woo-request-notice' ).remove();
	});

//********************************************************
// You're still here? It's over. Go home.
//********************************************************
});
