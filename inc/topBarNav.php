<head>
  <link rel="stylesheet" href="<?php echo base_url ?>plugins/styleindex.css?v=<?php echo time(); ?>">
  <style>
    /* NAVBAR */
    #login-nav {
      position: fixed !important;
      top: 0 !important;
      z-index: 1037;
      padding: 1em 1.5em !important;
    }

    .nav-item .dropdown-menu {
      color: rgba(255, 255, 255, 0.6) !important;
    }

    .btn-rounded {
      border-radius: 50%;
    }

    /* Notification Icon and Badge */
    .notification_icon .badge {
      font-size: 0.75rem;
      padding: 0.25em 0.5em;
      border-radius: 50%;
      background-color: red;
      color: white;
      position: absolute;
      top: 0;
      right: 0;
      transform: translate(50%, -50%);
    }

    /* Dropdown Menu Styling */
    .dropdown-menu-right {
      min-width: 250px;
      padding: 0;
      border-radius: 0.25rem;
    }

    .dropdown-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .dropdown-item i {
      margin-right: 0.5rem;
    }

    .dropdown-header {
      font-weight: bold;
    }
  </style>
</head>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg w-100" id="login-nav">
  <div class="container ">
    <a href="./" class="navbar-brand">
      <img src="<?php echo htmlspecialchars(validate_image($_settings->info('logo')), ENT_QUOTES, 'UTF-8') ?>"
        alt="Site Logo" class="brand-image img-circle elevation-3">
      <span class="myBrandName"><?= $_settings->info('short_name') ?></span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="modal" data-bs-target="#navbar-modal"
      aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">

      <!-- Button Toggle -->
      <input type="checkbox" id="toggle-menu" class="toggle-menu">
      <label for="toggle-menu" class="toggle-btn">
        <div class="bar"></div>
        <div class="bar"></div>
        <div class="bar"></div>
      </label>
    </button>

    <div class="collapse navbar-collapse mr-20" id="navbarSupportedContent">
      <ul class="navbar-nav justify-content-center flex-grow-1 ms-auto mb-lg-0">
        <li class="nav-item">
          <a class="nav-link active <?= isset($page) && $page == 'home' ? "active" : "" ?>" aria-current="page"
            href="./">HOME</a>
        </li>
        <li class="nav-item">
          <a href="./?page=projects"
            class="nav-link <?= isset($page) && $page == 'projects' ? "active" : "" ?>">PROJECTS</a>
        </li>
        <li class="nav-item dropdown">
          <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle <?= isset($page) && $page == 'projects_per_program' ? "active" : "" ?>">PROGRAM</a>
          <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow"
            style="left: 0px; right: inherit;">
            <?php
            $programs = $conn->query("SELECT * FROM program_list where status = 1 order by name asc");
            $dI = $programs->num_rows;
            while ($row = $programs->fetch_assoc()):
              $dI--;
              ?>
              <li>
                <a href="./?page=projects_per_program&id=<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?>"
                  class="dropdown-item"><?= ucwords($row['name']) ?></a>
                <?php if ($dI != 0): ?>
                <li class="dropdown-divider"></li>
              <?php endif; ?>
          </li>
        <?php endwhile; ?>
      </ul>
      </li>
      <li class="nav-item dropdown">
        <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
          class="nav-link dropdown-toggle <?= isset($page) && $page == 'projects_per_curriculum' ? "active" : "" ?>">CURRICULUM</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow" style="left: 0px; right: inherit;">
          <?php
          $curriculums = $conn->query("SELECT * FROM curriculum_list where status = 1 order by name asc");
          $cI = $curriculums->num_rows;
          while ($row = $curriculums->fetch_assoc()):
            $cI--;
            ?>
            <li>
              <a href="./?page=projects_per_curriculum&id=<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?>"
                class="dropdown-item"><?= ucwords($row['name']) ?></a>
              <?php if ($cI != 0): ?>
              <li class="dropdown-divider"></li>
            <?php endif; ?>
        </li>
      <?php endwhile; ?>
      </ul>
      </li>
      <li class="nav-item">
        <a href="./?page=about" class="nav-link <?= isset($page) && $page == 'about' ? "active" : "" ?>">ABOUT US</a>
      </li>
      <?php if ($_settings->userdata('id') > 0): ?>
        <li class="nav-item">
          <a href="./?page=profile" class="nav-link <?= isset($page) && $page == 'profile' ? "active" : "" ?>">Profile</a>
        </li>
        <li class="nav-item">
          <a href="./?page=submit-archive"
            class="nav-link <?= isset($page) && $page == 'submit-archive' ? "active" : "" ?>">Submit Thesis/Capstone</a>
        </li>
      <?php endif; ?>
      </ul>
    </div>

    <!-- Right Section: Search and User Profile -->
    <div class="myRightNav d-flex gap-3 align-items-center myNavLinks">
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

      <!-- Notification Bell Icon -->
      <div class="me-3 position-relative">
        <a class="nav-link notification_icon" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true"
          aria-expanded="false">
          <i class="fa fa-bell text-white"></i>
          <span class="badge badge-danger navbar-badge">3</span> <!-- Example notification count -->
        </a>

        <!-- Dropdown Menu -->
        <div class="dropdown-menu dropdown-menu-right">
          <span class="dropdown-item dropdown-header">3 Notifications</span>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-envelope mr-2"></i> 1 new message
            <span class="float-right text-muted text-sm">3 mins</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-users mr-2"></i> 5 friend requests
            <span class="float-right text-muted text-sm">12 hours</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
        </div>
      </div>


      <!-- User Profile -->
      <?php if ($_settings->userdata('id') > 0): ?>
        <div class="dropdown">
          <button type="button" class="btn btn-rounded badge badge-light dropdown-toggle dropdown-icon"
            data-toggle="dropdown">
            <span>
              <img src="<?= htmlspecialchars(validate_image($_settings->userdata('avatar')), ENT_QUOTES, 'UTF-8') ?>"
                class="img-circle elevation-2 user-img" id="student-img-avatar" alt="User Avatar">
            </span>
            <span class="sr-only">Toggle Dropdown</span>
          </button>
          <div class="dropdown-menu" role="menu">
            <span class="myName">Howdy,
              <?= htmlspecialchars(!empty($_settings->userdata('email')) ? $_settings->userdata('email') : $_settings->userdata('username'), ENT_QUOTES, 'UTF-8') ?></span>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="<?= base_url . 'classes/Login.php?f=student_logout' ?>"><i
                class="fas fa-sign-out-alt"></i> Logout</a>
          </div>
        </div>
      <?php else: ?>
        <li class="nav-item">
          <a href="./ms_login.php" class="myNavLinks mx-1 text-light" style="text-decoration: none;">Sign Up</a>
        </li>
        <li class="nav-item">
          <a href="./login.php" class="myNavLinks mx-1 text-light">Student Sign In</a>
        </li>
        <li class="nav-item">
          <a href="./admin" class="myNavLinks mx-1 text-light">Admin Sign In</a>
        </li>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- Modal -->
<div class="modal fade" data-bs-backdrop="static" id="navbar-modal" tabindex="-1" aria-labelledby="navbar-modalLabel"
  aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <div class="myHeaderLeft my-1">
          <img src="<?php echo htmlspecialchars(validate_image($_settings->info('logo')), ENT_QUOTES, 'UTF-8') ?>"
            alt="Site Logo" class="brand-image img-circle elevation-3">
          <span class="myBrandName"><?= $_settings->info('short_name') ?></span>
        </div>
        <div class="myHeaderRight d-flex align-items-center">

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

          <!-- Notification Bell Icon -->
          <div class="me-3 position-relative">
            <a class="notification_icon" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true"
              aria-expanded="false">
              <i class="fa fa-bell text-white"></i>
              <span class="badge badge-danger navbar-badge">3</span> <!-- Example notification count -->
            </a>

            <!-- Dropdown Menu -->
            <div class="dropdown-menu dropdown-menu-right">
              <span class="dropdown-item dropdown-header">3 Notifications</span>
              <div class="dropdown-divider"></div>
              <a href="#" class="dropdown-item">
                <i class="fas fa-envelope mr-2"></i> 1 new message
                <span class="float-right text-muted text-sm">3 mins</span>
              </a>
              <div class="dropdown-divider"></div>
              <a href="#" class="dropdown-item">
                <i class="fas fa-users mr-2"></i> 5 friend requests
                <span class="float-right text-muted text-sm">12 hours</span>
              </a>
              <div class="dropdown-divider"></div>
              <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
            </div>
          </div>


          <!-- User Profile -->
          <?php if ($_settings->userdata('id') > 0): ?>
            <div class="dropdown">
              <button type="button" class="btn btn-rounded badge badge-light dropdown-toggle dropdown-icon"
                data-toggle="dropdown">
                <span>
                  <img src="<?= htmlspecialchars(validate_image($_settings->userdata('avatar')), ENT_QUOTES, 'UTF-8') ?>"
                    class="img-circle elevation-2 user-img" id="student-img-avatar" alt="User Avatar">
                </span>
                <span class="sr-only">Toggle Dropdown</span>
              </button>
              <div class="dropdown-menu" role="menu">
                <span class="myName">Howdy,
                  <?= htmlspecialchars(!empty($_settings->userdata('email')) ? $_settings->userdata('email') : $_settings->userdata('username'), ENT_QUOTES, 'UTF-8') ?></span>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="<?= base_url . 'classes/Login.php?f=student_logout' ?>"><i
                    class="fas fa-sign-out-alt"></i> Logout</a>
              </div>
            </div>
          <?php else: ?>
            <li class="nav-item">
              <a href="./ms_login.php" class="myNavLinks mx-1 text-light" style="text-decoration: none;">Sign Up</a>
            </li>
            <li class="nav-item">
              <a href="./login.php" class="myNavLinks mx-1 text-light">Student Sign In</a>
            </li>
            <li class="nav-item">
              <a href="./admin" class="myNavLinks mx-1 text-light">Admin Sign In</a>
            </li>
          <?php endif; ?>
        </div>
      </div>
      <div class="modal-body">
        <ul class="navbar-nav ms-auto mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active <?= isset($page) && $page == 'home' ? "active" : "" ?>" aria-current="page"
              href="./"><i class="fa fa-home"></i> HOME</a>
          </li>
          <li class="nav-item">
            <a href="./?page=projects" class="nav-link <?= isset($page) && $page == 'projects' ? "active" : "" ?>"><i
                class="fa fa-project-diagram"></i> PROJECTS</a>
          </li>
          <li class="nav-item dropdown">
            <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
              class="nav-link dropdown-toggle <?= isset($page) && $page == 'projects_per_program' ? "active" : "" ?>"><i
                class="fa fa-graduation-cap"></i> PROGRAM</a>
            <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow"
              style="left: 0px; right: inherit;">
              <?php
              $programs = $conn->query("SELECT * FROM program_list where status = 1 order by name asc");
              $dI = $programs->num_rows;
              while ($row = $programs->fetch_assoc()):
                $dI--;
                ?>
                <li>
                  <a href="./?page=projects_per_program&id=<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?>"
                    class="dropdown-item"><?= ucwords($row['name']) ?></a>
                  <?php if ($dI != 0): ?>
                  <li class="dropdown-divider"></li>
                <?php endif; ?>
            </li>
          <?php endwhile; ?>
        </ul>
        </li>
        <li class="nav-item dropdown">
          <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle <?= isset($page) && $page == 'projects_per_curriculum' ? "active" : "" ?>"><i
              class="fa fa-school"></i> CURRICULUM</a>
          <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow"
            style="left: 0px; right: inherit;">
            <?php
            $curriculums = $conn->query("SELECT * FROM curriculum_list where status = 1 order by name asc");
            $cI = $curriculums->num_rows;
            while ($row = $curriculums->fetch_assoc()):
              $cI--;
              ?>
              <li>
                <a href="./?page=projects_per_curriculum&id=<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?>"
                  class="dropdown-item"><?= ucwords($row['name']) ?></a>
                <?php if ($cI != 0): ?>
                <li class="dropdown-divider"></li>
              <?php endif; ?>
          </li>
        <?php endwhile; ?>
        </ul>
        </li>
        <li class="nav-item">
          <a href="./?page=about" class="nav-link <?= isset($page) && $page == 'about' ? "active" : "" ?>"><i
              class="fa fa-info-circle"></i> ABOUT US</a>
        </li>
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- /.navbar -->
<script>
  $(function () {
    $('#search-form').submit(function (e) {
      e.preventDefault();
      const query = $('[name="q"]').val();
      if (query.length === 0) {
        location.href = './';
      } else {
        location.href = './?' + $(this).serialize();
      }
    });
    $('#search_icon').click(function () {
      $('#search-field').addClass('show');
      $('#search-input').focus();
    });
    $('#search-input').focusout(function () {
      $('#search-field').removeClass('show');
    });
    $('#search-input').keydown(function (e) {
      if (e.which == 13) {
        location.href = "./?page=projects&q=" + encodeURIComponent($(this).val());
      }
    });
  });
</script>