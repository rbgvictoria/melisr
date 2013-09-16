$(function() {
    $('input[name="check_names"]').click(function(event) {
       event.preventDefault();
       $(this).parents('form').attr('action', 'http://203.55.15.78/dev/melisr/index.php/recordset/check_names').submit();
    });
})