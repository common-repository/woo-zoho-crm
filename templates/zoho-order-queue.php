<?php 
if( !defined( 'ABSPATH' ) ) exit;
	
	
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}	
	if ( current_user_can( 'activate_plugins' ) && current_user_can( 'update_core' ) ){
		$zohoobj = new wczcMakeApiCall();	
		$prepareArrayObject = new PrepareArray();
		$customrObject = new Customer();
		$productObject = new Product();
		$salesOrderObject = new SalesOrder();
		
		/* get config data */
		global $wpdb;
		$config_table=$wpdb->prefix.'woo_zoho_crm';	
		$config_row = $wpdb->get_row( "SELECT * FROM $config_table" );
		/* end get config data */
?>
		<div class="wrap">
			<?php echo "<h2>" . __( 'Orders Manual Sync', 'woocommerce-zoho-crm' ) . "</h2>"; ?>
			<?php
				class Order_List extends WP_List_Table {
					/** Class constructor */
					public function __construct() {
						parent::__construct( [
							'singular' => __( 'Order', 'woocommerce-zoho-crm' ), //singular name of the listed records
							'plural'   => __( 'Orders', 'woocommerce-zoho-crm' ), //plural name of the listed records
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
					public static function get_order( $per_page = 10, $page_number = 1) {
						global $wpdb;
						$orderTableName = $wpdb->prefix.'posts';
						$orderSql = "SELECT $orderTableName.ID, $orderTableName.post_date FROM $orderTableName WHERE $orderTableName.post_type = 'shop_order' AND post_status != 'draft'";
						if ( ! empty( $_REQUEST['orderby'] ) ) {
							$orderSql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
							$orderSql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
						}
						$orderSql .= " LIMIT $per_page";
						$orderSql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
						
						$orderData = $wpdb->get_results( $orderSql, 'ARRAY_A' );
						$orderResult = json_decode(json_encode($orderData), true);			
						return $orderResult;
					}

					/**
					 * Returns the count of records in the database.
					 *
					 * @return null|string
					 */
					public static function record_count() {
						global $wpdb;
						$orderTableName = $wpdb->prefix.'posts';
						$orderSql = "SELECT COUNT(*) FROM $orderTableName WHERE $orderTableName.post_type = 'shop_order' AND post_status != 'draft'";
						
						return $wpdb->get_var( $orderSql );
						
						/* $query = new WC_Order_Query(array(
							'limit' => -1
						));
						$orders = $query->get_orders();
						
						$total_orders = count($orders);
						return $total_orders; */
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
							case 'ID':
							case 'post_date':
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
						
					function column_action( $item ) {							
						$action = '<form action="" method="post">                      
										<input name="module" value="Sales_Orders" type="hidden" />
										<input name="woo_order_id" value="'.$item['ID'].'" type="hidden" />
										<button class="button" name="SynctoSalesOrder" value="SynctoOrders" type="submit">Sync</button>
									</form>';
						return $action;
					}

					/**
					 *  Associative array of columns
					 *
					 * @return array
					 */
					function get_columns() {
						$columns = [
							'ID'    => __( 'Woocommerce Id', 'woocommerce-zoho-crm' ),
							'post_date'    => __( 'Create Time', 'woocommerce-zoho-crm' ),
							'action'    => __( 'Action', 'woocommerce-zoho-crm' )
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
							'ID' => array( 'ID', true ),
							'post_date' => array( 'post_date', true )
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
						
						$this->items = self::get_order( $per_page, $current_page );
					}
				}
				
				$Orders_List = new Order_List();
				$Orders_List->prepare_items();
				$Orders_List->display(); 
			
				if(isset($_POST['SynctoSalesOrder'])){
					if($_POST['module'] == 'Sales_Orders'){
						try{
							$response = $salesOrderObject->createOrUpdateSalesOrder($_POST['woo_order_id']);
						}
						catch(\Exception $e){
							throw new Exception($e->getMessage());
						}
					}
				}
	?>
		</div>
	<?php
	}