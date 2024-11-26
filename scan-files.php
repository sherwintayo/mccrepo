<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  function scanFileWithVirusTotal($filePath)
  {
    $apiKey = 'f430e0c1613a705f37dfa211d9e3d74cb95a74768ec443c81c11d63f66fd25da'; // Replace with your VirusTotal API key
    $url = 'https://www.virustotal.com/api/v3/files';

    // Prepare CURL request
    $file = curl_file_create($filePath); // Create a CURL file object
    $data = ['file' => $file];

    $headers = [
      "x-apikey: $apiKey",
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Handle the response
    if ($httpCode === 200) {
      $result = json_decode($response, true);
      if (isset($result['data']['id'])) {
        return [
          'status' => 'success',
          'scan_id' => $result['data']['id'],
        ];
      } else {
        return [
          'status' => 'error',
          'msg' => 'VirusTotal API did not return a scan ID.',
        ];
      }
    } else {
      return [
        'status' => 'error',
        'msg' => 'Failed to connect to VirusTotal API. HTTP Code: ' . $httpCode,
      ];
    }
  }

  function getVirusTotalReport($scanId)
  {
    $apiKey = 'f430e0c1613a705f37dfa211d9e3d74cb95a74768ec443c81c11d63f66fd25da'; // Replace with your VirusTotal API key
    $url = "https://www.virustotal.com/api/v3/analyses/$scanId";

    $headers = [
      "x-apikey: $apiKey",
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
      return json_decode($response, true);
    } else {
      return [
        'status' => 'error',
        'msg' => 'Failed to retrieve scan report. HTTP Code: ' . $httpCode,
      ];
    }
  }

  $allowedTypes = ['image/png', 'image/jpeg', 'application/pdf', 'application/zip', 'text/plain'];

  foreach ($_FILES as $file) {
    $fileTmp = $file['tmp_name'];
    $fileType = mime_content_type($fileTmp);

    // Log for debugging
    error_log("Scanning file: " . $file['name']);
    error_log("Temp path: " . $fileTmp);
    error_log("File MIME Type: " . $fileType);

    // Validate file type
    if (!in_array($fileType, $allowedTypes)) {
      echo json_encode([
        'status' => 'malicious',
        'msg' => "File type {$fileType} is not allowed.",
      ]);
      exit;
    }

    // Scan file with VirusTotal
    $scanResult = scanFileWithVirusTotal($fileTmp);

    if ($scanResult['status'] === 'error') {
      echo json_encode([
        'status' => 'error',
        'msg' => $scanResult['msg'],
      ]);
      exit;
    }

    // Wait for VirusTotal scan result (Polling)
    $scanId = $scanResult['scan_id'];
    sleep(5); // Give some time for VirusTotal to analyze

    $report = getVirusTotalReport($scanId);
    if (isset($report['data']['attributes']['stats'])) {
      $maliciousCount = $report['data']['attributes']['stats']['malicious'];

      if ($maliciousCount > 0) {
        echo json_encode([
          'status' => 'malicious',
          'msg' => "{$file['name']} contains malware detected by {$maliciousCount} antivirus engines.",
        ]);
        exit;
      }
    }
  }

  // If all files are clean
  echo json_encode([
    'status' => 'clean',
    'msg' => 'All files are clean.',
  ]);
  exit;
}
?>