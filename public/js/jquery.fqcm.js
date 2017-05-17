$(function() {

    $('.selectall').on('click', function(event) {
        event.preventDefault();
        if ($(this).parent('div').next('table').find('input').prop('checked')) {
            $(this).parent('div').next('table').find('input').prop('checked', false);
        }
        else {
            $(this).parent('div').next('table').find('input').prop('checked', true);
        }
    });
    
    $('.catnostring').hide();
    $('[name=catnostring]').on('click', function(event) {
        event.preventDefault();
        if ($('.catnostring').is(':visible')) {
            $('.catnostring').hide();
        }
        else {
            var checked = [];
            $('table input:checked').each(function() {
                var catNo = $(this).parent('td').next('td').html();
                checked.push(catNo);
            });
            console.log(checked);
            $('.catnostring').show().find('textarea').eq(0).html(checked.join(','));
        }
    });

});