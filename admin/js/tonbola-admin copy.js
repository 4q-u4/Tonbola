(function($) {
    'use strict';

    $(document).ready(function() {
        // Form submission handler
        $('#tonbola-form').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            
            $.ajax({
                url: tonbola_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'tonbola_submit_form',
                    nonce: tonbola_ajax.nonce,
                    cell_number: $('#cell_number').val(),
                    person_name: $('#person_name').val(),
                    dropdown: $('#dropdown').val()
                },
                beforeSend: function() {
                    $('#message').removeClass('success error').addClass('hidden');
                },
                success: function(response) {
                    if (response.success) {
                        $('#message').removeClass('hidden error').addClass('success').text(response.data.message);
                        // Clear the form
                        $('#cell_number').val('');
                        $('#person_name').val('');
                        $('#dropdown').val('option1');
                    } else {
                        $('#message').removeClass('hidden success').addClass('error').text(response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    $('#message').removeClass('hidden success').addClass('error').text('Ajax error occurred: ' + error);
                }
            });
        });

        // New button handler
        $('#new-button').click(function() {
            $('#cell_number').val('');
            $('#person_name').val('');
            $('#dropdown').val('option1');
            $('#message').addClass('hidden');
        });

        // Clear button handler
        $('#clear-button').click(function() {
            if (confirm('Are you sure you want to clear all data?')) {
                $.ajax({
                    url: tonbola_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'tonbola_clear_data',
                        nonce: tonbola_ajax.nonce
                    },
                    beforeSend: function() {
                        $('#message').removeClass('success error').addClass('hidden');
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#message').removeClass('hidden error').addClass('success').text(response.data.message);
                        } else {
                            $('#message').removeClass('hidden success').addClass('error').text('Error occurred: ' + response.data.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#message').removeClass('hidden success').addClass('error').text('Ajax error occurred: ' + error);
                    }
                });
            }
        });
    });
   
    
        $(document).ready(function() {
            function refreshTable() {
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
            }
    
            // Refresh button click handler
            $('#tonbola-refresh-button').on('click', refreshTable);
    
            // Optional: Refresh the table every 30 seconds
            setInterval(refreshTable, 30000);
        });
   
})(jQuery);