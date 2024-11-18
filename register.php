<?php
include('config.php');
require_once('inc/header.php');
?>
<!DOCTYPE html>
<html lang="en" class="" style="height: auto;">

<head>
    <title>Registration Form</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="dist/css/registerstyle.css">
</head>

<body class="hold-transition ">
    <script>
        start_loader();
    </script>
    <style>
        html,
        body {
            height: calc(100%) !important;
            width: calc(100%) !important;
        }

        body {
            background-image: url("<?php echo validate_image($_settings->info('cover')) ?>");
            background-size: cover;
            background-repeat: no-repeat;
        }

        .login-title {
            text-shadow: 2px 2px black;
        }

        #login {
            direction: rtl;
        }

        #login>* {
            direction: ltr;
        }

        #logo-img {
            height: 150px;
            width: 150px;
            object-fit: scale-down;
            object-position: center center;
            border-radius: 100%;
        }
    </style>

    <div class="container register">
        <div class="row">
            <!-- Left Section -->
            <div class="col-md-3 register-left">
                <img src="<?= validate_image($_settings->info('logo')) ?>" alt="Logo" />
                <h3>Welcome</h3>
                <p>Register and start your journey!</p>
                <a href="<?php echo base_url ?>" class="btn btn-light">Go Back</a><br />
            </div>

            <!-- Right Section -->
            <div class="col-md-9 register-right">
                <ul class="nav nav-tabs nav-justified" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="student-tab" data-toggle="tab" href="#student" role="tab"
                            aria-controls="student" aria-selected="true">Student</a>
                    </li>
                </ul>

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
                                            <input type="text" name="firstname" id="firstname"
                                                placeholder="First Name *" class="form-control" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <input type="text" name="middlename" id="middlename"
                                                placeholder="Middle Name (optional)" class="form-control">
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <input type="text" name="lastname" id="lastname" placeholder="Last Name *"
                                                class="form-control" required>
                                        </div>
                                    </div>

                                    <!-- Gender -->
                                    <div class="form-group">
                                        <label>Gender:</label><br />
                                        <label class="radio inline padding-right-10">
                                            <input type="radio" name="gender" value="Male" checked> <span>Male</span>
                                        </label>
                                        <label class="radio inline">
                                            <input type="radio" name="gender" value="Female"> <span>Female</span>
                                        </label>
                                    </div>

                                    <!-- Program and Curriculum -->
                                    <div class="form-group">
                                        <label for="program_id">Program</label>
                                        <select name="program_id" id="program_id" class="form-control select2" required>
                                            <option value="" disabled selected>Select Program</option>
                                            <?php
                                            $program = $conn->query("SELECT * FROM program_list WHERE status = 1 ORDER BY name ASC");
                                            while ($row = $program->fetch_assoc()):
                                                ?>
                                                <option value="<?= $row['id'] ?>"><?= ucwords($row['name']) ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="curriculum_id">Curriculum</label>
                                        <select name="curriculum_id" id="curriculum_id" class="form-control select2"
                                            required>
                                            <option value="" disabled selected>Select Curriculum</option>
                                        </select>
                                    </div>

                                    <!-- Email and Password -->
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <input type="email" name="email" id="email" placeholder="Email *"
                                                class="form-control" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <input type="password" name="password" id="password"
                                                placeholder="Password *" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <input type="password" id="cpassword" placeholder="Confirm Password *"
                                            class="form-control" required>
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

    <script>
        var cur_arr = $.parseJSON('<?= json_encode($cur_arr) ?>');
        $(document).ready(function () {
            end_loader();
            $('.select2').select2({
                width: "100%"
            });

            // Update Curriculum Options on Program Change
            $('#program_id').change(function () {
                const did = $(this).val();
                $('#curriculum_id').html('<option value="" disabled selected>Select Curriculum</option>');
                if (!!cur_arr[did]) {
                    Object.keys(cur_arr[did]).map(k => {
                        const opt = $("<option>");
                        opt.attr('value', cur_arr[did][k].id);
                        opt.text(cur_arr[did][k].name);
                        $('#curriculum_id').append(opt);
                    });
                }
            });

            // Registration Form Submit with Validations
            $('#registration-form').submit(function (e) {
                e.preventDefault();
                var _this = $(this);
                $(".pop-msg").remove();
                $('#password, #cpassword').removeClass("is-invalid");

                var el = $("<div>");
                el.addClass("alert pop-msg my-2");
                el.hide();

                // Password match check
                if ($("#password").val() !== $("#cpassword").val()) {
                    el.addClass("alert-danger");
                    el.text("Password does not match.");
                    $('#password, #cpassword').addClass("is-invalid");
                    $('#cpassword').after(el);
                    el.show('slow');
                    return false;
                }

                // XSS and Validation Checks for invalid characters and email
                var hasError = false;
                _this.find('input[type="text"], input[type="email"], input[type="password"]').each(function () {
                    var input = $(this);
                    var value = input.val();

                    // Check for invalid characters (' and ") and for angle brackets (< and >)
                    if (hasInvalidChars(value)) {
                        setValidationMessage(this, "Input must not contain single quotes, double quotes, or angle brackets.");
                        hasError = true;
                        return false; // Exit loop
                    } else {
                        setValidationMessage(this, ""); // Clear custom validity if no error
                    }

                    // Validate email input
                    if (input.attr('type') === 'email' && !validateEmail(value)) {
                        setValidationMessage(this, "Please include an '@' in the email address.");
                        hasError = true;
                        return false; // Exit loop
                    }
                });

                if (hasError) {
                    return false; // Prevent form submission if any input has an error
                }

                start_loader();
                $.ajax({
                    url: _base_url_ + "classes/Users.php?f=save_student",
                    method: 'POST',
                    data: _this.serialize(),
                    dataType: 'json',
                    error: err => {
                        console.log(err);
                        el.text("An error occurred while saving the data.");
                        el.addClass("alert-danger");
                        _this.prepend(el);
                        el.show('slow');
                        end_loader();
                    },
                    success: function (resp) {
                        if (resp.status === 'success') {
                            location.href = "./login.php";
                        } else if (!!resp.msg) {
                            el.text(resp.msg);
                            el.addClass("alert-danger");
                            _this.prepend(el);
                            el.show('slow');
                        } else {
                            el.text("An error occurred while saving the data.");
                            el.addClass("alert-danger");
                            _this.prepend(el);
                            el.show('slow');
                        }
                        end_loader();
                        $('html, body').animate({ scrollTop: 0 }, 'fast');
                    }
                });
            });

            // Function to check for invalid characters
            var hasInvalidChars = function (input) {
                return /['"<>]/.test(input); // Prevents single quotes, double quotes, and angle brackets
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