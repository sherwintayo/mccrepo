<?php
require_once 'config.php'; // Adjust path as needed

header('Content-Type: application/json');

// Check if required POST parameters are set
if (isset($_POST['fileId'], $_POST['reason'], $_POST['fileType']) && $_SESSION['user_logged_in']) {
    $fileId = (int) $_POST['fileId'];
    $reason = $conn->real_escape_string($_POST['reason']);
    $fileType = $conn->real_escape_string($_POST['fileType']);
    $userId = $_SESSION['user_id']; // Ensure user is logged in and has a valid user ID

    // Prepare and execute the SQL statement to insert the download request
    $stmt = $conn->prepare("INSERT INTO download_requests (user_id, file_id, reason, status) VALUES (?, ?, ?, 'pending')");
    $stmt->bind_param("iis", $userId, $fileId, $reason);

    if ($stmt->execute()) {
        // If insertion is successful, send a success response
        echo json_encode(['status' => 'success']);
    } else {
        // If insertion fails, send an error response
        echo json_encode(['status' => 'error', 'message' => 'Failed to save request in the database.']);
    }

    $stmt->close();
} else {
    // If required POST parameters are missing or user not logged in, return an error
    echo json_encode(['status' => 'error', 'message' => 'Invalid request or missing parameters.']);
}

$conn->close();
