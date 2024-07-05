<?php
include('server.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars($_POST["email"]);
    $username = htmlspecialchars($_POST["username"]);

    $response = isUnique($conn, $email, $username);
    echo $response;

    $conn->close();
}
?>
