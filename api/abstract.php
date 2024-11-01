<?php 
class PrepareArray{
	protected $table;

	protected $zohoPostData;
	
	/* Contacts */
	public function prepareArrayForCustomer($id,$email)	{
		try{
			if($id != 0){
				global $wpdb;
				$CustomerArray = [];				
				$customerUserData = (array)get_userdata( $id );
				$customerUserMetdaData = get_user_meta( $id );

				if($customerUserMetdaData['first_name'][0] != ''  && $customerUserMetdaData['last_name'][0] != ''){
					$this->zohoPostData = array(
						"Email"=>$customerUserData['data']->user_email,
						"First_Name"=>($customerUserMetdaData['first_name'][0]),
						"Last_Name"=>($customerUserMetdaData['last_name'][0])
					);
				}else if($customerUserMetdaData['billing_first_name'][0] != ''  && $customerUserMetdaData['billing_last_name'][0] != ''){
					$this->zohoPostData = array(
						"Email"=>$customerUserData['data']->user_email,
						"First_Name"=>($customerUserMetdaData['billing_first_name'][0]),
						"Last_Name"=>($customerUserMetdaData['billing_last_name'][0])
					);
				}else{
					$this->zohoPostData = array(
						"Last_Name"=> $customerUserData['data']->user_nicename,
						"First_Name"=> $customerUserData['data']->user_nicename,						
						"Email"=>$customerUserData['data']->user_email,
					);
				}

				$customerDataArray = get_user_meta( $id );
				
				$additionalArray = [];
				//$additionalArray['Full_Name'] = $customerDataArray['first_name'][0].' '.$customerDataArray['last_name'][0];	
				$additionalArray['Phone'] = $customerDataArray['billing_phone'][0];	
				$additionalArray['Mailing_Street'] = $customerDataArray['billing_address_1'][0].' '.$customerDataArray['billing_address_2'][0];	
				$additionalArray['Mailing_City'] = $customerDataArray['billing_city'][0];	
				$additionalArray['Mailing_State'] = $customerDataArray['billing_state'][0];	
				$additionalArray['Mailing_Zip'] = $customerDataArray['billing_postcode'][0];	
				$additionalArray['Mailing_Country'] = $customerDataArray['billing_country'][0];	
				$additionalArray['Secondary_Email'] = $customerDataArray['billing_email'][0];	
				$shippingStreet = '';
				if(isset($customerDataArray['shipping_address_1']) && $customerDataArray['shipping_address_1'][0]){
					$shippingStreet .= $customerDataArray['shipping_address_1'][0];
				}
				if(isset($customerDataArray['shipping_address_2']) && $customerDataArray['shipping_address_2'][0]){
					$shippingStreet .= ' '.$customerDataArray['shipping_address_2'][0];
				}
				$additionalArray['Other_Street'] = $shippingStreet;
				
				$additionalArray['Other_City'] = (isset($customerDataArray['shipping_city'][0]))?$customerDataArray['shipping_city'][0]:'';
				$additionalArray['Other_State'] = (isset($customerDataArray['shipping_state'][0]))?$customerDataArray['shipping_state'][0]:'';
				$additionalArray['Other_Zip'] = (isset($customerDataArray['shipping_postcode'][0]))?$customerDataArray['shipping_postcode'][0]:'';
				$additionalArray['Other_Country'] = (isset($customerDataArray['shipping_country'][0]))?$customerDataArray['shipping_country'][0]:'';
				$additionalArray['Description'] = $customerDataArray['description'][0];	
				
				$this->zohoPostData = array_merge($this->zohoPostData,$additionalArray);			
				$postData = json_encode(['data'=>[$this->zohoPostData]]);
				return $postData;
			}else{
				$get_order = array(
					'email' => $email,
				);
				$get_orderby_email = wc_get_orders( $get_order );
				foreach($get_orderby_email as $get_orderdata){
					$first_name = $get_orderdata->get_billing_first_name();
					$last_name = $get_orderdata->get_billing_last_name();
					$billing_email = $get_orderdata->get_billing_email();
				}
				if($billing_email != ''){
					$this->zohoPostData = array(
						"First_Name"=>$first_name,
						"Last_Name"=>$last_name,
						"Email"=>$billing_email,
					);
					$postData = json_encode(['data'=>[$this->zohoPostData]]);

					return $postData;
				}
			}
		}
		catch(\Exception $e){
			throw new Exception("Error PrepareArray for customer ".$e->getMessage());			
		}		
	}
	/* end Contacts */
	/* Products */
	public function prepareArrayForProducts($id){
		try{
			$product = wc_get_product( $id );
			$productData = '';
			$productAdditionalData = [];
			$productData = [];
			$children_ids = $product->get_children();

			if( $product->is_type( 'simple' ) || $product->is_downloadable('yes') ){
				$productData = $product->get_data();
				$productAdditionalData = (array)get_post( $id );
				$productData = array_merge($productData,$productAdditionalData);
				$product_category = get_term( $productData['category_ids'][0], 'product_cat' );
				
				$additionalArray = [];
				
				$additionalArray['Product_Name'] = $productData['name'];	
				$additionalArray['Product_Code'] = $productData['sku'];	
				$additionalArray['Product_Category'] = $product_category->name;	
				$additionalArray['Sales_Start_Date'] = (isset($productData['date_on_sale_from']) && $productData['date_on_sale_from'])?date_format($productData['date_on_sale_from'],"Y-m-d"):'';	
				$additionalArray['Sales_End_Date'] = (isset($productData['date_on_sale_to']) && $productData['date_on_sale_to'])?date_format($productData['date_on_sale_to'],"Y-m-d"):'';	
				$additionalArray['Unit_Price'] = $productData['price'];	
				$additionalArray['Tax'] = $productData['tax_class'];			
				$additionalArray['Qty_in_Stock'] = $productData['stock_quantity'];	
				$additionalArray['Description'] = $productData['description'];		
				
				$this->zohoPostData = $additionalArray;							
				$postData = json_encode(['data'=>[$this->zohoPostData]]);				
				return $postData;
			}else{
				$variableProduct = new WC_Product_Variation($id); //children_id
				$sku = get_post_meta( $id, '_sku', true ); //$children_id
				$product_name = $variableProduct->get_name();
				$qty = $variableProduct->get_stock_quantity();
				$pid = $id;
			
				$productData = $variableProduct->get_data();			
				$productAdditionalData = (array)get_post( $pid );  
				$productData = array_merge($productData,$productAdditionalData);				
				
				$additionalArray['Product_Name'] = $product_name;	
				$additionalArray['Product_Code'] = $sku;	
				$additionalArray['Sales_Start_Date'] = (isset($productData['date_on_sale_from']) && $productData['date_on_sale_from'])?date_format($productData['date_on_sale_from'],"Y-m-d"):'';	
				$additionalArray['Sales_End_Date'] = (isset($productData['date_on_sale_to']) && $productData['date_on_sale_to'])?date_format($productData['date_on_sale_to'],"Y-m-d"):'';	
				$additionalArray['Unit_Price'] = $productData['price'];			
				$additionalArray['Qty_in_Stock'] = $qty;	
				$additionalArray['Description'] = $productData['description'];		

				$this->zohoPostData = $additionalArray;

				$postData = json_encode(['data'=>[$this->zohoPostData]]);

				return $postData;
			}
		}
		catch(\Exception $e){
			throw new  Exception("Error PrepareArray for product ".$e->getMessage());			
		}		
	}
	/* end Product */
    /* Order */
	public function prepareArrayForSalesOrder($id){
		try{				
			global $wpdb;

			$orderData = wc_get_order( $id )->get_data();				
			$customer_id ='';
			$customer_email = '';	
			$accountnm = '';
			
			if($orderData['customer_id'] > 0){		
				$customerData = get_user_by('id',$orderData['customer_id']);	
				$customer_id = $customerData->data->ID;
				$customer_email = $customerData->data->user_email;
				$customer_name = $customerData->data->display_name;
				$accountnm = $customerData->data->user_nicename;
			}else{		
				$customer_id = $orderData['customer_id'];
				$customer_email = $orderData['billing']['email'];
				$customer_name = $orderData['billing']['first_name'].' '.$orderData['billing']['last_name'];
				$accountnm = $orderData['billing']['first_name'].' '.$orderData['billing']['last_name'];
			}

			$customerObject = new Customer();
			$customerResponse = $customerObject->createOrUpdateCustomer($customer_id,$customer_email);
			$customerId = $customerResponse['data'][0]['details']['id'];
			
			$additionalArray = [];
			
			$additionalArray['Billing_Street'] = $orderData['billing']['address_1'].' '.$orderData['billing']['address_2'];	
			$additionalArray['Billing_City'] = $orderData['billing']['city'];	
			$additionalArray['Billing_State'] = $orderData['billing']['state'];	
			$additionalArray['Billing_Code'] = $orderData['billing']['postcode'];	
			$additionalArray['Billing_Country'] = $orderData['billing']['country'];
			$additionalArray['Shipping_Street'] = $orderData['shipping']['address_1'].' '.$orderData['shipping']['address_2'];	
			$additionalArray['Shipping_City'] = $orderData['shipping']['city'];	
			$additionalArray['Shipping_State'] = $orderData['shipping']['state'];	
			$additionalArray['Shipping_Code'] = $orderData['shipping']['postcode'];	
			$additionalArray['Shipping_Country'] = $orderData['shipping']['country'];	
			$additionalArray['Status'] = $orderData['status'];	
			$additionalArray['Discount'] = $orderData['discount_total'];	
			$additionalArray['Tax'] = $orderData['total_tax'];	
			$additionalArray['Grand_Total'] = $orderData['total'];	
			$additionalArray['Description'] = $orderData['customer_note'];	

			$accountObject = new Account();

			$accountResponse = $accountObject->createOrUpdateAccount($customer_id,$accountnm);//customer_email

			$mergeArray = [];

			if($accountnm == $orderData['billing']['first_name'].' '.$orderData['billing']['last_name']){
				$mergeArray = array("Subject" => (string)$id,"Account_Name"=> array("name"=>$accountnm.'-'.$customer_email,"id"=> $accountResponse['data'][0]['details']['id']));	
			}else{
				$mergeArray = array("Subject" => (string)$id,"Account_Name"=> array("name"=>$accountnm, "id"=> $accountResponse['data'][0]['details']['id']));	
			}

			$additionalArray = array_merge($additionalArray,$mergeArray);

			$order1 = wc_get_order( $id );
			$items = $order1->get_items();
			
			$productObject = new Product();		

			foreach($items as $item){
				$product_id = $item->get_product_id();
				$sku = '';
				$id = '';
				$product_variation_id = $item->get_variation_id();//['variation_id'];

				if($product_variation_id == 0){
					$product = new WC_Product($item->get_product_id());								
					$product_name = $product->get_name();
					$sku = $product->get_sku();
					$qty = $item->get_quantity();
					$id = $product_id;
				}else{
					$product11 = new WC_Product_Variation($product_variation_id);				
					$sku = get_post_meta( $product_variation_id, '_sku', true );
					$product_name = $product11->get_name();
					$qty = $item->get_quantity();
					$id = $product_variation_id;
				}

				if(!isset($sku)){
					continue; //returns an error
				}					
				
				$productResponse = $productObject->createOrUpdateProduct($id,$sku);
				$zohoProductId = $productResponse['data'][0]['details']['id'];	

				$zohoProductData[] = ["product" => [
					"Product_Code" => $sku,
					"name" => $product_name,
					"id" => $zohoProductId
					],
					"quantity" => $qty,					
				];	
			}
			$additionalArray['Product_Details'] = $zohoProductData;
			
			$postData = json_encode(['data'=>[$additionalArray]]);
			return $postData;
		}	
		catch(\Exception $e){
			throw new Exception("Error PrepareArray for Sales Order ".$e->getMessage());
			
		}
	}
    /* end Order */
    /* Account */
	public function prepareArrayForAccount($id,$accountName){
		try{
			$this->zohoPostData = array(
				"Account_Name"=>$accountName
			);		
			$postData = json_encode(['data'=>[$this->zohoPostData]]);
			return $postData;
		}
		catch(\Exception $e){
			throw new Exception("Error PrepareArray for Account ".$e->getMessage());			
		}		
	}
	/* end Account */
}