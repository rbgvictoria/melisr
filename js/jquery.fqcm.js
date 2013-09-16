$(function() {

$('.selectall').toggle(function(event) {
    event.preventDefault();
    $(this).parent('div').next('table').find('input').attr('checked', 'checked');
}, function(event) {
    event.preventDefault();
    $(this).parent('div').next('table').find('input').removeAttr('checked');
});

})