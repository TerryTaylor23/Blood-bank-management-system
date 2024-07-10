<?php
// Start the session
session_start();

// Include database connection
$con = new mysqli("localhost", "root", "", "blood_bank");

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Initialize variables
$login_success = false;
$login_error = '';
$next_donation_date = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = md5($_POST["password"]);  // Hash the password using md5

    // Prepare and bind
    $stmt = $con->prepare("SELECT * FROM user WHERE username = ? AND password = ? LIMIT 1");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION["uname"] = $user["username"];
        $_SESSION["user_id"] = $user["user_id"];  // Corrected session variable name
        $_SESSION["user_type"] = $user["user_type"];
        $login_success = true;

        // If user is a donor, get the last donation date and calculate the next donation date
        if ($user["user_type"] === "donor") {
            $donor_id = $user["user_id"];
            $donor_query = $con->prepare("SELECT last_donation_date FROM donor WHERE Donor_ID = ? LIMIT 1");
            $donor_query->bind_param("i", $donor_id);
            $donor_query->execute();
            $donor_result = $donor_query->get_result();
            if ($donor_result->num_rows == 1) {
                $donor_data = $donor_result->fetch_assoc();
                $last_donation_date = $donor_data["last_donation_date"];
                $next_donation_date = date('Y-m-d', strtotime($last_donation_date . ' + 56 days'));
                $_SESSION["next_donation_date"] = $next_donation_date;
            }
            $donor_query->close();
        }
    } else {
        $login_error = 'Wrong username or password';
    }

    $stmt->close();
    $con->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link href="assets/img/micon.png" rel="icon">
  <link href="assets/img/micon.png" rel="apple-touch-icon">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Health-Connect</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="login.css">
</head>
<body>
  <div class="container">
    <div class="login-box">
      <h1>Login</h1>
      <form id="loginForm" action="login.php" method="post">
        <div class="form-group">
          <label for="username">Username:</label>
          <input type="text" id="username" name="username" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="password">Password:</label>
          <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <input type="submit" name="login" class="btn btn-primary" value="Login">
      </form>
    </div>
  </div>

  <script>
    $(document).ready(function() {
      <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
        <?php if ($login_success): ?>
          <?php
            $fname = $_SESSION["uname"];
            $user_type = $_SESSION["user_type"];
            switch ($user_type) {
                case "donor":
                    $dashboard = 'donor_dashboard.php';
                    break;
                case "admin":
                    $dashboard = 'admin_dashboard.php';
                    break;
                case "hospital":
                    $dashboard = 'hospital_dashboard.php';
                    break;
                default:
                    $dashboard = 'login.php';
            }
          ?>

          <?php if ($user_type === "donor" && isset($_SESSION["next_donation_date"])): ?>
            Swal.fire({
              title: 'Welcome!',
              text: 'Hello <?php echo $fname; ?>. Your next donation date is <?php echo $_SESSION["next_donation_date"]; ?>',
              icon: 'success'
            }).then(function() {
              window.location.href = '<?php echo $dashboard; ?>';
            });
          <?php else: ?>
            Swal.fire({
              title: 'Welcome!',
              text: 'Hello <?php echo $fname; ?>',
              icon: 'success'
            }).then(function() {
              window.location.href = '<?php echo $dashboard; ?>';
            });
          <?php endif; ?>

        <?php else: ?>
          Swal.fire({
            title: 'Error',
            text: '<?php echo $login_error; ?>',
            icon: 'error'
          });
        <?php endif; ?>
      <?php endif; ?>
    });
  </script>
</body>
</html>
