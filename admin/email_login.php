<?php require_once('../config.php') ?>
<!DOCTYPE html>
<html lang="en">
<?php require_once('inc/header.php') ?>

<head>
  <title>Email Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../myStyles/loginstyle.css">
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<style>
  body {
    background-image: url("../dist/img/background.png");
    background-size: cover;
    background-repeat: no-repeat;
  }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<body class="hold-transition">
  <div class="container register" style="margin-top: 7vh;">
    <div class="row">
      <!-- Left Section -->
      <div class="col-md-3 register-left">
        <img src="<?= validate_image($_settings->info('logo')) ?>" alt="Logo" />
        <h3>Welcome Admin</h3>
        <p>Enter your email to request a login link.</p>
        <button class="myButton" onclick="location.href = '../'">Go to Site</button>
      </div>

      <!-- Right Section -->
      <div class="col-md-9 register-right">
        <h3 class="register-heading">Email Login</h3>
        <div class="row register-form">
          <div class="col-md-12">
            <form id="email-login-frm" action="" method="POST" novalidate>
              <!-- Email Field -->
              <div class="form-group">
                <input type="email" name="email" id="email" placeholder="Email *"
                  class="form-control form-control-border" required>
              </div>

              <div class="form-group">
                <div class="g-recaptcha" data-sitekey="6LcvKpIqAAAAADbEzoBwvwKZ9r-loWJLfGIuPgKW"></div>
              </div>

              <!-- Submit Button -->
              <div class="row">
                <div class="col-md-12 text-right">
                  <button type="submit" class="btn btn-primary btn-flat">Send Login Link</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $(document).ready(function () {
      $('#email-login-frm').on('submit', function (e) {
        e.preventDefault(); // Prevent default form submission

        let email = $('#email').val();
        let recaptchaResponse = grecaptcha.getResponse(); // Get reCAPTCHA response

        if (!recaptchaResponse) {
          Swal.fire({
            icon: 'error',
            title: 'Captcha Required',
            text: 'Please complete the reCAPTCHA.',
            confirmButtonText: 'OK'
          });
          return;
        }

        console.log("Submitting email: " + email); // Debug message

        $.ajax({
          url: _base_url_ + "classes/Login.php?f=email_login", // Backend route
          method: 'POST',
          data: { email, recaptchaResponse },
          dataType: 'json',
          success: function (response) {
            console.log(response); // Debug response
            if (response.status === 'success') {
              Swal.fire({
                icon: 'success',
                title: 'Login Link Sent',
                text: 'Check your email for the login link.',
                confirmButtonText: 'OK'
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response.message,
                confirmButtonText: 'Retry'
              });
            }
          },
          error: function (xhr, status, error) {
            console.error(xhr.responseText); // Debug server error
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Unable to process your request. Try again later.',
              confirmButtonText: 'OK'
            });
          }
        });
      });
    });

  </script>
</body>

</html>