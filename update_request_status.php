<?php
session_start();

// Database connection
$con = new mysqli("localhost", "root", "", "blood_bank");

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = 'User ID not found. Please log in again.';
    header('Location: login.php');
    exit();
}

// Get form data
$requestId = $_POST['requestId'];
$newStatusId = $_POST['statusId'];

// Update request status
$query = "
    UPDATE blood_request
    SET Request_Status_ID = ?
    WHERE Request_ID = ?
";
$stmt = $con->prepare($query);
$stmt->bind_param("ii", $newStatusId, $requestId);

if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Request status updated successfully.';
} else {
    $_SESSION['error_message'] = 'Failed to update request status.';
}

$stmt->close();
$con->close();

header('Location: view_requests.php'); // Redirect to the page where requests are listed
exit();
?>
