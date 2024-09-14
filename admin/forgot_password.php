<?php require_once('../config.php'); ?>
<!DOCTYPE html>
<html lang="en" class="" style="height: auto;">
<?php require_once('inc/header.php') ?>
<body class="hold-transition">
  <script>
    start_loader()
  </script>
  <style>
    html, body {
      height: 100% !important;
      width: 100% !important;
    }
    body {
      background: linear-gradient(135deg, #141e30 20%, #243b55 100%);
      background-size: cover;
      background-repeat: no-repeat;
      height: 100%;
      width: 100%;
    }

    .forgot-password-title {
      text-shadow: 3px 3px black;
      padding: 20px 0 0 0;
    }

    .myColor {
      background-image: linear-gradient(to right, #f83600 50%, #f9d423 150%);
    }

    .myForgotForm {
      background: transparent;
      border: 2px solid #f83600;
      backdrop-filter: blur(2px);
      border-radius: 20px;
    }

    .btnForgot {
      border-radius: 20px;
      border: 0;
      background-image: linear-gradient(to right, #f83600 50%, #f9d423 150%);
    }

    /* Responsive styles */
    @media (max-width: 575.98px) {
      .myContainer {
        margin: 20px;
      }

      .forgot-form {
        width: 100%;
      }

      .myForgotForm {
        border-radius: 20px;
      }
    }

    @media (min-width: 1200px) {
      .forgot-form {
        width: 50%;
      }
    }
  </style>

  <div class="d-flex flex-column align-items-center w-100" id="forgot-password">
    <div class="body d-flex flex-column justify-content-center align-items-center">
      <div class="w-100">
        <h1 class="text-center py-5 my-5 forgot-password-title"><b>Forgot Password</b></h1>
      </div>
      <div class="row myContainer">
        <div class="myForgotForm col-lg-7 p-3 w-100 d-flex justify-content-center align-items-center text-navy">
          <div class="d-flex flex-column w-100 px-3">
            <h2 class="text-center font-weight-bold text-white">Reset your Password</h2>
            <hr class="my-3" />
            <form action="forgot_password_process.php" method="post" class="forgot-form">
              <div class="row">
                <div class="col-lg-12">
                  <div class="input-group form-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text rounded-0"><i class="far fa-envelope fa-lg fa-fw"></i></span>
                    </div>
                    <input type="email" name="email" id="email" placeholder="Enter your email address" class="form-control form-control-border" required>
                  </div>
                </div>
              </div>
              <div class="row mt-4">
                <div class="col-lg-12 text-center">
                  <button type="submit" class="btnForgot btn btn-primary btn-flat text-white">Send Reset Link</button>
                </div>
              </div>
            </form>
          </div>
        </div>
        <div class="col-lg-5 d-flex flex-column justify-content-center myColor p-4">
          <h1 class="text-center font-weight-bold text-white">Hello Friends!</h1>
          <hr class="my-3 bg-light myHr" />
          <p class="text-center font-weight-bolder text-light lead">Enter your email and we'll help you reset your password!</p>
        </div>
      </div>
    </div>
  </div>

  <!-- jQuery and Bootstrap -->
  <script src="plugins/jquery/jquery.min.js"></script>
  <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="dist/js/adminlte.min.js"></script>

  <script>
    $(document).ready(function() {
      end_loader();
    });
  </script>
</body>
</html>
