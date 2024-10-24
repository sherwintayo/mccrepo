<?php require_once('./config.php') ?>
<!-- <?php session_start(); // Start session handling ?> -->
<!DOCTYPE html>
<html lang="en" class="" style="height: auto;">
 <?php require_once('inc/header.php') ?>
<body class="hold-transition">
  <script>
    start_loader()
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
    /* body{
      background-image: url("<?php echo validate_image($_settings->info('cover')) ?>");
      background-size:cover;
      background-repeat:no-repeat;
      background-position:center center;
   height: 100%;
   width: 100%;
    } */

    .login-title{
      text-shadow: 3px 3px black;
      padding: 20px 0 0 0;
    }
    #logo-img{
        height:150px;
        width:150px;
        object-fit:scale-down;
        object-position:center center;
        border-radius:100%;
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


/* Extra small devices (portrait phones, less than 576px) */
@media (max-width: 575.98px) {

    .myContainer{
        margin: 20px;
    }
  
    .login-form {
        width: 100%;
        
    }

    .card {
        width: 100%;
        margin: 0;
    }

    .myLoginForm {
  border-radius: 20px 20px 0 0;
   }
}

/* Small devices (landscape phones, less than 768px) */
@media (max-width: 767.98px) {  

    .myContainer{
        margin: 20px;
    }

    .login-form {
        width: 100%;
        
    }

    .card {
        width: 100%;
    }
}

/* Medium devices (tablets, less than 992px) */
@media (max-width: 991.98px) {
    .login-form {
        width: 70%;
    }

    .card {
        width: 90%;
    }
}

/* Large devices (desktops, less than 1200px) */
@media (max-width: 1199.98px) {
    .login-form {
        width: 60%;
    }

    .card {
        width: 80%;
    }
}

/* Extra large devices (large desktops, 1200px and up) */
@media (min-width: 1200px) {
    .login-form {
        width: 50%;
    }

    .card {
        width: 70%;
    }
}

  </style>
  <?php if($_settings->chk_flashdata('success')): ?>
      <script>
        alert_toast("<?php echo $_settings->flashdata('success') ?>",'success')
      </script>
      <?php endif;?> 
<div class=" d-flex flex-column align-items-center w-100" id="login">
   
    <div class="body d-flex flex-column justify-content-center align-items-center">
     <div class="w-100">
        <h1 class="text-center py-5 my-5 login-title"><b><?php echo $_settings->info('name') ?></b></h1>
      </div> 
        <div class="row myContainer">
                <div class="myLoginForm col-lg-7 p-3 d-flex justify-content-center align-items-center text-navy">
               
                     <div class="d-flex flex-column w-100 px-3">
                    <h1 class="text-center font-weight-bold text-white">Sign in to Account</h1>
                    <hr class="my-3" />
                    <form action="" id="slogin-form">
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
                  <div class="row">
                   <div class="col-lg-12">
                     <div class="input-group form-group">
                      <div class="input-group-prepend">
                          <span class="input-group-text rounded-0"><i class="fas fa-key fa-lg fa-fw"></i></span>
                        </div>
                           <input type="password" name="password" id="password" placeholder="Password" class="form-control form-control-border" required>
                            </div>
                          </div>
                     </div>
                     <div class="row">  
                       <div class="col-6">
                            <a class="text-light font-weight-bolder" href="<?php echo base_url ?>">Go Back</a>
                            </div>         
                           <div class="col-6">  
                             <div class="form-group text-right">
                                <button class="btnLogin btn btn-primary btn-flat text-white"> Login</button>
                             </div>
                       </div>
                       <div class="row mt-2">
                            <div class="col-lg-12 text-center">
                                <a href="forgot_password.php" class="text-light">Forgot Password?</a>
                            </div>
                        </div>

                     </div>
                  </form>
                 </div>
                   
                </div>
                <div class="col-lg-5 d-flex flex-column justify-content-center myColor p-4">
                <h1 class="text-center font-weight-bold text-white">Hello Friends!</h1>
                <hr class="my-3 bg-light myHr" />
                <p class="text-center font-weight-bolder text-light lead">Enter your personal details and 
                    start your journey with us!</p>
                <button class="btn btn-outline-light btn-lg align-self-center font-weight-bolder mt-4 myLinkBtn" 
                onclick="location.href = '#'">Sign Up</button>
                </div>
         </div>   
    </div>
</div>

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>
<!-- Select2 -->
<script src="<?php echo base_url ?>plugins/select2/js/select2.full.min.js"></script>



<!-- Scripts -->
<script>
  $(document).ready(function(){
    end_loader();

    // Validation functions from the admin login
    var validateEmail = function(email) {
      var emailReg = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
      return emailReg.test(email);
    };

    // var validatePassword = function(password) {
    //   var passwordReg = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
    //   return passwordReg.test(password);
    // };

    var hasInvalidChars = function(input) {
      return input.includes("'");
    };

    var setValidationMessage = function(input, message) {
      input.setCustomValidity(message);
      input.reportValidity();
    };

    $('#slogin-form').submit(function(e){
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

        // Validate password format
        // if (!validatePassword(password)) {
        //   setValidationMessage(passwordInput, "Password must be at least 8 characters long and contain an uppercase letter, lowercase letter, number, and special character.");
        //   return; // Stop submission if validation fails
        // }

        // Check for invalid characters in email and password
        if (hasInvalidChars(email)) {
          setValidationMessage(emailInput, "Email must not contain single quotes: '" + email + "'");
          return; // Stop submission if validation fails
        }

        if (hasInvalidChars(password)) {
          setValidationMessage(passwordInput, "Password must not contain single quotes.");
          return; // Stop submission if validation fails
        }

        // Existing AJAX request logic
        start_loader();

        $.ajax({
          url: _base_url_ + "classes/Login.php?f=student_login",
          method: 'POST',
          data: _this.serialize(),
          dataType: 'json',
          error: err => {
            console.log(err);
            alert("An error occurred.");
            end_loader();
          },
          success: function(resp){
            end_loader();
            if (resp.status == 'success') {
              <?php $_SESSION['user_logged_in'] = true; ?>
              window.location.href = "./";
            } else {
              alert("Login failed: " + (resp.msg || "Invalid credentials."));
            }
          }
        });
    });
  });
</script>

</body>
</html>
