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
  body {
    background-image: url("../dist/img/background.png");
    background-size: cover;
    background-repeat: no-repeat;
  }
</style>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<body class="hold-transition ">
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
                  <button type="submit" class="btn btn-primary btn-flat" id="login-btn">Login</button>
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

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $(document).ready(function () {
      $('#login-frm').on('submit', function (event) {
        event.preventDefault(); // Prevent form submission

        const _this = $(this);
        const loginBtn = $('#login-btn');
        const formData = _this.serialize(); // Serialize form data

        // Reset button state and feedback
        loginBtn.attr('disabled', false);
        loginBtn.text('Login');

        $.ajax({
          url: _base_url_ + "classes/Login.php?f=login", // Backend login endpoint
          method: 'POST',
          data: formData,
          dataType: 'json',
          success: function (response) {
            if (response.status === 'success') {
              Swal.fire({
                icon: 'success',
                title: 'Verification Sent',
                text: 'We have sent a verification link to your email. Please check your inbox.',
                confirmButtonText: 'OK'
              }).then(() => {
                // Disable the login button
                loginBtn.attr('disabled', true);
                loginBtn.text('Verification Sent'); // Update button text
              });
            } else if (response.status === 'captcha_failed') {
              Swal.fire({
                icon: 'error',
                title: 'Captcha Failed',
                text: response.message,
                confirmButtonText: 'OK'
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
    });
  </script>
</body>

</html>