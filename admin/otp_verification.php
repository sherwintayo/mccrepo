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
        <form action="" method="POST">
          <div class="form-group">
            <input type="number" id="otp" class="form-control w-50 mx-auto" placeholder="Enter OTP" required>
          </div>
          <button id="verify-btn" class="btn btn-primary mt-3">Verify OTP</button>
        </form>
        <div id="timer" class="mt-3"></div>
      </div>
    </div>
  </div>

  <script>
    // Countdown Timer
    let expiryTime = 90; // 1 minute and 30 seconds
    const timerElement = document.getElementById('timer');

    const countdown = setInterval(() => {
      const minutes = Math.floor(expiryTime / 60);
      const seconds = expiryTime % 60;
      timerElement.textContent = `Time remaining: ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
      expiryTime--;

      if (expiryTime < 0) {
        clearInterval(countdown);
        Swal.fire({
          icon: 'error',
          title: 'OTP Expired',
          text: 'Redirecting to login page...',
          showConfirmButton: false,
          timer: 2000
        }).then(() => {
          window.location.href = 'login.php';
        });
      }
    }, 1000);

    // OTP Verification
    document.getElementById('verify-btn').addEventListener('click', () => {
      const otp = document.getElementById('otp').value;

      fetch('classes/Login.php?f=verify_otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ otp })
      })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            Swal.fire({
              icon: 'success',
              title: 'OTP Verified',
              text: 'Redirecting to dashboard...',
              showConfirmButton: false,
              timer: 2000
            }).then(() => {
              window.location.href = 'dashboard.php';
            });
          } else if (data.status === 'expired') {
            Swal.fire('OTP Expired', 'Please login again.', 'error')
              .then(() => window.location.href = 'login.php');
          } else {
            Swal.fire('Invalid OTP', data.message, 'error');
          }
        });
    });
  </script>
</body>

</html>