<?php
session_start();
require_once('./config.php');

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
  header("Location: login.php"); // Redirect to login if not authenticated
  exit;
}

// Check if file ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "Invalid file request.";
  exit;
}

// Fetch file details from database
$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM archive_list WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$file = $stmt->get_result()->fetch_assoc();

if (!$file) {
  echo "File not found.";
  exit;
}

// Define paths to files
$files = [
  'document' => $file['document_path'],
  'project' => $file['folder_path'],
  'sql' => $file['sql_path']
];

// Serve files as a ZIP archive
$zip = new ZipArchive();
$zipFileName = "Archive_Files.zip";

if ($zip->open($zipFileName, ZipArchive::CREATE) === TRUE) {
  foreach ($files as $type => $filePath) {
    if (file_exists($filePath)) {
      $zip->addFile($filePath, basename($filePath));
    }
  }
  $zip->close();

  header('Content-Type: application/zip');
  header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
  header('Content-Length: ' . filesize($zipFileName));

  readfile($zipFileName);
  unlink($zipFileName); // Delete the ZIP file after download
  exit;
} else {
  echo "Failed to create ZIP file.";
  exit;
}
?>