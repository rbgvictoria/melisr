var href = location.href;
var base_url = href.substr(0, href.indexOf('index.php'));
var site_url = base_url + 'index.php';

/*$(function() {
    $('select').change(function(event) {
        event.preventDefault();
        $(this).parents('form').submit();
    });
})*/

$(function() {
    $('input[name="discipline"]').change(function(e) {
       e.preventDefault();
       $('select#year').val(false);
       $('select#institution').val(false);
       $('select#filter').val(false);
       
       var href = site_url + '/loanreturn/loans/discipline/' + $(this).val();
       location.href = href;
       
    });
});