<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
  // Redirect to login if not logged in
  header("Location: login.php");
  exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("Invalid request.");
}

// Fetch file paths based on the `id`
$id = (int) $_GET['id'];
require_once 'config.php';  // Include database connection

$stmt = $conn->prepare("SELECT document_path, folder_path, sql_path FROM archive_list WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if (!$result) {
  die("Files not found.");
}

// Define paths
$documentPath = $result['document_path'];
$projectPath = $result['folder_path'];
$sqlPath = $result['sql_path'];

// Use PHP's ZipArchive to bundle files
$zip = new ZipArchive();
$zipFilename = "All_Files.zip";

if ($zip->open($zipFilename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
  exit("Cannot open <$zipFilename>\n");
}

if ($documentPath)
  $zip->addFile($documentPath, basename($documentPath));
if ($projectPath)
  $zip->addFile($projectPath, basename($projectPath));
if ($sqlPath)
  $zip->addFile($sqlPath, basename($sqlPath));

$zip->close();

// Download the zip file
header('Content-Type: application/zip');
header('Content-disposition: attachment; filename=' . $zipFilename);
header('Content-Length: ' . filesize($zipFilename));
readfile($zipFilename);

// Clean up
unlink($zipFilename);
?>