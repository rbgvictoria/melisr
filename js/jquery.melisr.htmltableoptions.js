$(document).ready(function() {
	$('#loantable, #loanpreptable').dataTable( {
		"bPaginate": false,
		"bLengthChange": false,
		"bFilter": false,
		"bSort": true,
		"bInfo": false,
		"bAutoWidth": false,
		"sPaginationType": "full_numbers",
		//"sDom": '<"top"lip><"table"rt>',
		"bSortClasses": false  // different color for sort columns
	} );
});