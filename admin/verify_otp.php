<?php require_once('../config.php'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Verify OTP</title>
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
      <div class="col-md-3 register-left">
        <img src="<?= validate_image($_settings->info('logo')) ?>" alt="Logo" />
        <h3>Verify OTP</h3>
        <p>Enter the 6-digit OTP sent to your email.</p>
        <button class="myButton" onclick="location.href = '<?php echo base_url ?>'">Go to Site</button>
      </div>
      <div class="col-md-9 register-right">
        <form id="otpVerifyForm">
          <div class="form-group">
            <input type="text" name="otp" id="otp" placeholder="Enter OTP" class="form-control form-control-border"
              maxlength="6" required>
          </div>
          <button type="submit" class="btn btn-primary btn-block">Verify OTP</button>
        </form>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    $('#otpVerifyForm').on('submit', function (e) {
      e.preventDefault();

      const otp = $('#otp').val();

      $.ajax({
        url: _base_url_ + 'admin/verify_otp_process', // Backend for OTP verification
        method: 'POST',
        data: { otp },
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            Swal.fire({
              icon: 'success',
              title: 'OTP Verified!',
              text: response.message
            }).then(() => {
              window.location.href = 'reset_password?token=' + response.token; // Redirect to reset password
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