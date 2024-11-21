<?php
include('config.php');
require_once('inc/header.php');
?>
<!DOCTYPE html>
<html lang="en" class="" style="height: auto;">

<head>
  <title>MS Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="myStyles/registerstyle.css">
</head>

<style>
  /* html,
  body {
    height: calc(100%) !important;
    width: calc(100%) !important;
  } */

  body {
    background-image: url("dist/img/background.png");
    background-size: cover;
    background-repeat: no-repeat;
  }

  /* Add CSS for the eye icon */
  .position-relative {
    position: relative;
  }

  .toggle-password {
    position: absolute;
    top: 50%;
    right: 20px;
    transform: translateY(-50%);
    cursor: pointer;
    color: #0e0c0c;
  }

  .toggle-password:hover {
    color: #000;
  }

  /*Stong Password*/
  .password-strength .progress {
    height: 8px;
    border-radius: 5px;
  }

  .password-strength .progress-bar {
    transition: width 0.5s ease-in-out;
  }

  .progress-bar.weak {
    background-color: red;
  }

  .progress-bar.moderate {
    background-color: orange;
  }

  .progress-bar.good {
    background-color: yellow;
  }

  .progress-bar.strong {
    background-color: green;
  }

  /*Stong Password Validation*/

  #password-validation li {
    font-size: 0.9em;
  }

  #password-validation li.valid {
    color: green;
  }

  #password-validation li.invalid {
    color: red;
  }

  /* Add animation for smooth fading in */
  #password-validation {
    opacity: 0;
    /* Initially hidden */
    transform: translateY(-10px);
    /* Slight upward shift */
    transition: opacity 0.3s ease, transform 0.3s ease;
    /* Smooth transition */
  }

  #password-validation.show {
    opacity: 1;
    /* Fully visible */
    transform: translateY(0);
    /* Reset position */
  }

  /* Add slight bounce effect for individual list items when valid */
  @keyframes bounceIn {
    0% {
      transform: scale(0.8);
      opacity: 0.5;
    }

    50% {
      transform: scale(1.1);
      opacity: 1;
    }

    100% {
      transform: scale(1);
    }
  }

  #password-validation li.valid {
    animation: bounceIn 0.3s ease;
    /* Bounce effect when valid */
  }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>


<body class="hold-transition ">
  <script>
    start_loader();
  </script>

  <div class="container register">
    <div class="row">
      <div class="col-md-3 register-left">
        <img src="<?= validate_image($_settings->info('logo')) ?>" alt="Logo" style="margin-top: 20vh;" />
        <h3>Welcome</h3>
        <p>Register and start your journey!</p>

      </div>
      <div class="col-md-9 register-right">
        <div class="tab-content" id="myTabContent">
          <!-- Student Registration Tab -->
          <div class="tab-pane fade show active" id="student" role="tabpanel" aria-labelledby="student-tab">
            <h3 class="register-heading">Student Registration</h3>
            <div class="row register-form">
              <div class="col-md-12">
                <form action="" id="registration-form" method="POST" novalidate>
                  <input type="hidden" name="id">

                  <!-- Name Fields -->
                  <div class="row">
                    <div class="col-md-6 form-group">
                      <input type="text" name="firstname" id="firstname" placeholder="Firstname"
                        class="form-control form-control-border" required>
                    </div>
                    <div class="col-md-6 form-group">
                      <input type="text" name="middlename" id="middlename" placeholder="Middlename (optional)"
                        class="form-control form-control-border">
                    </div>
                    <div class="col-md-6 form-group">
                      <input type="text" name="lastname" id="lastname" placeholder="Lastname"
                        class="form-control form-control-border" required>
                    </div>
                  </div>

                  <!-- Gender -->
                  <div class="row">
                    <div class="form-group col-auto">
                      <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="genderMale" name="gender" value="Male"
                          required checked>
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

                  <!-- Program and Curriculum -->
                  <div class="form-group">
                    <span class="text-navy"><small>Program</small></span>
                    <select name="program_id" id="program_id" class="form-control form-control-border select2"
                      data-placeholder="Select Here Program" required>
                      <option value=""></option>
                      <?php
                      $program = $conn->query("SELECT * FROM program_list WHERE status = 1 ORDER BY name ASC");
                      while ($row = $program->fetch_assoc()):
                        ?>
                        <option value="<?= $row['id'] ?>"><?= ucwords($row['name']) ?></option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <span class="text-navy"><small>Curriculum</small></span>
                    <select name="curriculum_id" id="curriculum_id" class="form-control form-control-border select2"
                      data-placeholder="Select Here Curriculum" required>
                      <option value="" disabled selected>Select Program First</option>
                      <?php
                      $curriculum = $conn->query("SELECT * FROM curriculum_list WHERE status = 1 ORDER BY name ASC");
                      $cur_arr = [];
                      while ($row = $curriculum->fetch_assoc()) {
                        $row['name'] = ucwords($row['name']);
                        $cur_arr[$row['program_id']][] = $row;
                      }
                      ?>
                    </select>
                  </div>

                  <!-- Email and Password -->
                  <div class="form-group">
                    <input type="email" name="email" id="email" placeholder="Email"
                      class="form-control form-control-border" required>
                  </div>
                  <!-- Password strength meter -->
                  <div class="form-group">
                    <div class="password-strength mt-2" style="display: none;">
                      <div class="progress">
                        <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%;"
                          aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <small id="password-strength-text" class="text-muted">Enter a strong password.</small>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6 form-group position-relative">
                      <input type="password" name="password" id="password" placeholder="Password"
                        class="form-control form-control-border" required>
                      <span class="toggle-password" onclick="toggleVisibility('password')">
                        <i class="fa fa-eye" id="eye-password"></i>
                      </span>
                    </div>
                    <div class="col-md-6 form-group position-relative">
                      <input type="password" id="cpassword" placeholder="Confirm Password"
                        class="form-control form-control-border" required>
                      <span class="toggle-password" onclick="toggleVisibility('cpassword')">
                        <i class="fa fa-eye" id="eye-cpassword"></i>
                      </span>
                    </div>
                    <ul id="password-validation" class="list-unstyled mt-2" style="display: none;">
                      <li id="min-length" class="text-muted">❌ At least 8 characters</li>
                      <li id="uppercase" class="text-muted">❌ At least one uppercase letter</li>
                      <li id="lowercase" class="text-muted">❌ At least one lowercase letter</li>
                      <li id="number" class="text-muted">❌ At least one number</li>
                      <li id="special-char" class="text-muted">❌ At least one special character</li>
                    </ul>

                  </div>



                  <div class="form-group">
                    <div class="g-recaptcha" data-sitekey="6LdkGoUqAAAAAEmIB2Py685bbQiALvcZ3a4MOjDx"></div>
                  </div>

                  <!-- Buttons -->
                  <div class="row">
                    <div class="col-md-6">
                      <a href="<?php echo base_url ?>" class="btn btn-light">Go Back</a>
                    </div>
                    <div class="col-md-6 text-right">
                      <button type="submit" class="btn btn-primary">Register</button>
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

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- jQuery -->
  <script src="plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- AdminLTE App -->
  <script src="dist/js/adminlte.min.js"></script>
  <!-- Select2 -->
  <script src="<?php echo base_url ?>plugins/select2/js/select2.full.min.js"></script>
  <!-- My Script -->
  <script src="<?php echo base_url ?>plugins/myScript.js"></script>
  <script src="<?php echo base_url ?>myScripts/strongpass.js"></script>


  <script>
    var cur_arr = $.parseJSON('<?= json_encode($cur_arr) ?>');
    $(document).ready(function () {
      end_loader();
      $('.select2').select2({
        width: "100%"
      });

      $('#program_id').change(function () {
        var did = $(this).val();
        $('#curriculum_id').html("");
        if (!!cur_arr[did]) {
          Object.keys(cur_arr[did]).map(k => {
            var opt = $("<option>");
            opt.attr('value', cur_arr[did][k].id);
            opt.text(cur_arr[did][k].name);
            $('#curriculum_id').append(opt);
          });
        }
        $('#curriculum_id').trigger("change");
      });

      // Registration Form Submit with Validations
      $('#registration-form').submit(function (e) {
        e.preventDefault();

        // Get reCAPTCHA response token
        let recaptchaResponse = grecaptcha.getResponse();

        if (!recaptchaResponse) {
          Swal.fire({
            icon: 'error',
            title: 'reCAPTCHA Error',
            text: 'Please complete the reCAPTCHA challenge.'
          });
          return false;
        }

        // Add reCAPTCHA response to form data
        let formData = $(this).serialize() + "&g-recaptcha-response=" + recaptchaResponse;

        var _this = $(this);
        $(".pop-msg").remove();
        $('#password, #cpassword').removeClass("is-invalid");

        // Password match validation
        if ($("#password").val() !== $("#cpassword").val()) {
          Swal.fire({
            icon: 'error',
            title: 'Password Mismatch',
            text: 'Password and Confirm Password do not match.'
          });
          $('#password, #cpassword').addClass("is-invalid");
          return false;
        }

        start_loader();
        // AJAX submission
        $.ajax({
          url: _base_url_ + "classes/Users.php?f=save_student",
          method: 'POST',
          data: formData,
          dataType: 'json',
          success: function (resp) {
            end_loader(); // Hide loader

            if (resp.status === 'success') {
              Swal.fire({
                icon: 'success',
                title: 'Registration Successful',
                text: 'Your account has been successfully created.',
                confirmButtonText: 'Go to Login'
              }).then(() => {
                window.location.href = "./login.php"; // Redirect on success
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Registration Failed',
                text: resp.msg || 'An unknown error occurred. Please try again later.'
              });
              grecaptcha.reset(); // Reset reCAPTCHA for another attempt
            }
          },
          error: function (xhr, status, error) {
            end_loader(); // Hide loader

            // Log the response for debugging
            console.error(xhr.responseText);

            Swal.fire({
              icon: 'error',
              title: 'Server Error',
              text: 'An error occurred while processing your request. Please try again later.',
              footer: `<pre>${xhr.responseText}</pre>` // Show raw response
            });
            grecaptcha.reset(); // Reset reCAPTCHA for another attempt
          }
        });

      });


      // Function to check for invalid characters
      var hasInvalidChars = function (input) {
        return /['"<script>]/.test(input); // Prevents single quotes, double quotes, and angle brackets
      };

      // Validate Email Format (Ensure @ symbol is present)
      var validateEmail = function (email) {
        var emailReg = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        return emailReg.test(email);
      };

      // Set custom validation message for inputs
      var setValidationMessage = function (input, message) {
        input.setCustomValidity(message);
        input.reportValidity();
      };
    });

  </script>
</body>

</html>