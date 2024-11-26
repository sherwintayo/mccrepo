<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Include ClamAV for scanning
  function scanFileWithClamAV($filePath)
  {
    $cmd = "clamscan --stdout --no-summary " . escapeshellarg($filePath);
    $output = [];
    $returnVar = 0;
    exec($cmd, $output, $returnVar);
    return $returnVar === 0; // Return true if file is clean
  }

  $allowedTypes = ['image/png', 'image/jpeg', 'application/pdf', 'application/zip', 'text/plain'];

  foreach ($_FILES as $file) {
    $fileTmp = $file['tmp_name'];
    $fileType = mime_content_type($fileTmp);

    // Validate file type
    if (!in_array($fileType, $allowedTypes)) {
      echo json_encode([
        'status' => 'malicious',
        'msg' => "File type {$fileType} is not allowed."
      ]);
      exit;
    }

    // Scan file with ClamAV
    if (!scanFileWithClamAV($fileTmp)) {
      echo json_encode([
        'status' => 'malicious',
        'msg' => "{$file['name']} contains a virus or malicious code."
      ]);
      exit;
    }
  }

  echo json_encode([
    'status' => 'clean',
    'msg' => 'All files are clean.'
  ]);
  exit;
}
?>