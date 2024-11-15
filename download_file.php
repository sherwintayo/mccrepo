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
    // Provide download for the appropriate file
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="All_Files.zip"');
    readfile($file['folder_path']); // Adjust logic for bundling if necessary
    exit;
  }
}

http_response_code(404);
echo "File not found or unauthorized access.";
exit;
?>