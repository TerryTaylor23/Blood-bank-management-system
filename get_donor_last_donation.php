<?php
include('server.php');

if (isset($_GET['donor_id'])) {
    $donor_id = intval($_GET['donor_id']);
    $query = $conn->prepare("SELECT last_donation_date FROM donor WHERE Donor_ID = ?");
    $query->bind_param("i", $donor_id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['last_donation_date' => $row['last_donation_date']]);
    } else {
        echo json_encode(['last_donation_date' => null]);
    }
    $query->close();
} else {
    echo json_encode(['last_donation_date' => null]);
}

$conn->close();
?>
