<?php
session_start();

// Database connection
$con = new mysqli("localhost", "root", "", "blood_bank");

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $renewPassword = $_POST['renewPassword'];

    // Fetch current password from the database
    $query = $con->prepare("SELECT password FROM user WHERE user_id = ?");
    $query->bind_param("i", $user_id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $hashedCurrentPassword = md5($currentPassword);

        // Check if current password matches
        if ($hashedCurrentPassword == $user['password']) {
            // Check if new passwords match
            if ($newPassword == $renewPassword) {
                $hashedNewPassword = md5($newPassword);

                // Update password in the database
                $updateQuery = $con->prepare("UPDATE user SET password = ? WHERE user_id = ?");
                $updateQuery->bind_param("si", $hashedNewPassword, $user_id);
                if ($updateQuery->execute()) {
                    echo "Password changed successfully.";
                } else {
                    echo "Error updating password.";
                }
                $updateQuery->close();
            } else {
                echo "New passwords do not match.";
            }
        } else {
            echo "Current password is incorrect.";
        }
    } else {
        echo "User not found.";
    }
    $query->close();
}
$con->close();
?>
