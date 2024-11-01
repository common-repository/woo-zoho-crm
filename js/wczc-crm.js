jQuery(document).ready(function() {
	jQuery('#table-fieldmapping').DataTable();
	jQuery('#table-contacts').DataTable();
	jQuery('#table-orders').DataTable();
	jQuery('#table-products').DataTable();
	jQuery('#report').DataTable( {
		"order": [[ 0, "desc" ]]
	});
});




/* filedmapping section load */
jQuery(function() {
	jQuery('.fields').hide();
	jQuery(".fields select").prop('disabled', true);
	jQuery('#zoho_module').change(function(){
		jQuery('.fields').hide();
		jQuery(".fields select").prop('disabled', true);							
		jQuery('#' + jQuery(this).val()).show();
		jQuery('#' + jQuery(this).val()).children('select').prop('disabled', false);
	});
});
/* filedmapping section load */