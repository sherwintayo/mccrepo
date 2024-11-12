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
            <li><a href="#" onclick="loadContent('my_archives.php')">My Archives</a></li>
            <li><a href="#" onclick="loadContent('submit_projects.php')">Submit Capstone Projects</a></li>
            <li><a href="#" onclick="loadContent('notifications.php')">Notifications</a></li>
            <li><a href="#" onclick="loadContent('account_settings.php')">Account Settings</a></li>
          </ul>
        </nav>


        <!-- Page Content Display Area -->
        <div class="page" id="page-content">
          <img src="img/img_1.avif" alt="Photo" />
          <img src="img/img_2.avif" alt="Photo" />
          <img src="img/img_3.avif" alt="Photo" />
          <img src="img/img_4.avif" alt="Photo" />
          <img src="img/img_5.avif" alt="Photo" />
          <img src="img/img_6.avif" alt="Photo" />
        </div>
      </div>
    </div>
  </div>
</body>

<!-- JavaScript for AJAX content loading -->
<script>
  function loadContent(page) {
    const pageContent = document.getElementById('page-content');
    pageContent.innerHTML = '<p>Loading...</p>'; // Show loading text
    fetch(page)
      .then(response => response.text())
      .then(data => {
        pageContent.innerHTML = data; // Insert the loaded content
      })
      .catch(error => {
        pageContent.innerHTML = '<p>Error loading content.</p>';
        console.error('Error loading content:', error);
      });
  }
</script>