$(function() {
    $('input[name="record_set"]').click(function(event) {
       event.preventDefault();
       $(this).parents('form').attr('action', 'http://203.55.15.78/dev/melisr/index.php/recordset').submit();
    });
})