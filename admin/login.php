<?php require_once('../config.php') ?>
<!DOCTYPE html>
<html lang="en" class="" style="height: auto;">
<?php require_once('inc/header.php') ?>

<head>
  <title>MS Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../myStyles/loginstyle.css">
</head>
<style>
  /* html,
  body {
    height: calc(100%) !important;
    width: calc(100%) !important;
  } */

  body {
    background-image: url("../dist/img/background.png");
    background-size: cover;
    background-repeat: no-repeat;
  }
</style>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<body class="hold-transition ">
  <script>
    start_loader()
  </script>

  <?php if ($_settings->chk_flashdata('success')): ?>
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: "<?php echo $_settings->flashdata('success') ?>",
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
    </script>
  <?php endif; ?>


  <div class="container register" style="margin-top: 7vh;">
    <div class="row">
      <!-- Left Section -->
      <div class="col-md-3 register-left">
        <img src="<?= validate_image($_settings->info('logo')) ?>" alt="Logo" />
        <h3>Welcome Admin</h3>
        <p>Enter your credentials to access the admin panel.</p>
        <button class="myButton" onclick="location.href = '../'">Go to Site</button>
      </div>

      <!-- Right Section -->
      <div class="col-md-9 register-right">
        <div class="tab-content" id="myTabContent">
          <div class="tab-pane fade show active" id="admin-login-tab" role="tabpanel">
            <h3 class="register-heading">Admin Login</h3>
            <div class="row register-form">
              <div class="col-md-12">
                <form id="login-frm" action="" method="POST" novalidate>
                  <!-- Email Field -->
                  <div class="form-group">
                    <input type="email" name="username" id="username" placeholder="Email *"
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
                      <a href="../login" class="btn btn-light">Student Signin</a>
                    </div>
                    <div class="col-md-6 text-right">
                      <button type="submit" class="btn btn-primary btn-flat">Login</button>
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
  <script src="../plugins/jquery/jquery.min.js"></script>
  <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../dist/js/adminlte.min.js"></script>

  <!-- Validation Script -->
  <script>
    (function ($) {
      'use strict';

      // Check for invalid characters (single and double quotes, angle brackets)
      var hasInvalidChars = function (input) {
        return /['"<>&]/.test(input);
      };

      // Validate Email Format (Ensure @ symbol is present)
      var validateEmail = function (email) {
        var emailReg = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        return emailReg.test(email);
      };

      // Set custom validation message
      var setValidationMessage = function (input, message) {
        input.setCustomValidity(message);
        input.reportValidity();
      };

      $('#login-frm').on('submit', function (event) {
        var _this = $(this);
        var hasError = false;

        // XSS and Validation Checks for invalid characters and email
        _this.find('input[type="text"], input[type="email"], input[type="password"]').each(function () {
          var input = $(this);
          var value = input.val();

          // Check for invalid characters (' and ") and for angle brackets (< and >)
          if (hasInvalidChars(value)) {
            setValidationMessage(this, "Input must not contain single quotes, double quotes, or angle brackets.");
            hasError = true;
            return false; // Exit loop
          } else {
            setValidationMessage(this, ""); // Clear custom validity if no error
          }

          // Validate email input
          if (input.attr('type') === 'email' && !validateEmail(value)) {
            setValidationMessage(this, "Please include an '@' in the email address.");
            hasError = true;
            return false; // Exit loop
          }
        });

        if (hasError) {
          event.preventDefault(); // Prevent form submission if any input has an error
          return false;
        }
      });
    })(jQuery);

    $(document).ready(function () {
      end_loader();
    });
  </script>

</body>

</html>