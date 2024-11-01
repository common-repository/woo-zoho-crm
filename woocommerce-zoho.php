<?php
/*
Plugin Name:  Woocommerce Zoho CRM Integration
Plugin URI:   https://magesture.com/product/woocommrece-zoho-crm-bi-directional-integration/
Description:  The WooCommerce Zoho CRM Integration sync the products, the customer to contact and the orders to sales orders in Zoho CRM.
Version:      1.0.2
Author:       Magesture
Author URI:   https://magesture.com/
Text Domain:  woocommerce-zoho-crm
Domain Path:  /languages
*/

if( !defined( 'ABSPATH' ) ) exit;


/* check WooCommerce is activated */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( !class_exists( 'WooCommerce' ) ) {
	if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) ) { 
		add_action( 'admin_notices', 'wczc_error_notice' );			
	} 
} 

function wczc_error_notice() {
    ?>
    <div class="notice error my-acf-notice">
        <p><strong><?php echo esc_html( __( 'WooCommerce Zoho CRM Plugin requires WooCommerce to be activated','woocommerce-zoho-crm' ) ); ?></strong></p>
    </div>
    <?php
}
/* end check WooCommerce is activated */

require_once(plugin_dir_path( __FILE__ ) . '/includes/plugin-pages.php');
require_once(plugin_dir_path( __FILE__ ) . '/api/api.php');

register_activation_hook( __FILE__, 'wczc_install' );
register_activation_hook( __FILE__, 'wczc_install_data' );