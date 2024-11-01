<?php 		
class wczcMakeApiCall{	
	
	public function makeApiCall($url,$method,$data=null){
		/* get config data */
		global $wpdb;
		$config_table=$wpdb->prefix.'woo_zoho_crm';	
		$config_row = $wpdb->get_row( "SELECT * FROM $config_table" );
		/* end get config data */
		if ($config_row->zoho_server_name == "https://crm.zoho.in"){
			$server_url = "zoho.in";
		}else if ($config_row->zoho_server_name == "https://crm.zoho.com"){
			$server_url = "zoho.com";
		}else if ($config_row->zoho_server_name == "https://crm.zoho.eu"){
			$server_url = "zoho.eu";
		}else if ($config_row->zoho_server_name == "https://crm.zoho.com.cn"){
			$server_url = "zoho.com.cn";
		}
		
		$database = strtotime($config_row->time);
		$current = strtotime(current_time( 'mysql' ));
		$dteDiff  = $current-$database; 
		
		if($dteDiff > 2000){
			$curl = curl_init();
			curl_setopt_array($curl, array(			
				CURLOPT_URL => "https://accounts.".$server_url."/oauth/v2/token?refresh_token=".$config_row->zoho_refresh_token."&client_id=".$config_row->zoho_client_id."&client_secret=".$config_row->zoho_client_secret_key."&grant_type=refresh_token",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 300,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "POST",			  
				CURLOPT_POSTFIELDS => '',
				CURLOPT_HTTPHEADER => array(
					"Content-Type: application/json",
					"cache-control: no-cache",
					"scope: ZohoCRM.modules.all,ZohoCRM.settings.fields.all,ZohoCRM.settings.all,ZohoCRM.users.ALL,crmapi"
				),
			));

			$response = json_decode(curl_exec($curl),true);
			
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
				echo "cURL Error #:" . $err;
			} else {	
				$post_data=array(			
					'zoho_api_token' => $response['access_token'],
					'time' => current_time( 'mysql' )
				);
				$id = $config_row->id;
				$wpdb->update($config_table, $post_data, array('id'=>$id));
				$token = $response['access_token'];				
			}
			
		}else{
			$token = $config_row->zoho_api_token;
		}
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 300,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $method,			  
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_HTTPHEADER => array(
				"Authorization: Bearer ".$token,
				"Content-Type: application/json",
				"cache-control: no-cache",
				"scope: ZohoCRM.modules.all,ZohoCRM.settings.fields.all,ZohoCRM.settings.all,ZohoCRM.users.ALL,crmapi"
			),
		));

		$response = json_decode(curl_exec($curl),true);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			echo "cURL Error #:" . $err;
		} else {
			return $response;
		}
	}	
}
