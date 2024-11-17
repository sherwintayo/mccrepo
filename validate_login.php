<?php
session_start();
header('Content-Type: application/json');

$response = ['is_logged_in' => false];
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
  $response['is_logged_in'] = true;
}

echo json_encode($response);
