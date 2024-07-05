<?php
include('server.php');

$sql = "SELECT firstname, lastname, email, Blood_Type, Rh_Factor, User_ID FROM donor";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['firstname']}</td>
                <td>{$row['lastname']}</td>
                <td>{$row['email']}</td>
                <td>{$row['Blood_Type']}</td>
                <td>{$row['Rh_Factor']}</td>
                <td><button class='btn btn-danger btn-sm' onclick='deleteDonor({$row['User_ID']})'>Delete</button></td>
            </tr>";
    }
} else {
    echo "<tr><td colspan='6'>No donors found</td></tr>";
}

$conn->close();
?>
