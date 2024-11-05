<?php
session_start();
require_once('config.php');

$response = ['status' => 'error', 'message' => 'Invalid request'];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'User not logged in or session expired.';
    echo json_encode($response);
    exit;
}

if (isset($_POST['fileId'], $_POST['reason']) && !empty($_POST['reason'])) {
    $fileId = intval($_POST['fileId']);
    $reason = trim($_POST['reason']);
    $userId = $_SESSION['user_id'];

    try {
        // Insert download request into download_requests table
        $stmt = $conn->prepare("INSERT INTO download_requests (user_id, file_id, reason, status, requested_at) VALUES (?, ?, ?, 'pending', NOW())");
        $stmt->bind_param("iis", $userId, $fileId, $reason);

        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Request submitted successfully.'];
        } else {
            $response['message'] = 'Failed to submit the request. Please try again later.';
        }
    } catch (Exception $e) {
        $response['message'] = 'An error occurred: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid input or user not logged in.';
}

header('Content-Type: application/json');
echo json_encode($response);
?>