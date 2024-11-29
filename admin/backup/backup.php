<?php
require_once('../config.php'); // Include the config file

if (!isset($conn)) {
  die("Database connection failed.");
}

// Set headers to trigger file download
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="database_backup.sql"');

$tables = array();
$query = "SHOW TABLES";
$result = $conn->query($query);

if ($result) {
  // Fetch all table names
  while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
  }
}

// Begin the SQL dump
$output = "-- Database Backup\n-- Generated on " . date('Y-m-d H:i:s') . "\n\n";

foreach ($tables as $table) {
  // Generate DROP TABLE statement
  $output .= "DROP TABLE IF EXISTS `$table`;\n";

  // Generate CREATE TABLE statement
  $createTableResult = $conn->query("SHOW CREATE TABLE `$table`");
  if ($createTableResult) {
    $createTableRow = $createTableResult->fetch_row();
    $output .= $createTableRow[1] . ";\n\n";
  }

  // Generate INSERT statements for table data
  $dataResult = $conn->query("SELECT * FROM `$table`");
  if ($dataResult && $dataResult->num_rows > 0) {
    while ($row = $dataResult->fetch_assoc()) {
      $columns = array_keys($row);
      $values = array_map(function ($value) use ($conn) {
        return is_null($value) ? 'NULL' : "'" . $conn->real_escape_string($value) . "'";
      }, array_values($row));

      $output .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
    }
    $output .= "\n";
  }
}

// Output the SQL dump
echo $output;
exit;
?>