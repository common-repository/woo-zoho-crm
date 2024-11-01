<?php 
if( !defined( 'ABSPATH' ) ) exit;

include_once(plugin_dir_path(__DIR__). "/includes/install.php");

if( !class_exists( 'wczc_pages_zoho' ) ){
	class wczc_pages_zoho{		
		/* add menu in admin side */		
		public function __construct(){
			add_action( 'admin_menu', array( $this, 'wczc_add_pages' )); 
		}
		function wczc_add_pages(){
			// Add a new main menu:
			if (function_exists('check_admin_referer')){
				add_menu_page(__('Zoho CRM','menu-zoho'), __('Zoho CRM','menu-zoho'), 'manage_options', 'wczc-menu', 'wczc_main_page', plugin_dir_url( __DIR__ ).'/images/zoho.png', 56 );

				// Add a second submenu to the custom top-level menu:
				add_submenu_page('wczc-menu', __('Zoho Report','menu-zoho'), __('Zoho Report','menu-zoho'), 'manage_options', 'zoho-report', 'zoho_report');
				add_submenu_page('wczc-menu', __('Customers Manual Sync','menu-zoho'), __('Customers Manual Sync','menu-zoho'), 'manage_options', 'zoho-queue', 'zoho_queue');
				add_submenu_page('wczc-menu', __('Products Manual Sync','menu-zoho'), __('Products Manual Sync','menu-zoho'), 'manage_options', 'zoho-product-queue', 'zoho_product_queue');
				add_submenu_page('wczc-menu', __('Orders Manual Sync','menu-zoho'), __('Orders Manual Sync','menu-zoho'), 'manage_options', 'zoho-order-queue', 'zoho_order_queue');			
				add_submenu_page('wczc-menu', __('Support','menu-zoho'), __('Support','menu-zoho'), 'manage_options', 'support', 'support');
				add_submenu_page('wczc-menu', __('Upgrade Pro','menu-zoho'), __('Upgrade Pro','menu-zoho'), 'manage_options', 'upgrade-pro', 'upgrade_pro');
				function wczc_main_page(){
					require_once(plugin_dir_path(__DIR__) . '/templates/settings.php');
				}
				// of the custom Test Toplevel menu
				function zoho_report() {
					require_once(plugin_dir_path(__DIR__) . '/templates/report.php');
				}	
				function zoho_queue() {
					require_once(plugin_dir_path(__DIR__) . '/templates/zoho-queue.php');
				}
				function zoho_product_queue() {
					require_once(plugin_dir_path(__DIR__) . '/templates/zoho-product-queue.php');
				}
				function zoho_order_queue() {
					require_once(plugin_dir_path(__DIR__) . '/templates/zoho-order-queue.php');
				}
				function support() {
					require_once(plugin_dir_path(__DIR__) . '/templates/support.php');
				}
				function upgrade_pro() {
					require_once(plugin_dir_path(__DIR__) . '/templates/upgrade-pro.php');
				}				
			}		
		}
		/* end add menu in admin side */
	}	
	new wczc_pages_zoho();
}	

require_once(plugin_dir_path(__DIR__) . '/api/abstract.php');
require_once(plugin_dir_path(__DIR__) . '/api/customer.php');
require_once(plugin_dir_path(__DIR__) . '/api/product.php');
require_once(plugin_dir_path(__DIR__) . '/api/salesorder.php');
require_once(plugin_dir_path(__DIR__) . '/api/account.php');
require_once(plugin_dir_path(__DIR__) . '/api/events.php');			

/* make sku required field */
add_action( 'admin_head', 'wczc_require_sku_field' );
function wczc_require_sku_field() {
	$screen         = get_current_screen();
	$screen_id      = $screen ? $screen->id : ''; 
	if ( $screen_id == 'product' ) {
?>
		<script>
			jQuery(document).ready(function(jQuery){
				jQuery('#title').prop('required',true);  // Set title field as required.
				jQuery( '#publish' ).on( 'click', function() {
					postname = jQuery.trim(jQuery('#title').val());
					if ( postname == '' || postname == 0  ) {
						alert( 'Product Name must be set in Product Name.' );
						jQuery( '#title' ).focus();  // Focus on title field.
						return false ;
					}
				});
				
				jQuery('#_sku').prop('required',true);  // Set sku field as required.
				jQuery( '#publish' ).on( 'click', function() {
					sku = jQuery.trim(jQuery('#_sku').val());
					if ( sku == '' || sku == 0  ) {
						alert( 'Sku must be set in the Inventory tab.' );						
						jQuery( '.inventory_tab > a' ).click();  // Click on 'Shipping' tab.
						jQuery( '#_sku' ).focus();  // Focus on sku field.
						return false;
					}
				});
			});
		</script>
<?php
	}
}
/* end make sku required field */

/* css and js file */
function wczc_scripts() {	
	if (function_exists('check_admin_referer')){
		$pages_list = array('wczc-menu', 'zoho-report', 'zoho-order', 'customer', 'zoho-queue', 'support', 'upgrade-pro');
		if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], $pages_list)) {
			wp_enqueue_style('bootstrap', plugins_url( '/css/bootstrap.css',  dirname(__FILE__) ));
			wp_enqueue_style('bootstrap4', plugins_url( '/css/dataTables.bootstrap4.min.css',  dirname(__FILE__) ));		
			wp_enqueue_style('sweetalert-app', plugins_url( '/css/sweetalert-app.css',  dirname(__FILE__) ));
			wp_enqueue_style('font-awesome-min', plugins_url( '/css/font-awesome.min.css',  dirname(__FILE__) ));
			wp_enqueue_style('custom', plugins_url( '/css/custom.css',  dirname(__FILE__) ));

			wp_enqueue_script( 'datatables-script', plugins_url( '/js/datatables.min.js', dirname(__FILE__) ) );
			wp_enqueue_script( 'sweetalert-min-script', plugins_url( '/js/sweetalert.min.js', dirname(__FILE__) ) );
			wp_enqueue_script( 'wczc-crm-script', plugins_url( '/js/wczc-crm.js', dirname(__FILE__) ) );
		}
	}
};	
add_action( 'admin_enqueue_scripts', 'wczc_scripts');
/* css and js file */