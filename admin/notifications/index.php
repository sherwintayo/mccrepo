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
          // Query to retrieve download requests
          $qry = $conn->query("SELECT dr.id, u.firstname, u.lastname, dr.reason, dr.status, dr.requested_at 
                                             FROM download_requests dr 
                                             JOIN users u ON dr.user_id = u.id 
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
                <?php if ($row['status'] == 'pending'): ?>
                  <button type="button" class="btn btn-success btn-sm approve_request"
                    data-id="<?php echo $row['id']; ?>">Approve</button>
                  <button type="button" class="btn btn-danger btn-sm reject_request"
                    data-id="<?php echo $row['id']; ?>">Reject</button>
                <?php endif; ?>
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
      updateRequestStatus(requestId, 'approved');
    });

    // Reject request action
    $('.reject_request').click(function () {
      const requestId = $(this).data('id');
      updateRequestStatus(requestId, 'rejected');
    });

    // Function to update request status
    function updateRequestStatus(id, status) {
      $.ajax({
        url: 'update_request_status.php',
        method: 'POST',
        data: { id: id, status: status },
        dataType: 'json',
        success: function (response) {
          if (response.status === 'success') {
            location.reload(); // Reload to reflect changes
          } else {
            alert('Failed to update the request status.');
          }
        },
        error: function () {
          alert('An error occurred while updating the request.');
        }
      });
    }
  });
</script>