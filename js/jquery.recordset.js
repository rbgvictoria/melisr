
$(function() {
    $('input[name="check_names"]').click(function(event) {
       event.preventDefault();
       $(this).parents('form').attr('action', location.href + '/check_names').submit();
    });
})