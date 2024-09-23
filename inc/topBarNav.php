<style>
  .user-img{
        position: absolute;
        height: 27px;
        width: 27px;
        object-fit: cover;
        left: -7%;
        top: -12%;
  }
  .btn-rounded{
        border-radius: 50px;
  }
  .notification-icon {
    position: relative;
    display: inline-block;
  }
  .notification-icon .badge {
        position: absolute;
    top: -10px;
    right: -10px;
    background-color: red;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 12px;
  }
  .notification-dropdown {
    display: none;
    position: absolute;
    top: 60px; 
    right: 10%;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.1);
    z-index: 1050;
    max-height: 300px;
    overflow-y: auto;
    min-width: 300px;
    max-width: 400px;
    max-height: 400px;
    margin-top: 40px;
  }
  .notification-dropdown .dropdown-item {
    padding: 10px;
    border-bottom: 1px solid #ddd;
  }
  .notification-dropdown .dropdown-item:last-child {
    border-bottom: none;
  }
  .notification-dropdown .dropdown-item.read {
    background-color: #f5f5f5;
  }
  .notification-dropdown .dropdown-item .mark-read {
    font-size: 12px;
    color: #007bff;
    cursor: pointer;
  }
</style>
<!-- Navbar -->
      <style>
        #login-nav{
          position:fixed !important;
          top: 0 !important;
          z-index: 1037;
          padding: 1em 1.5em !important;
        }
        #top-Nav{
          top: 4em;
        }
        .text-sm .layout-navbar-fixed .wrapper .main-header ~ .content-wrapper, .layout-navbar-fixed .wrapper .main-header.text-sm ~ .content-wrapper {
          margin-top: calc(3.6) !important;
          padding-top: calc(5em) !important;
      }
      </style>
<nav class="navbar navbar-expand-lg w-100" id="login-nav">
  <div class="container ">

  <a href="./" class="navbar-brand">
            <img src="<?php echo validate_image($_settings->info('logo'))?>" alt="Site Logo" class="brand-image img-circle elevation-3" 
            style="height: 49px; opacity: .8;">
            <span class= "brand-text font-weight-bolder"><?= $_settings->info('short_name') ?></span>
          </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" 
    aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon" style="color: white;"></span>
    </button>

    <div class="collapse navbar-collapse mr-20" id="navbarSupportedContent">
      <ul class="navbar-nav justify-content-center flex-grow-1 ms-auto mb-lg-0 ">
        <li class="nav-item">
          <a class="nav-link active <?= isset($page) && $page =='home' ? "active" : "" ?>" aria-current="page" href="./">HOME</a>
        </li>
        <li class="nav-item">
          <a href="./?page=projects" class="nav-link <?= isset($page) && $page =='projects' ? "active" : "" ?>">PROJECTS</a>
        </li>
        <li class="nav-item dropdown">
                <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle  <?= isset($page) && $page =='projects_per_program' ? "active" : "" ?>">PROGRAM</a>
                <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow" style="left: 0px; right: inherit;">
                  <?php 
                    $programs = $conn->query("SELECT * FROM program_list where status = 1 order by name asc");
                    $dI =  $programs->num_rows;
                    while($row = $programs->fetch_assoc()):
                      $dI--;
                  ?>
                  <li>
                    <a href="./?page=projects_per_program&id=<?= $row['id'] ?>" class="dropdown-item"><?= ucwords($row['name']) ?></a>
                    <?php if($dI != 0): ?>
                    <li class="dropdown-divider"></li>
                    <?php endif; ?>
                  </li>
                  <?php endwhile; ?>
                </ul>
              </li>
              <li class="nav-item dropdown">
                <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle  <?= isset($page) && $page =='projects_per_curriculum' ? "active" : "" ?>">CURRICULUM</a>
                <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow" style="left: 0px; right: inherit;">
                  <?php 
                    $curriculums = $conn->query("SELECT * FROM curriculum_list where status = 1 order by name asc");
                    $cI =  $curriculums->num_rows;
                    while($row = $curriculums->fetch_assoc()):
                      $cI--;
                  ?>
                  <li>
                    <a href="./?page=projects_per_curriculum&id=<?= $row['id'] ?>" class="dropdown-item"><?= ucwords($row['name']) ?></a>
                    <?php if($cI != 0): ?>
                    <li class="dropdown-divider"></li>
                    <?php endif; ?>
                  </li>
                  <?php endwhile; ?>
                </ul>
              </li>
              <li class="nav-item">
                <a href="./?page=about" class="nav-link <?= isset($page) && $page =='about' ? "active" : "" ?>">ABOUT US</a>
              </li>
          </ul>
        </div>

    <!-- Right Section: Search, Notification, and User Profile -->
    <div class="myRightNav d-flex align-items-center gap-3">
      <!-- Search Icon -->
      <div class="me-3">
        <a href="javascript:void(0)" class="text-navy" id="search_icon">
          <i class="fa fa-search text-white"></i>
        </a>
        <div class="position-relative">
          <div id="search-field" class="position-absolute">
            <input type="search" id="search-input" class="form-control rounded-0" required placeholder="Search..." value="<?= isset($_GET['q']) ? $_GET['q'] : '' ?>">
          </div>
        </div>
      </div>

      <!-- Notification Icon -->
      <?php if ($_settings->userdata('id') > 0) : ?>
      <span class="notification-icon me-3" id="notificationIcon">
        <a href="javascript:void(0)"><i class="fa fa-bell text-white"></i></a>
        <?php
          $user_id = $_settings->userdata('id');
          $notifications_query = $conn->query("SELECT COUNT(*) as unread_count FROM notifications WHERE student_id = $user_id AND status = 'unread'");
          $unread_notifications = $notifications_query->fetch_assoc()['unread_count'];
        ?>
        <?php if ($unread_notifications > 0) : ?>
        <span class="badge"><?= $unread_notifications ?></span>
        <?php endif; ?>
      </span>

      <!-- User Profile -->
      <div class="nav-item dropdown">
        <button type="button" class="btn btn-rounded badge badge-light dropdown-toggle dropdown-icon" data-toggle="dropdown">
          <span>
            <img src="<?= validate_image($_settings->userdata('avatar')) ?>" class="img-circle elevation-2 user-img" id="student-img-avatar" alt="User Avatar">
          </span>
          <span class="sr-only">Toggle Dropdown</span>
        </button>
        <div class="dropdown-menu" role="menu">
          <span class="ml-3">Howdy, <?= !empty($_settings->userdata('email')) ? $_settings->userdata('email') : $_settings->userdata('username') ?></span>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="<?= base_url.'classes/Login.php?f=student_logout' ?>"><span class="fas fa-sign-out-alt"></span> Logout</a>
        </div>
      </div>

      <?php else : ?>
      <a href="./ms_login.php" class="mx-1 text-light">Register</a>
      <a href="./login.php" class="mx-1 text-light ">Student Login</a>
      <a href="./admin" class="mx-1 text-light">Admin login</a>
      <?php endif; ?>


      </div>
    </div>
  </div>
</nav>


  
      <!-- /.navbar -->
      <script>
  $(function(){
    $('#search-form').submit(function(e){
            e.preventDefault()
            if($('[name="q"]').val().length == 0)
            location.href = './';
            else
            location.href = './?'+$(this).serialize();
          })
          $('#search_icon').click(function(){
              $('#search-field').addClass('show')
              $('#search-input').focus();
              
          })
          $('#search-input').focusout(function(e){
            $('#search-field').removeClass('show')
          })
          $('#search-input').keydown(function(e){
            if(e.which == 13){
              location.href = "./?page=projects&q="+encodeURI($(this).val());
            }
          });



    // Mark notification as read
    $('#notificationDropdown').on('click', '.mark-read', function (e) {
      e.preventDefault(); // Prevent the default action
      const notificationId = $(this).data('id');
      $.ajax({
        url: 'update_notification_status.php',
        method: 'POST',
        data: { id: notificationId, status: 'read' },
        success: function () {
          $(`#notificationDropdown .dropdown-item[data-id="${notificationId}"]`).remove();
          // Update badge count
          let badge = $('#notificationIcon .badge');
          let currentCount = parseInt(badge.text());
          if (currentCount > 1) {
            badge.text(currentCount - 1);
          } else {
            badge.remove(); // Remove badge if count is 0
          }
        },
        error: function (xhr, status, error) {
          console.error('Error marking notification as read:', error);
        }
      });
    });
  });
</script>