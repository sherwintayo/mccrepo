<?php
include('config.php');
require_once('inc/header.php');
?>
<!DOCTYPE html>
<html lang="en" class="" style="height: auto;">

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

    <div class="h-100 d-flex align-items-center w-100" id="login">
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
                        <form action="" id="registration-form" method="POST" novalidate>
                            <input type="hidden" name="id">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <input type="text" name="firstname" id="firstname" placeholder="Firstname"
                                            class="form-control form-control-border" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <input type="text" name="middlename" id="middlename"
                                            placeholder="Middlename (optional)"
                                            class="form-control form-control-border">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <input type="text" name="lastname" id="lastname" placeholder="Lastname"
                                            class="form-control form-control-border" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-auto">
                                    <div class="custom-control custom-radio">
                                        <input class="custom-control-input" type="radio" id="genderMale" name="gender"
                                            value="Male" required checked>
                                        <label for="genderMale" class="custom-control-label">Male</label>
                                    </div>
                                </div>
                                <div class="form-group col-auto">
                                    <div class="custom-control custom-radio">
                                        <input class="custom-control-input" type="radio" id="genderFemale" name="gender"
                                            value="Female">
                                        <label for="genderFemale" class="custom-control-label">Female</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <span class="text-navy"><small>Program</small></span>
                                        <select name="program_id" id="program_id"
                                            class="form-control form-control-border select2"
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
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <span class="text-navy"><small>Curriculum</small></span>
                                        <select name="curriculum_id" id="curriculum_id"
                                            class="form-control form-control-border select2"
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
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <input type="email" name="email" id="email" placeholder="Email"
                                            class="form-control form-control-border" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <input type="password" name="password" id="password" placeholder="Password"
                                            class="form-control form-control-border" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <input type="password" id="cpassword" placeholder="Confirm Password"
                                            class="form-control form-control-border" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <button><a href="<?php echo base_url ?>">Go Back</a></button>
                                </div>
                                <div class="col-6">
                                    <div class="form-group text-right">
                                        <button class="btn btn-default bg- btn-flat">Register</button>
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
    <!-- My Script -->
    <script src="<?php echo base_url ?>plugins/myScript.js"></script>

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