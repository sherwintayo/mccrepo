<?php require_once('../config.php'); ?>
<!DOCTYPE html>
<html lang="en" class="" style="height: auto;">
<!-- <?php require_once('inc/header.php') ?> -->

<head>
  <title>Forgot Password</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../dist/css/registerstyle.css">
</head>

<style>
  /* html,
  body {
    height: calc(100%) !important;
    width: calc(100%) !important;
  } */

  body {
    background-image: url("../dist/img/background.png");
    background-size: cover;
    background-repeat: no-repeat;
  }
</style>

<body class="hold-transition">
  <script>
    start_loader()
  </script>


  <body class="hold-transition">
    <div class="container register" style="margin-top: 14vh;">
      <div class="row">
        <!-- Left Section -->
        <div class="col-md-3 register-left">
          <img src="<?= validate_image($_settings->info('logo')) ?>" alt="Logo" />
          <h3>Forgot Password?</h3>
          <p>We'll help you reset your password in no time.</p>
          <button class="myButton" onclick="location.href = '<?php echo base_url ?>'">Go Back</button>
        </div>

        <!-- Right Section -->
        <div class="col-md-9 register-right">
          <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="forgot-password-tab" role="tabpanel">
              <h3 class="register-heading">Reset Your Password</h3>
              <div class="row register-form">
                <div class="col-md-12">
                  <form action="forgot_password_process.php" method="POST">
                    <!-- Email Field -->
                    <div class="form-group">
                      <input type="email" name="email" id="email" placeholder="Enter your email address"
                        class="form-control form-control-border" required>
                    </div>

                    <!-- Buttons -->
                    <div class="row mt-4">
                      <div class="col-lg-12 text-center">
                        <button type="submit" class="btn btn-primary btn-flat">Send Reset Link</button>
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
    <script src="../plugins/jquery/jquery.min.js"></script>
    <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../dist/js/adminlte.min.js"></script>

    <script>
      $(document).ready(function () {
        end_loader();
      });
    </script>
  </body>

</html>