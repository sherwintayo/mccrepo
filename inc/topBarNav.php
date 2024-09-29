<head>
<link rel="stylesheet" href="<?php echo base_url ?>plugins/styleindex.css?v=<?php echo time(); ?>">
<style>

      /* NAVBAR */
#login-nav{
          position:fixed !important;
          top: 0 !important;
          z-index: 1037;
          padding: 1em 1.5em !important;
        }
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
</head>
   <!-- NAVBAR -->
<nav class="navbar navbar-expand-lg w-100" id="login-nav">
  <div class="container ">
  <a href="./" class="navbar-brand">
            <img src="<?php echo htmlspecialchars(validate_image($_settings->info('logo')), ENT_QUOTES, 'UTF-8') ?>" alt="Site Logo" class="brand-image img-circle elevation-3" 
            style="">
            <span class="myBrandName"><?= $_settings->info('short_name') ?></span>
          </a>

          <button class="navbar-toggler" type="button" 
          data-bs-toggle="modal" data-bs-target="#navbar-modal"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">

            <!-- Button Toggle -->
          <input type="checkbox" id="toggle-menu" class="toggle-menu">
          <label for="toggle-menu" type="button" class="toggle-btn">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
          </label>

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
                        <a href="./?page=projects_per_program&id=<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8')   ?>" class="dropdown-item"><?= ucwords($row['name']) ?></a>
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
                        <a href="./?page=projects_per_curriculum&id=<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?>" class="dropdown-item"><?= ucwords($row['name']) ?></a>
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
     <div class="myRightNav d-flex align-items-center">
            <!-- Search Icon -->
            <div class="me-3">
              <a href="javascript:void(0)" class="text-navy" id="search_icon">
                <i class="fa fa-search text-white"></i>
              </a>
              <div class="position-relative">
                <div id="search-field" class="position-absolute">
                  <input type="search" id="search-input" class="form-control rounded-0" required placeholder="Search..." 
                  value="<?= htmlspecialchars(isset($_GET['q']) ? $_GET['q'] : '', ENT_QUOTES, 'UTF-8') ?>">
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
                  <img src="<?= htmlspecialchars(validate_image($_settings->userdata('avatar')), ENT_QUOTES, 'UTF-8') ?>" class="img-circle elevation-2 user-img" id="student-img-avatar" alt="User Avatar">
                </span>
                <span class="sr-only">Toggle Dropdown</span>
              </button>
              <div class="dropdown-menu" role="menu">
                <span class="ml-3">Howdy, <?= htmlspecialchars( !empty($_settings->userdata('email')) ? $_settings->userdata('email') : 
                $_settings->userdata('username'), ENT_QUOTES, 'UTF-8') ?></span>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="<?= base_url.'classes/Login.php?f=student_logout' ?>"><span class="fas fa-sign-out-alt"></span> Logout</a>
              </div>
            </div>
            
            <div class="links">
            <?php else : ?>
              <li class="nav-item">
              <a href="./ .php" class="myNavLinks mx-1 text-light">Sign Up</a>
              </li>
              <li class="nav-item">
              <a href="./login.php" class="myNavLinks mx-1 text-light ">Student Sign In</a>
              </li>
              <li class="nav-item">
              <a href="./admin" class="myNavLinks mx-1 text-light">Admin Sign In</a>
              </li>
            <?php endif; ?>
            </div>

                      
          </div>
    </div>
  </div>
</nav>



<!-- Modal -->
<div class="modal fade" data-bs-backdrop="static" id="navbar-modal" tabindex="-1" 
aria-labelledby="navbar-modalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">

      <div class="myHeaderLeft my-1">
      <img src="<?php echo htmlspecialchars(validate_image($_settings->info('logo')), 
      ENT_QUOTES, 'UTF-8') ?>" alt="Site Logo" class="brand-image img-circle elevation-3">
            <span class="myBrandName"><?= $_settings->info('short_name') ?></span>
      </div>
      <div class="myHeaderRight d-flex align-items-center">
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
            <div class="dropdown">
              <button type="button" class="btn btn-rounded badge badge-light dropdown-toggle dropdown-icon" data-toggle="dropdown">
                <span>
                  <img src="<?= htmlspecialchars(validate_image($_settings->userdata('avatar')), ENT_QUOTES, 'UTF-8') ?>" class="img-circle elevation-2 user-img" id="student-img-avatar" alt="User Avatar">
                </span>
                <span class="sr-only">Toggle Dropdown</span>
              </button>
              <div class="dropdown-menu" role="menu">
                <span class="myName">Howdy, <?= htmlspecialchars( !empty($_settings->userdata('email')) ? $_settings->userdata('email') : 
                $_settings->userdata('username'), ENT_QUOTES, 'UTF-8') ?></span>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="<?= base_url.'classes/Login.php?f=student_logout' ?>">
                  <i class="fas fa-sign-out-alt"></i> Logout</a>
              </div>
            </div>

            <?php else : ?>
              <li class="nav-item">
              <a href="./ .php" class="mx-1 text-light">Sign Up</a>
              </li>
              <li class="nav-item">
              <a href="./login.php" class="mx-1 text-light ">Student Sign In</a>
              </li>
              <li class="nav-item">
              <a href="./admin" class="mx-1 text-light">Admin Sign In</a>
              </li>
            <?php endif; ?>
      </div>
   

      </div>
      <div class="modal-body">
      <ul class="navbar-nav ms-auto mb-lg-0 ">
            <li class="nav-item">
              <a class="nav-link active <?= isset($page) && $page =='home' ? "active" : "" ?>" 
              aria-current="page" href="./"><i class="fa fa-home"></i> HOME</a>
            </li>
            <li class="nav-item">
              <a href="./?page=projects" class="nav-link <?= isset($page) && $page =='projects' ? 
              "active" : "" ?>"><i class="fa fa-project-diagram"></i> PROJECTS</a>
            </li>
            <li class="nav-item dropdown">
                    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" 
                    class="nav-link dropdown-toggle  <?= isset($page) && $page =='projects_per_program' ? 
                    "active" : "" ?>"><i class="fa fa-graduation-cap"></i> PROGRAM</a>
                    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow" style="left: 0px; right: inherit;">
                      <?php 
                        $programs = $conn->query("SELECT * FROM program_list where status = 1 order by name asc");
                        $dI =  $programs->num_rows;
                        while($row = $programs->fetch_assoc()):
                          $dI--;
                      ?>
                      <li>
                        <a href="./?page=projects_per_program&id=<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8')   ?>"
                         class="dropdown-item"><?= ucwords($row['name']) ?></a>
                        <?php if($dI != 0): ?>
                        <li class="dropdown-divider"></li>
                        <?php endif; ?>
                      </li>
                      <?php endwhile; ?>
                    </ul>
                  </li>
                  <li class="nav-item dropdown">
                    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" 
                    class="nav-link dropdown-toggle  <?= isset($page) && $page =='projects_per_curriculum' ? 
                    "active" : "" ?>"><i class="fa fa-school"></i> CURRICULUM</a>
                    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow" style="left: 0px; right: inherit;">
                      <?php 
                        $curriculums = $conn->query("SELECT * FROM curriculum_list where status = 1 order by name asc");
                        $cI =  $curriculums->num_rows;
                        while($row = $curriculums->fetch_assoc()):
                          $cI--;
                      ?>
                      <li>
                        <a href="./?page=projects_per_curriculum&id=<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?>" class="dropdown-item"><?= ucwords($row['name']) ?></a>
                        <?php if($cI != 0): ?>
                        <li class="dropdown-divider"></li>
                        <?php endif; ?>
                      </li>
                      <?php endwhile; ?>
                    </ul>
                  </li>
                  <li class="nav-item">
                    <a href="./?page=about" class="nav-link <?= isset($page) && $page =='about' ? 
                    "active" : "" ?>"><i class="fa fa-info-circle"></i> ABOUT US</a>
                  </li>
              </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" 
        data-bs-dismiss="modal"><label for="toggle-menu">Close</label>
      </button>
      </div>
    </div>
  </div>
</div>



  
      <!-- /.navbar -->
      <script>
  $(function(){
          $('#search-form').submit(function(e){
            e.preventDefault();
            const query = $('[name="q"]').val();
            if(query.length === 0) {
              location.href = './';
            } else {
              location.href = './?' + $(this).serialize();
            }
          });
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
   $('#notificationDropdown').on('click', '.mark-read', function(e){
      e.preventDefault();
      const notificationId = $(this).data('id');
      $.ajax({
        url: 'update_notification_status.php',
        method: 'POST',
        data: { id: notificationId, status: 'read' },
        success: function(){
          const sanitizedId = $('<div>').text(notificationId).html();
          $(`#notificationDropdown .dropdown-item[data-id="${sanitizedId}"]`).remove();
          let badge = $('#notificationIcon .badge');
          let currentCount = parseInt(badge.text());
          if(currentCount > 1){
            badge.text(currentCount - 1);
          } else {
            badge.remove();
          }
        },
        error: function(xhr, status, error){
          console.error('Error marking notification as read:', error);
        }
      });
    });
  });
</script>