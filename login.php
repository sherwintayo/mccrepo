<?php require_once('./config.php') ?>
<!-- <?php session_start(); // Start session handling ?> -->
<!DOCTYPE html>
<html lang="en" class="" style="height: auto;">
<?php require_once('inc/header.php') ?>

<head>
  <title>MS Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="myStyles/registerstyle.css">
</head>
<style>
  html,
  /* body {
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
  <!-- <script>
    start_loader()
  </script> -->

  <?php if ($_settings->chk_flashdata('success')): ?>
    <script>
      alert_toast("<?php echo $_settings->flashdata('success') ?>", 'success')
    </script>
  <?php endif; ?>
  <div class="container register" style="margin-top: 14vh;" id="login">
    <div class="row">
      <!-- Left Section -->
      <div class="col-md-3 register-left">
        <img src="<?= validate_image($_settings->info('logo')) ?>" alt="Logo" />
        <h3>Welcome Back</h3>
        <p>Enter your credentials to access your account.</p>
        <button class="myButton" onclick="location.href = 'ms_login.php'">Sign Up</button>
      </div>

      <!-- Right Section -->
      <div class="col-md-9 register-right">
        <div class="tab-content" id="myTabContent">
          <div class="tab-pane fade show active" id="login-tab" role="tabpanel">
            <h3 class="register-heading">Login to Your Account</h3>
            <div class="row register-form">
              <div class="col-md-12">
                <form action="" id="slogin-form">
                  <!-- Email Field -->
                  <div class="form-group">
                    <input type="email" name="email" id="email" placeholder="Email *"
                      class="form-control form-control-border" required>
                  </div>

                  <!-- Password Field -->
                  <div class="form-group">
                    <input type="password" name="password" id="password" placeholder="Password *"
                      class="form-control form-control-border" required>
                  </div>

                  <div class="form-group">
                    <div class="g-recaptcha" data-sitekey="6LdkGoUqAAAAAEmIB2Py685bbQiALvcZ3a4MOjDx"></div>
                  </div>

                  <!-- Buttons -->
                  <div class="row">
                    <div class="col-md-6">
                      <a href="<?php echo base_url ?>" class="btn btn-light">Go Back</a>
                    </div>
                    <div class="col-md-6 text-right">
                      <button class="btn btn-primary btn-flat">Login</button>
                    </div>
                  </div>

                  <!-- Forgot Password Link -->
                  <div class="row mt-2">
                    <div class="col-lg-12 text-center">
                      <a href="forgot_password.php" class="text-primary">Forgot Password?</a>
                    </div>
                  </div>
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
  <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="dist/js/adminlte.min.js"></script>
  <script src="<?php echo base_url ?>plugins/select2/js/select2.full.min.js"></script>



  <!-- Scripts -->
  <script>
    $(document).ready(function () {
      end_loader();

      // Validation functions from the admin login
      var validateEmail = function (email) {
        var emailReg = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        return emailReg.test(email);
      };

      // var validatePassword = function(password) {
      //   var passwordReg = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
      //   return passwordReg.test(password);
      // };

      var hasInvalidChars = function (input) {
        return input.includes("'");
      };

      var setValidationMessage = function (input, message) {
        input.setCustomValidity(message);
        input.reportValidity();
      };

      $('#slogin-form').submit(function (e) {
        e.preventDefault();
        var _this = $(this);
        var el = $("<div>");
        el.addClass("alert pop-msg my-2").hide();

        // Fetching input values for validation
        var emailInput = $('#email')[0];
        var passwordInput = $('#password')[0];
        var email = emailInput.value;
        var password = passwordInput.value;

        // Reset custom validation messages
        emailInput.setCustomValidity("");
        passwordInput.setCustomValidity("");

        // Validate email format
        if (!validateEmail(email)) {
          setValidationMessage(emailInput, "Invalid email format: put an @ in '" + email + "'");
          return; // Stop submission if validation fails
        }

        // Validate password format
        // if (!validatePassword(password)) {
        //   setValidationMessage(passwordInput, "Password must be at least 8 characters long and contain an uppercase letter, lowercase letter, number, and special character.");
        //   return; // Stop submission if validation fails
        // }

        // Check for invalid characters in email and password
        if (hasInvalidChars(email)) {
          setValidationMessage(emailInput, "Email must not contain single quotes: '" + email + "'");
          return; // Stop submission if validation fails
        }

        if (hasInvalidChars(password)) {
          setValidationMessage(passwordInput, "Password must not contain single quotes.");
          return; // Stop submission if validation fails
        }

        // Existing AJAX request logic
        start_loader();

        $.ajax({
          url: _base_url_ + "classes/Login.php?f=student_login",
          method: 'POST',
          data: _this.serialize(),
          dataType: 'json',
          error: err => {
            console.log(err);
            alert("An error occurred.");
            end_loader();
          },
          success: function (resp) {
            end_loader();
            if (resp.status == 'success') {
              <?php $_SESSION['user_logged_in'] = true; ?>
              window.location.href = "./";
            } else {
              alert("Login failed: " + (resp.msg || "Invalid credentials."));
            }
          }
        });
      });
    });
  </script>

</body>

</html>