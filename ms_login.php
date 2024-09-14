<?php require_once('./config.php'); ?>
<!DOCTYPE html>
<html lang="en" class="" style="height: auto;">
<?php require_once('inc/header.php') ?>
<body class="hold-transition">
  <script>
    start_loader();
  </script>
  <style>
    html, body{
      height:calc(100%) !important;
      width:calc(100%) !important;
    }
    body {
        background: linear-gradient(135deg, #141e30 20%, #243b55 100%);
        background-size: cover;
        background-repeat: no-repeat;
        height: 100%;
        width: 100%;
    }
    .myColor{
        background-image: linear-gradient(to right, #f83600 50%, #f9d423 150%);
    }
    .myLoginForm {
      background: transparent;
      border: 2px solid #f83600;
      backdrop-filter: blur(2px);
      border-radius: 20px 0 0 20px;
    }
    .btnLogin{
      border-radius: 0 20px 20px 0;
      border: 0;
      background-image: linear-gradient(to right, #f83600 50%, #f9d423 150%);
    }
    @media (max-width: 575.98px) {
      .myContainer{
          margin: 20px;
      }
      .login-form {
          width: 100%;
      }
      .myLoginForm {
        border-radius: 20px 20px 0 0;
      }
    }
    @media (min-width: 1200px) {
      .login-form {
        width: 50%;
      }
    }
  </style>

  <div class="d-flex flex-column align-items-center w-100" id="login">
    <div class="body d-flex flex-column justify-content-center align-items-center">
      <div class="w-100">
        <h1 class="text-center py-5 my-5 login-title"><b><?php echo $_settings->info('name') ?></b></h1>
      </div>
      <div class="row myContainer">
        <div class="myLoginForm col-lg-7 p-3 w-100 d-flex justify-content-center align-items-center text-navy">
          <div class="d-flex flex-column w-100 px-3">
            <h1 class="text-center font-weight-bold text-white">Forgot Password</h1>
            <hr class="my-3" />
            <form id="slogin-form">
              <div class="form-group">
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text rounded-0"><i class="far fa-envelope fa-lg fa-fw"></i></span>
                  </div>
                  <input type="email" name="email" id="email" placeholder="Email" class="form-control form-control-border" required>
                </div>
              </div>
              <div class="form-group text-right">
                <button type="submit" class="btnLogin btn btn-primary btn-flat text-white">Send</button>
              </div>
              <div class="text-center">
                <a class="text-light" href="<?php echo base_url ?>">Go Back</a>
              </div>
            </form>
          </div>
        </div>
        <div class="col-lg-5 d-flex flex-column justify-content-center myColor p-4">
          <h1 class="text-center font-weight-bold text-white">Hello Friends!</h1>
          <hr class="my-3 bg-light myHr" />
          <p class="text-center font-weight-bolder text-light lead">Enter your personal details and start your journey with us!</p>
          <button class="btn btn-outline-light btn-lg align-self-center font-weight-bolder mt-4 myLinkBtn" onclick="location.href = 'register.php'">Sign Up</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function() {
      end_loader();
      
      // Handle form submission via AJAX
      $('#slogin-form').submit(function(e) {
        e.preventDefault();
        
        let email = $('#email').val();
        const domain = "@mcclawis.edu.ph";
        
        if (!email.endsWith(domain)) {
          alert("Please enter a valid email address with the domain " + domain);
          return false;
        }
        
        start_loader();
        
        $.ajax({
          url: 'ms_login_process.php',  // Use the method from Login.php
          method: 'POST',
          data: $(this).serialize(),
          dataType: 'json',
          success: function(response) {
            end_loader();
            if (response.status === 'success') {
              Swal.fire({
                icon: 'success',
                title: 'Link Sent',
                text: 'Check your email for the reset link.',
                showConfirmButton: false,
                timer: 2000
              }).then(() => {
                // Redirect to login.php after success
                window.location.href = "login.php";
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response.msg || 'An error occurred.',
              });
            }
          },
          error: function() {
            end_loader();
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'An unexpected error occurred.',
            });
          }
        });
      });
    });
  </script>

  <!-- jQuery -->
  <script src="plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- AdminLTE App -->
  <script src="dist/js/adminlte.min.js"></script>
</body>
</html>
