<?php
include('server.php');

if (isset($_GET['Donor_ID'])) {
    $donorID = intval($_GET['Donor_ID']);
    $query = "SELECT Donation_Date FROM donor WHERE Donor_ID = $donorID";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['donation_date' => $row['Donation_Date']]);
    } else {
        echo json_encode(['donation_date' => '']);
    }
} else {
    echo json_encode(['donation_date' => '']);
}
?>
