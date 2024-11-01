<?php
	/* get config data */
	global $wpdb;
	$config_table=$wpdb->prefix.'woo_zoho_crm';	
	$config_row = $wpdb->get_row( "SELECT * FROM $config_table" );
	/* end get config data */

	/* Add & Update Customer */
	if ($config_row->contact_sync == "Yes"){
		/* Add Customer */
		function wczc_add_user_to_zoho( $user_id ){
			$user_info = get_userdata($user_id);
			$customrObject = new Customer();
			$customrObject->createOrUpdateCustomer($user_id,$user_info->user_email);
		}
		add_action( 'user_register', 'wczc_add_user_to_zoho');
		/* End Add Customer */
		
		/* Update Customer */		
		function wczc_update_user_to_zoho( $customer_get_id ) { 
			$user_info = get_userdata($customer_get_id);	
			$customrObject = new Customer();
			$customrObject->createOrUpdateCustomer($customer_get_id,$user_info->user_email);
		};  
		add_action( 'woocommerce_update_customer', 'wczc_update_user_to_zoho', 10, 1 ); 		
		/* End Update Customer */	
	}
	/* End Add & Update Customer */

	/* Add & Update Product */
	if ($config_row->product_sync == "Yes"){
		add_action( 'save_post', 'wczc_sync_on_product_save',10,3 );

		function wczc_sync_on_product_save($post_id){
			global $post_type; 

			if ($post_type !== 'product'){
				return;
			}
			
			$product = wc_get_product( $post_id);
			if( $product->is_type( 'simple' ) || $product->is_downloadable('yes') ){		
				if(isset($post_id) && $product->get_sku() != ''){
					$productObject = new Product();
					$productObject->createOrUpdateProduct($post_id,$product->get_sku());
				}		
			}else{
				$children_ids = $product->get_children();		
				foreach($children_ids as $children_id){
					$variable_product = new WC_Product_Variation($children_id);	
					if(!$variable_product->get_sku()){
						throw new Exception("SKU is not found.".$e->getMessage());				
					}
					$productObject = new Product();
					$productObject->createOrUpdateProduct($children_id,$variable_product->get_sku());
				}		
			}
		}
	}
	/* End Add & Update Product */

	/* Add & Update order */
	if ($config_row->order_sync == "Yes"){
		/* Woocommerce Update Order */
		add_action( 'save_post', 'action_woocommerce_update_order',10,3 );
		function action_woocommerce_update_order($order_id, $items){
			global $post_type; 

			if ($post_type !== 'shop_order'){
				return;
			}
			$orderObject = new SalesOrder();
			$orderObject->createOrUpdateSalesOrder($order_id);			
		}
		/* End Woocommerce Update Order */		
		/* Add Order */   
		add_action('woocommerce_thankyou', 'wczc_woocommerce_order_create', 10, 1);
		function wczc_woocommerce_order_create( $order_id ) {
			if ( ! $order_id )
				return;

			if( ! get_post_meta( $order_id, '_thankyou_action_done', true ) ) {
				$orderObject = new SalesOrder();
				$orderObject->createOrUpdateSalesOrder($order_id);
			}
		}
		/* End Add Order */	
	}
	/* End Add & Update order */