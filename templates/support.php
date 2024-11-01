<?php 
	if( !defined( 'ABSPATH' ) ) exit;
	if ( current_user_can( 'activate_plugins' ) && current_user_can( 'update_core' ) ) {
?>
		<div class="wrap">
			<?php echo "<h2>" . esc_html( __( 'Support', 'woocommerce-zoho-crm' )) . "</h2>"; ?>
			<div class="row">
				<div class="col-sm-8 text-support">
					<?php echo "<br/><h6>" . esc_html( __( 'Please contact us for support and customization','woocommerce-zoho-crm')). " <a href='mailto:support@magesture.com'>support@magesture.com</a></h6>"; ?>
				</div>		
			</div>	
		</div>	
<?php }