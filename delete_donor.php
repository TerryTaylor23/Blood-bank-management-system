<?php
include('server.php'); // Ensure this file establishes the $conn connection

$response = array('success' => false);

if (isset($_POST['id'])) {
    $donorId = intval($_POST['id']);

    // Delete the donor record from the donor table
    $stmt = $conn->prepare("DELETE FROM donor WHERE Donor_ID = ?");
    if ($stmt === false) {
        error_log('Prepare failed: ' . htmlspecialchars($conn->error));
    } else {
        $stmt->bind_param("i", $donorId);
        $stmt->execute();

        // Check if deletion was successful
        if ($stmt->affected_rows > 0) {
            // Delete the user record from the user table
            $stmt = $conn->prepare("DELETE FROM user WHERE ID = ?");
            if ($stmt === false) {
                error_log('Prepare failed: ' . htmlspecialchars($conn->error));
            } else {
                $stmt->bind_param("i", $donorId);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    $response['success'] = true;
                } else {
                    error_log('User deletion failed: ' . htmlspecialchars($stmt->error));
                }
            }
        } else {
            error_log('Donor deletion failed: ' . htmlspecialchars($stmt->error));
        }

        $stmt->close();
    }
} else {
    error_log('ID not set in POST request.');
}

$conn->close();
echo json_encode($response);
?>
