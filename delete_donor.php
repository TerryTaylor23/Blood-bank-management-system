<?php
include('server.php');

$response = array('success' => false);

if (isset($_POST['id'])) {
    $donorId = intval($_POST['id']);

    // Delete the donor record from the donor table
    $stmt = $conn->prepare("DELETE FROM donor WHERE User_ID = ?");
    $stmt->bind_param("i", $donorId);
    $stmt->execute();

    // Check if deletion was successful
    if ($stmt->affected_rows > 0) {
        // Delete the user record from the user table
        $stmt = $conn->prepare("DELETE FROM user WHERE ID = ?");
        $stmt->bind_param("i", $donorId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $response['success'] = true;
        }
    }

    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>
