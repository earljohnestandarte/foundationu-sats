$(document).ready(function() {
    $('.reply-toggle').on('click', function() {
        var target = $(this).data('target');
        $('.reply-form').not('.' + target).hide();
        $('.' + target).toggle();
    });
});
