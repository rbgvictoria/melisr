$(document).ready(function() {
	$('#loantable, #loanpreptable').DataTable({
            paging: true,
            pageType: 'first_last_numbers',
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1],[10, 25, 50, 100, "All"]],
            searching: false,
            order: [[0, 'desc']]
        });
        
        $('#loantable td, #loanpreptable td').css('background-color', '#f1ebd0');
        
	$('.image-records').DataTable( {
	    "sPaginationType": "full_numbers",
            "bAutoWidth": false
	} );
    
    
});