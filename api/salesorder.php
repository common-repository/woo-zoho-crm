<?php 
class SalesOrder extends PrepareArray{	
	public function createOrUpdateSalesOrder($id){		
		/* get config data */
		global $wpdb;
		$config_table=$wpdb->prefix.'woo_zoho_crm';	
		$config_row = $wpdb->get_row( "SELECT * FROM $config_table" );
		/* end get config data */
		
		$url = $config_row->zoho_server_name."/crm/v2/Sales_Orders/search?criteria=(Subject:equals:".$id.")";
		$zohoobj = new wczcMakeApiCall();
		$data = '';
		$response = $zohoobj->makeApiCall($url,'GET',$data);		
		$zohoOrderId = $response['data'][0]['id'];

		if(isset($response) && is_array($response) && isset($zohoOrderId)){
			$url = $config_row->zoho_server_name."/crm/v2/Sales_Orders/" . $zohoOrderId ;	
			$data = $this->prepareArrayForSalesOrder($id);			
			$response = $zohoobj->makeApiCall($url,'PUT',$data);
			$response['data']['action'] = "Record Update";
		}else{
			$url = $config_row->zoho_server_name."/crm/v2/Sales_Orders";			
			$data = $this->prepareArrayForSalesOrder($id);		
			$response = $zohoobj->makeApiCall($url,'POST',$data);
			$response['data']['action'] = "Record Insert";
		}

		$report_table = $wpdb->prefix.'woo_zoho_crm_report';
		$post_data=array(
			'wp_id' => $id,
			'record_id' => (isset($response['data'][0]['details']['id']))?$response['data'][0]['details']['id']:'',
			'action' => $response['data']['action'],
			'type' => $response['data'][0]['status'],
			'message' => $response['data'][0]['message'],
			'zoho_details' => serialize($response['data']),
			'table_name' => 'Sales_Orders',
			'datetime' => date('Y-m-d H:i:s'),
		);
		$wpdb->insert( $report_table, $post_data);
		return $response;
	}
}