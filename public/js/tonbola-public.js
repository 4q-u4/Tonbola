jQuery(document).ready(function($) {
    // Refresh button click handler
    $('#tonbola-refresh-button').on('click', function() {
        $.ajax({
            url: tonbola_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'tonbola_refresh_table',
                nonce: tonbola_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#tonbola-table-container').html(response.data.table_html);
                } else {
                    console.error('Error refreshing table: ' + response.data.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Ajax error occurred: ' + error);
            }
        });
    });
});