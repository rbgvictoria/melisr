$(document).ready(function() {
	$('#loantable, #loanpreptable').DataTable({
            paging: false,
            searching: false,
            order: [[0, 'desc']]
        });
        
	$('.image-records').DataTable( {
		"sPaginationType": "full_numbers",
        "bAutoWidth": false
	} );
    
    
});