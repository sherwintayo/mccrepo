<?php
require_once('../config.php'); // Load the config and DBConnection
if (!isset($conn)) {
  die("Database connection failed.");
}

// CSRF validation
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid CSRF token.");
  }
}

// Get All Table Names From the Database
$tables = array();
$sql = "SHOW TABLES";
$result = $conn->query($sql);

if (!$result) {
  die("Error fetching tables: " . $conn->error);
}

while ($row = $result->fetch_row()) {
  $tables[] = $row[0];
}

$sqlScript = "";
foreach ($tables as $table) {
  // Table structure
  $query = "SHOW CREATE TABLE `$table`";
  $result = $conn->query($query);
  if (!$result) {
    die("Error fetching table structure: " . $conn->error);
  }
  $row = $result->fetch_row();
  $sqlScript .= "\n\n" . $row[1] . ";\n\n";

  // Table data
  $query = "SELECT * FROM `$table`";
  $result = $conn->query($query);
  if (!$result) {
    die("Error fetching table data: " . $conn->error);
  }

  $columnCount = $result->field_count;
  while ($row = $result->fetch_row()) {
    $sqlScript .= "INSERT INTO `$table` VALUES(";
    for ($j = 0; $j < $columnCount; $j++) {
      $sqlScript .= isset($row[$j]) ? '"' . $conn->real_escape_string($row[$j]) . '"' : 'NULL';
      $sqlScript .= $j < ($columnCount - 1) ? ',' : '';
    }
    $sqlScript .= ");\n";
  }
  $sqlScript .= "\n";
}

// Save and trigger download
if (!empty($sqlScript)) {
  $backupFileName = "database_backup_" . date('Y-m-d_H-i-s') . ".sql";
  header('Content-Type: application/octet-stream');
  header('Content-Disposition: attachment; filename="' . $backupFileName . '"');
  echo $sqlScript;
  exit;
}
?>