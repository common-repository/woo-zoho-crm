<?php 
	if( !defined( 'ABSPATH' ) ) exit;

	if ( current_user_can( 'activate_plugins' ) && current_user_can( 'update_core' ) ){
?>
		<div class="wrap">
			<?php
				echo "<h2>" . esc_html( __( 'Magesture Zoho CRM Integration Options', 'woocommerce-zoho-crm' )) . "</h2>";
				/* get config data */
				global $wpdb;
				$config_table=$wpdb->prefix.'woo_zoho_crm';	
				$config_row = $wpdb->get_row( "SELECT * FROM $config_table" );
				/* end get config data */
			?>
			<br/>
			<br/>		
			<form action="" method="POST" class="config-form">
				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<div class="row">
								<div class="col-sm-3"><?php echo "<strong>" . esc_html( __( 'Choose Zoho Server', 'woocommerce-zoho-crm' )) . "</strong>";?></div>
								<div class="col-sm-9">
									<input type="radio" class="zoho-server form-control"  name="zoho_server" id="crm_zoho_in" value="https://crm.zoho.in" <?php if ( $config_row->zoho_server_name == 'https://crm.zoho.in') { echo 'checked="checked"'; } ?>  /><label for="crm_zoho_in"><?php echo esc_html( __( 'crm.zoho.in', 'woocommerce-zoho-crm' )); ?></label><br/>
									<input type="radio" class="zoho-server form-control" name="zoho_server" id="crm_zoho_com" value="https://crm.zoho.com" <?php if ( $config_row->zoho_server_name == 'https://crm.zoho.com') { echo 'checked="checked"'; } ?>  /> <label for="crm_zoho_com"><?php echo esc_html( __( 'crm.zoho.com', 'woocommerce-zoho-crm' )); ?></label><br/>
									<input type="radio" class="zoho-server form-control" name="zoho_server" id="crm_zoho_eu" value="https://crm.zoho.eu" <?php if ( $config_row->zoho_server_name == 'https://crm.zoho.eu') { echo 'checked="checked"'; } ?> /> <label for="crm_zoho_eu"><?php echo esc_html( __( 'crm.zoho.eu', 'woocommerce-zoho-crm' )); ?></label><br/>
									<input type="radio" class="zoho-server form-control" name="zoho_server" id="crm_zoho_cn" value="https://crm.zoho.com.cn" <?php if ( $config_row->zoho_server_name == 'https://crm.zoho.com.cn') { echo 'checked="checked"'; } ?> /> <label for="crm_zoho_cn"><?php echo esc_html( __( 'crm.zoho.com.cn', 'woocommerce-zoho-crm' )); ?></label>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="row">
								<div class="col-sm-3"><?php echo "<strong>" . esc_html( __( 'Zoho Client ID', 'woocommerce-zoho-crm' )) . "</strong>";?></div>
								<div class="col-sm-9">
									<?php										
										if($config_row->zoho_server_name = 'https://crm.zoho.in'){
											$zohoUrl = 'https://www.zoho.in';
										}else if($config_row->zoho_server_name = 'https://crm.zoho.com'){
											$zohoUrl = 'https://www.zoho.com';
										}else if($config_row->zoho_server_name = 'https://crm.zoho.eu'){
											$zohoUrl = 'https://www.zoho.eu';
										}else if($config_row->zoho_server_name = 'https://crm.zoho.com.cn'){
											$zohoUrl = 'https://www.zoho.com.cn';
										}
									?>
									<input type="text" class="form-control form-control-sm" name="zoho_client_id" id="zoho_client_id" value="<?php echo $config_row->zoho_client_id; ?>" required />
									<a id="get-access-token-url-developer" href="<?php echo $zohoUrl; ?>/accounts/protocol/oauth-setup.html" class="btn btn-info woocommerce-save-button" target="_blank"><?php echo esc_html( __( 'How to create Client id and Secret key', 'woocommerce-zoho-crm' )); ?></a>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="row">
								<div class="col-sm-3"><?php echo "<strong>" . esc_html( __( 'Zoho Client Secret Key', 'woocommerce-zoho-crm' )) . "</strong>";?></div>
								<div class="col-sm-9">
									<input type="text" class="form-control form-control-sm" name="zoho_client_secret_key" id="zoho_client_secret_key" value="<?php echo $config_row->zoho_client_secret_key; ?>" required />
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="row">
								<div class="col-sm-3"><?php echo "<strong>" . esc_html( __( 'Zoho API Auth Token', 'woocommerce-zoho-crm' )) . "</strong>";?></div>
								<div class="col-sm-9">
									<input type="text" class="form-control form-control-sm" name="zoho_api_token" id="zoho_api_token" value="<?php echo $config_row->zoho_api_token; ?>" readonly/>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="row">
								<div class="col-sm-3"><?php echo "<strong>" . esc_html( __( 'Zoho Authorized redirect URIs', 'woocommerce-zoho-crm' )) . "</strong>";?></div>
								<div class="col-sm-9">
									<input type="hidden" class="form-control form-control-sm" name="callback_url" id="callback_url" value="<?php echo admin_url(); ?>admin.php?page=wczc-menu" readonly />
									<h6><?php echo admin_url(); ?>admin.php?page=wczc-menu</h6>
									<p><strong>Note : </strong>Enter this url in Authorized redirect URIs field in Zoho Client ID Details Settings.</p>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="row">
								<div class="col-sm-3"><?php echo "<strong>" . esc_html( __( 'Auto sync Products', 'woocommerce-zoho-crm' )) . "</strong>";?></div>
								<div class="col-sm-9">
									<input type="radio" class="form-control"  name="product_sync" id="product_yes" value="Yes" <?php if ( $config_row->product_sync == 'Yes') { echo 'checked="checked"'; } ?> /><label for="product_yes"><?php echo esc_html( __( 'Yes', 'woocommerce-zoho-crm' )); ?> &nbsp;&nbsp;</label>
									<input type="radio" class="form-control"  name="product_sync" id="product_no" value="No" <?php if ( $config_row->product_sync == 'No') { echo 'checked="checked"'; } ?>  /><label for="product_no"><?php echo esc_html( __( 'No', 'woocommerce-zoho-crm' )); ?></label>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="row">
								<div class="col-sm-3"><?php echo "<strong>" . esc_html( __( 'Auto sync Orders', 'woocommerce-zoho-crm' )) . "</strong>";?></div>
								<div class="col-sm-9">
									<input type="radio" class="form-control"  name="order_sync" id="order_yes" value="Yes" <?php if ( $config_row->order_sync == 'Yes') { echo 'checked="checked"'; } ?> /><label for="order_yes"><?php echo esc_html( __( 'Yes', 'woocommerce-zoho-crm' )); ?> &nbsp;&nbsp;</label>
									<input type="radio" class="form-control"  name="order_sync" id="order_no" value="No" <?php if ( $config_row->order_sync == 'No') { echo 'checked="checked"'; } ?>  /><label for="order_no"><?php echo esc_html( __( 'No', 'woocommerce-zoho-crm' )); ?></label>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="row">
								<div class="col-sm-3"><?php echo "<strong>" . esc_html( __( 'Auto sync Contacts', 'woocommerce-zoho-crm' )) . "</strong>"; ?></div>
								<div class="col-sm-9">
									<input type="radio" class="form-control"  name="contact_sync" id="contact_yes" value="Yes" <?php if ( $config_row->contact_sync == 'Yes') { echo 'checked="checked"'; } ?> /><label for="contact_yes"><?php echo esc_html( __( 'Yes', 'woocommerce-zoho-crm' )); ?> &nbsp;&nbsp;</label>
									<input type="radio" class="form-control"  name="contact_sync" id="contact_no" value="No" <?php if ( $config_row->contact_sync == 'No') { echo 'checked="checked"'; } ?>  /><label for="contact_no"><?php echo esc_html( __( 'No', 'woocommerce-zoho-crm' )); ?></label>
								</div>
							</div>
						</div>				
						<button name="save" class="button-primary woocommerce-save-button" type="submit" value="Save changes"><?php echo esc_html( __( 'Save changes', 'woocommerce-zoho-crm' )); ?></button>	
					</div>
				</div>
			</form>	
		</div>
		<script>
			jQuery(document).ready(function() {
				jQuery('input:radio[name=zoho_server]').change(function() {
					if (this.value == 'https://crm.zoho.in') {
						jQuery('#get-access-token-url-developer').attr("href", "https://www.zoho.in/accounts/protocol/oauth-setup.html");
					}else if (this.value == 'https://crm.zoho.com') {
						jQuery('#get-access-token-url-developer').attr("href", "https://www.zoho.com/accounts/protocol/oauth-setup.html");
					}else if (this.value == 'https://crm.zoho.eu') {
						jQuery('#get-access-token-url-developer').attr("href", "https://www.zoho.eu/accounts/protocol/oauth-setup.html");
					}else if (this.value == 'https://crm.zoho.com.cn') {
						 jQuery('#get-access-token-url-developer').attr("href", "https://www.zoho.com.cn/accounts/protocol/oauth-setup.html");
					}
				});
			});
		</script>
		<?php
			if(isset($_POST['save'])){
				global $wpdb;
				$config_table=$wpdb->prefix.'woo_zoho_crm';

				if (sanitize_text_field($_POST['zoho_server']) == "https://crm.zoho.in"){
					$url = "zoho.in";
				}else if (sanitize_text_field($_POST['zoho_server']) == "https://crm.zoho.com"){
					$url = "zoho.com";
				}else if (sanitize_text_field($_POST['zoho_server']) == "https://crm.zoho.eu"){
					$url = "zoho.eu";
				}else if (sanitize_text_field($_POST['zoho_server']) == "https://crm.zoho.com.cn"){
					$url = "zoho.com.cn";
				}
				
				$post_data=array(
					'zoho_server_name' => sanitize_text_field($_POST['zoho_server']),
					'zoho_client_id' => sanitize_text_field($_POST['zoho_client_id']),
					'zoho_client_secret_key' => sanitize_text_field($_POST['zoho_client_secret_key']),
					'zoho_api_token' => sanitize_text_field($_POST['zoho_api_token']),
					'zoho_callback_url' => sanitize_text_field($_POST['callback_url']),
					'product_sync' => sanitize_text_field($_POST['product_sync']),
					'order_sync' => sanitize_text_field($_POST['order_sync']),
					'contact_sync' => sanitize_text_field($_POST['contact_sync']),
					'time' => current_time( 'mysql' ) 
				);
				if ( $config_row != null ) {
					$id = $config_row->id;
					$wpdb->update($config_table, $post_data, array('id'=>$id));
				}else{
					$wpdb->insert( $config_table, $post_data);
				}
				echo "<script type='text/javascript'>
						window.location = 'https://accounts.".$url."/oauth/v2/auth?scope=ZohoCRM.modules.ALL,ZohoCRM.settings.all&client_id=".sanitize_text_field($_POST['zoho_client_id'])."&response_type=code&access_type=offline&redirect_uri=".$config_row->zoho_callback_url."&prompt=consent'
					</script>";
			}
		
	}
	
	global $wpdb;
	$config_table=$wpdb->prefix.'woo_zoho_crm';	
	$config_row = $wpdb->get_row( "SELECT * FROM $config_table" );
	if ($config_row->zoho_server_name == "https://crm.zoho.in"){
		$url = "zoho.in";
	}else if ($config_row->zoho_server_name == "https://crm.zoho.com"){
		$url = "zoho.com";
	}else if ($config_row->zoho_server_name == "https://crm.zoho.eu"){
		$url = "zoho.eu";
	}else if ($config_row->zoho_server_name == "https://crm.zoho.com.cn"){
		$url = "zoho.com.cn";
	}	
	
	if(isset($_GET['code'])){	
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://accounts.".$url."/oauth/v2/token?code=".$_GET['code']."&redirect_uri=".$config_row->zoho_callback_url."&client_id=".$config_row->zoho_client_id."&client_secret=".$config_row->zoho_client_secret_key."&grant_type=authorization_code",
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

		if ($err){
			echo "cURL Error #:" . $err;
		} else {	
			$post_data=array(			
				'zoho_api_token' => $response['access_token'],
				'zoho_refresh_token' => $response['refresh_token'],
				'time' => current_time( 'mysql' )
			);
			$id = $config_row->id;
			$wpdb->update($config_table, $post_data, array('id'=>$id));
			
			header("Location: ".wp_nonce_url(admin_url()."admin.php?page=wczc-menu"));	
		}
	}