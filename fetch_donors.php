<?php
// Database connection
$con = new mysqli("localhost", "root", "", "blood_bank");

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

$query = "
    SELECT d.Donor_ID, d.firstname, d.lastname, d.email, b.Blood_Type, b.Rh_Factor
    FROM donor d
    JOIN blood_bag b ON d.Donor_ID = b.Donor_ID
";
$result = $con->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['firstname']) . "</td>";
        echo "<td>" . htmlspecialchars($row['lastname']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Blood_Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Rh_Factor']) . "</td>";
        echo "<td>";
        echo "<button class='btn btn-danger' onclick='deleteDonor(" . $row['Donor_ID'] . ")'>Delete</button>";
        echo " ";
        echo "<a href='add_medical_record.php?donor_id=" . $row['Donor_ID'] . "' class='btn btn-primary'><i class='bi bi-database-add'></i></a>";
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No donors found.</td></tr>";
}

$con->close();
?>
