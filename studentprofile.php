<head>
  <link rel="stylesheet" href="<?php echo base_url ?>myStyles/stdntprof_style.css?v=<?php echo time(); ?>">
  <style>
    .header__wrapper header {
      width: 100%;
      background: url("<?php echo validate_image($_settings->info('cover')) ?>") no-repeat 50% 20% / cover;
      min-height: calc(100px + 15vw);
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
          <h4>Team Members</h4>
          <p><?= nl2br(htmlspecialchars($members)) ?></p>

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
            <li><a href="#" class="nav-link" onclick="setActive(this, 'submit_capstone')">submit capstone projects</a>
            </li>
            <li><a href="#" class="nav-link" onclick="setActive(this, 'notifications')">notifications</a></li>
            <li><a href="#" class="nav-link" onclick="setActive(this, 'account_settings')">account settings</a></li>
          </ul>
        </nav>

        <!-- Default page content (my_archives) -->
        <div class="page" id="content-area">
          <!-- The initial content for "my archives" will go here -->
          <img src="img/img_1.avif" alt="Photo" />
          <img src="img/img_2.avif" alt="Photo" />
          <img src="img/img_3.avif" alt="Photo" />
        </div>
      </div>
    </div>
  </div>

  <script>
    const archives = <?php echo json_encode($archives); ?>;

    // JavaScript function to dynamically load content
    function loadContent(page) {
      const contentArea = document.getElementById("content-area");

      if (page === 'my_archives') {
        let html = `
          <h2>My Submitted Projects</h2>
          <div class="card-deck d-flex flex-wrap">`;

        archives.forEach((archive) => {
          const statusLabel = archive.status == 1 ? 'Published' : 'Unpublished';
          const statusClass = archive.status == 1 ? 'badge-success' : 'badge-secondary';

          html += `
            <div class="card shadow-sm border-primary m-2" style="width: 18rem;">
              <img src="${archive.banner_path ? `<?= base_url ?>${archive.banner_path}` : 'img/default.jpg'}" class="card-img-top" alt="Project Banner" style="height: 180px; object-fit: cover;">
              <div class="card-body">
                <h5 class="card-title">${archive.title}</h5>
                <p class="card-text">Archive Code: ${archive.archive_code}</p>
              </div>
              <div class="card-footer d-flex justify-content-between align-items-center ml3">
                <span class="badge ${statusClass}">${statusLabel}</span>
                <div class="dropdown">
                  <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Actions
                  </button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="<?= base_url ?>/?page=view_archive&id=${archive.id}" target="_blank">
                      <i class="fa fa-external-link-alt text-gray"></i> View
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item delete_data" href="javascript:void(0)" data-id="${archive.id}">
                      <i class="fa fa-trash text-danger"></i> Delete
                    </a>
                  </div>
                </div>
              </div>
            </div>`;
        });

        html += `</div>`; // Close card-deck div
        contentArea.innerHTML = html;

        // Add delete functionality
        document.querySelectorAll('.delete_data').forEach(button => {
          button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            _conf("Are you sure to delete this project permanently?", "delete_archive", [id]);
          });
        });
      } else if (page === 'submit_capstone') {
        contentArea.innerHTML = `
          <h2>Submit Capstone Projects</h2>
          <p>Upload and submit your capstone project files here.</p>
          <button>Submit Project</button>`;
      } else if (page === 'notifications') {
        contentArea.innerHTML = `
          <h2>Notifications</h2>
          <p>You have no new notifications at this time.</p>`;
      } else if (page === 'account_settings') {
        contentArea.innerHTML = `
          <h2>Account Settings</h2>
          <p>Manage your account information and preferences here.</p>`;
      } else {
        contentArea.innerHTML = `<p>Page not found.</p>`;
      }
    }

    // Load the default page content ("my_archives") when the page is first loaded
    document.addEventListener("DOMContentLoaded", function () {
      loadContent('my_archives');
    });

    // Set active class on click
    function setActive(link, page) {
      document.querySelectorAll('.nav-link').forEach(nav => nav.classList.remove('active'));
      link.classList.add('active');
      loadContent(page); // Load the respective page content
    }

    // Set default active link when the page loads
    document.addEventListener('DOMContentLoaded', function () {
      const defaultNav = document.querySelector('.nav-link');
      if (defaultNav) {
        defaultNav.classList.add('active');
      }
    });
  </script>
</body>