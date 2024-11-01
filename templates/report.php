<?php 
	if( !defined( 'ABSPATH' ) ) exit;	

	if ( ! class_exists( 'WP_List_Table' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}	
	if ( current_user_can( 'activate_plugins' ) && current_user_can( 'update_core' ) ) {
	?>
		<div class="wrap">
			<?php 	
				echo "<h2>" . __( 'Zoho Report', 'menu-zoho' ) . "</h2>";	
				global $wpdb;
				$table=$wpdb->prefix.'woo_zoho_crm_report';	
				$report_row = $wpdb->get_results( "SELECT * FROM $table" );
			?>
			<?php
				class Report_List extends WP_List_Table {
					/** Class constructor */
					public function __construct() {
						parent::__construct( [
							'singular' => __( 'Report', 'woocommerce-zoho-crm' ), //singular name of the listed records
							'plural'   => __( 'Report', 'woocommerce-zoho-crm' ), //plural name of the listed records
							'ajax'     => true //does this table support ajax?
						] );
					}

					/**
					 * Retrieve customers data from the database
					 *
					 * @param int $per_page
					 * @param int $page_number
					 *
					 * @return mixed
					 */
					public static function get_report( $per_page = 10, $page_number = 1) {
						global $wpdb;
						$table = $wpdb->prefix.'woo_zoho_crm_report';	
						$report_row = "SELECT * FROM $table";
						
						if ( ! empty( $_REQUEST['orderby'] ) ) {
							$report_row .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
							$report_row .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' DESC';
						}else{
							$report_row .= ' ORDER BY id desc';
						}
						$report_row .= " LIMIT $per_page";
						$report_row .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
						
						$reportData = $wpdb->get_results( $report_row, 'ARRAY_A' );
						$reportResult = json_decode(json_encode($reportData), true);						
						return $reportResult;						
					}

					/**
					 * Returns the count of records in the database.
					 *
					 * @return null|string
					 */
					public static function record_count() {
						global $wpdb;
						$table = $wpdb->prefix.'woo_zoho_crm_report';	
						$report_row = "SELECT COUNT(*) FROM $table";						
						return $wpdb->get_var( $report_row );
					}
					
					/** Text displayed when no customer data is available */
					public function no_items() {
						_e( 'No customers avaliable.', 'woocommerce-zoho-crm' );
					}

					/**
					 * Render a column when no column specific method exist.
					 *
					 * @param array $item
					 * @param string $column_name
					 *
					 * @return mixed
					 */
					public function column_default( $item, $column_name ) {
						switch ( $column_name ) {
							case 'id':
							case 'wp_id':
							case 'record_id':
							case 'table_name':
							case 'action':
							case 'type':
							case 'message':
							case 'datetime':
								return $item[ $column_name ];
							default:
								return print_r( $item, true ); //Show the whole array for troubleshooting purposes
						}
					}

					/**
					 * Method for name column
					 *
					 * @param array $item an array of DB data
					 *
					 * @return string
					 */
						
					function column_message( $item ) {
						$response = print_r(unserialize($item['zoho_details']), true);
						if ($item['type'] != 'success'){
							$message = $item['message'].'<div class="details-outer">
										<a class="btn btn-details">Zoho Response</a>
										<div class="details-inner"><pre>'.$response.'</pre></div></div>';
						}else{
							$message = $item['message'];
						}
						
						return $message;
					}

					/**
					 *  Associative array of columns
					 *
					 * @return array
					 */
					function get_columns() {
						$columns = [
							'id'    => __( 'Id', 'woocommerce-zoho-crm' ),
							'wp_id'    => __( 'Woocommerce Id', 'woocommerce-zoho-crm' ),
							'record_id'    => __( 'Record Id', 'woocommerce-zoho-crm' ),
							'table_name'    => __( 'Table Name', 'woocommerce-zoho-crm' ),
							'action'    => __( 'Action', 'woocommerce-zoho-crm' ),
							'type'    => __( 'Type', 'woocommerce-zoho-crm' ),
							'message'    => __( 'Message', 'woocommerce-zoho-crm' ),
							'datetime'    => __( 'Sync Date & Time', 'woocommerce-zoho-crm' )
						];
						return $columns;
					}

					/**
					 * Columns to make sortable.
					 *
					 * @return array
					 */
					
					public function get_sortable_columns() {
						$sortable_columns = array(
							'id' => array( 'id', true ),
							'wp_id' => array( 'wp_id', true ),
							'record_id' => array( 'record_id', true ),
							'table_name' => array( 'table_name', true ),
							'type' => array( 'type', true ),
							'datetime' => array( 'datetime', true )
						);
						return $sortable_columns;
					}
					
					/**
					 * Handles data query and filter, sorting, and pagination.
					 */
					public function prepare_items() {
						$columns = $this->get_columns();
						$hidden = array();
						$sortable = $this->get_sortable_columns();
						$this->_column_headers = array($columns, $hidden, $sortable);
														
						/** Process bulk action */
						$this->process_bulk_action();
						$per_page     = $this->get_items_per_page( 'customers_per_page', 10 );
						$current_page = $this->get_pagenum();
						$total_items  = self::record_count();

						$this->set_pagination_args( [
							'total_items' => $total_items, //WE have to calculate the total number of items
							'per_page'    => $per_page //WE have to determine how many items to show on a page
						] );
						
						$this->items = self::get_report( $per_page, $current_page );
					}
				}
				
				$Report_List = new Report_List();
				$Report_List->prepare_items();
				$Report_List->display(); 
			?>			
		</div>		
	<?php 
	}