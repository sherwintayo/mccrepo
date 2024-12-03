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
                  data-ip="<?php echo $row['ip_address']; ?>" data-lat="<?php echo $row['latitude']; ?>"
                  data-lng="<?php echo $row['longitude']; ?>">View Location</button>
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
        <div id="map"></div>
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
      const ip = $(this).data('ip');
      const lat = $(this).data('lat');
      const lng = $(this).data('lng');

      if (!lat || !lng) {
        $('#locationDetails').html('<p class="text-danger">No location data available for this IP.</p>');
        $('#map').html('');
        $('#locationModal').modal('show');
        return;
      }

      // Add details to the modal
      $('#locationDetails').html(`
        <p><strong>IP Address:</strong> ${ip}</p>
        <p><strong>Coordinates:</strong> ${lat}, ${lng}</p>
      `);

      // Initialize map
      const map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: parseFloat(lat), lng: parseFloat(lng) },
        zoom: 10,
      });
      new google.maps.Marker({
        position: { lat: parseFloat(lat), lng: parseFloat(lng) },
        map: map,
      });

      $('#locationModal').modal('show');
    });
  });
</script>

<!-- Include Google Maps JavaScript API -->
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB4aVZ-DHpcT29wc3W9zS0ssz61FNBOtWw">
</script>














<!-- <style>
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
    <h3 class="card-title">Login Activity</h3>
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
            <th>User ID</th>
            <th>Login Time</th>
            <th>Logout Time</th>
            <th>IP Address</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i = 1;
          $qry = $conn->query("SELECT * FROM `login_activity` ORDER BY `login_time` DESC");
          while ($row = $qry->fetch_assoc()):
            ?>
            <tr>
              <td class="text-center"><?php echo $i++; ?></td>
              <td><?php echo $row['user_id']; ?></td>
              <td><?php echo date("Y-m-d H:i", strtotime($row['login_time'])); ?></td>
              <td>
                <?php echo $row['logout_time'] ? date("Y-m-d H:i", strtotime($row['logout_time'])) : 'Still Logged In'; ?>
              </td>
              <td><?php echo $row['ip_address']; ?></td>
              <td class="text-center">
                <?php
                echo $row['status'] == 1
                  ? "<span class='badge badge-success badge-pill'>Successful</span>"
                  : "<span class='badge badge-danger badge-pill'>Failed</span>";
                ?>
              </td>
              <td>
                <button type="button" class="btn btn-sm btn-primary view-location"
                  data-ip="<?php echo $row['ip_address']; ?>">View Location</button>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>


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
        
        </div>
        <div id="map"></div>
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
      const ip = $(this).data('ip');

      $('#locationDetails').html('Loading location details...');
      $('#map').html('');

    
      $.post('./admin/login_activity/location_fetcher.php', { ip: ip }, function (response) {
        if (response.status === 'success') {
          const { city, region, country, latitude, longitude } = response.data;

       
          $('#locationDetails').html(`
            <p><strong>City:</strong> ${city}</p>
            <p><strong>Region:</strong> ${region}</p>
            <p><strong>Country:</strong> ${country}</p>
            <p><strong>Coordinates:</strong> ${latitude}, ${longitude}</p>
          `);

         
          const map = new google.maps.Map(document.getElementById('map'), {
            center: { lat: parseFloat(latitude), lng: parseFloat(longitude) },
            zoom: 10,
          });
          new google.maps.Marker({
            position: { lat: parseFloat(latitude), lng: parseFloat(longitude) },
            map: map,
          });
        } else {
          $('#locationDetails').html('<p class="text-danger">Failed to fetch location details.</p>');
        }
      }, 'json');

      $('#locationModal').modal('show');
    });
  });
</script>


<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB4aVZ-DHpcT29wc3W9zS0ssz61FNBOtWw">
</script> -->