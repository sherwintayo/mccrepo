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
      const apiToken = 'FEF1A8D3324183EDBA2D0BD4F9128A6F'; // Replace with your actual token

      $('#locationDetails').html('Loading location details...');
      $('#map').html('');

      // Fetch location data
      $.getJSON(`https://ipinfo.io/${ip}/json?token=${apiToken}`, function (data) {
        const { city, region, country, loc } = data;
        const [latitude, longitude] = loc.split(',');

        // Add details to the modal
        $('#locationDetails').html(`
          <p><strong>City:</strong> ${city}</p>
          <p><strong>Region:</strong> ${region}</p>
          <p><strong>Country:</strong> ${country}</p>
          <p><strong>Coordinates:</strong> ${latitude}, ${longitude}</p>
        `);

        // Initialize map
        const map = new google.maps.Map(document.getElementById('map'), {
          center: { lat: parseFloat(latitude), lng: parseFloat(longitude) },
          zoom: 10,
        });
        new google.maps.Marker({
          position: { lat: parseFloat(latitude), lng: parseFloat(longitude) },
          map: map,
        });
      }).fail(function () {
        $('#locationDetails').html('<p class="text-danger">Failed to fetch location details.</p>');
      });

      $('#locationModal').modal('show');
    });
  });
</script>

<!-- Include Google Maps JavaScript API -->
<script async defer src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY">
</script>