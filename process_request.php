<?php
include('server.php');

// Start session to access session variables
session_start();

// Retrieve form data
$bloodType = $_POST['bloodType'];
$rhFactor = $_POST['rhFactor'];
$quantity = $_POST['quantity'];
$requestDate = $_POST['requestDate'];

function getAvailableBloodBags($conn, $bloodType, $quantity) {
    $stmt = $conn->prepare("SELECT * FROM blood_bag WHERE Blood_Type = ? AND Status = 'Available' LIMIT ?");
    $stmt->bind_param("si", $bloodType, $quantity);
    $stmt->execute();
    $result = $stmt->get_result();
    $bags = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $bags;
}

function updateBloodBagStatus($conn, $bagId, $status) {
    $stmt = $conn->prepare("UPDATE blood_bag SET Status = ? WHERE Bag_ID = ?");
    $stmt->bind_param("si", $status, $bagId);
    $stmt->execute();
    $stmt->close();
}

function insertBloodRequest($conn, $bloodType, $rhFactor, $quantity, $requestDate) {
    $stmt = $conn->prepare("INSERT INTO blood_request (Blood_Type, Rh_Factor, Quantity, Request_Date, Request_Status_ID) VALUES (?, ?, ?, ?, ?)");
    $requestStatusId = 1; // Assuming 1 is the ID for 'Pending' status
    $stmt->bind_param("ssisi", $bloodType, $rhFactor, $quantity, $requestDate, $requestStatusId);
    $stmt->execute();
    $stmt->close();
}

function isValidExpiry($expiryDate) {
    $currentDate = date("Y-m-d");
    return $expiryDate > $currentDate;
}

function isCompatible($requested, $available) {
    $compatibleBloodTypes = [
        'A+' => ['A+', 'A-', 'O+', 'O-'],
        'A-' => ['A-', 'O-'],
        'B+' => ['B+', 'B-', 'O+', 'O-'],
        'B-' => ['B-', 'O-'],
        'AB+' => ['AB+', 'AB-', 'A+', 'A-', 'B+', 'B-', 'O+', 'O-'],
        'AB-' => ['AB-', 'A-', 'B-', 'O-'],
        'O+' => ['O+', 'O-'],
        'O-' => ['O-']
    ];
    
    $requestedType = $requested['type'] . $requested['factor'];
    $availableType = $available['type'] . $available['factor'];

    return in_array($availableType, $compatibleBloodTypes[$requestedType]);
}

// Retrieve available blood bags
$availableBags = getAvailableBloodBags($conn, $bloodType, $quantity);
$requestSent = false;

foreach ($availableBags as $bag) {
    if (!isValidExpiry($bag['Expiry_Date'])) {
        continue; // Skip if the bag has expired
    }
    
    if (isCompatible(['type' => $bloodType, 'factor' => $rhFactor], ['type' => $bag['Blood_Type'], 'factor' => $bag['Rh_Factor']])) {
        // Mark the bag as reserved
        updateBloodBagStatus($conn, $bag['Bag_ID'], 'Reserved');
        
        // Insert the request into the blood_request table
        insertBloodRequest($conn, $bloodType, $rhFactor, $quantity, $requestDate);
        
        $requestSent = true;
        break; // Stop after reserving the first compatible bag
    }
}

$conn->close();

if ($requestSent) {
    // Set success message in the session
    $_SESSION['success_message'] = "Request sent successfully!";
    
    // Redirect to the request page
    header('Location: request.php');
    exit();
} else {
    // Handle the case where no compatible blood bags were found
    $_SESSION['error_message'] = "No compatible blood bags found!";
    header('Location: request.php');
    exit();
}
?>
