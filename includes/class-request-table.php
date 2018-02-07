<?php
/**
 * Our table setup for the delete requests.
 *
 * @package LiquidWeb_Woo_GDPR
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


// WP_List_Table is not loaded automatically so we need to load it in our application
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class UserDeleteRequests_Table extends WP_List_Table {

	/**
	 * UserDeleteRequests_Table constructor.
	 *
	 * REQUIRED. Set up a constructor that references the parent constructor. We
	 * use the parent reference to set some default configs.
	 */
	public function __construct() {

		// Set parent defaults.
		parent::__construct( array(
			'singular' => __( 'User Deletion Request', 'liquidweb-woocommerce-gdpr' ),
			'plural'   => __( 'User Deletion Requests', 'liquidweb-woocommerce-gdpr' ),
			'ajax'     => false,
		) );
	}

	/**
	 * Prepare the items for the table to process
	 *
	 * @return Void
	 */
	public function prepare_items() {

		// Roll out each part.
		$columns    = $this->get_columns();
		$hidden     = array();
		$sortable   = $this->get_sortable_columns();
		$dataset    = $this->table_data();

		// Handle our sorting.
		usort( $dataset, array( $this, 'sort_data' ) );

		$paginate   = 10;
		$current    = $this->get_pagenum();

		// Set my pagination args.
		$this->set_pagination_args( array(
			'total_items' => count( $dataset ),
			'per_page'    => $paginate
		));

		// Slice up our dataset.
		$dataset    = array_slice( $dataset, ( ( $current - 1 ) * $paginate ), $paginate );

		// Do the column headers
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Make sure we have the bulk action running.
		$this->process_bulk_action();

		// And the result.
		$this->items = $dataset;
	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table.
	 *
	 * @return Array
	 */
	public function get_columns() {

		// Build our array of column setups.
		$setup  = array(
			'cb'            => '<input type="checkbox" />',
			'display_name'  => __( 'Customer Name', 'liquidweb-woocommerce-gdpr' ),
			'email_address' => __( 'Email Address', 'liquidweb-woocommerce-gdpr' ),
			'request_date'  => __( 'Request Date', 'liquidweb-woocommerce-gdpr' ),
			'datatypes'     => __( 'Data Types', 'liquidweb-woocommerce-gdpr' ),
		);

		// Return filtered.
		return apply_filters( 'lw_woo_gdpr_column_items', $setup );
	}

	/**
	 * Return null for our table, since no row actions exist.
	 *
	 * @param  object $item         The item being acted upon.
	 * @param  string $column_name  Current column name.
	 * @param  string $primary      Primary column name.
	 *
	 * @return null
	 */
	protected function handle_row_actions( $item, $column_name, $primary ) {
		return apply_filters( 'lw_woo_gdpr_table_row_actions', '', $item, $column_name, $primary );
 	}

	/**
	 * Define the sortable columns.
	 *
	 * @return Array
	 */
	public function get_sortable_columns() {

		// Build our array of sortable columns.
		$setup  = array(
			'display_name'  => array( 'display_name', false ),
			'email_address' => array( 'email_address', true ),
			'request_date'  => array( 'request_date', true ),
		);

		// Return it, filtered.
		return apply_filters( 'lw_woo_gdpr_table_sortable_columns', $setup );
	}

	/**
	 * Return available bulk actions.
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {

		// Make a basic array of the actions we wanna include.
		$setup  = array( 'lw_woo_gdpr_delete' => __( 'Process Requests', 'liquidweb-woocommerce-gdpr' ) );

		// Return it filtered.
		return apply_filters( 'lw_woo_gdpr_table_bulk_actions', $setup );
	}

	/**
	 * Checkbox column.
	 *
	 * @param  array  $item  The item from the data array.
	 *
	 * @return string
	 */
	protected function column_cb( $item ) {

		// Set my ID.
		$id = absint( $item['id'] );

		// Return my checkbox.
		return '<input type="checkbox" name="lw_woo_gdpr_users[]" id="cb-' . $id . '" value="' . $id . '" /><label for="cb-' . $id . '" class="screen-reader-text">' . __( 'Select user', 'liquidweb-woocommerce-gdpr' ) . '</label>';
	}

	/**
	 * The display_name column.
	 *
	 * @param  array  $item  The item from the data array.
	 *
	 * @return string
	 */
	protected function column_display_name( $item ) {

		// Build my markup.
		$setup  = '<a title="' . __( 'View profile', 'liquidweb-woocommerce-gdpr' ) . '" href="' . get_edit_user_link( $item['id'] ) . '">' . esc_html( $item['showname'] ) . '</a>';

		// Return my formatted date.
		return apply_filters( 'lw_woo_gdpr_column_display_name', $setup, $item );
	}

	/**
	 * The email column.
	 *
	 * @param  array  $item  The item from the data array.
	 *
	 * @return string
	 */
	protected function column_email_address( $item ) {

		// Build my markup.
		$setup  = '<a title="' . __( 'Email user', 'liquidweb-woocommerce-gdpr' ) . '" href="' . esc_url( 'mailto:' . antispambot( $item['email_address'] ) ) . '">' . esc_html( antispambot( $item['email_address'] ) ) . '</a>';

		// Return my formatted date.
		return apply_filters( 'lw_woo_gdpr_column_email_address', $setup, $item );
	}

	/**
	 * The request date column.
	 *
	 * @param  array  $item  The item from the data array.
	 *
	 * @return string
	 */
	protected function column_request_date( $item ) {

		// Grab the desired date foramtting.
		$format = apply_filters( 'lw_woo_gdpr_column_date_format', 'Y/m/d \a\t g:ia' );

		// Set my stamp.
		$stamp  = absint( $item['request_date'] );
		$local  = lw_woo_gdpr_gmt_to_local( absint( $item['request_date'] ), $format );

		// Set my date with the formatting.
		$show   = sprintf( _x( '%s ago', '%s = human-readable time difference', 'liquidweb-woocommerce-gdpr' ), human_time_diff( $stamp, current_time( 'timestamp', 1 ) ) );

		// Wrap it in an accessible tag.
		$setup  = '<abbr title="' . esc_attr( $local ) . '">' . esc_attr( $show ) . '</abbr>';

		// Return my formatted date.
		return apply_filters( 'lw_woo_gdpr_column_request_date', $setup, $item );
	}

	/**
	 * The data types column.
	 *
	 * @param  array  $item  The item from the data array.
	 *
	 * @return string
	 */
	protected function column_datatypes( $item ) {

		// Sanitize all the types we have.
		$types  = array_map( 'sanitize_text_field', $item['datatypes'] );

		// Build my markup display.
		$setup  = ! empty( $types ) ? '<em>' . implode( ', ', $types ) . '</em>' : '';

		// Return my formatted data types.
		return apply_filters( 'lw_woo_gdpr_column_datatypes', $setup, $item );
	}

	/**
	 * Get the table data
	 *
	 * @return Array
	 */
	private function table_data() {

		// Bail if we don't have requests.
		if ( false === $requests = lw_woo_gdpr_maybe_requests_exist() ) {
			return array();
		}

		// Set my empty.
		$data   = array();

		// Loop my userdata.
		foreach ( $requests as $user_id => $request_data ) {

			// Fetch our userdata.
			$user   = get_user_by( 'id', $user_id );

			// Set the array of the data we want.
			$setup  = array(
				'id'            => $user_id,
				'username'      => $user->user_login,
				'showname'      => $user->display_name,
				'email_address' => $user->user_email,
				'request_date'  => $request_data['timestamp'],
				'datatypes'     => $request_data['datatypes'],
			);

			// Run it through a filter.
			$data[] = apply_filters( 'lw_woo_gdpr_table_data_item', $setup );
		}

		// Return our data.
		return apply_filters( 'lw_woo_gdpr_table_data_array', $data, $requests );
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  array  $dataset      Our entire dataset.
	 * @param  string $column_name  Current column name
	 *
	 * @return mixed
	 */
	public function column_default( $dataset, $column_name ) {

		// Run our column switch.
		switch ( $column_name ) {

			case 'display_name' :
			case 'email_address' :
			case 'request_date' :
			case 'datatypes' :
				return ! empty( $dataset[ $column_name ] ) ? $dataset[ $column_name ] : '';

			default :
				return apply_filters( 'lw_woo_gdpr_table_column_default', '', $dataset, $column_name );
		}
	}

	/**
	 * Handle bulk actions.
	 *
	 * @see $this->prepare_items()
	 */
	protected function process_bulk_action() {

		// Bail if we aren't on the page.
		if ( empty( $this->current_action() ) || 'lw_woo_gdpr_delete' !== $this->current_action() ) {
			return;
		}

		// Make sure we have the page we want.
		if ( empty( $_GET['page'] ) || LW_WOO_GDPR_MENU_BASE !== esc_attr( $_GET['page'] ) ) {
			return;
		}

		// Bail if a nonce was never passed.
		if ( empty( $_REQUEST['_wpnonce'] ) ) {
			return;
		}

		// Fail on a bad nonce.
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'bulk-userdeletionrequests' ) ) {
			LW_Woo_GDPR_Admin::admin_page_redirect( array( 'success' => 0, 'errcode' => 'bad-nonce' ) );
		}

		// Check for the array of users being passed.
		if ( empty( $_POST['lw_woo_gdpr_users'] ) ) {
			LW_Woo_GDPR_Admin::admin_page_redirect( array( 'success' => 0, 'errcode' => 'no-users' ) );
		}

		// Check our user IDs.
		$users  = array_filter( $_POST['lw_woo_gdpr_users'], 'absint' );

		// Check for the array of users being passed.
		if ( empty( $users ) ) {
			LW_Woo_GDPR_Admin::admin_page_redirect( array( 'success' => 0, 'errcode' => 'no-users' ) );
		}

		// Set a total (blank) for now.
		$total  = array();

		// Now loop my users and process accordingly.
		foreach ( $users as $user_id ) {

			// Fetch my data types and downloads.
			$datatypes  = get_user_meta( $user_id, 'woo_gdpr_deleteme_request', true );

			// Handle my datatypes if we have them.
			if ( ! empty( $datatypes ) ) {

				// Loop my types.
				foreach ( $datatypes as $datatype ) {

					// Remove any files from the meta we have for this data type.
					lw_woo_gdpr()->remove_file_from_meta( $user_id, $datatype );

					// Delete the datatype and add it to the count.
					if ( false !== $items = lw_woo_gdpr()->delete_userdata( $user_id, $datatype ) ) {
						$total[] = $items;
					}
				}
			}

			// Remove any data files we may have.
			lw_woo_gdpr()->delete_user_files( $user_id );

			// Remove this user from our overall data array.
			lw_woo_gdpr()->update_user_delete_requests( $user_id, null, 'remove' );
		}

		// Remove any empties.
		$total  = array_filter( $total );

		// Set a count total.
		$count  = ! empty( $total ) ? array_sum( $total ) : 0;

		// Now handle my redirect.
		LW_Woo_GDPR_Admin::admin_page_redirect( array( 'success' => 1, 'action' => 'deleted', 'count' => absint( $count ) ) );
	}

	/**
	 * Allows you to sort the data by the variables set in the $_GET
	 *
	 * @return Mixed
	 */
	private function sort_data( $a, $b ) {

		// Set defaults and check for query strings.
		$ordby  = ! empty( $_GET['orderby'] ) ? $_GET['orderby'] : 'request_date';
		$order  = ! empty( $_GET['order'] ) ? $_GET['order'] : 'desc';

		// Set my result up.
		$result = strcmp( $a[ $ordby ], $b[ $ordby ] );

		// Return it one way or the other.
		return 'asc' === $order ? $result : -$result;
	}
}
