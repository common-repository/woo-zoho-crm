<?php 
class Customer extends PrepareArray{
	public function createOrUpdateCustomer($id,$email){				
		/* get config data */
		global $wpdb;
		$config_table=$wpdb->prefix.'woo_zoho_crm';	
		$config_row = $wpdb->get_row( "SELECT * FROM $config_table" );
		/* end get config data */
		
		$zohoobj = new wczcMakeApiCall();
		$url = $config_row->zoho_server_name."/crm/v2/Contacts/search?criteria=(Email:equals:".$email.")";
		$method = "GET";
		$data = '';
		$response = $zohoobj->makeApiCall($url,$method,$data);

		if(isset($response) && is_array($response) && isset($response['data'][0]['id'])){
			$url = $config_row->zoho_server_name."/crm/v2/Contacts/".$response['data'][0]['id'];
			$method = "PUT";
			$data = $this->prepareArrayForCustomer($id,$email);
			$contactResponse = $zohoobj->makeApiCall($url,$method,$data);
			$contactResponse['data']['action'] = "Record Update";
		}else{
			$url = $config_row->zoho_server_name."/crm/v2/Contacts";
			$method = "POST";
			$data = $this->prepareArrayForCustomer($id,$email);
			$contactResponse = $zohoobj->makeApiCall($url,$method,$data);
			$contactResponse['data']['action'] = "Record Insert";
		}

		$report_table=$wpdb->prefix.'woo_zoho_crm_report';
		$post_data=array(
			'wp_id' => $email,
			'record_id' => (isset($contactResponse['data'][0]['details']['id']))?$contactResponse['data'][0]['details']['id']:'',
			'action' => $contactResponse['data']['action'],
			'type' => $contactResponse['data'][0]['status'],
			'message' => $contactResponse['data'][0]['message'],
			'zoho_details' => serialize($contactResponse['data']),
			'table_name' => 'Contacts',
			'datetime' => date('Y-m-d H:i:s'),
		);
		$wpdb->insert( $report_table, $post_data);

		return $contactResponse;
	}
}