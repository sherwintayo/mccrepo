<?php
// Assume $conn is already available in this file through the config or session
$notifications = [];
$count = 0;

// Fetch all notifications: download requests, new students, new archives, and login attempts
$stmt = $conn->prepare("
    SELECT 
        dr.id AS request_id, 
        s.firstname, 
        s.lastname, 
        dr.reason, 
        dr.requested_at, 
        al.title,
        NULL AS student_id, 
        NULL AS student_firstname, 
        NULL AS student_lastname,
        NULL AS student_created_at,
        NULL AS archive_title,
        NULL AS login_ip,
        NULL AS login_blocked_until
    FROM 
        download_requests dr
    JOIN 
        student_list s ON dr.user_id = s.id
    JOIN 
        archive_list al ON dr.file_id = al.id
    WHERE 
        dr.status = 'pending'

    UNION ALL

    SELECT 
        NULL AS request_id, 
        s.firstname, 
        s.lastname, 
        'New student added' AS reason, 
        s.date_created AS requested_at,
        NULL AS title,
        s.id AS student_id,
        s.firstname AS student_firstname,
        s.lastname AS student_lastname,
        s.date_created AS student_created_at,
        NULL AS archive_title,
        NULL AS login_ip,
        NULL AS login_blocked_until
    FROM 
        student_list s
    ORDER BY requested_at DESC

    UNION ALL

    SELECT 
        NULL AS request_id, 
        NULL AS firstname,
        NULL AS lastname,
        'New archive uploaded' AS reason,
        al.date_created AS requested_at,
        NULL AS title,
        NULL AS student_id,
        NULL AS student_firstname,
        NULL AS student_lastname,
        NULL AS student_created_at,
        al.title AS archive_title,
        NULL AS login_ip,
        NULL AS login_blocked_until
    FROM 
        archive_list al
    ORDER BY requested_at DESC

    UNION ALL

    SELECT 
        NULL AS request_id, 
        NULL AS firstname,
        NULL AS lastname,
        'Suspicious login attempt detected' AS reason,
        la.blocked_until AS requested_at,
        NULL AS title,
        NULL AS student_id,
        NULL AS student_firstname,
        NULL AS student_lastname,
        NULL AS student_created_at,
        NULL AS archive_title,
        la.ip_address AS login_ip,
        la.blocked_until AS login_blocked_until
    FROM 
        Login_Attempt la
    WHERE 
        la.blocked_until IS NOT NULL
    ORDER BY requested_at DESC
    LIMIT 10
");

$stmt->execute();
$result = $stmt->get_result();
$count = $result->num_rows;

while ($row = $result->fetch_assoc()) {
  $notifications[] = $row;
}
?>

<style>
  .user-img {
    position: absolute;
    height: 27px;
    width: 27px;
    object-fit: cover;
    left: -7%;
    top: -12%;
  }

  .btn-rounded {
    border-radius: 50px;
  }

  .notification-item {
    display: flex;
    align-items: center;
    padding: 10px;
  }

  .notification-item:hover {
    background-color: #f1f1f1;
  }

  /* Ensure the dropdown content wraps properly and fits within the menu */
  .myDropdown {
    width: 300px;
    /* Limit the height of the dropdown */
    overflow-y: auto;
    /* Add scrolling for overflow */
  }

  .notification-link {
    display: block;
    /* Ensure proper block structure */
    white-space: normal;
    /* Allow wrapping of long text */
    margin-bottom: 5px;
    /* Add space between items */
  }

  .notification-time {
    font-size: 0.85rem;
  }

  .myIcon,
  .notification-time {
    color: #6BAEFC;
  }


  .unread-indicator {
    display: inline-block;
    width: 8px;
    height: 8px;
    background-color: blue;
    border-radius: 50%;
    margin-left: 5px;
  }

  /* Adjust the text size for readability */
  .notification-reason {
    font-size: 0.9rem;
    color: gray;
  }
</style>

<!-- Navbar -->
<nav
  class="main-header navbar navbar-expand navbar-light border border-dark border-top-0 border-left-0 border-right-0 navbar-light text-sm shadow-sm">
  <!-- Left navbar links -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="<?php echo base_url ?>"
        class="nav-link"><b><?php echo (!isMobileDevice()) ? $_settings->info('name') : $_settings->info('short_name'); ?>
          - Admin</b></a>
    </li>
  </ul>
  <!-- Right navbar links -->
  <ul class="navbar-nav ml-auto">
    <!-- Notification Bell Icon -->
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-bell"></i>
        <?php if ($count > 0): ?>
          <span class="badge badge-danger navbar-badge"><?php echo $count; ?></span>
        <?php endif; ?>
      </a>
      <!-- Dropdown Menu -->
      <div class="dropdown-menu myDropdown dropdown-menu-right"
        style="width: 300px; max-height: 600px; overflow-y: auto;">
        <span class="dropdown-item dropdown-header">
          <?php if ($count > 0): ?>
            You have <?= $count ?> New Notification<?= $count > 1 ? 's' : '' ?>
          <?php else: ?>
            You have no new notifications
          <?php endif; ?>
        </span>
        <div class="dropdown-divider"></div>

        <?php if ($count > 0): ?>
          <?php foreach ($notifications as $notification): ?>
            <?php if ($notification['request_id']): ?>
              <!-- Download request notification -->
              <a href="javascript:void(0);" class="dropdown-item notification-link">
                <i class="fas fa-envelope myIcon"></i>
                <strong><?php echo htmlspecialchars($notification['firstname'] . ' ' . $notification['lastname']); ?></strong>
                wants to download the
                <strong>"<?php echo htmlspecialchars($notification['title']); ?>"</strong>.
                <br>
                <span class="notification-time text-muted float-left">
                  <?php echo date('M d, H:i', strtotime($notification['requested_at'])); ?>
                </span>
              </a>
              <div class="dropdown-divider"></div>
            <?php elseif ($notification['student_id']): ?>
              <!-- New student added notification -->
              <a href="javascript:void(0);" class="dropdown-item notification-link">
                <i class="fas fa-user myIcon"></i>
                <strong><?php echo htmlspecialchars($notification['student_firstname'] . ' ' . $notification['student_lastname']); ?></strong>
                has been added to the system.
                <br>
                <span class="notification-time text-muted float-left">
                  <?php echo date('M d, H:i', strtotime($notification['student_created_at'])); ?>
                </span>
              </a>
              <div class="dropdown-divider"></div>
            <?php elseif ($notification['archive_title']): ?>
              <!-- New archive notification -->
              <a href="javascript:void(0);" class="dropdown-item notification-link">
                <i class="fas fa-file-archive myIcon"></i>
                A new archive "<strong><?php echo htmlspecialchars($notification['archive_title']); ?></strong>" has been
                uploaded.
                <br>
                <span class="notification-time text-muted float-left">
                  <?php echo date('M d, H:i', strtotime($notification['requested_at'])); ?>
                </span>
              </a>
              <div class="dropdown-divider"></div>
            <?php elseif ($notification['login_ip']): ?>
              <!-- Suspicious login notification -->
              <a href="javascript:void(0);" class="dropdown-item notification-link">
                <i class="fas fa-exclamation-triangle myIcon text-warning"></i>
                Suspicious login attempt detected from IP:
                <strong><?php echo htmlspecialchars($notification['login_ip']); ?></strong>.
                <br>
                <span class="notification-time text-muted float-left">
                  <?php echo date('M d, H:i', strtotime($notification['login_blocked_until'])); ?>
                </span>
              </a>
              <div class="dropdown-divider"></div>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php else: ?>
          <span class="dropdown-item text-light-50">No new notifications</span>
        <?php endif; ?>

        <a href="<?php echo base_url ?>admin/?page=notifications" class="dropdown-item dropdown-footer text-center">
          See All Notifications
        </a>
      </div>
    </li>


    <!-- User Dropdown Menu -->
    <li class="nav-item">
      <div class="btn-group nav-link">
        <button type="button" class="btn btn-rounded badge badge-light dropdown-toggle dropdown-icon"
          data-toggle="dropdown">
          <span><img src="<?php echo validate_image($_settings->userdata('avatar')) ?>"
              class="img-circle elevation-2 user-img" alt="User Image"></span>
          <span
            class="ml-3"><?php echo ucwords($_settings->userdata('firstname') . ' ' . $_settings->userdata('lastname')) ?></span>
          <span class="sr-only">Toggle Dropdown</span>
        </button>
        <div class="dropdown-menu" role="menu">
          <a class="dropdown-item" href="<?php echo base_url . 'admin/?page=user' ?>"><span class="fa fa-user"></span>
            My
            Account</a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="<?php echo base_url . '/classes/Login.php?f=logout' ?>"><span
              class="fas fa-sign-out-alt"></span> Logout</a>
        </div>
      </div>
    </li>
  </ul>
</nav>
<!-- /.navbar -->

<!-- Modal HTML -->
<div class="modal fade" id="requestModal" tabindex="-1" role="dialog" aria-labelledby="requestModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="requestModalLabel">Download Request</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p><strong>Student:</strong> <span id="modalStudentName"></span></p>
        <p><strong>Title:</strong> <span id="modalRequestTitle"></span></p>
        <p><strong>Reason:</strong> <span id="modalRequestReason"></span></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" id="approveRequestBtn">Approve</button>
        <button type="button" class="btn btn-danger" id="rejectRequestBtn">Reject</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Do it Later</button>
      </div>
    </div>
  </div>
</div>

<script>
  // Show modal with request details
  function showRequestModal(notification) {
    const id = notification.getAttribute('data-id');
    const firstName = notification.getAttribute('data-firstname');
    const lastName = notification.getAttribute('data-lastname');
    const reason = notification.getAttribute('data-reason');
    const title = notification.getAttribute('data-title');

    // Populate modal with request details
    document.getElementById('modalStudentName').textContent = firstName + ' ' + lastName;
    document.getElementById('modalRequestReason').textContent = reason;
    document.getElementById('modalRequestTitle').textContent = title;

    // Store request ID on buttons for tracking
    document.getElementById('approveRequestBtn').setAttribute('data-id', id);
    document.getElementById('rejectRequestBtn').setAttribute('data-id', id);

    $('#requestModal').modal('show');
  }

  // Initialize modal event listeners when the modal is shown
  $('#requestModal').on('show.bs.modal', function () {
    // Approve request action
    $('#approveRequestBtn').off('click').on('click', function () {
      const id = $(this).data('id');
      updateRequestStatus(id, 'approved');
    });

    // Reject request action
    $('#rejectRequestBtn').off('click').on('click', function () {
      const id = $(this).data('id');
      updateRequestStatus(id, 'rejected');
    });
  });

  // AJAX function to handle status update with SweetAlert
  function updateRequestStatus(id, status) {
    $.ajax({
      url: _base_url_ + "admin/update_request_status.php",
      method: 'POST',
      data: { id: id, status: status },
      dataType: 'json',
      success: function (response) {
        if (response.status === 'success') {
          Swal.fire({
            title: 'Success',
            text: 'Request ' + status + ' successfully.',
            icon: 'success',
            confirmButtonText: 'OK'
          }).then(() => {
            $('#requestModal').modal('hide');
            location.reload(); // Refresh page to update notifications
          });
        } else {
          Swal.fire({
            title: 'Error',
            text: 'Failed to update request status: ' + response.message,
            icon: 'error',
            confirmButtonText: 'OK'
          });
        }
      },
      error: function () {
        Swal.fire({
          title: 'Error',
          text: 'An error occurred while updating the request status.',
          icon: 'error',
          confirmButtonText: 'OK'
        });
      }
    });
  }
</script>