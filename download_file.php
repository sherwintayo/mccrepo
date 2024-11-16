<?php
session_start();
require 'config.php';

$student_id = $_SESSION['user_id'] ?? 0;
$download_id = intval($_GET['id'] ?? 0);

if ($student_id && $download_id > 0) {
  $query = $conn->prepare("
        SELECT al.document_path, al.folder_path, al.sql_path 
        FROM download_requests dr 
        JOIN archive_list al ON dr.file_id = al.id 
        WHERE dr.id = ? AND dr.user_id = ? AND dr.status = 'approved'
    ");
  $query->bind_param("ii", $download_id, $student_id);
  $query->execute();
  $result = $query->get_result();
  $file = $result->fetch_assoc();

  if ($file) {
    // Create a ZIP archive
    $zip = new ZipArchive();
    $zipFileName = tempnam(sys_get_temp_dir(), "zip") . ".zip";

    if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
      if (file_exists($file['document_path']))
        $zip->addFile($file['document_path'], 'Document_File.zip');
      if (file_exists($file['folder_path']))
        $zip->addFile($file['folder_path'], 'Project_File.zip');
      if (file_exists($file['sql_path']))
        $zip->addFile($file['sql_path'], 'SQL_File.zip');
      $zip->close();

      // Serve the ZIP file for download
      header('Content-Type: application/zip');
      header('Content-Disposition: attachment; filename="All_Files.zip"');
      header('Content-Length: ' . filesize($zipFileName));
      readfile($zipFileName);
      unlink($zipFileName); // Clean up temporary file
      exit;
    }
  }
}

http_response_code(404);
echo "File not found or unauthorized access.";
exit;
?>