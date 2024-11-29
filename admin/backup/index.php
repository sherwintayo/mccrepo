<?php
require_once('../config.php'); // Include the config file

if (!isset($conn)) {
  die("Database connection failed.");
}

// Fetch all table names
$tables = array();
$query = "SHOW TABLES";
$result = $conn->query($query);

if ($result) {
  while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
  }
} else {
  die("Error fetching tables: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Database Tables</title>
</head>

<body>
  <div class="container mt-4">
    <h1 class="text-center">Database Tables, Columns, and Data</h1>
    <hr>
    <a class="btn btn-primary" href="download_backup"> Backup Database</a>
    <!-- Display Each Table -->
    <?php foreach ($tables as $table): ?>
      <div class="card mt-4">
        <div class="card-header bg-dark text-white">
          <h4>Table: <?php echo htmlspecialchars($table); ?></h4>
        </div>
        <div class="card-body">
          <!-- Fetch Table Columns -->
          <?php
          $columns = array();
          $query = "SHOW COLUMNS FROM `$table`";
          $columnResult = $conn->query($query);

          if ($columnResult) {
            echo "<h5>Columns:</h5>";
            echo "<ul>";
            while ($columnRow = $columnResult->fetch_assoc()) {
              $columns[] = $columnRow['Field'];
              echo "<li>" . htmlspecialchars($columnRow['Field']) . " (" . htmlspecialchars($columnRow['Type']) . ")</li>";
            }
            echo "</ul>";
          } else {
            echo "<p class='text-danger'>Error fetching columns: " . $conn->error . "</p>";
          }
          ?>

          <!-- Fetch Table Data -->
          <?php
          $query = "SELECT * FROM `$table`";
          $dataResult = $conn->query($query);

          if ($dataResult && $dataResult->num_rows > 0): ?>
            <h5>Data:</h5>
            <div class="table-responsive">
              <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                  <tr>
                    <?php foreach ($columns as $column): ?>
                      <th><?php echo htmlspecialchars($column); ?></th>
                    <?php endforeach; ?>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($dataRow = $dataResult->fetch_assoc()): ?>
                    <tr>
                      <?php foreach ($columns as $column): ?>
                        <td><?php echo htmlspecialchars($dataRow[$column] ?? 'NULL'); ?></td>
                      <?php endforeach; ?>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p class="text-warning">No data available in this table.</p>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <hr>
  </div>
</body>

</html>