<style>
  .img-avatar {
    width: 45px;
    height: 45px;
    object-fit: cover;
    object-position: center;
    border-radius: 100%;
  }
</style>

<div class="card card-outline card-primary">
  <div class="card-header">
    <h3 class="card-title">List of Download Requests</h3>
  </div>
  <div class="card-body">
    <div class="container-fluid">
      <table class="table table-hover table-striped">
        <colgroup>
          <col width="5%">
          <col width="15%">
          <col width="25%">
          <col width="25%">
          <col width="10%">
          <col width="20%">
        </colgroup>
        <thead>
          <tr>
            <th>#</th>
            <th>Requested By</th>
            <th>Reason</th>
            <th>Requested At</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i = 1;
          $qry = $conn->query("SELECT dr.id, s.firstname, s.lastname, dr.reason, dr.status, dr.requested_at 
                               FROM download_requests dr 
                               JOIN student_list s ON dr.user_id = s.id 
                               ORDER BY dr.requested_at DESC");

          while ($row = $qry->fetch_assoc()):
            ?>
            <tr>
              <td class="text-center"><?php echo $i++; ?></td>
              <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
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
                    <a class="dropdown-item approve_request" href="javascript:void(0)" data-id="<?php echo $row['id']; ?>">
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
  </div>
</div>

<script>
  $(document).ready(function () {
    // Approve request action
    $('.approve_request').click(function () {
      const requestId = $(this).data('id');
      _conf("Are you sure you want to approve this request?", "updateRequestStatus", [requestId, 'approved']);
    });

    // Reject request action
    $('.reject_request').click(function () {
      const requestId = $(this).data('id');
      _conf("Are you sure you want to reject this request?", "updateRequestStatus", [requestId, 'rejected']);
    });

    // Delete request action
    $('.delete_request').click(function () {
      const requestId = $(this).data('id');
      _conf("Are you sure you want to delete this request?", "deleteRequest", [requestId]);
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