<?php require_once('../config.php'); ?>
<!DOCTYPE html>
<html lang="en">
<?php require_once('inc/header.php'); ?>

<head>
  <title>OTP Verification</title>
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

<body>
  <div class="container register" style="margin-top: 7vh;">
    <div class="row">
      <div class="col-md-3 register-left">
        <h3>Verify OTP</h3>
        <p>Check your email for the OTP code. Enter it below.</p>
      </div>
      <div class="col-md-9 register-right">
        <h3 class="register-heading">OTP Verification</h3>
        <form id="otp-frm" method="POST">
          <div class="form-group">
            <input type="number" name="otp" id="otp" placeholder="Enter OTP *" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary">Verify OTP</button>
        </form>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function () {
      $('#otp-frm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
          url: _base_url_ + "classes/Login.php?f=verify_otp",
          method: "POST",
          data: { otp: $('#otp').val() },
          dataType: "json",
          success: function (response) {
            if (response.status === 'success') {
              window.location.href = '../admin/'; // Redirect to dashboard
            } else if (response.status === 'expired') {
              alert('OTP expired. Redirecting to login.');
              window.location.href = '../admin/login.php'; // Redirect to login
            } else {
              alert('Invalid OTP.');
            }
          },
          error: function () {
            alert('Unable to process request.');
          }
        });
      });
    });
  </script>
</body>

</html>