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

// Initialize message variable for notifications
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
  $filePath = __DIR__ . '/' . $fileName;
  $command = "mysqldump --host=$dbHost --user=$dbUser --password=$dbPass $dbName > $filePath";
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
    <div class="container mt-4">
        <h1 class="text-center">Database Manager</h1>

        <!-- Notification Message -->
        <?php if (!empty($message)): ?>
              <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>" role="alert">
                  <?php echo htmlspecialchars($message); ?>
              </div>
        <?php endif; ?>

        <div class="text-center mb-4">
            <a href="?download=true" class="btn btn-primary">Download Database as .sql</a>
        </div>

        <!-- Add Table Form -->
        <h3>Add Table</h3>
        <form method="post" class="mb-3">
            <input type="hidden" name="action" value="add_table">
            <div class="mb-3">
                <label>Table Name <small class="text-muted">(Alphanumeric and underscores only)</small></label>
                <input type="text" name="table_name" class="form-control" required pattern="[a-zA-Z_][a-zA-Z0-9_]*" placeholder="e.g., users">
            </div>
            <div class="mb-3">
                <label>Columns <small class="text-muted">(SQL syntax, e.g., id INT PRIMARY KEY)</small></label>
                <textarea name="columns" class="form-control" rows="3" required placeholder="e.g., id INT PRIMARY KEY, name VARCHAR(100)"></textarea>
            </div>
            <button type="submit" class="btn btn-success">Add Table</button>
        </form>

        <!-- Additional forms for other actions... -->

        <!-- Display Tables -->
        <h3>Existing Tables</h3>
        <?php foreach ($tables as $table): ?>
              <div class="card mb-3">
                  <div class="card-header bg-dark text-white">
                      Table: <?php echo htmlspecialchars($table); ?>
                  </div>
                  <div class="card-body">
                      <ul>
                          <?php
                          $query = "SHOW COLUMNS FROM `$table`";
                          $columnResult = $conn->query($query);
                          while ($columnRow = $columnResult->fetch_assoc()) {
                            echo "<li>" . htmlspecialchars($columnRow['Field']) . " (" . htmlspecialchars($columnRow['Type']) . ")</li>";
                          }
                          ?>
                      </ul>
                  </div>
              </div>
        <?php endforeach; ?>
    </div>
</body>

</html>
