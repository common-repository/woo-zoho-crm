<?php 
class Account extends PrepareArray
{	
	public function createOrUpdateAccount($id,$name)
	{
		
		/* get config data */
		global $wpdb;
		$config_table=$wpdb->prefix.'woo_zoho_crm';	
		$config_row = $wpdb->get_row( "SELECT * FROM $config_table" );
		/* end get config data */
		$zohoobj = new wczcMakeApiCall();

		$customerUserData = get_userdata( $id );
		
		$accountName = $name;

		if(isset($Account_Name))
		{
			$url = $config_row->zoho_server_name."/crm/v2/Accounts/search?criteria=(Account_Name:equals:".$accountName.")";
			$method = "GET";
			$response = $zohoobj->makeApiCall($url,$method,$data);		

			if(isset($response) && is_array($response) && isset($response['data'][0]['id']))
			{
				$url = $config_row->zoho_server_name."/v2/Accounts/".$response['data'][0]['id'];
				$method = "PUT";
				$data = $this->prepareArrayForAccount($id,$accountName);
				$accountResponse = $zohoobj->makeApiCall($url,$method,$data);
				return $accountResponse;
			}
			else
			{
				$url = $config_row->zoho_server_name."/crm/v2/Accounts";
				$method = "POST";
				$data = $this->prepareArrayForAccount($id,$accountName);
				$accountResponse = $zohoobj->makeApiCall($url,$method,$data);
				return $accountResponse;
			}	
		}		
	}
}