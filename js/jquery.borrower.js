$(function() {
    $('select#melrefno').change(function(event) {
        event.preventDefault();
        $(this).parents('form').submit();
    });
    
    $('input[name^="toreturn"]').click(function(e) {
        
        if ($(this).attr('checked')) {
            $(this).parent('td').children('select').val(1);
        }
        else {
            $(this).parent('td').children('select').val(0);
        }
    });
    
    $('select[name^="quantity"]').change(function(e) {
        if ($(this).children('option').filter(':selected').attr('value') == 0) {
            $(this).parent('td').children('input').removeAttr('checked');
        }
        else {
            $(this).parent('td').children('input').attr('checked', 'checked');
        }
    });
    
    var height = $('#workarea_col2').height();
    if ($('#numbers').height() < height) {
        height = height-32;
        $('#numbers').css('height', height + 'px');
    }
})