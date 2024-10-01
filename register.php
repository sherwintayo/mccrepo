<?php 
include('config.php') ?>
<!DOCTYPE html>
<html lang="en" class="" style="height: auto;">
<?php require_once('inc/header.php') ?>
<body class="hold-transition ">
  <script>
    start_loader()
  </script>
  <style>
    html, body{
      height:calc(100%) !important;
      width:calc(100%) !important;
    }
    body{
      background-image: url("<?php echo validate_image($_settings->info('cover')) ?>");
      background-size:cover;
      background-repeat:no-repeat;
    }
    .login-title{
      text-shadow: 2px 2px black
    }
    #login{
        direction:rtl
    }
    #login > *{
        direction:ltr
    }
    #logo-img{
        height:150px;
        width:150px;
        object-fit:scale-down;
        object-position:center center;
        border-radius:100%;
    }
  </style>

<div class="h-100 d-flex  align-items-center w-100" id="login">
    <div class="col-7 h-100 d-flex align-items-center justify-content-center">
      <div class="w-100">
        <center><img src="<?= validate_image($_settings->info('logo')) ?>" alt="" id="logo-img"></center>
        <h1 class="text-center py-5 login-title"><b><?php echo $_settings->info('name') ?> - Admin</b></h1>
      </div>
      
    </div>
    <div class="col-5 h-100 bg-gradient bg-navy">
        <div class="w-100 d-flex justify-content-center align-items-center h-100 text-navy">
            <div class="card card-outline card-primary rounded-0 shadow col-lg-10 col-md-10 col-sm-5">
                <div class="card-header">
                    <h5 class="card-title text-center text-dark"><b>Registration</b></h5>
                </div>
                <div class="card-body">
                    <form action="" id="registration-form" method="post" novalidate>
                        <input type="hidden" name="id">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <input type="text" name="firstname" id="firstname" autofocus placeholder="Firstname" class="form-control form-control-border" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <input type="text" name="middlename" id="middlename" placeholder="Middlename (optional)" class="form-control form-control-border">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <input type="text" name="lastname" id="lastname" placeholder="Lastname" class="form-control form-control-border" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-auto">
                                <div class="custom-control custom-radio">
                                    <input class="custom-control-input" type="radio" id="genderMale" name="gender" value="Male" required checked>
                                    <label for="genderMale" class="custom-control-label">Male</label>
                                </div>
                            </div>
                            <div class="form-group col-auto">
                                <div class="custom-control custom-radio">
                                    <input class="custom-control-input" type="radio" id="genderFemale" name="gender" value="Female">
                                    <label for="genderFemale" class="custom-control-label">Female</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <span class="text-navy"><small>Program</small></span>
                                    <select name="program_id" id="program_id" class="form-control form-control-border select2" data-placeholder="Select Here Program" required>
                                        <option value="" ></option>
                                        <?php 
                                        $program = $conn->query("SELECT * FROM program_list where status = 1 order by name asc");
                                        while($row = $program->fetch_assoc()):
                                        ?>
                                        <option value="<?= $row['id'] ?>"><?= ucwords($row['name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <span class="text-navy"><small>Curriculum</small></span>
                                    <select name="curriculum_id" id="curriculum_id" class="form-control form-control-border select2" data-placeholder="Select Here Curriculum" required>
                                        <option value="" disabled selected>Select Program First</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <input type="email" name="email" id="email" placeholder="Email" class="form-control form-control-border" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <input type="password" name="password" id="password" placeholder="Password" class="form-control form-control-border" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <input type="password" id="cpassword" placeholder="Confirm Password" class="form-control form-control-border" required>
                                </div>
                            </div>
                        </div>
						
                        <div class="row">
							 <div class="col-6">
								<button><a href="<?php echo base_url ?>">Go Back</a></button>
							</div>
                            <div class="col-6">
                                <div class="form-group text-right">
                                    <button class="btn btn-default bg- btn-flat"> Register</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
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

<!-- Validation Script -->
<script>
    (function($) {
      'use strict';

      // Validate Email Format
      var validateEmail = function(email) {
        var emailReg = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        return emailReg.test(email);
      };

      // Password must be at least 8 characters, contain uppercase, lowercase, number, and special character
      var validatePassword = function(password) {
        var passwordReg = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
        return passwordReg.test(password);
      };

      // Check for invalid characters (single quotes)
      var hasInvalidChars = function(input) {
        return input.includes("'");
      };

      // Set custom validation message
      var setValidationMessage = function(input, message) {
        input.setCustomValidity(message);
        input.reportValidity();
      };

      $('#registration-form').on('submit', function(event) {
        var emailInput = $('#email')[0];
        var passwordInput = $('#password')[0];
        var confirmPasswordInput = $('#cpassword')[0];
        var email = emailInput.value;
        var password = passwordInput.value;
        var confirmPassword = confirmPasswordInput.value;

        // Reset custom validation messages
        emailInput.setCustomValidity("");
        passwordInput.setCustomValidity("");
        confirmPasswordInput.setCustomValidity("");

        // Validate email format
        if (!validateEmail(email)) {
          setValidationMessage(emailInput, "Invalid email format.");
          event.preventDefault();
          return;
        }

        // Validate password format
        if (!validatePassword(password)) {
          setValidationMessage(passwordInput, "Password must be at least 8 characters long and contain an uppercase letter, lowercase letter, number, and special character.");
          event.preventDefault();
          return;
        }

        // Check if passwords match
        if (password !== confirmPassword) {
          setValidationMessage(confirmPasswordInput, "Passwords do not match.");
          event.preventDefault();
          return;
        }

        // Check for invalid characters in email and password
        if (hasInvalidChars(email)) {
          setValidationMessage(emailInput, "Email must not contain single quotes.");
          event.preventDefault();
          return;
        }

        if (hasInvalidChars(password)) {
          setValidationMessage(passwordInput, "Password must not contain single quotes.");
          event.preventDefault();
          return;
        }
      });
    })(jQuery);

    $(document).ready(function(){
      end_loader();
      $('.select2').select2({
        width:"100%"
      });

      // Populate curriculum based on selected program
      var cur_arr = $.parseJSON('<?= json_encode($cur_arr) ?>');
      $('#program_id').change(function(){
        var did = $(this).val()
        $('#curriculum_id').html("");
        if(!!cur_arr[did]){
            Object.keys(cur_arr[did]).map(k=>{
                var opt = $("<option>")
                    opt.attr('value',cur_arr[did][k].id)
                    opt.text(cur_arr[did][k].name)
                $('#curriculum_id').append(opt)
            })
        }
        $('#curriculum_id').trigger("change");
      });
    });
</script>

</body>
</html>
