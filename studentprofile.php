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

    /* Center the confirmation dialog */
    .confirm-modal {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 1050;
      background: white;
      border: 1px solid #ccc;
      border-radius: 5px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      width: 400px;
      max-width: 90%;
      text-align: center;
      padding: 20px;
    }

    .confirm-modal h5 {
      margin: 0 0 15px;
    }

    .confirm-modal .btn-container {
      display: flex;
      justify-content: space-around;
      margin-top: 20px;
    }

    .confirm-modal .btn {
      padding: 8px 15px;
      border-radius: 4px;
      border: none;
      cursor: pointer;
    }

    .confirm-modal .btn-confirm {
      background-color: #dc3545;
      color: white;
    }

    .confirm-modal .btn-cancel {
      background-color: #6c757d;
      color: white;
    }

    /* Modal backdrop */
    .modal-backdrop {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1049;
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
$qry = $conn->query("SELECT * FROM `archive_list` WHERE student_id = '{$_settings->userdata('id')}' ORDER BY unix_timestamp(`date_created`) ASC");
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
          <h2>My Submitted Projects</h2>
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
          <h2>Account Settings</h2>
          <p>Manage your account preferences here.</p>
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

    $(document).ready(function () {
      const archiveFormData = localStorage.getItem('archiveFormData');

      if (archiveFormData) {
        // Replace "Upload Archive" button with progress bar and file input UI
        $("#uploadArea").html(`
            <form id="uploadForm">
                <div class="container">
                    <h5>Files Required for Submission</h5>
                    <label>Project Image:</label>
                    <input type="file" name="img" accept="image/png, image/jpeg, image/jpg" required><br>
                    <label>Project Document (PDF):</label>
                    <input type="file" name="pdf" accept=".pdf" required><br>
                    <label>Multiple Files (ZIP):</label>
                    <input type="file" name="zipfiles[]" multiple accept=".zip" required><br>
                    <label>SQL File:</label>
                    <input type="file" name="sql" accept=".sql" required><br>
                </div>

                <div class="container mt-3">
                    <span id="percent">0%</span>
                    <div class="progress">
                        <div class="progress-bar"></div>
                    </div>
                    <span id="dataTransferred">0/0 MB</span>
                    <span id="Mbps">0 Mbps</span>
                    <span id="timeLeft">Calculating...</span>
                </div>

                <button type="submit" class="btn btn-success mt-3">Start Upload</button>
            </form>
        `);

        // Start upload process on form submission
        $('#uploadForm').submit(function (e) {
          e.preventDefault();

          const startTime = new Date().getTime();
          const formData = new FormData(this);

          // Append restored data from localStorage
          const restoredData = JSON.parse(archiveFormData);
          for (const key in restoredData) {
            formData.append(key, restoredData[key]);
          }

          $.ajax({
            xhr: function () {
              const xhr = new XMLHttpRequest();
              xhr.upload.addEventListener("progress", function (e) {
                if (e.lengthComputable) {
                  const percentComplete = (e.loaded / e.total) * 100;
                  const mbLoaded = Math.floor(e.loaded / (1024 * 1024));
                  const mbTotal = Math.floor(e.total / (1024 * 1024));
                  const timeElapsed = (new Date().getTime() - startTime) / 1000;
                  const bps = e.loaded / timeElapsed;
                  const mbps = Math.floor(bps / (1024 * 1024));
                  const timeLeft = (e.total - e.loaded) / bps;

                  // Update progress bar
                  $("#percent").text(Math.floor(percentComplete) + '%');
                  $(".progress-bar").css('width', percentComplete + '%');
                  $("#dataTransferred").text(`${mbLoaded}/${mbTotal} MB`);
                  $("#Mbps").text(`${mbps} Mbps`);
                  $("#timeLeft").text(`${Math.floor(timeLeft / 60)}:${Math.floor(timeLeft % 60)}s`);
                }
              }, false);
              return xhr;
            },
            type: 'POST',
            url: _base_url_ + 'classes/Master.php?f=save_archive',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
              const resp = JSON.parse(response);
              if (resp.status === 'success') {
                Swal.fire({
                  title: 'Upload Complete!',
                  text: resp.msg,
                  icon: 'success',
                  confirmButtonText: 'OK'
                }).then(() => {
                  localStorage.removeItem('archiveFormData');
                  window.location.reload();
                });
              } else {
                Swal.fire({
                  title: 'Error',
                  text: resp.msg,
                  icon: 'error',
                  confirmButtonText: 'Try Again'
                });
              }
            },
            error: function () {
              Swal.fire({
                title: 'Error',
                text: 'An unexpected error occurred during upload.',
                icon: 'error',
                confirmButtonText: 'Close'
              });
            }
          });
        });
      }
    });
  </script>
</body>