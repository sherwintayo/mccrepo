<?php require_once('./config.php'); ?>
<!DOCTYPE html>
<html lang="en">
<?php require_once('inc/header.php') ?>
<body>
<div class="container">
    <h2>Forgot Password</h2>
    <form id="ms-login-form" method="post">
        <div class="form-group">
            <label for="email">Enter your MS account:</label>
            <input type="email" name="email" id="email" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Send</button>
    </form>

    <!-- Placeholder for displaying response messages -->
    <div id="response-message" class="mt-3"></div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#ms-login-form').submit(function(e) {
        e.preventDefault();  // Prevent default form submission
        const email = $('#email').val();
        const domain = "@mcclawis.edu.ph";
        const responseMessage = $('#response-message');

        // Front-end validation
        if (!email.endsWith(domain)) {
            responseMessage.html('<div class="alert alert-danger">Please enter a valid email address with the domain ' + domain + '.</div>');
            return;
        }

        // Clear previous response
        responseMessage.html('');

        // Send form data via AJAX
        $.ajax({
            url: 'ms_login_process.php',
            method: 'POST',
            data: { email: email },
            dataType: 'json',
            beforeSend: function() {
                // Optionally, add a loading indicator
                responseMessage.html('<div class="alert alert-info">Processing...</div>');
            },
            success: function(response) {
                if (response.status === 'success') {
                    responseMessage.html('<div class="alert alert-success">' + response.message + '</div>');
                } else {
                    responseMessage.html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function() {
                responseMessage.html('<div class="alert alert-danger">An error occurred while processing your request. Please try again.</div>');
            }
        });
    });
});
</script>
</body>
</html>
