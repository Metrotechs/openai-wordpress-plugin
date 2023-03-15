jQuery(document).ready(function ($) {
    $('#openai-api-connector-form').on('submit', function (e) {
        e.preventDefault();

        var userInput = $('#user-input').val();

        $.ajax({
            url: openaiApiConnector.ajax_url,
            type: 'POST',
            data: {
                action: 'openai_api_connector_process_request',
                input: userInput,
                security: openaiApiConnector.security
            },
            beforeSend: function () {
                $('#response-container').html('Loading...');
            },
            success: function (response) {
                $('#response-container').html(response);
            },
            error: function () {
                $('#response-container').html('Error occurred.');
            }
        });
    });
});