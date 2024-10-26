jQuery(document).ready(function($) {
    const form = $('#ai-content-generator-form');
    const submitButton = $('#generate-content');
    const spinner = $('.spinner');

    form.on('submit', function(e) {
        e.preventDefault();

        // Disable form and show spinner
        submitButton.prop('disabled', true);
        spinner.addClass('is-active');

        // Get the prompt value
        const prompt = $('#content-prompt').val();

        // Send AJAX request
        $.ajax({
            url: aiContentGenerator.ajaxUrl,
            type: 'POST',
            data: {
                action: 'generate_content',
                nonce: aiContentGenerator.nonce,
                prompt: prompt
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    alert('Content generated successfully! Redirecting to edit page...');
                    
                    // Redirect to edit page
                    window.location.href = response.data.edit_url;
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred while generating content. Please try again.');
            },
            complete: function() {
                // Re-enable form and hide spinner
                submitButton.prop('disabled', false);
                spinner.removeClass('is-active');
            }
        });
    });
});