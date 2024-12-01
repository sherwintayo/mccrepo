<style>
  .img-avatar {
    width: 45px;
    height: 45px;
    object-fit: cover;
    object-position: center center;
    border-radius: 100%;
  }
</style>
<div class="card card-outline card-primary">
  <div class="card-header">
    <h3 class="card-title">List of Login Activities</h3>
  </div>
  <div class="card-body">
    <div class="container-fluid">
      <table class="table table-hover table-striped">
        <colgroup>
          <col width="5%">
          <col width="20%">
          <col width="20%">
          <col width="20%">
          <col width="15%">
          <col width="20%">
        </colgroup>
        <thead>
          <tr>
            <th>#</th>
            <th>Login Date</th>
            <th>User</th>
            <th>IP Address</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i = 1;
          $qry = $conn->query("SELECT * from `login_activity` order by `login_date` desc");
          while ($row = $qry->fetch_assoc()):
            ?>
            <tr>
              <td class="text-center"><?php echo $i++; ?></td>
              <td><?php echo date("Y-m-d H:i", strtotime($row['login_date'])); ?></td>
              <td><?php echo $row['user_name']; ?></td>
              <td><?php echo $row['ip_address']; ?></td>
              <td class="text-center">
                <?php
                switch ($row['status']) {
                  case '1':
                    echo "<span class='badge badge-success badge-pill'>Successful</span>";
                    break;
                  case '0':
                    echo "<span class='badge badge-danger badge-pill'>Failed</span>";
                    break;
                }
                ?>
              </td>
              <td align="center">
                <button type="button" class="btn btn-flat btn-default btn-sm view_location"
                  data-ip="<?php echo $row['ip_address']; ?>">
                  <span class="fa fa-map-marker-alt text-primary"></span> View Location
                </button>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal for displaying location -->
<div class="modal fade" id="locationModal" tabindex="-1" role="dialog" aria-labelledby="locationModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="locationModalLabel">Location Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <h4 id="location-details"></h4>
        <div id="map" style="width: 100%; height: 400px;"></div>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function () {
    $('.view_location').click(function () {
      let ip = $(this).data('ip');
      let apiToken = 'FEF1A8D3324183EDBA2D0BD4F9128A6F'; // Replace with your Ipinfo.io token
      let apiUrl = `https://ipinfo.io/${ip}/json?token=${apiToken}`;

      $.getJSON(apiUrl, function (data) {
        let locationDetails = `Greetings from ${data.city}, ${data.region}, ${data.country}! 🌍`;
        $('#location-details').text(locationDetails);

        let loc = data.loc.split(',');
        let lat = loc[0];
        let lng = loc[1];

        // Initialize Map
        let map = L.map('map').setView([lat, lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19,
        }).addTo(map);

        L.marker([lat, lng]).addTo(map)
          .bindPopup(locationDetails)
          .openPopup();
      });

      $('#locationModal').modal('show');
    });
  });
</script>

<!-- Leaflet.js for the map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>