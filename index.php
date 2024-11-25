<?php require_once('./config.php'); ?>
<!DOCTYPE html>
<html lang="en" class="" style="height: auto;">
<link rel="stylesheet" href="./myStyles/index.css">
<style>
  /* General fix for overflow issues */
  html,
  body {
    overflow-x: hidden;
    /* Prevent horizontal scrolling */
    margin: 0;
    padding: 0;
    width: 100%;
    /* Ensure no element exceeds 100% width */
    box-sizing: border-box;
  }

  #header {
    height: 70vh;
    width: calc(100%);
    position: relative;
    top: -1em;
    margin-top: 20px;
  }


  #header:before {
    content: "";
    position: absolute;
    height: calc(100%);
    width: calc(100%);
    background-image: url(<?= htmlspecialchars(validate_image($_settings->info("cover")), ENT_QUOTES, 'UTF-8') ?>);
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center center;
  }

  #header>div {
    position: absolute;
    height: calc(100%);
    width: calc(100%);
    z-index: 2;
  }

  #top-Nav a.nav-link.active {
    color: #001f3f;
    font-weight: 900;
    position: relative;
  }

  #top-Nav a.nav-link.active:before {
    content: "";
    position: absolute;
    border-bottom: 2px solid #001f3f;
    width: 33.33%;
    left: 33.33%;
    bottom: 0;
  }

  /* Add this to make sure large images or content don't cause overflow */
  img,
  iframe,
  video,
  object,
  embed {
    max-width: 100%;
    height: auto;
  }
</style>
<?php require_once('inc/header.php') ?>

<body class="layout-top-nav layout-fixed layout-navbar-fixed" style="height: auto;">
  <div class="wrapper" style="background-color: #fafcfd;">
    <?php $page = isset($_GET['page']) ? $_GET['page'] : 'home'; ?>
    <?php require_once('inc/topBarNav.php') ?>
    <?php if ($_settings->chk_flashdata('success')): ?>
      <script>
        alert_toast("<?php echo htmlspecialchars($_settings->flashdata('success'), ENT_QUOTES, 'UTF-8') ?>", 'success')
      </script>
    <?php endif; ?>
    <!-- Content Wrapper. Contains page content -->
    <div class="content w-100 mt-5" style="margin-left: 0px;">
      <?php if ($page == "home" || $page == "about_us"): ?>
        <div id="header" class="shadow">
          <div class="d-flex justify-content-center h-100 w-100 align-items-center flex-column">
            <h1 class="w-100 text-center site-title">
              <?php echo htmlspecialchars("MADRIDEJOS COMMUNITY COLLEGE REPOSITORIES") ?>
            </h1>
            <a href="./?page=projects" class="btn btn-lg btn-light rounded-pill w-25" id="enrollment"><b>Explore
                Projects</b></a>
          </div>
        </div>
      <?php endif; ?>
      <!-- Main content -->
      <section class="content" style="margin-top: 12vh;">
        <div class="container">
          <?php
          if (!file_exists($page . ".php") && !is_dir($page)) {
            include '404.html';
          } else {
            if (is_dir($page))
              include $page . '/index.php';
            else
              include $page . '.php';

          }
          ?>
        </div>
      </section>

      <div class="floating-buttons">
        <?php if ($_settings->userdata('id') > 0): ?>
          <!-- User Manual Button -->
          <div class="floating-btn-container">
            <button class="floating-btn user-manual-btn" onclick="toggleSidebar('userManualSidebar')">
              <i class="fa fa-book"></i>
            </button>
            <span class="floating-btn-text">Click User Manual</span>
          </div>
        <?php endif; ?>
        <!-- Chatbot Button -->
        <div class="floating-btn-container">
          <button class="floating-btn chatbot-btn">
            <a href="https://mccbsitquizandexam.com" target="_blank">
              <i class="fa fa-comments"></i>
            </a>
          </button>
          <span class="floating-btn-text">Click Chatbot</span>
        </div>
      </div>


      <!-- Sidebar for User Manual -->
      <div id="userManualSidebar" class="sidebar hidden">
        <a href="javascript:void(0)" class="closebtn" onclick="toggleSidebar('userManualSidebar')">&times;</a>
        <div class="container">
          <h2>User Manual</h2>
          <div class="sidebar-content">
            <p>This is the user manual content.</p>
          </div>
        </div>
      </div>
      <!-- Background Overlay -->
      <div id="overlay" class="overlay hidden" onclick="closeAllSidebars()"></div>



      <!-- /.content -->
      <div class="modal fade" id="confirm_modal" role='dialog'>
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Confirmation</h5>
            </div>
            <div class="modal-body">
              <div id="delete_content"></div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-primary" id='confirm' onclick="">Continue</button>
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>
      <div class="modal fade" id="uni_modal" role='dialog'>
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title"></h5>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-primary" id='submit'
                onclick="$('#uni_modal form').submit()">Save</button>
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
          </div>
        </div>
      </div>
      <div class="modal fade" id="uni_modal_right" role='dialog'>
        <div class="modal-dialog modal-full-height  modal-md" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title"></h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span class="fa fa-arrow-right"></span>
              </button>
            </div>
            <div class="modal-body">
            </div>
          </div>
        </div>
      </div>
      <div class="modal fade" id="viewer_modal" role='dialog'>
        <div class="modal-dialog modal-md" role="document">
          <div class="modal-content">
            <button type="button" class="btn-close" data-dismiss="modal"><span class="fa fa-times"></span></button>
            <img src="" alt="">
          </div>
        </div>
      </div>
    </div>
    <!-- /content-wrapper -->
    <?php require_once('inc/footer.php') ?>
</body>
<script>
  function openSidebar(id) {
    document.getElementById(id + 'Sidebar').style.width = "300px";
  }

  function closeSidebar(id) {
    document.getElementById(id + 'Sidebar').style.width = "0";
  }



  function toggleSidebar(id) {
    const sidebar = document.getElementById(id);
    const overlay = document.getElementById('overlay');
    if (sidebar.classList.contains('hidden')) {
      sidebar.classList.remove('hidden');
      sidebar.classList.add('visible');
      overlay.classList.add('visible'); // Show overlay
    } else {
      sidebar.classList.remove('visible');
      sidebar.classList.add('hidden');
      overlay.classList.remove('visible'); // Hide overlay
    }
  }

  function closeAllSidebars() {
    const sidebars = document.querySelectorAll('.sidebar');
    const overlay = document.getElementById('overlay');
    sidebars.forEach(sidebar => {
      sidebar.classList.remove('visible');
      sidebar.classList.add('hidden');
    });
    overlay.classList.remove('visible'); // Hide overlay
  }


</script>

</html>