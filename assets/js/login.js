$(document).ready(function() {

    // --- FORM SUBMIT HANDLER ---
    $('#login-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        // Get the new message elements
        var messageDiv = $('#login-message');
        var messageIcon = $('#login-message-icon');
        var messageText = $('#login-message-text');
        
        // Get the submit button
        var button = $('#submit-button');

        messageDiv.addClass('hidden'); // Hide on new submit
        button.prop('disabled', true).text('Logging In...'); // Change button text

        $.ajax({
            url: 'api/auth/login_process.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // --- Show Success Message ---
                    messageText.text("Login successful! Redirecting...");
                    messageIcon.text('check_circle');
                    
                    // Change classes for success (green)
                    messageDiv.removeClass('hidden border-amber-500/50 bg-amber-500/10 text-amber-300')
                              .addClass('border-green-500/50 bg-green-500/10 text-green-300');
                    
                    // --- NEW REDIRECT LOGIC ---
                    // This is the fix. We will redirect to the root page.
                    // The "Smart Bouncer" at the top of login.php will catch us
                    // and send us to the *correct* dashboard.
                    // This forces the browser to re-read the new cookie.
                    setTimeout(function() {
                        window.location.href = 'index.php'; // Redirect to a neutral page
                    }, 1000); // Wait 1 sec
                    
                } else {
                    // --- Show Error Message ---
                    messageText.text(response.message);
                    messageIcon.text('warning');
                    
                    // Set classes for error (amber/red)
                    messageDiv.removeClass('hidden border-green-500/50 bg-green-500/10 text-green-300')
                              .addClass('border-amber-500/50 bg-amber-500/10 text-amber-300');
                    
                    button.prop('disabled', false).text('Log In');
                }
            },
            error: function() {
                // --- Show System Error ---
                messageText.text('A system error occurred. Please try again.');
                messageIcon.text('warning');
                messageDiv.removeClass('hidden border-green-500/50 bg-green-500/10 text-green-300')
                          .addClass('border-amber-500/50 bg-amber-500/10 text-amber-300');
                button.prop('disabled', false).text('Log In');
            }
        });
    });

    // --- SHOW/HIDE PASSWORD HANDLER ---
    $('#toggle-password').on('click', function() {
        var passwordInput = $('#password');
        var passwordIcon = $('#toggle-password-icon');
        
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            passwordIcon.text('visibility'); // Set icon to "open eye"
        } else {
            passwordInput.attr('type', 'password');
            passwordIcon.text('visibility_off'); // Set icon to "closed eye"
        }
    });

});