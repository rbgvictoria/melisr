$(function() {
    $('#transfer').click(function(event) {
        if($(this).attr('checked')) {
            var loanid = $('input[name="loanid"]').attr('value');
            var url = 'http://melisr.rbg.vic.gov.au/loanreturn/transferTo/';
            $('#ajax').load(url + loanid);
            //$('#ajax').html(url + loanid);
        }
        else {
            $('#ajax').html('');
        }
    });
})