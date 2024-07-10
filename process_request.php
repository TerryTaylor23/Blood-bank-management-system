<?php
session_start();

// Database connection
$con = new mysqli("localhost", "root", "", "blood_bank");

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Check if user_id is set in session
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = 'User ID not found. Please log in again.';
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch hospital_id using user_id from hospital_staff table
$query = "
    SELECT hs.hospital_id
    FROM user u
    JOIN hospital_staff hs ON u.user_id = hs.User_ID
    WHERE u.user_id = ?
";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $hospitalId = $row['hospital_id'];
} else {
    $_SESSION['error_message'] = 'Hospital not found for the user.';
    header('Location: login.php');
    exit();
}

// Get form data
$bloodType = $_POST['bloodType'];
$rhFactor = $_POST['rhFactor'];
$quantityNeeded = $_POST['quantity'];
$requestDate = $_POST['requestDate'];

// Check availability
$query = "
    SELECT bag_id
    FROM blood_bag
    WHERE Blood_Type = ? AND Rh_Factor = ? AND Status = 'Available'
    LIMIT ?
";
$stmt = $con->prepare($query);
$stmt->bind_param("ssi", $bloodType, $rhFactor, $quantityNeeded);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows >= $quantityNeeded) {
    // Update the database to reserve the blood bags
    $query = "
        UPDATE blood_bag
        SET Status = 'Reserved'
        WHERE bag_id IN (
            SELECT bag_id
            FROM (
                SELECT bag_id
                FROM blood_bag
                WHERE Blood_Type = ? AND Rh_Factor = ? AND Status = 'Available'
                LIMIT ?
            ) as subquery
        )
    ";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ssi", $bloodType, $rhFactor, $quantityNeeded);
    $stmt->execute();

    // Insert the request into the blood_request table
    $query = "
        INSERT INTO blood_request (Hospital_ID, Blood_Type, Rh_Factor, Quantity, Request_Date, Request_Status_ID)
        VALUES (?, ?, ?, ?, ?, ?)
    ";
    $requestStatusId = 1;  // Assuming 1 means 'Pending' or similar
    $stmt = $con->prepare($query);
    $stmt->bind_param("issisi", $hospitalId, $bloodType, $rhFactor, $quantityNeeded, $requestDate, $requestStatusId);
    $stmt->execute();

    $_SESSION['success_message'] = 'Blood request processed successfully.';
} else {
    $_SESSION['error_message'] = 'Insufficient blood available for the requested type and Rh factor.';
}

$stmt->close();
$con->close();

header('Location: request.php');
exit();
?>
