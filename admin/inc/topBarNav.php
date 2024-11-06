<?php
// Assume $conn is already available in this file through the config or session
$notifications = [];
$count = 0;

// Fetch pending download requests
$stmt = $conn->prepare("SELECT dr.id, s.firstname, s.lastname, dr.reason, dr.requested_at 
                        FROM download_requests dr 
                        JOIN student_list s ON dr.user_id = s.id 
                        WHERE dr.status = 'pending' 
                        ORDER BY dr.requested_at DESC 
                        LIMIT 10");
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

  .notification-reason {
    font-size: 0.9em;
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
      <div class="dropdown-menu dropdown-menu-right" style="width: 300px;">
        <span class="dropdown-item dropdown-header"><?php echo $count; ?> New Requests</span>
        <div class="dropdown-divider"></div>

        <?php if ($count > 0): ?>
          <?php foreach ($notifications as $notification): ?>
            <div class="notification-item" data-id="<?php echo $notification['id']; ?>"
              data-firstname="<?php echo htmlspecialchars($notification['firstname']); ?>"
              data-lastname="<?php echo htmlspecialchars($notification['lastname']); ?>"
              data-reason="<?php echo htmlspecialchars($notification['reason']); ?>" onclick="showRequestModal(this)">
              <div>
                <strong><?php echo htmlspecialchars($notification['firstname'] . ' ' . $notification['lastname']); ?></strong>
                <span
                  class="text-muted float-right text-sm"><?php echo date('M d, H:i', strtotime($notification['requested_at'])); ?></span>
                <div class="notification-reason"><?php echo htmlspecialchars($notification['reason']); ?></div>
              </div>
            </div>
            <div class="dropdown-divider"></div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="dropdown-item text-center">No new requests</div>
        <?php endif; ?>

        <a href="<?php echo base_url ?>admin/?page=notifications" class="dropdown-item dropdown-footer">See All
          Requests</a>
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
</nav>
<!-- /.navbar -->

<script>
  // Show modal with request details
  function showRequestModal(notification) {
    const id = notification.getAttribute('data-id');
    const firstName = notification.getAttribute('data-firstname');
    const lastName = notification.getAttribute('data-lastname');
    const reason = notification.getAttribute('data-reason');

    document.getElementById('modalStudentName').textContent = firstName + ' ' + lastName;
    document.getElementById('modalRequestReason').textContent = reason;

    $('#requestModal').modal('show');

    // Set data-id on buttons to track request ID for the action
    document.getElementById('approveRequestBtn').setAttribute('data-id', id);
    document.getElementById('rejectRequestBtn').setAttribute('data-id', id);
  }

  // AJAX function to handle status update
  function updateRequestStatus(id, status) {
    $.ajax({
      url: 'update_status.php',
      method: 'POST',
      data: { id: id, status: status },
      dataType: 'json',
      success: function (response) {
        if (response.status === 'success') {
          alert('Request ' + status + ' successfully.');
          $('#requestModal').modal('hide');
          location.reload(); // Refresh page to update notifications
        } else {
          alert('Failed to update request status: ' + response.message);
        }
      },
      error: function () {
        alert('An error occurred while updating the request status.');
      }
    });
  }

  // Button click events
  document.getElementById('approveRequestBtn').onclick = function () {
    const id = this.getAttribute('data-id');
    updateRequestStatus(id, 'approved');
  };

  document.getElementById('rejectRequestBtn').onclick = function () {
    const id = this.getAttribute('data-id');
    updateRequestStatus(id, 'rejected');
  };
</script>