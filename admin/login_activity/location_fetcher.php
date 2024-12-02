<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ip'])) {
  $ip = $_POST['ip'];
  $api_key = 'FEF1A8D3324183EDBA2D0BD4F9128A6F'; // Replace with your actual IPinfo.io API key

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'https://api.ip2location.io/?' . http_build_query([
    'ip' => $ip,
    'key' => $api_key,
    'format' => 'json',
  ]));
  curl_setopt($ch, CURLOPT_FAILONERROR, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_TIMEOUT, 10);

  $response = curl_exec($ch);
  curl_close($ch);

  if ($response) {
    $data = json_decode($response, true);

    // Extract necessary details
    $latitude = $data['latitude'] ?? null;
    $longitude = $data['longitude'] ?? null;
    $city = $data['city'] ?? 'Unknown';
    $region = $data['region'] ?? 'Unknown';
    $country = $data['country_name'] ?? 'Unknown';

    echo json_encode([
      'status' => 'success',
      'data' => compact('latitude', 'longitude', 'city', 'region', 'country'),
    ]);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Unable to fetch location details.']);
  }
}
