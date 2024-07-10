<?php
session_start();
include("server.php");

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details from the database
$query = $conn->prepare("
    SELECT u.username, u.user_type, hs.firstname, hs.lastname, hs.Staff_Role
    FROM user u
    LEFT JOIN hospital_staff hs ON u.user_id = hs.User_ID
    WHERE u.user_id = ?
");
$query->bind_param("i", $user_id);  
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $username = $user['username'];
    $user_type = $user['user_type'];
    $firstname = $user['firstname'];
    $lastname = $user['lastname'];
    $role = $user['Staff_Role'];
} else {
    // If user is not found, redirect to login page
    header('Location: login.php');
    exit();
}
$query->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $donorId = $_POST['donor_id'];
    $medicalHistory = $_POST['medical_history'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $age = $_POST['age'];
    $lastUpdate = date('Y-m-d');

    $query = $conn->prepare("INSERT INTO medical_records (Donor_ID, Medical_History, Height, Weight, Age, Last_Update) VALUES (?, ?, ?, ?, ?, ?)");
    $query->bind_param("issdis", $donorId, $medicalHistory, $height, $weight, $age, $lastUpdate);

    if ($query->execute()) {
        $_SESSION['success_message'] = "Medical record added successfully.";
    } else {
        $_SESSION['error_message'] = "Error adding medical record.";
    }

    $query->close();
    header('Location: add_medical_record.php?donor_id=' . $donorId);
    exit();
}

// Fetch donor ID from GET request
$donorId = isset($_GET['donor_id']) ? intval($_GET['donor_id']) : 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Record</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Add Medical Record</h2>
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); ?></div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    <form action="add_medical_record.php" method="post">
        <input type="hidden" name="donor_id" value="<?php echo htmlspecialchars($donorId); ?>">
        <div class="form-group">
            <label for="medical_history">Medical History</label>
            <textarea class="form-control" id="medical_history" name="medical_history" rows="5" required></textarea>
        </div>
        <div class="form-group">
            <label for="height">Height (cm)</label>
            <input type="number" class="form-control" id="height" name="height" required>
        </div>
        <div class="form-group">
            <label for="weight">Weight (kg)</label>
            <input type="number" class="form-control" id="weight" name="weight" required>
        </div>
        <div class="form-group">
            <label for="age">Age</label>
            <input type="number" class="form-control" id="age" name="age" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Medical Record</button>
        <button type="button" class="btn btn-secondary" onclick="window.print()">Print</button>
    </form>
</div>
</body>
</html>
