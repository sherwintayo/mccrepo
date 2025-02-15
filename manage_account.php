<?php
$user = $conn->query("SELECT s.*,d.name as program, c.name as curriculum,CONCAT(lastname,', ',firstname,' ',middlename) as fullname FROM student_list s inner join program_list d on s.program_id = d.id inner join curriculum_list c on s.curriculum_id = c.id where s.id ='{$_settings->userdata('id')}'");
foreach ($user->fetch_array() as $k => $v) {
    $$k = $v;
}
?>
<style>
    .student-img {
        object-fit: scale-down;
        object-position: center center;
        height: 200px;
        width: 200px;
    }
</style>
<div class="content py-4">
    <div class="card card-outline card-primary shadow rounded-0">
        <div class="card-header rounded-0">
            <h5 class="card-title">Update Details</h5>
        </div>
        <div class="card-body rounded-0">
            <div class="container-fluid">
                <form action="" id="update-form">
                    <input type="hidden" name="id" value="<?= $_settings->userdata('id') ?>">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="firstname" class="control-label text-navy">FirstName</label>
                                <input type="text" name="firstname" id="firstname" autofocus placeholder="Firstname"
                                    class="form-control form-control-border"
                                    value="<?= isset($firstname) ? $firstname : "" ?>" required>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="middlename" class="control-label text-navy">MiddleName</label>
                                <input type="text" name="middlename" id="middlename" placeholder="Middlename (optional)"
                                    class="form-control form-control-border"
                                    value="<?= isset($middlename) ? $middlename : "" ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="lastname" class="control-label text-navy">LastName</label>
                                <input type="text" name="lastname" id="lastname" placeholder="Lastname"
                                    class="form-control form-control-border"
                                    value="<?= isset($lastname) ? $lastname : "" ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-auto">
                            <label for="" class="control-label text-navy">Gender</label>
                        </div>
                        <div class="form-group col-auto">
                            <div class="custom-control custom-radio">
                                <input class="custom-control-input" type="radio" id="genderMale" name="gender"
                                    value="Male" required <?= isset($gender) && $gender == "Male" ? "checked" : "" ?>>
                                <label for="genderMale" class="custom-control-label">Male</label>
                            </div>
                        </div>
                        <div class="form-group col-auto">
                            <div class="custom-control custom-radio">
                                <input class="custom-control-input" type="radio" id="genderFemale" name="gender"
                                    value="Female" <?= isset($gender) && $gender == "Female" ? "checked" : "" ?>>
                                <label for="genderFemale" class="custom-control-label">Female</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="email" class="control-label text-navy">Email</label>
                                <input type="email" name="email" id="email" placeholder="Email"
                                    class="form-control form-control-border" required
                                    value="<?= isset($email) ? $email : "" ?>">
                            </div>
                            <div class="form-group">
                                <label for="password" class="control-label text-navy">New Password</label>
                                <input type="password" name="password" id="password" placeholder="Password"
                                    class="form-control form-control-border">
                            </div>

                            <div class="form-group">
                                <label for="cpassword" class="control-label text-navy">Confirm New Password</label>
                                <input type="password" id="cpassword" placeholder="Confirm Password"
                                    class="form-control form-control-border">
                            </div>
                            <small class='text-muted'>Leave the New Password and Confirm New Password Blank if you don't
                                wish to change your password.</small>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="img" class="control-label text-muted">Choose Image</label>
                                <input type="file" id="img" name="img" class="form-control form-control-border"
                                    accept="image/png,image/jpeg" onchange="displayImg(this,$(this))">
                            </div>

                            <div class="form-group text-center">
                                <img src="<?= validate_image(isset($avatar) ? $avatar : "") ?>" alt="My Avatar"
                                    id="cimg" class="img-fluid student-img bg-gradient-dark border">
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="oldpassword">Please Enter your Current Password</label>
                                <input type="password" name="oldpassword" id="oldpassword"
                                    placeholder="Current Password" class="form-control form-control-border" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group text-center">
                                <button class="btn btn-default bg-navy btn-flat"> Update</button>
                                <a href="./?page=profile" class="btn btn-light border btn-flat"> Cancel</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function displayImg(input, _this) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#cimg').attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        } else {
            $('#cimg').attr('src', "<?= validate_image(isset($avatar) ? $avatar : "") ?>");
        }
    }

    $(function () {
        // Update Form Submit
        $('#update-form').submit(function (e) {
            e.preventDefault();
            var _this = $(this);
            $('#password, #cpassword').removeClass("is-invalid");

            // Check if passwords match
            if ($("#password").val() !== $("#cpassword").val()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Password and Confirm Password do not match.'
                });
                $('#password, #cpassword').addClass("is-invalid");
                return false;
            }

            // Start Loader
            start_loader();

            // AJAX Request
            $.ajax({
                url: _base_url_ + "classes/Users.php?f=update_student",
                data: new FormData(_this[0]),
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                dataType: 'json',
                success: function (resp) {
                    end_loader();

                    // Handle Success or Failure
                    if (resp.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: resp.msg || 'Your details have been updated successfully.',
                        }).then(() => {
                            // Redirect to profile page
                            location.href = "./?page=profile";
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: resp.msg || 'An error occurred while updating your details.',
                        });
                    }
                },
                error: function (err) {
                    // Handle Unexpected Errors
                    console.error(err);
                    end_loader();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An unexpected error occurred. Please try again later.',
                    });
                }
            });
        });
    });
</script>