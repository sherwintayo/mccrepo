<?php
require_once('../config.php');

// Fetch login attempts and history
$qry = $conn->query("
    SELECT 
        la.ip_address, 
        la.attempts, 
        la.latitude, 
        la.longitude, 
        lah.block_count
    FROM 
        Login_Attempt la
    LEFT JOIN 
        login_attempt_history lah
    ON 
        la.ip_address = lah.ip_address
    ORDER BY 
        la.attempts DESC
");

$data = [];
while ($row = $qry->fetch_assoc()) {
  $data[] = $row;
}
?>

<style>
  .img-avatar {
    width: 45px;
    height: 45px;
    object-fit: cover;
    object-position: center center;
    border-radius: 100%;
  }

  .modal-dialog {
    max-width: 700px;
  }

  #map {
    height: 400px;
    width: 100%;
  }
</style>

<div class="card card-outline card-primary">
  <div class="card-header">
    <h3 class="card-title">Login Attempt Activity</h3>
  </div>
  <div class="card-body">
    <div class="container-fluid">
      <table class="table table-hover table-striped">
        <colgroup>
          <col width="5%">
          <col width="20%">
          <col width="15%">
          <col width="15%">
          <col width="20%">
          <col width="10%">
          <col width="15%">
        </colgroup>
        <thead>
          <tr>
            <th>#</th>
            <th>IP Address</th>
            <th>Longitude</th>
            <th>Latitude</th>
            <th>Attempts</th>
            <th>Blocks</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i = 1;
          foreach ($data as $row):
            ?>
            <tr>
              <td class="text-center"><?php echo $i++; ?></td>
              <td><?php echo $row['ip_address']; ?></td>
              <td><?php echo $row['longitude'] ?: 'N/A'; ?></td>
              <td><?php echo $row['latitude'] ?: 'N/A'; ?></td>
              <td><?php echo $row['attempts']; ?></td>
              <td><?php echo $row['block_count'] ?: 0; ?></td>
              <td>
                <button type="button" class="btn btn-sm btn-primary view-location"
                  data-lat="<?php echo $row['latitude']; ?>" data-lng="<?php echo $row['longitude']; ?>">View
                  Location</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal for Location Details -->
<div class="modal fade" id="locationModal" tabindex="-1" aria-labelledby="locationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="locationModalLabel">Location Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="locationDetails">
          <!-- Location details will be dynamically added here -->
        </div>
        <div id="mapContainer" style="width: 100%; height: 400px;">
          <!-- Google Maps iframe will be dynamically added here -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function () {
    $('.table td, .table th').addClass('py-1 px-2 align-middle');
    $('.table').dataTable({
      columnDefs: [
        { orderable: false, targets: 6 }
      ],
    });

    $('.view-location').on('click', function () {
      const lat = $(this).data('lat');
      const lng = $(this).data('lng');

      if (!lat || !lng) {
        $('#locationDetails').html('<p class="text-danger">No location data available.</p>');
        $('#mapContainer').html('');
        $('#locationModal').modal('show');
        return;
      }

      // Add details to the modal
      $('#locationDetails').html(`
        <p><strong>Coordinates:</strong> ${lat}, ${lng}</p>
      `);

      // Add Google Maps iframe
      $('#mapContainer').html(`
        <iframe
          src='https://www.google.com/maps?q=${lat},${lng}&h1=es;z=14&output=embed'
          style="width: 100%; height: 100%; border: 0;">
        </iframe>
      `);

      $('#locationModal').modal('show');
    });
  });
</script>