<?php require_once('../config.php'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Forgot Password - OTP</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../myStyles/loginstyle.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<style>
  body {
    background-image: url("../dist/img/background.png");
    background-size: cover;
    background-repeat: no-repeat;
  }
</style>

<body class="hold-transition">
  <div class="container register" style="margin-top: 14vh;">
    <div class="row">
      <!-- Left Section -->
      <div class="col-md-3 register-left">
        <img src="<?= validate_image($_settings->info('logo')) ?>" alt="Logo" />
        <h3>Forgot Password</h3>
        <p>Enter your email to receive a 6-digit OTP for resetting your password.</p>
        <button class="myButton" onclick="location.href = '<?php echo base_url ?>'">Go to Site</button>
      </div>

      <!-- Right Section -->
      <div class="col-md-9 register-right">
        <div class="tab-content" id="myTabContent">
          <div class="tab-pane fade show active" id="otp-tab" role="tabpanel">
            <h3 class="register-heading">Reset Password via OTP</h3>
            <div class="row register-form">
              <div class="col-md-12">
                <!-- OTP Form -->
                <form id="otpRequestForm">
                  <div class="form-group">
                    <input type="email" name="email" id="email" placeholder="Enter your email address"
                      class="form-control form-control-border" required>
                  </div>
                  <div class="row mt-4">
                    <div class="col-lg-12 text-center">
                      <button type="submit" class="btn btn-primary btn-flat">Send OTP</button>
                    </div>
                  </div>
                </form>
                <p class="text-center mt-2">Already have an OTP? <a href="verify_otp">Verify it here</a>.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    $('#otpRequestForm').on('submit', function (e) {
      e.preventDefault(); // Prevent default form submission

      const email = $('#email').val();

      $.ajax({
        url: _base_url_ + 'admin/send_otp_process.php', // Backend script to handle OTP generation
        method: 'POST',
        data: { email },
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            Swal.fire({
              icon: 'success',
              title: 'OTP Sent!',
              text: response.message
            }).then(() => {
              window.location.href = 'verify_otp'; // Redirect to OTP verification
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: response.message
            });
          }
        },
        error: function () {
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An unexpected error occurred. Please try again.'
          });
        }
      });
    });
  </script>
</body>

</html>