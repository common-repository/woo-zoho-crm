<?php
/**
 * Uninstall
 */
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

	global $wpdb;

	$table_name = $wpdb->prefix . 'woo_zoho_crm';
	$report_table_name = $wpdb->prefix . 'woo_zoho_crm_report';
	$mapping_table_name = $wpdb->prefix . 'woo_zoho_crm_field_mapping';

	$wpdb->query("DROP TABLE IF EXISTS " . $table_name);
	$wpdb->query("DROP TABLE IF EXISTS " . $report_table_name);
	$wpdb->query("DROP TABLE IF EXISTS " . $mapping_table_name);
