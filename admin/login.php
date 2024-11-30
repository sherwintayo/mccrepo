<?php require_once('../config.php') ?>
<?php
if (isset($_GET['token'])) {
  $token = $_GET['token'];

  $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token_hash = ? AND reset_token_expires_at >= NOW()");
  $stmt->bind_param("s", $token);
  $stmt->execute();
  $qry = $stmt->get_result();

  if ($qry->num_rows > 0) {
    $user = $qry->fetch_assoc();

    // Log the user in by setting session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];

    // Clear the token
    $clearStmt = $conn->prepare("UPDATE users SET reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = ?");
    $clearStmt->bind_param("i", $user['id']);
    $clearStmt->execute();

    header("Location: ../admin/");
    exit;
  } else {
    echo "Invalid or expired token.";
  }
}

?>
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
                    <div class="g-recaptcha" data-sitekey="6LfFJYcqAAAAAK6Djr0QOH68F4r_Aehziia0XYa9"></div>
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
                      <a href="forgot_password" class="text-primary">Forgot Password?</a>
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
        event.preventDefault(); // Prevent default form submission

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
          return false; // Exit if validation fails
        }

        // If validation passes, submit via AJAX
        var formData = _this.serialize(); // Serialize form data

        $.ajax({
          url: _base_url_ + "classes/Login.php?f=login", // Adjust URL for the backend login script
          method: 'POST',
          data: formData,
          dataType: 'json',
          success: function (response) {
            if (response.status === 'verify_email_sent') {
              Swal.fire({
                icon: 'success',
                title: 'Verification Sent',
                text: 'We have sent a verification link to your email. Please check your inbox.',
                confirmButtonText: 'OK'
              }).then(() => {
                // Disable the login button after alert confirmation
                $('#login-frm button[type="submit"]').attr('disabled', true);
              });
            } else if (response.status === 'captcha_failed') {
              Swal.fire({
                icon: 'error',
                title: 'Captcha Failed',
                text: response.message,
                confirmButtonText: 'Try Again'
              });
            } else if (response.status === 'notverified') {
              Swal.fire({
                icon: 'error',
                title: 'Account Not Verified',
                text: 'Please contact admin to verify your account.',
                confirmButtonText: 'OK'
              });
            } else if (response.status === 'incorrect') {
              Swal.fire({
                icon: 'error',
                title: 'Login Failed',
                text: 'Incorrect username or password.',
                confirmButtonText: 'Retry'
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An unexpected error occurred. Please try again later.',
                confirmButtonText: 'OK'
              });
            }
          },
          error: function () {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Unable to process your request at this time.',
              confirmButtonText: 'OK'
            });
          }
        });
      });

      $(document).ready(function () {
        end_loader(); // Ensure loader is ended when the document is ready
      });
    })(jQuery);
  </script>


</body>

</html>