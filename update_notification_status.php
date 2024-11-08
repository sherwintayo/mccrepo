<?php
require_once 'config.php'; // Adjust this path as needed

// Ensure `id` and `status` are provided in the request
if (isset($_POST['id']) && isset($_POST['status'])) {
    $notification_id = (int) $_POST['id'];
    $status = $_POST['status'] === 'read' ? 'read' : 'unread';

    // Update the notification status
    $stmt = $conn->prepare("UPDATE notifications SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $notification_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update notification status"]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}

$conn->close();
?>