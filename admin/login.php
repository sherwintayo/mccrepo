<?php require_once('../config.php') ?>
<!DOCTYPE html>
<html lang="en" class="" style="height: auto;">
<?php require_once('inc/header.php') ?>
<body class="hold-transition ">
  <script>
    start_loader()
  </script>
  <style>
    html, body{
      height:calc(100%) !important;
      width:calc(100%) !important;
    }
    body {
        background: linear-gradient(135deg, #141e30 20%, #243b55 100%);
        background-size: cover;
        background-repeat: no-repeat;
        height: 100%;
        width: 100%;
    }
    
    .login-title{
      text-shadow: 3px 3px black;
      padding: 20px 0 0 0;
    }

    #logo-img{
        height:150px;
        width:150px;
        object-fit:scale-down;
        object-position:center center;
        border-radius:100%;
    }
    
    .myColor{
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

    /* Media Queries for responsiveness */
    @media (max-width: 575.98px) {
        .myContainer {
            margin: 20px;
        }

        .myLoginForm {
            border-radius: 20px 20px 0 0;
        }
    }

    @media (max-width: 767.98px) {  
        .myContainer {
            margin: 20px;
        }
    }

    @media (max-width: 991.98px) {
        .login-form {
            width: 70%;
        }
    }

    @media (max-width: 1199.98px) {
        .login-form {
            width: 60%;
        }
    }

    @media (min-width: 1200px) {
        .login-form {
            width: 50%;
        }
    }
  </style>


<div class="d-flex flex-column align-items-center w-100" id="login">
    <div class="body d-flex flex-column justify-content-center align-items-center">
      <div class="w-100">
        <h1 class="text-center py-5 my-5 login-title"><b><?php echo $_settings->info('name') ?> - Admin</b></h1>
      </div>
      <div class="row myContainer">
        <div class="myLoginForm col-lg-7 p-3 w-100 d-flex justify-content-center align-items-center text-navy">
          <div class="d-flex flex-column w-100 px-3">
            <h1 class="text-center font-weight-bold text-white">Sign in to Account</h1>
            <hr class="my-3" />
            <form id="login-frm" action="" method="post" novalidate>
              <div class="input-group form-group">
                <div class="input-group-prepend">
                  <span class="input-group-text rounded-0"><i class="fas fa-user fa-lg fa-fw"></i></span>
                </div>
                <input type="email" name="username" id="username" placeholder="Email" class="form-control form-control-border" required>
              </div>
              <div class="input-group form-group">
                <div class="input-group-prepend">
                  <span class="input-group-text rounded-0"><i class="fas fa-key fa-lg fa-fw"></i></span>
                </div>
                <input type="password" name="password" id="password" placeholder="Password" class="form-control form-control-border" required>
              </div>
              <div class="row">
                <div class="col-6">
                  <a class="text-light font-weight-bolder" href="<?php echo base_url ?>">Go Back</a>
                </div>
                <div class="col-6 text-right">
                  <button type="submit" class="btnLogin btn btn-primary btn-flat text-white ">Login</button>
                </div>
              </div>
              <div class="row mt-2">
                <div class="col-lg-12 text-center">
                  <a href="forgot_password.php" class="text-light">Forgot Password?</a>
                </div>
              </div>
            </form>
          </div>
        </div>
        <div class="col-lg-5 d-flex flex-column justify-content-center myColor p-4">
          <h1 class="text-center font-weight-bold text-white">Hello Friends!</h1>
          <hr class="my-3 bg-light" />
          <p class="text-center font-weight-bolder text-light lead">Enter your personal details and start your journey with us!</p>
          <button class="btn btn-outline-light btn-lg align-self-center font-weight-bolder mt-4" onclick="location.href = 'register.php'">Sign Up</button>
        </div>
      </div>
    </div>
  </div>

  <!-- jQuery -->
  <script src="plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- AdminLTE App -->
  <script src="dist/js/adminlte.min.js"></script>

  <!-- Validation Script -->
  <script>
    (function($) {
      'use strict';

      // Validate Email Format
      var validateEmail = function(email) {
        var emailReg = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        return emailReg.test(email);
      };

            // Password must be at least 8 characters, contain uppercase, lowercase, number, and special character
      var validatePassword = function(password) {
        var passwordReg = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
        return passwordReg.test(password);
      };


      // Check for invalid characters (single quotes)
      var hasInvalidChars = function(input) {
        return input.includes("'");
      };

      // Set custom validation message
      var setValidationMessage = function(input, message) {
        input.setCustomValidity(message);
        input.reportValidity();
      };

      $('#login-frm').on('submit', function(event) {
        var usernameInput = $('#username')[0];
        var passwordInput = $('#password')[0];
        var username = usernameInput.value;
        var password = passwordInput.value;

        // Reset custom validation messages
        usernameInput.setCustomValidity("");
        passwordInput.setCustomValidity("");

        // Validate email format
        if (!validateEmail(username)) {
          setValidationMessage(usernameInput, "Invalid email format: put a @ in '" + username + "'");
          event.preventDefault();
          return;
        }

        if (!validateEmail(password)) {
          setValidationMessage(passwordInput, "Password must be at least 8 characters long and contain an uppercase letter, lowercase letter, number, and special character.");
          event.preventDefault();
          return;
        }

        // Check for invalid characters in username and password
        if (hasInvalidChars(username)) {
          setValidationMessage(usernameInput, "Username must not contain single quotes: '" + username + "'");
          event.preventDefault();
          return;
        }

        if (hasInvalidChars(password)) {
          setValidationMessage(passwordInput, "Password must not contain single quotes.");
          event.preventDefault();
          return;
        }
      });

    })(jQuery);

    $(document).ready(function(){
      end_loader();
    });
  </script>

</body>
</html>
