<head>

  <link rel="stylesheet" href="<?php echo base_url ?>myStyles/stdntprof_style.css?v=<?php echo time(); ?>">
  <style>
    .header__wrapper header {
      width: 100%;
      background: url("<?php echo validate_image($_settings->info('cover')) ?>") no-repeat 50% 20% / cover;
      min-height: calc(100px + 15vw);
    }

    /* Add page visibility control */
    .page {
      display: none;
    }

    .page.active {
      display: block;
    }
  </style>
</head>
<?php require_once('./config.php'); ?>
<?php
$user = $conn->query("
    SELECT 
        s.*, 
        d.name AS program, 
        c.name AS curriculum, 
        CONCAT(s.lastname, ', ', s.firstname, ' ', s.middlename) AS fullname, 
        a.*, 
        COUNT(a.id) AS total_projects,
        SUM(CASE WHEN a.status = 1 THEN 1 ELSE 0 END) AS total_published,
        SUM(CASE WHEN a.status = 0 THEN 1 ELSE 0 END) AS total_unpublished
    FROM student_list s
    INNER JOIN program_list d ON s.program_id = d.id
    INNER JOIN curriculum_list c ON s.curriculum_id = c.id
    LEFT JOIN archive_list a ON a.student_id = s.id
    WHERE s.id = '{$_settings->userdata('id')}'
    GROUP BY s.id
");

foreach ($user->fetch_array() as $k => $v) {
  $$k = $v;
}

// Fetch project archives data for the "my archives" section
$archives = [];
$qry = $conn->query("SELECT * FROM archive_list WHERE student_id = '{$_settings->userdata('id')}' ORDER BY unix_timestamp(date_created) ASC");
while ($row = $qry->fetch_assoc()) {
  $archives[] = $row;
}
?>

<body>
  <div class="header__wrapper">
    <header></header>
    <div class="cols__container">
      <div class="left__col">
        <div class="img__container">
          <img src="<?= validate_image($avatar) ?>" alt="Student Image" />
          <span></span>
        </div>
        <h2><?= ucwords($fullname) ?></h2>
        <p>Programmer</p>
        <p><?= $email ?></p>

        <ul class="about">
          <li><span><?= $total_projects ?></span>Projects</li>
          <li><span><?= $total_published ?></span>Published</li>
          <li><span><?= $total_unpublished ?></span>Unpublished</li>
        </ul>

        <div class="content">
          <ul>
            <li><i class="fab fa-twitter"></i></li>
            <i class="fab fa-pinterest"></i>
            <i class="fab fa-facebook"></i>
            <i class="fab fa-dribbble"></i>
          </ul>
        </div>
      </div>
      <div class="right__col">
        <nav>
          <ul>
            <li><a href="#" class="nav-link active" onclick="setActive(this, 'my_archives')">my archives</a></li>
            <li><a href="#" class="nav-link" onclick="setActive(this, 'notifications')">notifications</a></li>
            <li><a href="#" class="nav-link" onclick="setActive(this, 'account_settings')">account settings</a></li>
          </ul>
          <div id="uploadArea">
            <button id="uploadArchiveBtn" class="btn btn-primary d-flex align-items-center"
              onclick="redirectToSubmitArchive()">
              <i class="fa fa-upload mr-2"></i> Upload Archive
            </button>
          </div>
        </nav>

        <!-- Default page content (my_archives) -->
        <div id="my_archives" class="page active">
          <div class="card-deck d-flex flex-wrap">
            <?php foreach ($archives as $archive): ?>
              <?php
              $statusLabel = $archive['status'] == 1 ? 'Published' : 'Unpublished';
              $statusClass = $archive['status'] == 1 ? 'badge-success' : 'badge-secondary';
              ?>
              <div class="card shadow-sm border-light m-2" style="width: 18rem;">
                <img
                  src="<?= $archive['banner_path'] ? base_url . $archive['banner_path'] : '/dist/img/no-image-available.png'; ?>"
                  class="card-img-top" alt="Project Banner" style="height: 180px; object-fit: cover;">
                <div class="card-body">
                  <h5 class="card-title"><?= htmlspecialchars($archive['title']); ?></h5>
                  <p class="card-text">Archive Code: <?= htmlspecialchars($archive['archive_code']); ?></p>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                  <span class="badge <?= $statusClass; ?>"><?= $statusLabel; ?></span>
                  <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown"
                      aria-haspopup="true" aria-expanded="false">
                      Actions
                    </button>
                    <div class="dropdown-menu">
                      <a class="dropdown-item" href="<?= base_url ?>/?page=view_archive&id=<?= $archive['id']; ?>"
                        target="_blank">
                        <i class="fa fa-external-link-alt text-gray"></i> View
                      </a>
                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item delete_data" href="javascript:void(0)" data-id="<?= $archive['id']; ?>">
                        <i class="fa fa-trash text-danger"></i> Delete
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>





        <div id="notifications" class="page">
          <h2>Notifications</h2>
          <p>You have no new notifications.</p>
        </div>

        <div id="account_settings" class="page">
          <div class="content py-4">
            <div class="card card-outline card-primary shadow rounded-0">
              <div class="card-header rounded-0">
                <h5 class="card-title">Update Details</h5>
              </div>
              <div class="card-body rounded-0">
                <div class="container-fluid">
                  <form action="" id="update-form">
                    <input type="hidden" name="id" value="<?= $_settings->userdata('id') ?>">

                    <!-- Profile Image -->
                    <div class="row mb-4 text-center">
                      <div class="col-lg-12">
                        <div class="form-group text-center">
                          <img src="<?= validate_image(isset($avatar) ? $avatar : "") ?>" alt="My Avatar" id="cimg"
                            class="img-fluid student-img bg-gradient-dark border">
                        </div>
                        <div class="form-group">
                          <label for="img" class="control-label text-muted">Choose Image</label>
                          <input type="file" id="img" name="img" class="form-control form-control-border"
                            accept="image/png,image/jpeg" onchange="displayImg(this, $(this))">
                        </div>
                      </div>
                    </div>

                    <!-- User Details -->
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="form-group">
                          <label for="firstname" class="control-label text-navy">First Name</label>
                          <input type="text" name="firstname" id="firstname" placeholder="First Name"
                            class="form-control form-control-border" value="<?= isset($firstname) ? $firstname : "" ?>"
                            required>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="form-group">
                          <label for="middlename" class="control-label text-navy">Middle Name</label>
                          <input type="text" name="middlename" id="middlename" placeholder="Middle Name (optional)"
                            class="form-control form-control-border"
                            value="<?= isset($middlename) ? $middlename : "" ?>">
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="form-group">
                          <label for="lastname" class="control-label text-navy">Last Name</label>
                          <input type="text" name="lastname" id="lastname" placeholder="Last Name"
                            class="form-control form-control-border" value="<?= isset($lastname) ? $lastname : "" ?>"
                            required>
                        </div>
                      </div>
                    </div>

                    <!-- Gender -->
                    <div class="row">
                      <div class="form-group col-auto">
                        <label class="control-label text-navy">Gender</label>
                      </div>
                      <div class="form-group col-auto">
                        <div class="custom-control custom-radio">
                          <input class="custom-control-input" type="radio" id="genderMale" name="gender" value="Male"
                            required <?= isset($gender) && $gender == "Male" ? "checked" : "" ?>>
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

                    <!-- Email and Password -->
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
                          <input type="password" name="password" id="password" placeholder="New Password"
                            class="form-control form-control-border">
                        </div>
                        <div class="form-group">
                          <label for="cpassword" class="control-label text-navy">Confirm New Password</label>
                          <input type="password" id="cpassword" placeholder="Confirm New Password"
                            class="form-control form-control-border">
                        </div>
                        <small class="text-muted">Leave the New Password and Confirm Password blank if you do not wish
                          to change
                          your password.</small>
                      </div>
                    </div>

                    <!-- Current Password -->
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="form-group">
                          <label for="oldpassword" class="control-label text-navy">Current Password</label>
                          <input type="password" name="oldpassword" id="oldpassword" placeholder="Current Password"
                            class="form-control form-control-border" required>
                        </div>
                      </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="row">
                      <div class="col-lg-12 text-center">
                        <div class="form-group">
                          <button class="btn btn-default bg-navy btn-flat">Update</button>
                          <a href="./?page=profile" class="btn btn-light border btn-flat">Cancel</a>
                        </div>
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
  </div>

  </div>
  </div>
  </div>

  <script>
    // Set the active page and show its content
    function setActive(link, pageId) {
      document.querySelectorAll('.nav-link').forEach(nav => nav.classList.remove('active'));
      document.querySelectorAll('.page').forEach(page => page.classList.remove('active'));

      link.classList.add('active');
      document.getElementById(pageId).classList.add('active');
    }


    $(function () {
      // Trigger delete confirmation with SweetAlert
      $('.delete_data').click(function () {
        const id = $(this).data('id'); // Get the ID of the item to delete

        Swal.fire({
          title: 'Are you sure?',
          text: "You won't be able to revert this!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#dc3545',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Yes, delete it!',
          cancelButtonText: 'Cancel'
        }).then((result) => {
          if (result.isConfirmed) {
            delete_archive(id); // Call delete function if confirmed
          }
        });
      });
    });

    // Delete archive function
    function delete_archive(id) {
      $.ajax({
        url: _base_url_ + "classes/Master.php?f=delete_archive",
        method: "POST",
        data: { id },
        dataType: "json",
        error: function (err) {
          console.error(err);
          Swal.fire('Error', 'An error occurred while deleting the archive.', 'error');

        },
        success: function (response) {
          if (response.status === 'success') {
            Swal.fire('Deleted!', 'Your archive has been deleted.', 'success').then(() => {
              location.reload(); // Reload to update the page
            });
          } else {
            Swal.fire('Failed', 'Failed to delete the archive.', 'error');
          }
        }
      });
    }

    // Redirect to submit-archive page
    function redirectToSubmitArchive() {
      window.location.href = './?page=submit-archive';
    }
  </script>

  <script>
    // Display the selected image
    function displayImg(input, _this) {
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
          $('#cimg').attr('src', e.target.result);
        };
        reader.readAsDataURL(input.files[0]);
      } else {
        $('#cimg').attr('src', "<?= validate_image(isset($avatar) ? $avatar : '') ?>");
      }
    }

    $(function () {
      // Handle form submission
      $('#update-form').submit(function (e) {
        e.preventDefault();
        const _this = $(this);
        $('#password, #cpassword').removeClass('is-invalid');

        // Validate password match
        if ($('#password').val() !== $('#cpassword').val()) {
          Swal.fire('Error', 'Passwords do not match.', 'error');
          $('#password, #cpassword').addClass('is-invalid');
          return false;
        }

        start_loader();
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
            if (resp.status === 'success') {
              Swal.fire('Success', 'Your details have been updated.', 'success').then(() => {
                location.reload();
              });
            } else {
              Swal.fire('Error', resp.msg || 'An error occurred.', 'error');
            }
          },
          error: function (err) {
            end_loader();
            Swal.fire('Error', 'An unexpected error occurred.', 'error');
          }
        });
      });
    });
  </script>

</body>