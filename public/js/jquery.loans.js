$(function() {
    $('input[name="discipline"]').change(function(e) {
       e.preventDefault();
       $('select#year').val(false);
       $('select#institution').val(false);
       $('select#filter').val(false);
       
       var href = 'http://melisr.rbg.vic.gov.au/loanreturn/loans/discipline/' + $(this).val();
       location.href = href;
       
    });
});