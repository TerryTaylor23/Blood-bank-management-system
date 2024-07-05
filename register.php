<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <link rel="stylesheet" href="main.css">
</head>
<body>
  <div class="container">
    <div class="register-box">
      <h1>Register</h1>
      <form action="register.php" method="post">
        <div class="textbox">
          <label for="firstname">First Name:</label>
          <input type="text" id="firstname" name="firstname" required>
        </div>

        <div class="textbox">
          <label for="lastname">Last Name:</label>
          <input type="text" id="lastname" name="lastname" required>
        </div>

        <div class="textbox">
          <label for="username">Username:</label>
          <input type="text" id="username" name="username" required>
        </div>
        <div class="textbox">
          <label for="sex">Sex:</label>
          <input type="radio" id="male" name="sex" value="Male" required>
          <label for="male">Male</label> 
          <input type="radio" id="female" name="sex" value="Female" required>
          <label for="female">Female</label>
        </div>

        <div class="textbox">
          <label for="phone">Phone Number:</label>
          <input type="tel" maxlength="10"  id="phone" name="phone" required>
        </div>

        <div class="textbox">
          <label for="password">Password:</label>
          <input type="password" id="password" name="password" required>
        </div>

        <div class="textbox">
          <label for="confirm_password">Confirm Password:</label>
          <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <div class="textbox">
          <label for="user_type">User Type:</label>
          <select id="user_type" name="user_type" required>
            <option value="donor">Donor</option>
            <option value="hospital">Hospital</option>
            <option value="admin">Admin</option>
          </select>
        </div>

        <input type="submit" name="register" class="btn" value="Register">
        <div class="login-link">
          <span>Already have an account? <a href="login.php">Log in here</a></span>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
<?php
// Configuration
$db_host = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'blood_bank';

// Create connection
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: ". $conn->connect_error);
}

// Register user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $firstname = $conn->real_escape_string($_POST['firstname']);
    $lastname = $conn->real_escape_string($_POST['lastname']);
    $sex = $conn->real_escape_string($_POST['sex']);
    $phone = $conn->real_escape_string($_POST['phone']); 
    $password = $conn->real_escape_string($_POST['password']);
    $user_type = $conn->real_escape_string($_POST['user_type']);
    
    // Check if phone number already exists
    $check_phone_query = "SELECT * FROM user WHERE phone = '$phone'";
    $check_phone_result = $conn->query($check_phone_query);
    
    if ($check_phone_result->num_rows > 0) {
        echo "Error: Phone number already registered";
    } else {
        // Proceed with registration...
        // Hash the password using md5
        $hashed_password = md5($password);
        
        // Insert new user into database
        $insert_query = "INSERT INTO user (username, firstname, lastname, sex, phonenumber, password, user_type) VALUES ('$username', '$firstname', '$lastname', '$sex', '$phone', '$hashed_password', '$user_type')";
        
        if ($conn->query($insert_query)) {
            header('Location: login.php');
        } else {
            echo "Error: ". $insert_query. "<br>". $conn->error;
        }
    }
}

// Close connection
$conn->close();
?>
