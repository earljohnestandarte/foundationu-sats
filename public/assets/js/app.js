$(document).ready(function() {
    $('#menuToggle, #sidebarOverlay').on('click', function() {
        $('#sidebar, #sidebarOverlay').toggleClass('active');
    });

    $('.notification-item').on('click', function(e) {
        e.preventDefault();
        var $this = $(this);
        var notificationId = $this.data('id');

        $.ajax({
            url: siteUrl('notification/markAsRead') + '/' + notificationId,
            method: 'POST',
            success: function(response) {
                if (response.success && response.redirectUrl) {
                    window.location.href = response.redirectUrl;
                } else {
                    location.reload();
                }
            },
            error: function() {
                location.reload();
            }
        });
    });
});
