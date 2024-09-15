<?php require_once('./config.php'); ?>
<!DOCTYPE html>
<html lang="en">
<?php require_once('inc/header.php') ?>
<body class="hold-transition">
<script>
  start_loader()
</script>
<style>
/* Add the styles from your example */
html, body {
  height: calc(100%) !important;
  width: calc(100%) !important;
  background: linear-gradient(135deg, #141e30 20%, #243b55 100%);
  background-size: cover;
  background-repeat: no-repeat;
}

.login-title {
  text-shadow: 3px 3px black;
  padding: 20px 0 0 0;
}

.myLoginForm {
  background: transparent;
  border: 2px solid #f83600;
  backdrop-filter: blur(2px);
  border-radius: 20px 0 0 20px;
}

.btnLogin {
  border-radius: 0 20px 20px 0;
  border: 0;
  background-image: linear-gradient(to right, #f83600 50%, #f9d423 150%);
}
</style>

<div class="d-flex flex-column align-items-center w-100" id="login">
  <div class="body d-flex flex-column justify-content-center align-items-center">
    <div class="w-100">
      <h1 class="text-center py-5 my-5 login-title"><b>Forgot Password</b></h1>
    </div>
    <div class="row myContainer">
      <div class="myLoginForm col-lg-7 p-3 w-100 d-flex justify-content-center align-items-center">
        <div class="d-flex flex-column w-100 px-3">
          <h1 class="text-center font-weight-bold text-white">Reset Password</h1>
          <hr class="my-3">
          <form id="ms-login-form">
            <div class="row">
              <div class="col-lg-12">
                <div class="input-group form-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text rounded-0"><i class="far fa-envelope fa-lg fa-fw"></i></span>
                  </div>
                  <input type="email" name="email" id="email" placeholder="Email" class="form-control form-control-border" required>
                </div>
              </div>
            </div>
            <div class="form-group text-right">
              <button class="btnLogin btn btn-primary btn-flat text-white">Send</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="plugins/jquery/jquery.min.js"></script>
<script>
$(document).ready(function() {
  $('#ms-login-form').submit(function(e) {
    e.preventDefault();
    var email = $('#email').val();
    if (!email.endsWith('@mcclawis.edu.ph')) {
      alert('Please enter a valid email address with the domain @mcclawis.edu.ph');
      return false;
    }

    $.ajax({
      url: 'ms_login_process.php',
      type: 'POST',
      data: { email: email },
      success: function(response) {
        Swal.fire({
          icon: 'success',
          title: 'Request Sent',
          text: response,
          showConfirmButton: false,
          timer: 1500
        });
      },
      error: function() {
        alert('An error occurred. Please try again.');
      }
    });
  });
});
</script>

</body>
</html>
