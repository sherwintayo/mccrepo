this is the login.php

<?php require_once('./config.php') ?>
<!-- <?php session_start(); // Start session handling ?> -->
<!DOCTYPE html>
<html lang="en" class="" style="height: auto;">
<?php require_once('inc/header.php') ?>

<head>
  <title>MS Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="myStyles/loginstyle.css">
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

  .position-relative {
    position: relative;
  }

  .toggle-password {
    position: absolute;
    top: 50%;
    right: 10px;
    /* Adjust as needed for proper alignment */
    transform: translateY(-50%);
    cursor: pointer;
    color: #aaa;
  }

  .toggle-password:hover {
    color: #000;
  }
</style>
<!-- <script src="https://www.google.com/recaptcha/api.js" async defer></script> -->
<script src="https://www.google.com/recaptcha/api.js?render=6LcvKpIqAAAAADbEzoBwvwKZ9r-loWJLfGIuPgKW"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<body class="hold-transition">
  <!-- <script>
    start_loader()
  </script> -->

  <?php if ($_settings->chk_flashdata('success')): ?>
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: "<?php echo $_settings->flashdata('success') ?>",
        toast: true,
        position: 'top-center',
        showConfirmButton: false,
        timer: 3000
      });
    </script>
  <?php endif; ?>

  <div class="container register" style="margin-top: 5vh; height: 600px;" id="login">
    <div class="row" style="height: 500px;">
      <!-- Left Section -->
      <div class="col-md-3 register-left">
        <img src="<?= validate_image($_settings->info('logo')) ?>" alt="Logo" />
        <h3>Welcome Back</h3>
        <p>Enter your credentials to access your account.</p>
        <button class="myButton" onclick="location.href = '<?php echo base_url ?>'">Go to Website</button>
      </div>

      <!-- Right Section -->
      <div class="col-md-9 register-right">
        <div class="tab-content" id="myTabContent">
          <div class="tab-pane fade show active" id="login-tab" role="tabpanel">
            <h3 class="register-heading">Sign to Your Account</h3>
            <div class="row register-form">
              <div class="col-md-12">
                <form action="" id="slogin-form">
                  <!-- Email Field -->
                  <div class="form-group">
                    <input type="email" name="email" id="email" placeholder="Email *"
                      class="form-control form-control-border" required>
                  </div>

                  <!-- Password Field -->
                  <div class="form-group position-relative">
                    <input type="password" name="password" id="password" placeholder="Password *"
                      class="form-control form-control-border" required>
                    <span class="toggle-password" onclick="toggleVisibility('password')">
                      <i class="fa fa-eye" id="eye-password"></i>
                    </span>
                  </div>

                  <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                  <!-- Buttons -->
                  <div class="row">
                    <div class="col-md-6">
                      <a href="ms_login" class="btn btn-light">Sign Up</a>
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

        // Check for invalid characters in email and password
        if (hasInvalidChars(email)) {
          setValidationMessage(emailInput, "Email must not contain single quotes: '" + email + "'");
          return; // Stop submission if validation fails
        }

        if (hasInvalidChars(password)) {
          setValidationMessage(passwordInput, "Password must not contain single quotes.");
          return; // Stop submission if validation fails
        }

        let formData = $(this).serialize();
        // Request reCAPTCHA v3 token
        grecaptcha.execute('6LcvKpIqAAAAADbEzoBwvwKZ9r-loWJLfGIuPgKW', { action: 'login' }).then(function (token) {
          formData += "&g-recaptcha-response=" + token;

          let formData = $(this).serialize();

          start_loader();

          $.ajax({
            url: _base_url_ + "classes/Login.php?f=student_login",
            method: 'POST',
            data: formData,
            dataType: 'json',
            error: err => {
              console.log(err);
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred. Please try again later.',
              });
              end_loader();
            },
            success: function (resp) {
              end_loader();
              if (resp.status == 'success') {
                <?php $_SESSION['user_logged_in'] = true; ?>
                window.location.href = "./";
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Login Failed',
                  text: resp.msg || 'Invalid credentials. Please try again.',
                });
              }
            }
          });
        });
      });
    });
  </script>
  <script>
    function toggleVisibility(inputId) {
      const inputField = document.getElementById(inputId);
      const icon = document.getElementById(eye - ${ inputId });
      if (inputField.type === "password") {
        inputField.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
      } else {
        inputField.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
      }
    }
  </script>

</body>

</html>