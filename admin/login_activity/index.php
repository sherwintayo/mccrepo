<?php
require '../config.php';

$stmt = $conn->query("
    SELECT la.*, u.username 
    FROM login_activity la
    JOIN users u ON la.user_id = u.id
    ORDER BY la.login_time DESC
");
$activities = $stmt->fetch_all(MYSQLI_ASSOC);
?>

<body>
  <div class="container mt-5">
    <h1>Login Activity</h1>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>Login Time</th>
          <th>IP Address</th>
          <th>User Agent</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($activities as $activity): ?>
          <tr>
            <td><?= htmlspecialchars($activity['id']) ?></td>
            <td><?= htmlspecialchars($activity['username']) ?></td>
            <td><?= htmlspecialchars($activity['login_time']) ?></td>
            <td><?= htmlspecialchars($activity['ip_address']) ?></td>
            <td><?= htmlspecialchars($activity['user_agent']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>

</html>