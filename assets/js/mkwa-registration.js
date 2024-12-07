jQuery(document).ready(function($) {
    $('#mkwaregistration').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: mkwaAjax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#mkwa-registration-response').html(`<p>${response.data}</p>`);
                if (response.success) {
                    window.location.href = mkwaAjax.redirect_url;
                }
            },
            error: function() {
                $('#mkwa-registration-response').html('<p>An error occurred. Please try again.</p>');
            }
        });
    });
});
