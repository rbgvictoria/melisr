$(function() {
    contentSize();
    $(window).resize(function() {
        contentSize();
    });
});

function contentSize() {
        var w = window.innerHeight;

        if (w > $('#container').height()) {
            var b = $('#banner').height();
            var m = $('#menu').height();
            var f = $('#footer').height();
            var pt = Number($('#content').css('padding-top').substr(0, $('#content').css('padding-top').indexOf('px')));
            var pb = Number($('#content').css('padding-bottom').substr(0, $('#content').css('padding-bottom').indexOf('px')));
            var height = w-(b+m+f+pt+pb);
            $('#content').css('height', height + 'px');
        }
}