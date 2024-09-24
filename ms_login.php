<?php require_once('./config.php'); ?>
<!DOCTYPE html>
<html lang="en" class="" style="height: auto;">
 <?php require_once('inc/header.php') ?>
<body class="hold-transition">
  <script>
    // Ensure start_loader() and end_loader() are defined properly
    function start_loader(){
        // Start loader logic
        console.log("Loader started...");
    }
    function end_loader(){
        // End loader logic
        console.log("Loader stopped...");
    }

    start_loader(); // Start the loader
  </script>
  <style>
    html, body {
      height:calc(100%) !important;
      width:calc(100%) !important;
    }
    body {
        background: linear-gradient(135deg, #141e30 20%, #243b55 100%);
        height: 100%;
        width: 100%;
    }
    .myColor {
        background-image: linear-gradient(to right, #f83600 50%, #f9d423 150%);
    }
    .myLoginForm {
        background: transparent;
        border: 2px solid #f83600;
        backdrop-filter: blur(2px);
        border-radius: 20px 0 0 20px;
    }
    .btnLogin {
        border-radius: 0 20px 20px 0;
        border: 0;
        background-image: linear-gradient(to right, #f83600 50%, #f9d423 150%);
    }
    /* Responsive styles omitted for brevity */
  </style>
  
<div class="d-flex flex-column align-items-center w-100" id="login">
    <div class="body d-flex flex-column justify-content-center align-items-center">
        <div class="w-100">
            <h1 class="text-center py-5 my-5 login-title"><b><?php echo $_settings->info('name') ?></b></h1>
        </div>
        <div class="row myContainer">
            <div class="myLoginForm col-lg-7 p-3 d-flex justify-content-center align-items-center text-navy">
                <div class="d-flex flex-column w-100 px-3">
                    <h1 class="text-center font-weight-bold text-white">MS Login</h1>
                    <hr class="my-3" />
                    <form id="forgot-password-form">
                    <div class="row">
                    <div class="col-lg-12 mb-1">
                        <div class="input-group form-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text rounded-0"><i class="far fa-envelope fa-lg fa-fw"></i></span>
                            </div>
                            <input type="email" name="email" id="email" placeholder="Enter your MS account" class="form-control form-control-border" required>
                        </div>
                        </div>
                        </div>

                        <div class="row">
                        <div class="col-6 mt-1">
                            <a class="text-light font-weight-bolder" href="<?php echo base_url ?>">Go Back</a>
                            </div>

                        <!-- Send Button -->
                        <div class="col-6 text-right">
                            <button type="submit" class="btnLogin btn btn-primary btn-flat text-white">Send</button>
                        </div>
                        </div>

                        <div id="response-message" class="alert" style="display:none;"></div>
                    </form>
                </div>
            </div>
            <div class="col-lg-5 d-flex flex-column justify-content-center myColor p-5">
                <h1 class="text-center font-weight-bold text-white">HI JOE!</h1>
                <hr class="my-3 bg-light myHr" />
                <p class="text-center font-weight-bolder text-light lead">Enter Your MS Account, we will send you a reset link!</p>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<script>
$(document).ready(function(){
    $('#forgot-password-form').on('submit', function(e){
        e.preventDefault();
        let email = $('#email').val();
        let domain = "@mcclawis.edu.ph";

        if (!email.endsWith(domain)) {
            alert("Please enter a valid email address with the domain " + domain);
            return false;
        }

        // Start the loader before sending the AJAX request
        start_loader();

        // AJAX request
        $.ajax({
            url: 'ms_login_process.php',
            method: 'POST',
            data: { email: email },
            dataType: 'json',
            beforeSend: function() {
                $('#response-message').hide().removeClass('alert-success alert-danger');
            },
            success: function(response) {
                if (response.status === 'success') {
                    $('#response-message').addClass('alert-success').text(response.message).show();
                } else {
                    $('#response-message').addClass('alert-danger').text(response.message).show();
                }
                // Stop the loader when the request is successful
                end_loader();
            },
            error: function() {
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
