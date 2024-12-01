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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  $tableName = $_POST['table_name'] ?? '';
  $columnName = $_POST['column_name'] ?? '';
  $columnType = $_POST['column_type'] ?? '';

  // Add a new table
  if ($action === 'add_table' && !empty($tableName)) {
    $columns = $_POST['columns'] ?? '';
    $query = "CREATE TABLE `$tableName` ($columns)";
    if ($conn->query($query)) {
      echo "Table '$tableName' created successfully.";
    } else {
      echo "Error creating table: " . $conn->error;
    }
  }

  // Delete a table
  if ($action === 'delete_table' && !empty($tableName)) {
    $query = "DROP TABLE IF EXISTS `$tableName`";
    if ($conn->query($query)) {
      echo "Table '$tableName' deleted successfully.";
    } else {
      echo "Error deleting table: " . $conn->error;
    }
  }

  // Add a column
  if ($action === 'add_column' && !empty($tableName) && !empty($columnName) && !empty($columnType)) {
    $query = "ALTER TABLE `$tableName` ADD `$columnName` $columnType";
    if ($conn->query($query)) {
      echo "Column '$columnName' added to table '$tableName' successfully.";
    } else {
      echo "Error adding column: " . $conn->error;
    }
  }

  // Delete a column
  if ($action === 'delete_column' && !empty($tableName) && !empty($columnName)) {
    $query = "ALTER TABLE `$tableName` DROP COLUMN `$columnName`";
    if ($conn->query($query)) {
      echo "Column '$columnName' deleted from table '$tableName' successfully.";
    } else {
      echo "Error deleting column: " . $conn->error;
    }
  }

  // Update a column
  if ($action === 'update_column' && !empty($tableName) && !empty($columnName) && !empty($columnType)) {
    $query = "ALTER TABLE `$tableName` MODIFY `$columnName` $columnType";
    if ($conn->query($query)) {
      echo "Column '$columnName' updated successfully in table '$tableName'.";
    } else {
      echo "Error updating column: " . $conn->error;
    }
  }
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
    <h1 class="text-center">Database Tables Management</h1>
    <hr>

    <!-- Add Table Form -->
    <h2>Add a New Table</h2>
    <form method="post">
      <input type="hidden" name="action" value="add_table">
      <label>Table Name: <input type="text" name="table_name" required></label><br>
      <label>Columns (e.g., `id INT PRIMARY KEY, name VARCHAR(100)`):</label><br>
      <textarea name="columns" rows="3" cols="50" required></textarea><br>
      <button type="submit">Add Table</button>
    </form>

    <!-- Delete Table Form -->
    <h2>Delete a Table</h2>
    <form method="post">
      <input type="hidden" name="action" value="delete_table">
      <label>Table Name: <input type="text" name="table_name" required></label><br>
      <button type="submit">Delete Table</button>
    </form>

    <!-- Add Column Form -->
    <h2>Add a Column to a Table</h2>
    <form method="post">
      <input type="hidden" name="action" value="add_column">
      <label>Table Name: <input type="text" name="table_name" required></label><br>
      <label>Column Name: <input type="text" name="column_name" required></label><br>
      <label>Column Type (e.g., `VARCHAR(100)`): <input type="text" name="column_type" required></label><br>
      <button type="submit">Add Column</button>
    </form>

    <!-- Update Column Form -->
    <h2>Update a Column in a Table</h2>
    <form method="post">
      <input type="hidden" name="action" value="update_column">
      <label>Table Name: <input type="text" name="table_name" required></label><br>
      <label>Column Name: <input type="text" name="column_name" required></label><br>
      <label>New Column Type (e.g., `INT NOT NULL`): <input type="text" name="column_type" required></label><br>
      <button type="submit">Update Column</button>
    </form>

    <!-- Delete Column Form -->
    <h2>Delete a Column from a Table</h2>
    <form method="post">
      <input type="hidden" name="action" value="delete_column">
      <label>Table Name: <input type="text" name="table_name" required></label><br>
      <label>Column Name: <input type="text" name="column_name" required></label><br>
      <button type="submit">Delete Column</button>
    </form>

    <hr>

    <!-- Display Tables and Columns -->
    <h2>Existing Tables</h2>
    <?php foreach ($tables as $table): ?>
      <div>
        <h3>Table: <?php echo htmlspecialchars($table); ?></h3>
        <?php
        $query = "SHOW COLUMNS FROM `$table`";
        $columnResult = $conn->query($query);

        if ($columnResult) {
          echo "<ul>";
          while ($columnRow = $columnResult->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($columnRow['Field']) . " (" . htmlspecialchars($columnRow['Type']) . ")</li>";
          }
          echo "</ul>";
        } else {
          echo "<p>Error fetching columns: " . $conn->error . "</p>";
        }
        ?>
      </div>
    <?php endforeach; ?>
  </div>
</body>

</html>