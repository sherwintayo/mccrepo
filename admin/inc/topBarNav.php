<?php
// Assume $conn is already available in this file through the config or session
$notifications = [];
$count = 0;

// Fetch pending download requests and new student notifications
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
        NULL AS student_created_at
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
        s.date_created AS student_created_at
    FROM 
        student_list s
    WHERE 
        s.date_created > (SELECT IFNULL(MAX(requested_at), '1970-01-01') FROM download_requests)
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
              <?php
              $titleWords = explode(' ', htmlspecialchars($notification['title']));
              $truncatedTitle = implode(' ', array_slice($titleWords, 0, 7)) . (count($titleWords) > 7 ? '...' : '');
              ?>
              <a href="javascript:void(0);" class="dropdown-item notification-link"
                data-id="<?php echo $notification['request_id']; ?>"
                data-firstname="<?php echo htmlspecialchars($notification['firstname']); ?>"
                data-lastname="<?php echo htmlspecialchars($notification['lastname']); ?>"
                data-reason="<?php echo htmlspecialchars($notification['reason']); ?>"
                data-title="<?php echo htmlspecialchars($notification['title']); ?>" onclick="showRequestModal(this)">
                <i class="fas fa-envelope myIcon"></i>
                <strong><?php echo htmlspecialchars($notification['firstname'] . ' ' . $notification['lastname']); ?></strong>
                wants to download the
                <strong>"<?php echo $truncatedTitle; ?>"</strong>
                for the reason
                <em>"<?php echo htmlspecialchars($notification['reason']); ?>"</em>.
                <br>
                <span class="notification-time text-muted float-left">
                  <?php echo date('M d, H:i', strtotime($notification['requested_at'])); ?>
                </span>
              </a><br>
              <div class="dropdown-divider"></div>
            <?php elseif ($notification['student_id']): ?>
              <!-- New student added notification -->
              <a href="javascript:void(0);" class="dropdown-item notification-link"
                data-id="<?php echo $notification['student_id']; ?>"
                data-firstname="<?php echo htmlspecialchars($notification['student_firstname']); ?>"
                data-lastname="<?php echo htmlspecialchars($notification['student_lastname']); ?>"
                data-reason="New student added" onclick="showStudentModal(this)">
                <i class="fas fa-user myIcon"></i>
                <strong><?php echo htmlspecialchars($notification['student_firstname'] . ' ' . $notification['student_lastname']); ?></strong>
                has been added to the system.
                <br>
                <span class="notification-time text-muted float-left">
                  <?php echo date('M d, H:i', strtotime($notification['student_created_at'])); ?>
                </span>
              </a><br>
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