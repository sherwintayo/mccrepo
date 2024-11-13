<?php
require_once 'config.php';

$response = ['status' => 'error', 'message' => 'Request could not be processed'];

if (isset($_POST['fileId'], $_POST['reason'], $_POST['fileType']) && !empty($_SESSION['user_id'])) {
    $fileId = intval($_POST['fileId']);
    $reason = $conn->real_escape_string($_POST['reason']);
    $fileType = $conn->real_escape_string($_POST['fileType']);
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO download_requests (user_id, file_id, reason, status, status_read) VALUES (?, ?, ?, 'pending', 'unread')");
    $stmt->bind_param("iis", $user_id, $fileId, $reason);
    if ($stmt->execute()) {
        $response = ['status' => 'success'];
    } else {
        $response['message'] = 'Failed to submit request.';
    }
    $stmt->close();
}

echo json_encode($response);
?>