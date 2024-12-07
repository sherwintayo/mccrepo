<?php
include('config.php');
require_once('inc/header.php');

// Check if the token is provided
if (!isset($_GET['token'])) {
  header("Location: error.php?error=missing_token");
  exit();
}


$token = $_GET['token'];
$token_hash = hash('sha256', $token);

// Validate the token and retrieve the email
$stmt = $conn->prepare("SELECT id, username FROM msaccount WHERE reset_token_hash = ? AND reset_token_hash_expires_at > NOW()");
$stmt->bind_param('s', $token_hash);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
  // Invalid or expired token
  header("Location: error.php?error=invalid_token");
  exit();
}

// If valid, allow access
$stmt->bind_result($user_id, $email);
$stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en" class="" style="height: auto;">

<head>
  <title>MS Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
  <link rel="stylesheet" href="myStyles/registerstyle.css">
  <link rel="stylesheet" href="myStyles/registrationvalidation.css">
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
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://www.google.com/recaptcha/api.js?render=6LcvKpIqAAAAADbEzoBwvwKZ9r-loWJLfGIuPgKW"></script>


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


                  <!-- Email (Read-Only) -->
                  <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>"
                      class="form-control" readonly>
                  </div>
                  <!-- Password strength meter -->
                  <div id="password-strength-container" class="password-strength mt-2" style="display: none;">
                    <div class="progress">
                      <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%;"
                        aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small id="password-strength-text" class="text-muted">Enter a strong password.</small>
                  </div>
                  <div class="row">
                    <div class="col-md-6 form-group position-relative pass-field">
                      <input type="password" name="password" id="password" placeholder="Password"
                        class="form-control form-control-border" required>
                      <span class="toggle-password" onclick="toggleVisibility('password')">
                        <i class="fa fa-eye" id="eye-password"></i>
                      </span>
                    </div>
                    <div class="col-md-6 form-group position-relative pass-field">
                      <input type="password" id="cpassword" placeholder="Confirm Password"
                        class="form-control form-control-border" required>
                      <span class="toggle-password" onclick="toggleVisibility('cpassword')">
                        <i class="fa fa-eye" id="eye-cpassword"></i>
                      </span>
                    </div>
                  </div>

                  <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                  <!-- Terms and Conditions -->
                  <div class="form-group">
                    <input type="checkbox" id="terms" name="terms">
                    <label for="terms">
                      I have read and agree to the <a href="#" data-toggle="modal" data-target="#termsModal">Terms and
                        Conditions</a>.
                    </label>
                  </div>

                  <div class="row">
                    <div class="content hidden">
                      <small>
                        <p>Password must contains</p>
                      </small>
                      <ul class="requirement-list d-flex flex-wrap">
                        <!-- Left Column -->
                        <div class="left-column">
                          <li>
                            <i class="fa-solid fa-circle"></i>
                            <small><span>At least 8 characters length</span></small>
                          </li>
                          <li>
                            <i class="fa-solid fa-circle"></i>
                            <small><span>At least 1 number (0...9)</span></small>
                          </li>
                        </div>
                        <!-- Right Column -->
                        <div class="right-column ml-3">
                          <li>
                            <i class="fa-solid fa-circle"></i>
                            <small><span>At least 1 lowercase letter (a...z)</span></small>
                          </li>
                          <li>
                            <i class="fa-solid fa-circle"></i>
                            <small><span>At least 1 special symbol (!...$)</span></small>
                          </li>
                          <li>
                            <i class="fa-solid fa-circle"></i>
                            <small><span>At least 1 uppercase letter (A...Z)</span></small>
                          </li>
                        </div>
                      </ul>
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

  <!-- Terms and Conditions Modal -->
  <div class="modal fade" id="termsModal" tabindex="-5" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="termsModalLabel">Terms and Conditions</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <h5>Welcome to our platform. By registering, you agree to the following terms:</h5>
          <ul>
            <li>Do not upload prohibited content.</li>
            <li>Respect intellectual property rights.</li>
            <li>Your data will be handled responsibly.</li>
            <li>Ensure that the information you provide is accurate and up to date.</li>
            <li>You are solely responsible for maintaining the confidentiality of your account credentials.</li>
            <li>Any misuse of the platform may lead to account suspension or termination.</li>
            <li>You agree not to engage in any activity that disrupts or interferes with the platform's functionality.
            </li>
            <li>We reserve the right to update these terms at any time without prior notice.</li>
            <li>All disputes arising from the use of the platform will be governed by the applicable laws of [your
              country/region].</li>
            <li>Failure to comply with these terms may result in permanent loss of account access.</li>
            <li>You consent to receive notifications and updates related to your account via email.</li>
            <li>You agree not to share your account with others or allow unauthorized access.</li>
          </ul>
          <p>Please read and understand these terms carefully. By proceeding with registration, you accept and agree to
            these conditions.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>


  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
  <script src="<?php echo base_url ?>myScripts/passvalidation.js"></script>


  <script>
    const cur_arr = $.parseJSON('<?= json_encode($cur_arr) ?>');
    $(document).ready(function () {
      end_loader();
      $('.select2').select2({
        width: "100%"
      });

      $('#program_id').change(function () {
        const did = $(this).val();
        $('#curriculum_id').html("");
        if (!!cur_arr[did]) {
          Object.keys(cur_arr[did]).map(k => {
            const opt = $("<option>");
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

        if (!$('#terms').is(':checked')) {
          Swal.fire({
            icon: 'error',
            title: 'Terms and Conditions',
            text: 'You must agree to the Terms and Conditions to register.',
          });
          e.preventDefault(); // Prevent form submission
          return false;
        }

        const _this = $(this);
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

        // Check if all password rules are met
        if (!isPasswordValid()) {
          Swal.fire({
            icon: 'error',
            title: 'Weak Password',
            text: 'Please ensure your password meets all the requirements.'
          });
          $('#password').addClass("is-invalid");
          return false;
        }


        const form = $(this);
        // Request reCAPTCHA v3 token
        grecaptcha.execute('6LcvKpIqAAAAADbEzoBwvwKZ9r-loWJLfGIuPgKW', { action: 'register' }).then(function (token) {
          form.find('input[name="g-recaptcha-response"]').val(token); // Append reCAPTCHA token

          start_loader();
          // AJAX submission
          $.ajax({
            url: _base_url_ + "classes/Users.php?f=save_student",
            method: 'POST',
            data: form.serialize(),
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
                  window.location.href = "./login"; // Redirect on success
                });
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Registration Failed',
                  text: resp.msg || 'An unknown error occurred. Please try again later.'
                });
              }
            },
            error: function (xhr, status, error) {
              end_loader();
              console.error(xhr.responseText);
              Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: 'An error occurred while processing your request. Please try again later.',
                footer: `<pre>${xhr.responseText}</pre>` // Show raw response
              });
            }
          });

        });
      });

      // Function to check for invalid characters
      const hasInvalidChars = function (input) {
        return /['"<script>]/.test(input); // Prevents single quotes, double quotes, and angle brackets
      };

      // Validate Email Format (Ensure @ symbol is present)
      const validateEmail = function (email) {
        const emailReg = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        return emailReg.test(email);
      };

      // Set custom validation message for inputs
      const setValidationMessage = function (input, message) {
        input.setCustomValidity(message);
        input.reportValidity();
      };
    });

  </script>
</body>

</html>