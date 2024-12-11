<style>
  .img-avatar {
    width: 45px;
    height: 45px;
    object-fit: cover;
    object-position: center;
    border-radius: 100%;
  }

  .buttons {
    margin-bottom: 6px;
    margin-left: 30px;
  }

  /* Button Styling */
  .btn-nav {
    margin-right: 5px;
    padding: 10px 20px;
    cursor: pointer;
    border-radius: 5px;
    border: none;
    color: #fefefe;
    background-color: #007bff;
  }

  /* Table Visibility */
  .table-container {
    display: none;
  }

  .active-table {
    display: block;
  }

  /* Optional: Active button styling */
  .btn-nav.active {
    background-color: #007bff;
    color: white;
  }
</style>


<div class="card card-outline card-primary">
  <div class="card-header">
    <h3 class="card-title">List of Notifications</h3>
    <!-- Navigation buttons -->
    <div class="buttons">
      <button class="btn-nav" id="downloadRequestsBtn">Download Requests</button>
      <button class="btn-nav" id="newUsersBtn">New Users</button>
      <button class="btn-nav" id="newProjectsBtn">New Projects</button>
      <button class="btn-nav" id="suspiciousLoginsBtn">Suspicious Logins</button>
    </div>
  </div>

  <div class="card-body">

    <div class="container-fluid">

      <!-- Download Requests Table -->
      <div id="downloadRequestsTable" class="table-container active-table">
        <table class="table table-hover table-striped">
          <colgroup>
            <col width="5%">
            <col width="15%">
            <col width="25%">
            <col width="25%">
            <col width="25%">
            <col width="10%">
            <col width="20%">
          </colgroup>
          <thead>
            <tr>
              <th>#</th>
              <th>Requested By</th>
              <th>Title</th>
              <th>Reason</th>
              <th>Requested At</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $i = 1;
            $qry = $conn->query("SELECT dr.id, s.firstname, s.lastname, dr.reason, dr.status, dr.requested_at, al.title  
                                 FROM download_requests dr 
                                 JOIN student_list s ON dr.user_id = s.id 
                                 JOIN archive_list al ON dr.file_id = al.id
                                 ORDER BY dr.requested_at DESC");

            while ($row = $qry->fetch_assoc()):
              ?>
              <tr>
                <td class="text-center"><?php echo $i++; ?></td>
                <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo htmlspecialchars($row['reason']); ?></td>
                <td><?php echo date("Y-m-d H:i", strtotime($row['requested_at'])); ?></td>
                <td class="text-center">
                  <?php
                  switch ($row['status']) {
                    case 'approved':
                      echo "<span class='badge badge-success'>Approved</span>";
                      break;
                    case 'rejected':
                      echo "<span class='badge badge-danger'>Rejected</span>";
                      break;
                    default:
                      echo "<span class='badge badge-secondary'>Pending</span>";
                      break;
                  }
                  ?>
                </td>
                <td align="center">
                  <button type="button" class="btn btn-flat btn-default btn-sm dropdown-toggle dropdown-icon"
                    data-toggle="dropdown">
                    Action
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>
                  <div class="dropdown-menu" role="menu">
                    <?php if ($row['status'] == 'pending'): ?>
                      <a class="dropdown-item approve_request" href="javascript:void(0)"
                        data-id="<?php echo $row['id']; ?>">
                        <span class="fa fa-check text-success"></span> Approve
                      </a>
                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item reject_request" href="javascript:void(0)" data-id="<?php echo $row['id']; ?>">
                        <span class="fa fa-times text-danger"></span> Reject
                      </a>
                      <div class="dropdown-divider"></div>
                    <?php endif; ?>
                    <a class="dropdown-item delete_request" href="javascript:void(0)" data-id="<?php echo $row['id']; ?>">
                      <span class="fa fa-trash text-danger"></span> Delete
                    </a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

      <div id="newUsersTable" class="table-container">
        <?php
        // Assume $conn is already available in this file through the config or session
        $unverifiedUsers = [];

        // Fetch all students with status = 2 (Unverified) from the student_list table
        $stmt = $conn->prepare("
            SELECT 
                s.id AS student_id, 
                s.firstname, 
                s.lastname, 
                s.date_created AS student_created_at,
                s.status AS student_status
            FROM 
                student_list s
            WHERE 
                s.status = 2  -- Only fetch unverified students
            ORDER BY s.date_created DESC
            LIMIT 10
          ");
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
          $unverifiedUsers[] = $row;
        }
        ?>
        <div class="table-container">
          <h3>Unverified Users</h3>
          <table class="table table-striped">
            <thead>
              <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Date Added</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($unverifiedUsers) > 0): ?>
                <?php foreach ($unverifiedUsers as $user): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($user['firstname']); ?></td>
                    <td><?php echo htmlspecialchars($user['lastname']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($user['student_created_at'])); ?></td>
                    <td>
                      <!-- Display status as Unverified -->
                      <span class="badge badge-danger">Unverified</span>
                    </td>
                    <td>
                      <a href="view_student.php?id=<?php echo $user['student_id']; ?>" class="btn btn-info btn-sm">View</a>
                      <a href="delete_student.php?id=<?php echo $user['student_id']; ?>"
                        class="btn btn-danger btn-sm">Delete</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5">No unverified users found</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>



      <div id="newProjectsTable" class="table-container">
        <!-- New Projects Table (similar structure) -->
      </div>
      <div id="suspiciousLoginsTable" class="table-container">
        <!-- Suspicious Logins Table (similar structure) -->
      </div>

    </div>
  </div>
</div>

<!-- Modal Structure -->
<div id="actionModal" class="modal fade" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirmation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p id="modalMessage"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="modalConfirm">Continue</button>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function () {
    // Handle button clicks to toggle table visibility
    $('#downloadRequestsBtn').click(function () {
      toggleTables('downloadRequestsTable');
    });

    $('#newUsersBtn').click(function () {
      toggleTables('newUsersTable'); // This is where the table for "New Users" is shown
    });

    $('#newProjectsBtn').click(function () {
      toggleTables('newProjectsTable');
    });

    $('#suspiciousLoginsBtn').click(function () {
      toggleTables('suspiciousLoginsTable');
    });

    // Toggle table visibility
    function toggleTables(tableId) {
      $('.table-container').removeClass('active-table'); // Hide all tables
      $('#' + tableId).addClass('active-table'); // Show the selected table

      // Highlight the active button
      $('.btn-nav').removeClass('active');
      $('#' + tableId + 'Btn').addClass('active');
    }
  });
</script>

<script>
  $(document).ready(function () {
    let actionType = '';
    let requestId = '';

    // Open modal for Approve request
    $('.approve_request').click(function () {
      requestId = $(this).data('id');
      actionType = 'approve';
      $('#modalMessage').text("Are you sure you want to approve this request?");
      $('#actionModal').modal('show');
    });

    // Open modal for Reject request
    $('.reject_request').click(function () {
      requestId = $(this).data('id');
      actionType = 'reject';
      $('#modalMessage').text("Are you sure you want to reject this request?");
      $('#actionModal').modal('show');
    });

    // Open modal for Delete request
    $('.delete_request').click(function () {
      requestId = $(this).data('id');
      actionType = 'delete';
      $('#modalMessage').text("Are you sure you want to delete this request?");
      $('#actionModal').modal('show');
    });

    // Confirm button inside modal
    $('#modalConfirm').click(function () {
      $('#actionModal').modal('hide'); // Hide modal on confirmation

      if (actionType === 'approve') {
        updateRequestStatus(requestId, 'approved');
      } else if (actionType === 'reject') {
        updateRequestStatus(requestId, 'rejected');
      } else if (actionType === 'delete') {
        deleteRequest(requestId);
      }
    });

    // Function to update request status
    function updateRequestStatus(id, status) {
      $.ajax({
        url: _base_url_ + "admin/update_request_status.php",
        method: 'POST',
        data: { id: id, status: status },
        dataType: 'json',
        success: function (response) {
          if (response.status === 'success') {
            location.reload();
          } else {
            alert('Failed to update the request status.');
          }
        },
        error: function () {
          alert('An error occurred while updating the request.');
        }
      });
    }

    // Function to delete request
    function deleteRequest(id) {
      $.ajax({
        url: _base_url_ + "admin/delete_request.php",
        method: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function (response) {
          if (response.status === 'success') {
            location.reload();
          } else {
            alert('Failed to delete the request.');
          }
        },
        error: function () {
          alert('An error occurred while deleting the request.');
        }
      });
    }
  });
</script>