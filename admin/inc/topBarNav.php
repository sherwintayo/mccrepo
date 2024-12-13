<?php
// Assume $conn is already available in this file through the config or session
$notifications = [];
$count = 0;

// Fetch pending download requests with archive titles
$stmt = $conn->prepare("SELECT 
                            dr.id, 
                            s.firstname, 
                            s.lastname, 
                            dr.reason, 
                            dr.requested_at, 
                            al.title 
                        FROM 
                            download_requests dr
                        JOIN 
                            student_list s ON dr.user_id = s.id
                        JOIN 
                            archive_list al ON dr.file_id = al.id
                        WHERE 
                            dr.status = 'pending' 
                        ORDER BY 
                            dr.requested_at DESC 
                        LIMIT 10");
$stmt->execute();
$result = $stmt->get_result();
$count += $result->num_rows;

while ($row = $result->fetch_assoc()) {
  $notifications[] = [
    "type" => "download_request",
    "id" => $row['id'],
    "name" => $row['firstname'] . " " . $row['lastname'],
    "reason" => $row['reason'],
    "title" => $row['title'],
    "date" => $row['requested_at'],
  ];
}

// Fetch new students added today
$today = date("Y-m-d");
$student_query = $conn->prepare("
    SELECT 
        s.id, 
        s.firstname, 
        s.lastname, 
        s.date_created 
    FROM 
        student_list s
    WHERE 
        DATE(s.date_created) = ?
");
$student_query->bind_param("s", $today);
$student_query->execute();
$student_result = $student_query->get_result();
$count += $student_result->num_rows;

while ($row = $student_result->fetch_assoc()) {
  $notifications[] = [
    "type" => "new_student",
    "id" => $row['id'],
    "name" => $row['firstname'] . " " . $row['lastname'],
    "date" => $row['date_created'],
  ];
}


// Fetch new archives added today
$archive_query = $conn->prepare("
    SELECT 
        a.id, 
        a.title, 
        a.date_created 
    FROM 
        archive_list a
    WHERE 
        DATE(a.date_created) = ?
");
$archive_query->bind_param("s", $today);
$archive_query->execute();
$archive_result = $archive_query->get_result();
$count += $archive_result->num_rows;

while ($row = $archive_result->fetch_assoc()) {
  $notifications[] = [
    "type" => "new_archive",
    "id" => $row['id'],
    "name" => $row['title'],
    "date" => $row['date_created'],
  ];
}

// Sort all notifications by date in descending order
usort($notifications, function ($a, $b) {
  return strtotime($b['date']) - strtotime($a['date']);
});
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
            <?php if ($notification['type'] === "download_request"): ?>
              <!-- Download Request Notification -->
              <a href="javascript:void(0);" class="dropdown-item notification-link"
                data-id="<?php echo $notification['request_id']; ?>"
                data-name="<?php echo htmlspecialchars($notification['name']); ?>"
                data-reason="<?php echo htmlspecialchars($notification['reason']); ?>"
                data-title="<?php echo htmlspecialchars($notification['title']); ?>" onclick="showRequestModal(this)">
                <i class="fas fa-envelope myIcon"></i>
                <strong><?php echo htmlspecialchars($notification['name']); ?></strong>
                wants to download the archive
                <strong>"<?php echo htmlspecialchars($notification['title']); ?>"</strong> for the reason
                <em>"<?php echo htmlspecialchars($notification['reason']); ?>"</em>.
                <br>
                <span class="notification-time text-muted float-left">
                  <?php echo date('M d, H:i', strtotime($notification['date'])); ?>
                </span>
              </a>
              <div class="dropdown-divider"></div>
            <?php elseif ($notification['type'] === "new_student"): ?>
              <!-- New Student Notification -->
              <a href="javascript:void(0);" class="dropdown-item notification-link"
                data-id="<?php echo $notification['id']; ?>"
                data-name="<?php echo htmlspecialchars($notification['name']); ?>">
                <i class="fas fa-user myIcon"></i>
                <strong><?php echo htmlspecialchars($notification['name']); ?></strong>
                has been added as a new student.
                <br>
                <span class="notification-time text-muted float-left">
                  <?php echo date('M d, H:i', strtotime($notification['date'])); ?>
                </span>
              </a>
              <div class="dropdown-divider"></div>
            <?php elseif ($notification['type'] === "new_archive"): ?>
              <!-- New Archive Notification -->
              <a href="javascript:void(0);" class="dropdown-item notification-link"
                data-id="<?php echo $notification['id']; ?>"
                data-name="<?php echo htmlspecialchars($notification['name']); ?>">
                <i class="fas fa-file-alt myIcon"></i>
                A new archive titled
                <strong>"<?php echo htmlspecialchars($notification['name']); ?>"</strong>
                has been added.
                <br>
                <span class="notification-time text-muted float-left">
                  <?php echo date('M d, H:i', strtotime($notification['date'])); ?>
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
    const name = notification.getAttribute('data-name');
    const lastName = notification.getAttribute('data-lastname');
    const reason = notification.getAttribute('data-reason');
    const title = notification.getAttribute('data-title');

    // Populate modal with request details
    document.getElementById('modalStudentName').textContent = name;
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