$(document).ready(function() {
    $('#menuToggle, #sidebarOverlay').on('click', function() {
        $('#sidebar, #sidebarOverlay').toggleClass('active');
    });

    $('.notification-item').on('click', function(e) {
        e.preventDefault();
        var notificationId = $(this).data('id');

        $.ajax({
            url: siteUrl('notification/markAsRead') + '/' + notificationId,
            method: 'POST',
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });
});
