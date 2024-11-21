<?php require_once('./config.php'); ?>
<!DOCTYPE html>
<html lang="en" class="" style="height: auto;">
<?php require_once('inc/header.php') ?>

<head>
    <title>MS Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="myStyles/registerstyle.css">
</head>
<style>
    /* html, body {
    height: calc(100%) !important;
    width: calc(100%) !important;
  } */
    body {
        background-image: url("dist/img/background.png");
        background-size: cover;
        background-repeat: no-repeat;
    }
</style>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<body class="hold-transition">
    <script>
        // Ensure start_loader() and end_loader() are defined properly
        function start_loader() {
            // Start loader logic
            console.log("Loader started...");
        }
        function end_loader() {
            // End loader logic
            console.log("Loader stopped...");
        }

        start_loader(); // Start the loader
    </script>


    <div class="container register" style="margin-top: 14vh;">
        <div class="row">
            <!-- Left Section -->
            <div class="col-md-3 register-left">
                <img src="<?= validate_image($_settings->info('logo')) ?>" alt="Logo" />
                <h3>Welcome!</h3>
                <p>Enter your MS 365 Email Account to receive a registration link.</p>
                <button class="myButton" onclick="location.href = '<?php echo base_url ?>'">Go Back</button>
            </div>

            <!-- Right Section -->
            <div class="col-md-9 register-right">
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="ms-login-tab" role="tabpanel">
                        <h3 class="register-heading">MS 365 Account Verification</h3>
                        <div class="row register-form">
                            <div class="col-md-12">
                                <form id="forgot-password-form" method="POST">
                                    <!-- Email Field -->
                                    <div class="form-group">
                                        <input type="email" name="email" id="email" placeholder="Enter your MS account"
                                            class="form-control form-control-border" required>
                                    </div>

                                    <!-- reCAPTCHA Widget -->
                                    <div class="form-group">
                                        <div class="g-recaptcha"
                                            data-sitekey="6LdkGoUqAAAAAEmIB2Py685bbQiALvcZ3a4MOjDx"></div>
                                    </div>

                                    <!-- Buttons -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <a href="<?php echo base_url ?>" class="btn btn-light">Go Back</a>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <button type="submit" class="btn btn-primary btn-flat">Send</button>
                                        </div>
                                    </div>

                                    <!-- Response Message -->
                                    <div id="response-message" class="alert" style="display: none;"></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="plugins/jquery/jquery.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#forgot-password-form').on('submit', function (e) {
                e.preventDefault();
                let email = $('#email').val();
                let domain = "@mcclawis.edu.ph";

                if (!email.endsWith(domain)) {
                    alert("Please enter a valid email address with the domain " + domain);
                    return false;
                }

                // Start the loader before sending the AJAX request
                start_loader();

                // Get the reCAPTCHA response token
                let recaptchaResponse = grecaptcha.getResponse();

                if (!recaptchaResponse) {
                    alert("Please complete the reCAPTCHA challenge.");
                    end_loader();
                    return false;
                }

                // AJAX request
                $.ajax({
                    url: 'ms_login_process.php',
                    method: 'POST',
                    data: { email: email, 'g-recaptcha-response': recaptchaResponse },
                    dataType: 'json',
                    beforeSend: function () {
                        $('#response-message').hide().removeClass('alert-success alert-danger');
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            $('#response-message').addClass('alert-success').text(response.message).show();
                        } else {
                            $('#response-message').addClass('alert-danger').text(response.message).show();
                        }
                        // Stop the loader when the request is successful
                        end_loader();
                    },
                    error: function () {
                        $('#response-message').addClass('alert-danger').text('An error occurred. Please try again.').show();
                        // Stop the loader on error
                        end_loader();
                    }
                });
            });
        });
    </script>
</body>

</html>