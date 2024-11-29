<?php
require_once('../config.php'); // Include the config file

if (!isset($conn)) {
  die("Database connection failed.");
}

// Function to generate database backup
function backup_database($conn, $database_name)
{
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
    $safeTable = $conn->real_escape_string($table);

    // Table structure
    $query = "SHOW CREATE TABLE `$safeTable`";
    $result = $conn->query($query);

    if (!$result) {
      die("Error fetching table structure for $safeTable: " . $conn->error);
    }

    $row = $result->fetch_row();
    $sqlScript .= "\n\n" . $row[1] . ";\n\n";

    // Table data
    $query = "SELECT * FROM `$safeTable`";
    $result = $conn->query($query);

    if (!$result) {
      die("Error fetching data for $safeTable: " . $conn->error);
    }

    while ($row = $result->fetch_assoc()) {
      $columns = array_map(fn($val) => isset ($val) ? '"' . $conn->real_escape_string($val) . '"' : 'NULL', $row);
      $sqlScript .= "INSERT INTO `$safeTable` VALUES(" . implode(", ", $columns) . ");\n";
    }
    $sqlScript .= "\n";
  }

  return $sqlScript;
}

// Generate and download backup
$database_name = DB_NAME;
$sqlScript = backup_database($conn, $database_name);

if (!empty($sqlScript)) {
  $backup_file_name = $database_name . '_backup_' . date('Y-m-d_H-i-s') . '.sql';

  // Set headers for download
  header('Content-Description: File Transfer');
  header('Content-Type: application/octet-stream');
  header('Content-Disposition: attachment; filename="' . $backup_file_name . '"');
  header('Content-Transfer-Encoding: binary');
  header('Expires: 0');
  header('Cache-Control: must-revalidate');
  header('Pragma: public');
  header('X-Content-Type-Options: nosniff');

  echo $sqlScript;
  exit;
} else {
  echo "No data available to back up.";
}
?>