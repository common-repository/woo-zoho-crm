<?php 

if( !defined( 'ABSPATH' ) ) exit;
	
	global $zoho_db_version;
	$zoho_db_version = '1.0';
	
	wczc_update_db_check();
	function wczc_update_db_check() {
		$zoho_db_version = '1.0';
		if ( $zoho_db_version > get_site_option( 'zoho_db_version' ) || !get_site_option( 'zoho_db_version' )) {
			wczc_install();
			update_option( "zoho_db_version", $zoho_db_version );
		}
	}
	add_action( 'plugins_loaded', 'wczc_update_db_check' );
	
	/* Update DB Version */
	global $wpdb;
	$zoho_db_version = '1.1';
	
	if ( $zoho_db_version > get_site_option( "zoho_db_version" ) ) {
		
		$report_table_name = $wpdb->prefix . 'woo_zoho_crm_report';
		$sql = $wpdb->query("ALTER TABLE $report_table_name	ADD `type` varchar(255) NOT NULL AFTER `action`, ADD `message` TEXT NOT NULL AFTER `type`, ADD `zoho_details` TEXT NOT NULL AFTER `message`");
		update_option( "zoho_db_version", $zoho_db_version );
	}
	/* Update */
	
	function wczc_install() {
		global $wpdb;
		global $zoho_db_version;
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$table_name = $wpdb->prefix . 'woo_zoho_crm';
		$report_table_name = $wpdb->prefix . 'woo_zoho_crm_report';
		$mapping_table_name = $wpdb->prefix . 'woo_zoho_crm_field_mapping';
		
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id int(9) NOT NULL AUTO_INCREMENT,
			zoho_server_name varchar(250) NOT null,
			zoho_client_id varchar(250) NOT null,
			zoho_client_secret_key varchar(250) NOT null,
			zoho_api_token varchar(250) NOT null,
			zoho_refresh_token varchar(250) NOT null,
			zoho_callback_url varchar(250) NOT null,
			product_sync varchar(3) NOT null,
			order_sync varchar(3) NOT null,
			contact_sync varchar(3) NOT null,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";		
		dbDelta( $sql );
		
		$sql = "CREATE TABLE IF NOT EXISTS $report_table_name (
			id int(9) NOT NULL AUTO_INCREMENT,
			wp_id varchar(255) NOT NULL,
			record_id varchar(255) NOT NULL,
			action varchar(255) NOT NULL,
			table_name varchar(255) NOT NULL,
			datetime datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";
		dbDelta( $sql );
		
		$sql = "CREATE TABLE IF NOT EXISTS $mapping_table_name (
			id int(11) NOT NULL AUTO_INCREMENT,
			zoho_field varchar(100) NOT NULL,
			woocommerce_field varchar(100) NOT NULL,
			status int(5) NOT NULL,
			type varchar(100) NOT NULL,
			description varchar(255) NOT NULL,
			is_predefined varchar(5) NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";
		dbDelta( $sql );

		add_option( 'zoho_db_version', $zoho_db_version );
	}

	function wczc_install_data() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'woo_zoho_crm';
		$mapping_table_name = $wpdb->prefix . 'woo_zoho_crm_field_mapping';
		
		$table_row = $wpdb->get_row( "SELECT * FROM $table_name" );
		$mapping_row = $wpdb->get_row( "SELECT * FROM $mapping_table_name" );
		
		if ( $table_row == null ) {
			$wpdb->insert( 
				$table_name, 
				array( 				
					'zoho_server_name' => 'https://crm.zoho.in', 
					'zoho_client_id' => '', 
					'zoho_client_secret_key' => '', 
					'zoho_api_token' => '', 
					'zoho_refresh_token' => '', 
					'zoho_callback_url' => admin_url().'admin.php?page=wczc-menu', 
					'product_sync' => 'Yes', 
					'order_sync' => 'Yes', 
					'contact_sync' => 'Yes',  
					'time' => current_time( 'mysql' ), 
				) 
			);
		}
		
		if ( $mapping_row == null ) {
			$all_items = array(
				array( 'zoho_field' => 'Last_Name', 'woocommerce_field' => 'last_name', 'status' => '1', 'type' => 'Contacts', 'description' => 'Mapping For Last Name', 'is_predefined' => 'Yes' ),
				array( 'zoho_field' => 'Product_Name', 'woocommerce_field' => 'post_name', 'status' => '1', 'type' => 'Products', 'description' => 'Mapping For Product Name', 'is_predefined' => 'Yes'), 
				array( 'zoho_field' => 'Product_Code', 'woocommerce_field' => 'sku', 'status' => '1', 'type' => 'Products', 'description' => 'Mapping For Product Code', 'is_predefined' => 'Yes' ),
				array( 'zoho_field' => 'First_Name', 'woocommerce_field' => 'first_Name', 'status' => '1', 'type' => 'Contacts', 'description' => 'Mapping For First Name', 'is_predefined' => 'Yes' ), 
				array( 'zoho_field' => 'Qty_in_Stock', 'woocommerce_field' => 'stock_quantity', 'status' => '1', 'type' => 'Products', 'description' => 'Mapping For Product Stock Quantity', 'is_predefined' => 'Yes'),
			);

			$query = "INSERT INTO $mapping_table_name (zoho_field, woocommerce_field, status, type, description, is_predefined) VALUES ";

			foreach ( $all_items as $all_item ) {
			  $query .= $wpdb->prepare(
				"(%s, %s, %d, %s, %s, %s),",
				$all_item['zoho_field'], $all_item['woocommerce_field'], $all_item['status'], $all_item['type'], $all_item['description'], $all_item['is_predefined']
			  );
			}

			$query = rtrim( $query, ',' ) . ';';
			
			$wpdb->query( $query );
		
		}
	}