<?php
error_reporting(0);
include 'backup_function.php';

if (isset($_POST['backupnow'])) {
  // Validate the POST parameters
  if (!empty($_POST['server']) && !empty($_POST['username']) && !empty($_POST['dbname'])) {
    $server = $_POST['server'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $dbname = $_POST['dbname'];

    // Attempt to backup the database
    try {
      backDb($server, $username, $password, $dbname);
    } catch (Exception $e) {
      echo "An error occurred while backing up the database: " . $e->getMessage();
    }
  } else {
    echo "Please provide all the required fields (Server, Username, and Database Name).";
  }
} else {
  echo "Invalid request. Please use the form to submit the database details.";
}
