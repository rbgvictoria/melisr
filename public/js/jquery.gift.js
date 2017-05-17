$(function() {
    $('input[name="save"]').attr('disabled', 'disabled');
    $('input, textarea, select').change(function() {
        $('input[name="save"]').removeAttr('disabled');
    });
})