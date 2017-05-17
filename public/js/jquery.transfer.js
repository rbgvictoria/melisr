$(function() {
    $('#transfer').click(function(event) {
        if($(this).attr('checked')) {
            var loanid = $('input[name="loanid"]').attr('value');
            var url = 'http://203.55.15.78/dev/melisr/index.php/loanreturn/transferTo/';
            $('#ajax').load(url + loanid);
            //$('#ajax').html(url + loanid);
        }
        else {
            $('#ajax').html('');
        }
    });
})