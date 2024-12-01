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
    <h3 class="card-title">Login Activity</h3>
  </div>
  <div class="card-body">
    <div class="container-fluid">
      <table class="table table-hover table-striped">
        <colgroup>
          <col width="5%">
          <col width="20%">
          <col width="20%">
          <col width="20%">
          <col width="20%">
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
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
  $(document).ready(function () {
    $('.table td, .table th').addClass('py-1 px-2 align-middle');
    $('.table').dataTable({
      columnDefs: [
        { orderable: false, targets: 5 }
      ],
    });
  });
</script>