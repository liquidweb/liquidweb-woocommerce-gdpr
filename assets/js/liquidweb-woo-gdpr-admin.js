
/**
 * Now let's get started.
 */
jQuery(document).ready( function($) {

//********************************************************
// Do our "select all" checkbox.
//********************************************************
	$( '.lw-woo-gdpr-requests-admin-table' ).on( 'change', '.lw-woo-gdpr-requests-select-all', function (event) {

		// Determine if we checked the box or not.
		var isChecked = this.checked ? true : false;

		// Now apply it to each box.
		$( '.lw-woo-gdpr-requests-select-single' ).each( function() {
			$( this ).prop( 'checked', isChecked ).change();
		});
	});

//********************************************************
// you're still here? it's over. go home.
//********************************************************
});
