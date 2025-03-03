<?php
require_once('../config.php'); // Include the config file

if (!isset($conn)) {
  die("Database connection failed.");
}

// Utility function to validate SQL identifiers (table/column names)
function isValidIdentifier($name)
{
  return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name);
}

// Log messages to a file
function logAction($message)
{
  $logFile = __DIR__ . '/logs.txt';
  file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] " . $message . PHP_EOL, FILE_APPEND);
}

// Export database or a single table
function exportData($conn, $table = null)
{
  $dbName = DB_NAME;
  $fileName = $table ? "export_{$table}_" . date('Ymd_His') . ".sql" : "database_backup_" . date('Ymd_His') . ".sql";

  header('Content-Type: application/sql');
  header('Content-Disposition: attachment; filename="' . $fileName . '"');

  if ($table) {
    // Export a single table
    $command = sprintf(
      "mysqldump --host=%s --user=%s --password=%s %s %s",
      escapeshellarg(DB_SERVER),
      escapeshellarg(DB_USERNAME),
      escapeshellarg(DB_PASSWORD),
      escapeshellarg($dbName),
      escapeshellarg($table)
    );
  } else {
    // Export the entire database
    $command = sprintf(
      "mysqldump --host=%s --user=%s --password=%s %s",
      escapeshellarg(DB_SERVER),
      escapeshellarg(DB_USERNAME),
      escapeshellarg(DB_PASSWORD),
      escapeshellarg($dbName)
    );
  }

  passthru($command, $returnVar);
  exit($returnVar === 0 ? 0 : "Error exporting data.");
}

// Handle export requests
if (isset($_GET['export']) && $_GET['export'] === 'true' && isset($_GET['action']) && $_GET['action'] === 'export_data') {
  $table = $_GET['export_table'] ?? null;

  if ($table && !isValidIdentifier($table)) {
    die("Invalid table name.");
  }

  exportData($conn, $table);
  exit;
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

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  $tableName = $_POST['table_name'] ?? '';
  $columnName = $_POST['column_name'] ?? '';
  $columnType = $_POST['column_type'] ?? '';

  // Validate table name and column name (server-side)
  if (!empty($tableName) && !isValidIdentifier($tableName)) {
    $message = "Invalid table name.";
    $messageType = "danger";
  } elseif (!empty($columnName) && !isValidIdentifier($columnName)) {
    $message = "Invalid column name.";
    $messageType = "danger";
  } else {
    // Process based on action
    switch ($action) {
      case 'add_table':
        $columns = $_POST['columns'] ?? '';
        $query = "CREATE TABLE `$tableName` ($columns)";
        if ($conn->query($query)) {
          $message = "Table '$tableName' created successfully.";
          $messageType = "success";
          logAction("Table '$tableName' created.");
        } else {
          $message = "Error creating table: " . $conn->error;
          $messageType = "danger";
          logAction("Failed to create table '$tableName': " . $conn->error);
        }
        break;

      case 'delete_table':
        $query = "DROP TABLE IF EXISTS `$tableName`";
        if ($conn->query($query)) {
          $message = "Table '$tableName' deleted successfully.";
          $messageType = "success";
          logAction("Table '$tableName' deleted.");
        } else {
          $message = "Error deleting table: " . $conn->error;
          $messageType = "danger";
          logAction("Failed to delete table '$tableName': " . $conn->error);
        }
        break;

      case 'add_column':
        $query = "ALTER TABLE `$tableName` ADD `$columnName` $columnType";
        if ($conn->query($query)) {
          $message = "Column '$columnName' added to table '$tableName' successfully.";
          $messageType = "success";
          logAction("Column '$columnName' added to table '$tableName'.");
        } else {
          $message = "Error adding column: " . $conn->error;
          $messageType = "danger";
          logAction("Failed to add column '$columnName' to table '$tableName': " . $conn->error);
        }
        break;

      case 'delete_column':
        $query = "ALTER TABLE `$tableName` DROP COLUMN `$columnName`";
        if ($conn->query($query)) {
          $message = "Column '$columnName' deleted from table '$tableName' successfully.";
          $messageType = "success";
          logAction("Column '$columnName' deleted from table '$tableName'.");
        } else {
          $message = "Error deleting column: " . $conn->error;
          $messageType = "danger";
          logAction("Failed to delete column '$columnName' from table '$tableName': " . $conn->error);
        }
        break;

      case 'update_column':
        $query = "ALTER TABLE `$tableName` MODIFY `$columnName` $columnType";
        if ($conn->query($query)) {
          $message = "Column '$columnName' updated successfully in table '$tableName'.";
          $messageType = "success";
          logAction("Column '$columnName' updated in table '$tableName'.");
        } else {
          $message = "Error updating column: " . $conn->error;
          $messageType = "danger";
          logAction("Failed to update column '$columnName' in table '$tableName': " . $conn->error);
        }
        break;

      case 'rename_table':
        $newTableName = $_POST['new_table_name'] ?? '';
        if (isValidIdentifier($newTableName)) {
          $query = "RENAME TABLE `$tableName` TO `$newTableName`";
          if ($conn->query($query)) {
            $message = "Table '$tableName' renamed to '$newTableName' successfully.";
            $messageType = "success";
            logAction("Table '$tableName' renamed to '$newTableName'.");
          } else {
            $message = "Error renaming table: " . $conn->error;
            $messageType = "danger";
            logAction("Failed to rename table '$tableName': " . $conn->error);
          }
        } else {
          $message = "Invalid new table name.";
          $messageType = "danger";
        }
        break;

      default:
        $message = "Invalid action.";
        $messageType = "danger";
        break;
    }
  }
}

// Handle database download
if (isset($_GET['download']) && $_GET['download'] === 'true') {
  $dbHost = DB_SERVER;
  $dbUser = DB_USERNAME;
  $dbPass = DB_PASSWORD;
  $dbName = DB_NAME;
  $fileName = "database_backup_" . date('Ymd_His') . ".sql";
  $filePath = base_url . '/' . $fileName;

  // Secure mysqldump command execution
  $command = sprintf(
    "mysqldump --host=%s --user=%s --password=%s %s > %s",
    escapeshellarg($dbHost),
    escapeshellarg($dbUser),
    escapeshellarg($dbPass),
    escapeshellarg($dbName),
    escapeshellarg($filePath)
  );
  exec($command, $output, $returnVar);

  if ($returnVar === 0 && file_exists($filePath)) {
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    readfile($filePath);
    unlink($filePath);
    exit;
  } else {
    $message = "Error generating database dump.";
    $messageType = "danger";
    logAction("Failed to generate database dump.");
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Database Manager</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body>
  <div class="container">
    <h1>Database Management</h1>

    <!-- Show message if available -->
    <?php if ($message): ?>
      <div class="alert alert-<?php echo $messageType; ?>">
        <?php echo $message; ?>
      </div>
    <?php endif; ?>

    <h2>Export Database</h2>
    <form method="get" action="index.php">
      <label for="export_table">Select Table (optional):</label>
      <select name="export_table" id="export_table">
        <option value="">All Tables</option>
        <?php foreach ($tables as $table): ?>
          <option value="<?php echo $table; ?>"><?php echo $table; ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" name="export" value="true">Export</button>
    </form>

    <!-- Update Table Form -->
    <h3>Rename Table</h3>
    <form method="post" class="mb-3">
      <input type="hidden" name="action" value="rename_table">
      <div class="mb-3">
        <label>Current Table Name</label>
        <input type="text" name="table_name" class="form-control" required pattern="[a-zA-Z_][a-zA-Z0-9_]*"
          placeholder="e.g., users">
      </div>
      <div class="mb-3">
        <label>New Table Name</label>
        <input type="text" name="new_table_name" class="form-control" required pattern="[a-zA-Z_][a-zA-Z0-9_]*"
          placeholder="e.g., customers">
      </div>
      <button type="submit" class="btn btn-warning">Rename Table</button>
    </form>

    <!-- Delete Table Form -->
    <h3>Delete Table</h3>
    <form method="post" class="mb-3">
      <input type="hidden" name="action" value="delete_table">
      <div class="mb-3">
        <label>Table Name</label>
        <input type="text" name="table_name" class="form-control" required pattern="[a-zA-Z_][a-zA-Z0-9_]*"
          placeholder="e.g., users">
      </div>
      <button type="submit" class="btn btn-danger">Delete Table</button>
    </form>

    <!-- Add Column Form -->
    <h3>Add Column</h3>
    <form method="post" class="mb-3">
      <input type="hidden" name="action" value="add_column">
      <div class="mb-3">
        <label>Table Name</label>
        <input type="text" name="table_name" class="form-control" required pattern="[a-zA-Z_][a-zA-Z0-9_]*"
          placeholder="e.g., users">
      </div>
      <div class="mb-3">
        <label>Column Name</label>
        <input type="text" name="column_name" class="form-control" required pattern="[a-zA-Z_][a-zA-Z0-9_]*"
          placeholder="e.g., age">
      </div>
      <div class="mb-3">
        <label>Column Type <small class="text-muted">(e.g., INT, VARCHAR(100))</small></label>
        <input type="text" name="column_type" class="form-control" required placeholder="e.g., VARCHAR(100)">
      </div>
      <button type="submit" class="btn btn-success">Add Column</button>
    </form>

    <!-- Update Column Form -->
    <h3>Update Column</h3>
    <form method="post" class="mb-3">
      <input type="hidden" name="action" value="update_column">
      <div class="mb-3">
        <label>Table Name</label>
        <input type="text" name="table_name" class="form-control" required pattern="[a-zA-Z_][a-zA-Z0-9_]*"
          placeholder="e.g., users">
      </div>
      <div class="mb-3">
        <label>Column Name</label>
        <input type="text" name="column_name" class="form-control" required pattern="[a-zA-Z_][a-zA-Z0-9_]*"
          placeholder="e.g., age">
      </div>
      <div class="mb-3">
        <label>New Column Type <small class="text-muted">(e.g., INT, VARCHAR(100))</small></label>
        <input type="text" name="column_type" class="form-control" required placeholder="e.g., INT NOT NULL">
      </div>
      <button type="submit" class="btn btn-warning">Update Column</button>
    </form>

    <!-- Delete Column Form -->
    <h3>Delete Column</h3>
    <form method="post" class="mb-3">
      <input type="hidden" name="action" value="delete_column">
      <div class="mb-3">
        <label>Table Name</label>
        <input type="text" name="table_name" class="form-control" required pattern="[a-zA-Z_][a-zA-Z0-9_]*"
          placeholder="e.g., users">
      </div>
      <div class="mb-3">
        <label>Column Name</label>
        <input type="text" name="column_name" class="form-control" required pattern="[a-zA-Z_][a-zA-Z0-9_]*"
          placeholder="e.g., age">
      </div>
      <button type="submit" class="btn btn-danger">Delete Column</button>
    </form>


    <!-- Additional forms for other actions... -->

    <!-- Display Tables -->
    <!-- Display Each Table -->
    <?php foreach ($tables as $table): ?>
      <div class="card mt-4">
        <div class="card-header bg-dark text-white">
          <h4>Table: <?php echo htmlspecialchars($table); ?></h4>
        </div>
        <div class="card-body">
          <a href="?export=true&export_table=<?php echo urlencode($table); ?>" class="btn btn-secondary mb-3">
            Export This Table
          </a>

          <!-- Fetch Table Columns -->
          <?php
          $columns = [];
          $query = "SHOW COLUMNS FROM $table";
          $columnResult = $conn->query($query);

          if ($columnResult) {
            echo "<h5>Columns:</h5><ul>";
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
          $query = "SELECT * FROM $table";
          $dataResult = $conn->query($query);

          if ($dataResult && $dataResult->num_rows > 0): ?>
            <h5>Data:</h5>
            <div class="table-responsive">
              <table class="table table-bordered table-striped">
                <thead>
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