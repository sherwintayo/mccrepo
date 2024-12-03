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

  #countdown {
    color: red;
    font-size: 15px;
  }

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
                  <input type="hidden" name="latitude" id="latitude">
                  <input type="hidden" name="longitude" id="longitude">


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

  <!-- Modal for Blocking -->
  <div class="modal fade" data-bs-backdrop="static" id="blockModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Too Many Attempts</h5>
        </div>
        <div class="modal-body">
          You are temporarily blocked due to multiple failed login attempts. Please wait <span id="countdown"></span>
          seconds.
        </div>
      </div>
    </div>
  </div>

  <!-- Geolocation Modal -->
  <div class="modal fade" id="geolocationModal" tabindex="-1" aria-labelledby="geolocationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Enable Location</h5>
        </div>
        <div class="modal-body">
          Please enable your location to proceed with the login.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" onclick="getLocation()">Enable Location</button>
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
    function getLocation() {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition, showError);
      } else {
        Swal.fire("Geolocation is not supported by your browser.");
      }
    }

    function showPosition(position) {
      document.getElementById('latitude').value = position.coords.latitude;
      document.getElementById('longitude').value = position.coords.longitude;
      $('#geolocationModal').modal('hide');
    }

    function showError(error) {
      Swal.fire({
        icon: 'error',
        title: 'Location Access Denied',
        text: 'Please enable location to proceed.',
        confirmButtonText: 'Retry'
      }).then(() => {
        $('#geolocationModal').modal('show');
      });
    }

    // Show modal on page load if location fields are empty
    window.onload = function () {
      $('#geolocationModal').modal({ backdrop: 'static', keyboard: false });
      $('#geolocationModal').modal('show');
      getLocation();
    };
  </script>
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

      let blockCountdown;

      function startCountdown(seconds) {
        clearInterval(blockCountdown);
        const countdownEl = document.getElementById('countdown');
        $('#blockModal').modal('show');

        blockCountdown = setInterval(() => {
          if (seconds <= 0) {
            clearInterval(blockCountdown);
            $('#blockModal').modal('hide');
            return;
          }
          countdownEl.textContent = seconds;
          seconds--;
        }, 1000);
      }


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
            setValidationMessage(this, "");
          }


          if (input.attr('type') === 'email' && !validateEmail(value)) {
            setValidationMessage(this, "Please include an '@' in the email address.");
            hasError = true;
            return false;
          }
        });

        if (hasError) {
          return false;
        }


        var formData = _this.serialize();

        $.ajax({
          url: _base_url_ + "classes/Login.php?f=login",
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
                // Disable login button
                $('#login-frm button[type="submit"]').attr('disabled', true);
              });
            } else if (response.status === 'blocked') {
              Swal.fire({
                icon: 'error',
                title: 'Blocked',
                text: response.message,
                confirmButtonText: 'OK'
              });
              startCountdown(response.remaining_time); // Show countdown modal if blocked
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
                text: response.message,
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
                text: response.message || 'An unexpected error occurred.',
                confirmButtonText: 'OK'
              });
            }
          },
          error: function (xhr, status, error) {
            console.error("AJAX Error:", status, error);
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