$(document).ready(function() {

    // --- FORM SUBMIT HANDLER ---
    $('#register-form').on('submit', function(e) {
        e.preventDefault();
        
        // Serialize all form data (first_name, last_name, address, email, etc.)
        var formData = $(this).serialize();

        // Find the message box (your form has two, we'll use the one at the top)
        var messageDiv = $('#register-message').first();
        var messageIcon = $('#register-message-icon').first();
        var messageText = $('#register-message-text').first();
        var button = $('#submit-button');

        messageDiv.addClass('hidden'); // Hide on new submit
        button.prop('disabled', true).text('Creating Account...');

        $.ajax({
            url: 'api/auth/register_process.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // --- Show Success Message ---
                    messageText.text(response.message);
                    messageIcon.text('check_circle');
                    
                    // Change classes for success (green)
                    messageDiv.removeClass('hidden border-amber-500/50 bg-amber-500/10 text-amber-300')
                              .addClass('border-green-500/50 bg-green-500/10 text-green-300');
                    
                    // Registration was successful, API logged us in.
                    // Redirect to the index page bouncer.
                    setTimeout(function() {
                        window.location.href = 'index.php'; 
                    }, 1500); // Wait 1.5 sec
                    
                } else {
                    // --- Show Error Message ---
                    messageText.text(response.message);
                    messageIcon.text('warning');
                    
                    // Set classes for error (amber)
                    messageDiv.removeClass('hidden border-green-500/50 bg-green-500/10 text-green-300')
                              .addClass('border-amber-500/50 bg-amber-500/10 text-amber-300');
                    
                    button.prop('disabled', false).text('Create Account');
                }
            },
            error: function() {
                // --- Show System Error ---
                messageText.text('A system error occurred. Please try again.');
                messageIcon.text('warning');
                messageDiv.removeClass('hidden border-green-500/50 bg-green-500/10 text-green-300')
                          .addClass('border-amber-500/50 bg-amber-500/10 text-amber-300');
                button.prop('disabled', false).text('Create Account');
            }
        });
    });

});