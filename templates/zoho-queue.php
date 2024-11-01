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
			<?php echo "<h2>" . __( 'Contacts Manual Sync', 'woocommerce-zoho-crm' ) . "</h2>"; ?>
			<?php
				function usersFinalData($users_data){
					return $users_data['data'];
				}
				
				class Customers_List extends WP_List_Table {
					/** Class constructor */
					public function __construct() {
						parent::__construct( [
							'singular' => __( 'Customer', 'woocommerce-zoho-crm' ), //singular name of the listed records
							'plural'   => __( 'Customers', 'woocommerce-zoho-crm' ), //plural name of the listed records
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
					public static function get_customer( $per_page = 10, $page_number = 1) {
						if (isset($_REQUEST['orderby']) && $_REQUEST['orderby']){
							$orderby = $_REQUEST['orderby'];
						}else{
							$orderby = 'ID';
						}
						if(isset($_REQUEST['order']) && $_REQUEST['order']){
							$order = $_REQUEST['order'];									
						}else{
							$order = 'ASC';
						}
						
						$users = get_users( array( 
								'fields' => 'all',
								'role'   => 'customer',
								'offset' => ($page_number - 1 ) * $per_page,
								'number' => $per_page,
								'orderby' => $orderby,
								'order'  => $order
							) 
						);
						
						$result = json_decode(json_encode($users), true);						
						return array_map('usersFinalData', $result);
					}

					/**
					 * Returns the count of records in the database.
					 *
					 * @return null|string
					 */
					public static function record_count() {
						$users = get_users( array( 
								'fields' => 'all',
								'role'   => 'customer'
							) 
						);
						$total_users = count($users);
						return $total_users;
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
							case 'user_email':
							case 'user_registered':
							case 'display_name':
							case 'woocommerce_name':
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
					function column_woocommerce_name( $item ) {							
						$title = '<strong>' . $item['display_name'] . '</strong>';
						return $title;
					}
					
					function column_action( $item ) {							
						$action = '<form action="" method="post">                      
								<input name="module" value="Contacts" type="hidden" />
								<input name="woo_id" value="'.$item['ID'].'" type="hidden" />
								<input name="woo_customer_email" value="'.$item['user_email'].'" type="hidden" />
								<button class="button" name="SynctoContacts" value="SynctoContacts" type="submit">Sync</button>
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
							'woocommerce_name'    => __( 'Woocommerce Name', 'woocommerce-zoho-crm' ),
							'user_email' => __( 'Email', 'woocommerce-zoho-crm' ),
							'user_registered' => __( 'Create Time', 'woocommerce-zoho-crm' ),
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
							'woocommerce_name' => array( 'display_name', true ),
							'user_email' => array( 'user_email', true ),
							'user_registered' => array( 'user_registered', true )
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
						
						$this->items = self::get_customer( $per_page, $current_page );
					}
				}
				
				$Customers_List = new Customers_List();
				$Customers_List->prepare_items();
				$Customers_List->display(); 
			?>
		</div>		
			
		<?php		
			if(isset($_POST['SynctoContacts'])){
				if($_POST['module'] == 'Contacts'){
					try{
						$response = $customrObject->createOrUpdateCustomer($_POST['woo_id'],$_POST['woo_customer_email']);
					}
					catch(\Exception $e)
					{
						throw new Exception("Error Processing Request".$e->getMessage());			
					}		
				}
			}
	}